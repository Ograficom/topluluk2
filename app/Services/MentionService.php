<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\UserMentionedNotification;
use DOMDocument;
use DOMNode;
use DOMText;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MentionService
{
    private const USERNAME_PATTERN = '/(?<![A-Za-z0-9._%+\-])@([A-Za-z0-9._-]{2,255})/u';

    /** @var array<string, \App\Models\User|null> */
    private static array $userCache = [];

    public function extractUsernames(?string $text): Collection
    {
        $text = $this->normalizeTextForExtraction($text);
        if ($text === '') {
            return collect();
        }

        preg_match_all(self::USERNAME_PATTERN, $text, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($username) => Str::lower(trim((string) $username)))
            ->filter()
            ->unique()
            ->values();
    }

    public function linkifyPlainText(?string $text, bool $convertNewlines = true): string
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        $users = $this->resolveUsers($this->extractUsernames($text))
            ->keyBy(fn (User $user) => Str::lower((string) $user->username));

        preg_match_all(self::USERNAME_PATTERN, $text, $matches, PREG_OFFSET_CAPTURE);

        $html = '';
        $cursor = 0;

        foreach ($matches[0] ?? [] as $index => $match) {
            $fullMatch = (string) ($match[0] ?? '');
            $offset = (int) ($match[1] ?? 0);
            $username = Str::lower((string) (($matches[1][$index][0] ?? '')));

            if ($offset < $cursor) {
                continue;
            }

            $html .= e(substr($text, $cursor, $offset - $cursor));
            $cursor = $offset + strlen($fullMatch);

            /** @var \App\Models\User|null $user */
            $user = $users->get($username);
            if (!$user || blank($user->username)) {
                $html .= e($fullMatch);
                continue;
            }

            $html .= $this->mentionAnchorHtml($user);
        }

        $html .= e(substr($text, $cursor));

        return $convertNewlines ? nl2br($html) : $html;
    }

    public function linkifyHtml(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapperId = '__mention_root__';

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="' . $wrapperId . '">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $dom->getElementById($wrapperId);
        if (!$root) {
            return $html;
        }

        $xpath = new DOMXPath($dom);
        $textNodes = [];

        foreach ($xpath->query('.//text()', $root) ?: [] as $node) {
            if (!$node instanceof DOMText || $this->shouldSkipTextNode($node)) {
                continue;
            }

            $textNodes[] = $node;
        }

        foreach ($textNodes as $textNode) {
            $rendered = $this->linkifyPlainText($textNode->nodeValue, false);
            if ($rendered === e($textNode->nodeValue ?? '')) {
                continue;
            }

            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($rendered);
            $textNode->parentNode?->replaceChild($fragment, $textNode);
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }

    public function notifyPostMentions(Post $post, ?string $previousContent = null): void
    {
        if (!$post->isPublishedNow()) {
            return;
        }

        $post->loadMissing('author:id,name,username');
        $actor = $post->author;
        if (!$actor) {
            return;
        }

        $current = $this->extractUsernames($post->content);
        $previous = $previousContent !== null ? $this->extractUsernames($previousContent) : collect();
        $this->dispatchMentionNotifications($current->diff($previous), $actor, $post);
    }

    public function notifyCommentMentions(Comment $comment, ?string $previousContent = null): void
    {
        $comment->loadMissing([
            'user:id,name,username',
            'post:id,title,slug,author_id',
        ]);

        $actor = $comment->user;
        if (!$actor) {
            return;
        }

        $current = $this->extractUsernames($comment->content);
        $previous = $previousContent !== null ? $this->extractUsernames($previousContent) : collect();
        $this->dispatchMentionNotifications($current->diff($previous), $actor, $comment);
    }

    public function searchUsers(string $query, int $limit = 8): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return collect();
        }

        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
        $prefix = str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';

        return User::query()
            ->select(['id', 'name', 'username', 'profile_photo_path'])
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->where(function ($builder) use ($like) {
                $builder
                    ->where('username', 'like', $like)
                    ->orWhere('name', 'like', $like);
            })
            ->orderByRaw('CASE WHEN username LIKE ? THEN 0 ELSE 1 END', [$prefix])
            ->orderBy('username')
            ->limit(max(1, min($limit, 12)))
            ->get();
    }

    private function dispatchMentionNotifications(Collection $usernames, User $actor, Post|Comment $subject): void
    {
        if ($usernames->isEmpty()) {
            return;
        }

        $targets = $this->resolveUsers($usernames);

        foreach ($targets as $target) {
            if (!$target) {
                continue;
            }

            if ((int) $target->id === (int) $actor->id) {
                continue;
            }

            $target->notify(new UserMentionedNotification($actor, $subject));
        }
    }

    private function resolveUsers(Collection $usernames): Collection
    {
        $usernames = $usernames
            ->map(fn ($username) => Str::lower(trim((string) $username)))
            ->filter()
            ->unique()
            ->values();

        $missing = $usernames
            ->reject(fn ($username) => array_key_exists($username, self::$userCache))
            ->values();

        if ($missing->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $missing->count(), '?'));

            $foundUsers = User::query()
                ->select(['id', 'name', 'username', 'profile_photo_path'])
                ->whereRaw('LOWER(username) IN (' . $placeholders . ')', $missing->all())
                ->get()
                ->keyBy(fn (User $user) => Str::lower((string) $user->username));

            foreach ($missing as $username) {
                self::$userCache[$username] = $foundUsers->get($username);
            }
        }

        return $usernames
            ->map(fn ($username) => self::$userCache[$username] ?? null)
            ->filter()
            ->values();
    }

    private function normalizeTextForExtraction(?string $text): string
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        $text = strip_tags($text);
        $text = preg_replace('/\[(gif|img):([^\]\s]+)\]/i', ' ', $text) ?? $text;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }

    private function mentionAnchorHtml(User $user): string
    {
        $username = (string) $user->username;

        return sprintf(
            '<a href="%s" class="mention-link" data-mentioned-user="%s">@%s</a>',
            e(route('users.show', ['user' => $username])),
            e($username),
            e($username)
        );
    }

    private function shouldSkipTextNode(DOMText $node): bool
    {
        for ($parent = $node->parentNode; $parent instanceof DOMNode; $parent = $parent->parentNode) {
            $name = strtoupper((string) $parent->nodeName);
            if (in_array($name, ['A', 'SCRIPT', 'STYLE', 'CODE', 'PRE', 'TEXTAREA'], true)) {
                return true;
            }
        }

        return false;
    }
}

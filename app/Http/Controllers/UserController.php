<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\MessagePreference;
use App\Models\MessageSetting;
use App\Notifications\UserFollowedNotification;
use App\Services\PostCommentPreviewService;
use App\Services\PostLinkPreviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $viewer = $request->user();

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%');
                });
            })
            ->when($viewer, function ($query) use ($viewer) {
                $query->withExists([
                    'followers as is_followed_by_viewer' => fn ($inner) => $inner->where('users.id', $viewer->id),
                ]);
            })
            ->withCount(['followers', 'followings'])
            ->orderByDesc('id')
            ->paginate(18)
            ->withQueryString();

        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $sort = strtolower($request->get('sort', 'new'));
        $activeTab = strtolower((string) $request->query('tab', 'stories'));

        if (!in_array($activeTab, ['stories', 'comments', 'followers', 'followings'], true)) {
            $activeTab = 'stories';
        }

        $viewer = $request->user();
        $isOwnProfile = $viewer && (int) $viewer->id === (int) $user->id;
        $hasBlockedUser = $viewer ? $viewer->hasBlocked($user) : false;
        $isBlockedByUser = $viewer ? $viewer->isBlockedBy($user) : false;

        $commentsQuery = Comment::query()
            ->approved()
            ->where('user_id', $user->id)
            ->with([
                'post:id,title,slug',
                'user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->latest('created_at');

        $commentsCount = (clone $commentsQuery)->count();
        $earnedBadges = $user->earnedBadgesCollection();
        $nextBadge = $user->nextBadge();

        if ($hasBlockedUser || $isBlockedByUser) {
            $user->loadCount(['followers', 'followings']);

            $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
            $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

            return view('profile.public', [
                'user' => $user,
                'posts' => collect(),
                'postsCount' => 0,
                'totalViews' => 0,
                'sort' => $sort,
                'latestPublishedAt' => null,
                'isFollowing' => false,
                'canStartChat' => false,
                'messagesEnabled' => false,
                'allowFollowingOnly' => true,
                'isOwnProfile' => $isOwnProfile,
                'hasBlockedUser' => $hasBlockedUser,
                'isBlockedByUser' => $isBlockedByUser,
                'activeTab' => $activeTab,
                'comments' => collect(),
                'commentsCount' => 0,
                'followers' => collect(),
                'followings' => collect(),
                'popularTags' => $popularTags,
                'popularComments' => $popularComments,
                'earnedBadges' => $earnedBadges,
                'nextBadge' => $nextBadge,
            ]);
        }

        $baseQuery = Post::query()
            ->published()
            ->where('author_id', $user->id)
            ->with([
                'category:id,name,slug,profile_image,cover_image',
                'latestComment.user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->withCount(['comments', 'reactions']);

        if ($viewer) {
            $baseQuery->withExists([
                'bookmarkers as is_bookmarked' => fn ($query) => $query->where('users.id', $viewer->id),
            ]);
        }

        $postsQuery = $sort === 'popular'
            ? (clone $baseQuery)
                ->orderByDesc('is_pinned')
                ->orderByDesc('reactions_count')
                ->orderByDesc('comments_count')
                ->orderByDesc('published_at')
            : (clone $baseQuery)
                ->orderByDesc('is_pinned')
                ->latest('published_at');

        $posts = $postsQuery->take(24)->get();

        $postsCount = (clone $baseQuery)->count();
        $totalViews = (clone $baseQuery)->sum('views_count');
        $latestPublishedAt = (clone $baseQuery)->max('published_at');
        $latestPublishedAt = $latestPublishedAt ? Carbon::parse($latestPublishedAt) : null;

        app(PostCommentPreviewService::class)->attachToPosts($posts);
        app(PostLinkPreviewService::class)->attachToPosts($posts);

        $user->loadCount(['followers', 'followings']);

        $followers = $user->followers()
            ->select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->orderByDesc('users.id')
            ->limit(20)
            ->get();

        $followings = $user->followings()
            ->select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->orderByDesc('users.id')
            ->limit(20)
            ->get();

        $comments = $commentsQuery
            ->limit(20)
            ->get();

        $isFollowing = false;

        if ($viewer && (int) $viewer->id !== (int) $user->id) {
            $isFollowing = $viewer->followings()->where('followed_id', $user->id)->exists();
        }

        $messageSettings = MessageSetting::current();
        $recipientPrefs = MessagePreference::forUser($user);
        $messagesEnabled = $messageSettings->is_enabled;
        $allowFollowingOnly = $messageSettings->allow_following_only || $recipientPrefs->allow_following_only;

        $canStartChat = (bool) (
            $viewer
            && !$isOwnProfile
            && !$hasBlockedUser
            && !$isBlockedByUser
            && $messagesEnabled
            && $recipientPrefs->allow_messages
            && ($allowFollowingOnly ? $isFollowing : true)
        );

        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

        return view('profile.public', [
            'user' => $user,
            'posts' => $posts,
            'postsCount' => $postsCount,
            'totalViews' => $totalViews,
            'sort' => $sort,
            'latestPublishedAt' => $latestPublishedAt,
            'isFollowing' => $isFollowing,
            'canStartChat' => $canStartChat,
            'messagesEnabled' => $messagesEnabled,
            'allowFollowingOnly' => $allowFollowingOnly,
            'isOwnProfile' => $isOwnProfile,
            'hasBlockedUser' => $hasBlockedUser,
            'isBlockedByUser' => $isBlockedByUser,
            'activeTab' => $activeTab,
            'comments' => $comments,
            'commentsCount' => $commentsCount,
            'followers' => $followers,
            'followings' => $followings,
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
            'earnedBadges' => $earnedBadges,
            'nextBadge' => $nextBadge,
        ]);
    }

    public function toggleFollow(Request $request, User $user)
    {
        $viewer = $request->user();

        if (!$viewer) {
            return redirect()->route('login');
        }

        if ((int) $viewer->id === (int) $user->id) {
            return back()->with('status', 'Kendinizi takip edemezsiniz.');
        }

        if ($viewer->hasBlocked($user)) {
            return back()->with('status', 'Takip etmek icin once engeli kaldirin.');
        }

        if ($viewer->isBlockedBy($user)) {
            return back()->with('status', 'Kullaniciya ulasilamiyor.');
        }

        $isFollowing = $viewer->followings()->where('followed_id', $user->id)->exists();

        if ($isFollowing) {
            $viewer->followings()->detach($user->id);
        } else {
            $viewer->followings()->syncWithoutDetaching([$user->id]);
            $user->notify(new UserFollowedNotification($viewer));
        }

        $response = [
            'following' => !$isFollowing,
            'followers_count' => $user->followers()->count(),
        ];

        if ($request->expectsJson()) {
            return Response::json($response);
        }

        return back()->with('status', $isFollowing ? 'Takipten cikildi.' : 'Takip edildi.');
    }

    public function toggleBlock(Request $request, User $user)
    {
        $viewer = $request->user();

        if (!$viewer) {
            return redirect()->route('login');
        }

        if ((int) $viewer->id === (int) $user->id) {
            return back()->with('status', 'Kendinizi engelleyemezsiniz.');
        }

        if (!$request->isMethod('post')) {
            return redirect()->route('users.show', $user);
        }

        $hasBlocked = $viewer->hasBlocked($user);

        if ($hasBlocked) {
            $viewer->blockedUsers()->detach($user->id);
            $blocked = false;
            $message = 'Engel kaldirildi.';
        } else {
            $viewer->blockedUsers()->syncWithoutDetaching([$user->id]);

            $viewer->followings()->detach($user->id);
            $viewer->followers()->detach($user->id);
            $user->followings()->detach($viewer->id);
            $user->followers()->detach($viewer->id);

            $blocked = true;
            $message = 'Kullanici engellendi.';
        }

        $response = [
            'blocked' => $blocked,
        ];

        if ($request->expectsJson()) {
            return Response::json($response);
        }

        return back()->with('status', $message);
    }
}

<?php

namespace App\Services;

class CommentMentionExtractorService
{
    // TODO: complex regex "/(?=[^\w!])@(\w+)\b/" → "/(?=[^\w!]|[ぁ-んァ-ヶー一-龠０-９])@(\w+)\b/u"
    public const MENTION_REGEX = '/(?=[^\w!])@(\w+)\b/';

    public function __construct(public $string)
    {
    }

    public function getMentionEntities()
    {
        return $this->buildMentionCollection(
            $this->match(self::MENTION_REGEX),
        );
    }

    protected function buildMentionCollection($mentions)
    {
        return array_map(function ($mention, $index) use ($mentions) {
            return [
                'body' => $mention[0],
                'body_plain' => $mentions[1][$index][0],
            ];
        }, $mentions[0], array_keys($mentions[0]));
    }

    protected function match($pattern)
    {
        preg_match_all($pattern, $this->string, $matches, PREG_OFFSET_CAPTURE);

        return $matches;
    }
}

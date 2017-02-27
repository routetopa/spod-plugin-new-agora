<?php

class SPODAGORA_CMP_SearchResult extends OW_Component
{

    public function __construct($roomId, $search_string, $userId, $page=0)
    {
        $raw_search_result = SPODAGORA_BOL_Service::getInstance()->getSearchResult($roomId, $search_string, $userId, $page);
        $this->assign('search_results', count($raw_search_result) > 0 ? $this->process_comment($raw_search_result) : null);
    }

    private function process_comment(&$comments)
    {
        $users_ids      = array_map(function($comments) { return $comments->ownerId;}, $comments);
        $avatars  = BOL_AvatarService::getInstance()->getDataForUserAvatars($users_ids);

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username      = $avatars[$comment->ownerId]["title"];
            $comment->owner_url     = $avatars[$comment->ownerId]["url"];
            $comment->avatar_url    = $avatars[$comment->ownerId]["src"];
            $comment->timestamp     = $this->process_timestamp($comment->timestamp, $today, $yesterday);


            switch ($comment->sentiment)
            {
                case 0 : $comment->sentiment_class = 'neutral'; break;
                case 1 : $comment->sentiment_class = 'satisfied'; break;
                case 2 : $comment->sentiment_class = 'dissatisfied'; break;
            }
        }

        return $comments;
    }

    private function process_timestamp($timestamp, $today, $yesterday)
    {
        $date = date('Ymd', strtotime($timestamp));

        if($date == $today)
            return date('H:i', strtotime($timestamp));

        if($date == $yesterday)
            return "yesterday " . date('H:i', strtotime($timestamp));

        return date('H:i m/d', strtotime($timestamp));
    }

}
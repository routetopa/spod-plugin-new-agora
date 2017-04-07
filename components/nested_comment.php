<?php

class SPODAGORA_CMP_NestedComment extends OW_Component
{
    private $userId;
    private $avatars;

    public function __construct($room_id, $parent_id, $level)
    {
        $this->userId = OW::getUser()->getId();

        $father_comment = SPODAGORA_BOL_Service::getInstance()->getCommentById($parent_id);
        $raw_comments = SPODAGORA_BOL_Service::getInstance()->getNestedComment($room_id, $parent_id, $level);
        array_unshift($raw_comments, $father_comment);
        $this->assign('comments', $this->process_comment($raw_comments));
    }

    private function process_comment(&$comments)
    {
        $users_ids      = array_map(function($comments) { return $comments->ownerId;}, $comments);
        $user_id        = $this->userId;
        $this->avatars  = SPODAGORA_CLASS_Tools::getInstance()->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars($users_ids));
        $this->assign('avatars', $this->avatars);

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username       = $this->avatars[$comment->ownerId]["title"];
            $comment->owner_url      = $this->avatars[$comment->ownerId]["url"];
            $comment->avatar_url     = $this->avatars[$comment->ownerId]["src"];
            $comment->avatar_css     = $this->avatars[$comment->ownerId]["css"];
            $comment->avatar_initial = $this->avatars[$comment->ownerId]["initial"];
            $comment->total_comment  = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp      = SPODAGORA_CLASS_Tools::getInstance()->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class       = $user_id == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';
            $comment->sentiment_class = $comment->sentiment == 0 ? 'neutral' : ($comment->sentiment == 1 ? 'satisfied' : 'dissatisfied');
            $comment->datalet_class   = '';
            $comment->datalet_html    = '';

            if (isset($comment->component)) {
                $comment->datalet_class = 'agora_fullsize_datalet';
                $comment->datalet_html  = $this->create_datalet_code($comment);
            }else{
                $comment->component = '';
            }

        }

        return $comments;
    }

    private function create_datalet_code($comment)
    {
        $params = json_decode($comment->params);
        $html  = "<link rel='import' href='".SPODPR_COMPONENTS_URL."datalets/{$comment->component}/{$comment->component}.html' />";
        $html .= "<{$comment->component} ";

        foreach ($params as $key => $value){
            $html .= $key."='".$value."' ";
        }

        $html .= " ></{$comment->component}>";

        return $html;
    }

}
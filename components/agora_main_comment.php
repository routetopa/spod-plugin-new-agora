<?php

class SPODAGORA_CMP_AgoraMainComment extends OW_Component
{
    public function __construct($roomId, $last_comment_id=10e10, $comment_id="")
    {
        if(empty($comment_id))
            $raw_comments = SPODAGORA_BOL_Service::getInstance()->getCommentListPaged($roomId, $last_comment_id);
        else
            $raw_comments = SPODAGORA_BOL_Service::getInstance()->getCommentListMissing($roomId, $last_comment_id, $comment_id);

        $this->assign('comments', SPODAGORA_CLASS_Tools::getInstance()->process_comment_include_datalet($raw_comments, OW::getUser()->getId()));
    }
}
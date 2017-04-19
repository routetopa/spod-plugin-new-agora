<?php

class SPODAGORA_CMP_NestedComment extends OW_Component
{
    public function __construct($room_id, $parent_id, $level)
    {
        $father_comment = SPODAGORA_BOL_Service::getInstance()->getCommentById($parent_id);
        $raw_comments = SPODAGORA_BOL_Service::getInstance()->getNestedComment($room_id, $parent_id, $level);
        array_unshift($raw_comments, $father_comment);
        $this->assign('comments', SPODAGORA_CLASS_Tools::getInstance()->process_comment_include_datalet($raw_comments, OW::getUser()->getId()));
    }
}
<?php

class SPODAGORA_CMP_AgoraAddRoom extends OW_Component
{
    public function __construct($room_id, $subject, $body)
    {
        $this->assign('room_id', $room_id);
        $this->assign('subject', $subject);
        $this->assign('body', $body);
        $this->assign('avatar', BOL_AvatarService::getInstance()->getDataForUserAvatars([OW::getUser()->getId()])[OW::getUser()->getId()]);
    }
}
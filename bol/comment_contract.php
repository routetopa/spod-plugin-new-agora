<?php

class SPODAGORA_BOL_CommentContract extends OW_Entity
{
    public $id;
    public $entityId;
    public $ownerId;
    public $comment;
    public $level;
    public $sentiment;
    public $timestamp;
    public $total_comment;

    public $username;
    public $owner_url;
    public $avatar_url;

    public $css_class;

}
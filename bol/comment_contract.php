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
    public $component;
    public $data;
    public $fields;
    public $params;


    public $username;
    public $owner_url;
    public $avatar_url;

    public $css_class;
    public $sentiment_class;
    public $datalet_class;
    public $datalet_id;

}
<?php

class SPODAGORA_BOL_Service
{

    /**
     * Singleton instance.
     *
     * @var AGORA_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return AGORA_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    // WRITER
    public function addComment($entityId, $parentId, $ownerId,
                               $comment, $level, $sentiment)
    {
        $c = new SPODAGORA_BOL_AgoraRoomComment();

        $c->entityId   = $entityId;
        $c->parentId   = $parentId;
        $c->ownerId    = $ownerId;
        $c->comment    = $comment;
        $c->level      = $level;
        $c->sentiment  = $sentiment;
        //$c->timestamp  = time();

        SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->save($c);

        return $c;
    }

    // READER
    public function getCommentList($roomId)
    {
        $sql = "SELECT id, entityId, ownerId, comment, level, sentiment, timestamp, total_comment 
                FROM spod.ow_spod_agora_room_comment 
                   LEFT JOIN 
                      (SELECT count(parentId) as total_comment, parentId 
                       FROM spod.ow_spod_agora_room_comment
                       WHERE entityId = {$roomId} and level = 1 GROUP BY parentId) 
                   as T on spod.ow_spod_agora_room_comment.id = T.parentId
                   where level = 0 and entityId = {$roomId}
                   order by timestamp asc;";

        $dbo = OW::getDbo();
        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_CommentContract');
    }

}
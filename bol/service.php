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
        $sql = "SELECT F.id, F.entityId, F.ownerId, F.comment, F.level, F.sentiment, F.timestamp, F.total_comment,
                       J.component, J.data, J.fields, J.params
                
                FROM (SELECT * 
                      FROM 
                        (SELECT ow_spod_agora_room_comment.id, entityId, ownerId, comment, level, sentiment, timestamp, total_comment 
                         FROM ow_spod_agora_room_comment LEFT JOIN 
                            (SELECT count(parentId) AS total_comment, parentId 
                             FROM ow_spod_agora_room_comment
                             WHERE entityId = {$roomId} AND level = 1 
                             GROUP BY parentId) AS T ON ow_spod_agora_room_comment.id = T.parentId ) 
                         AS K LEFT JOIN 
                            (SELECT dataletId, postId FROM ow_ode_datalet_post WHERE plugin = 'public-room') AS W ON K.id = W.postId ) 
                            AS F LEFT JOIN ow_ode_datalet 
                            AS J ON F.dataletId = J.id
                
                where level = 0 and entityId = {$roomId}
                order by F.timestamp asc;";

        $dbo = OW::getDbo();
        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_CommentContract');
    }

    public function getUnreadComment($roomId, $userId)
    {
        $sql = "SELECT * 
                FROM  ow_spod_agora_room_comment
                WHERE ow_spod_agora_room_comment.timeStamp > 
                    (SELECT last_access 
                     FROM ow_spod_agora_room_user_access 
                     WHERE userId = {$userId}) 
                AND   ow_spod_agora_room_comment.entityId = {$roomId} AND ow_spod_agora_room_comment.ownerId != {$userId}
                ORDER BY timestamp DESC";

        $dbo = OW::getDbo();
        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_AgoraRoomComment');
    }

    public function getNestedComment($room_id, $parent_id, $level)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('entityId', $room_id);
        $ex->andFieldEqual('parentId', $parent_id);
        $ex->andFieldEqual('level', $level);
        $ex->setOrder('timestamp asc');

        return SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->findListByExample($ex);
    }

    public function getCommentById($comment_id)
    {
        return SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->findById($comment_id);
    }

    public function addUserNotification($roomId, $userId)
    {
        $prun = new SPODAGORA_BOL_AgoraRoomUserNotification();
        $prun->userId = $userId;
        $prun->roomId = $roomId;

        return SPODAGORA_BOL_AgoraRoomUserNotificationDao::getInstance()->save($prun);
    }

    public function removeUserNotification($roomId, $userId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId',$userId);
        $ex->andFieldEqual('roomId',$roomId);

        return SPODAGORA_BOL_AgoraRoomUserNotificationDao::getInstance()->deleteByExample($ex);
    }

    public function getUserNotification($roomId, $userId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId',$userId);
        $ex->andFieldEqual('roomId',$roomId);

        $a = SPODAGORA_BOL_AgoraRoomUserNotificationDao::getInstance()->findObjectByExample($ex);
        return $a;
    }

}
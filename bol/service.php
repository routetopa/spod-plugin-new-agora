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

    public function addCommentWithTimestamp($entityId, $parentId, $ownerId,
                               $comment, $level, $sentiment, $timestamp)
    {
        $c = new SPODAGORA_BOL_AgoraRoomComment();

        $c->entityId   = $entityId;
        $c->parentId   = $parentId;
        $c->ownerId    = $ownerId;
        $c->comment    = $comment;
        $c->level      = $level;
        $c->sentiment  = $sentiment;
        $c->timestamp  = $timestamp;

        SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->save($c);

        return $c;
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

    public function addAgoraRoom($ownerId, $subject, $body)
    {
        $ar = new SPODAGORA_BOL_AgoraRoom();
        $ar->ownerId   = $ownerId;
        $ar->subject   = strip_tags($subject);
        $ar->body      = strip_tags($body);
        $ar->views     = 0;
        $ar->comments  = 0;
        $ar->opendata  = 0;
        $ar->post      = json_encode(["timestamp"=>time(), "opendata"=>$ar->opendata, "comments"=>$ar->comments, "views"=>$ar->views]);
        $ar->timestamp = date('Y-m-d H:i:s',time());
        SPODAGORA_BOL_AgoraRoomDao::getInstance()->save($ar);

        /*$event = new OW_Event('feed.action', array(
            'pluginKey' => 'spodpublic',
            'entityType' => 'spodpublic_public-room',
            'entityId' => $pr->id,
            'userId' => $ownerId
        ), array(

            'time' => time(),
            'string' => array('key' => 'spodpublic+create_new_room', 'vars'=>array('roomId' => $pr->id, 'roomSubject' => $subject))
        ));
        OW::getEventManager()->trigger($event);*/

        return $ar->id;
    }

    public function addAgoraRoomTimestamp($ownerId, $subject, $body, $timestamp, $views)
    {
        $ar = new SPODAGORA_BOL_AgoraRoom();
        $ar->ownerId   = $ownerId;
        $ar->subject   = strip_tags($subject);
        $ar->body      = strip_tags($body);
        $ar->views     = $views;
        $ar->comments  = 0;
        $ar->opendata  = 0;
        $ar->post      = json_encode(["timestamp"=>time(), "opendata"=>$ar->opendata, "comments"=>$ar->comments, "views"=>$ar->views]);
        $ar->timestamp = $timestamp;
        SPODAGORA_BOL_AgoraRoomDao::getInstance()->save($ar);

        /*$event = new OW_Event('feed.action', array(
            'pluginKey' => 'spodpublic',
            'entityType' => 'spodpublic_public-room',
            'entityId' => $pr->id,
            'userId' => $ownerId
        ), array(

            'time' => time(),
            'string' => array('key' => 'spodpublic+create_new_room', 'vars'=>array('roomId' => $pr->id, 'roomSubject' => $subject))
        ));
        OW::getEventManager()->trigger($event);*/

        return $ar->id;
    }

    public function addAgoraRoomStat($agoraId, $field)
    {
        $sql = "UPDATE ".OW_DB_PREFIX."spod_agora_room SET {$field} = {$field} + 1 WHERE id = {$agoraId};";
        $dbo = OW::getDbo();

        return $dbo->query($sql);
    }

    public function addAgoraDataletNode($datalet, $comment, $commentId, $parentId, $roomId)
    {

        $dt = json_decode($datalet["params"]);

        $comment = str_replace("'", "''",$comment);
        $title = isset($dt->title) ? str_replace("'", "''",$dt->title) : '';

        $node = array("url" => $dt->{"data-url"}, "title" => $title, "comment" => $comment, "parent_id" => $parentId, "comment_id" => $commentId);
        $node = json_encode($node);

        $sql = "UPDATE ".OW_DB_PREFIX."spod_agora_room SET datalet_graph = CONCAT(COALESCE(datalet_graph, ''), '{$node},') WHERE id = {$roomId};";
        $dbo = OW::getDbo();

        return $dbo->query($sql);
    }

    public function addAgoraRoomSuggestion($roomId, $dataset, $comment)
    {
        $agoraRoomSuggestion = new SPODAGORA_BOL_AgoraRoomSuggestion();
        $agoraRoomSuggestion->agoraRoomId  = intval($roomId);
        $agoraRoomSuggestion->dataset      = $dataset;
        $agoraRoomSuggestion->comment      = $comment;
        SPODAGORA_BOL_AgoraRoomSuggestionDao::getInstance()->save($agoraRoomSuggestion);
        return $agoraRoomSuggestion->id;
    }
    // READER

    public function getAgoraById($roomId)
    {
        return SPODAGORA_BOL_AgoraRoomDao::getInstance()->findById($roomId);
    }

    public function getAgora()
    {
        $example = new OW_Example();
        $example->setOrder('timestamp DESC');

        return SPODAGORA_BOL_AgoraRoomDao::getInstance()->findListByExample($example);
    }

    public function getMaxAgoraStat()
    {
        $sql = "select max(views) as maxView, max(comments) as maxComments, max(opendata) as maxOpendata from ow_spod_agora_room;";
        $dbo = OW::getDbo();

        return $dbo->queryForRow($sql);
    }

    public function getAgoraByOwner($ownerId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('ownerId', $ownerId);
        $example->setOrder('timestamp DESC');

        return SPODAGORA_BOL_AgoraRoomDao::getInstance()->findListByExample($example);
    }

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
        $dbo = OW::getDbo();
        $laq = "select id from ow_spod_agora_room_user_access where userId = {$userId} and roomId = {$roomId}";
        $row = $dbo->queryForRow($laq);

        if(empty($row))
        {
            $sql = "SELECT * 
                    FROM  ow_spod_agora_room_comment
                    WHERE ow_spod_agora_room_comment.entityId = {$roomId} AND ow_spod_agora_room_comment.ownerId != {$userId}
                    ORDER BY timestamp DESC";
            $laq = "insert into ow_spod_agora_room_user_access (userId, roomId, last_access) values ({$userId},{$roomId},CURRENT_TIMESTAMP());";
        }
        else
        {
            $sql = "SELECT * 
                    FROM  ow_spod_agora_room_comment
                    WHERE ow_spod_agora_room_comment.timeStamp > 
                        (SELECT last_access 
                         FROM ow_spod_agora_room_user_access 
                         WHERE userId = {$userId} and roomId = {$roomId})  
                    AND   ow_spod_agora_room_comment.entityId = {$roomId} AND ow_spod_agora_room_comment.ownerId != {$userId}
                    ORDER BY timestamp DESC";
            $laq = "update ow_spod_agora_room_user_access SET last_access = CURRENT_TIMESTAMP() where userId = {$userId} and roomId = {$roomId}";
        }

        $unread =  $dbo->queryForObjectList($sql,'SPODAGORA_BOL_AgoraRoomComment');
        $dbo->query($laq);

        return $unread;
    }

    public function getNestedComment($room_id, $parent_id, $level)
    {
        $sql = "SELECT T.id, T.entityId, T.ownerId, T.comment, T.level, T.sentiment, T.timestamp,
                       ow_ode_datalet.component, ow_ode_datalet.data, ow_ode_datalet.fields, ow_ode_datalet.params 
                FROM (ow_spod_agora_room_comment as T LEFT JOIN ow_ode_datalet_post as T1 on T.id = T1.postId) LEFT JOIN ow_ode_datalet ON T1.dataletId = ow_ode_datalet.id
                WHERE parentId = {$parent_id} and level = {$level} and entityId = {$room_id}
                order by T.timestamp asc;";

        $dbo = OW::getDbo();

        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_CommentContract');
    }

    public function getCommentById($comment_id)
    {
        return SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->findById($comment_id);
    }

    public function getUserNotification($roomId, $userId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId',$userId);
        $ex->andFieldEqual('roomId',$roomId);

        $a = SPODAGORA_BOL_AgoraRoomUserNotificationDao::getInstance()->findObjectByExample($ex);

        return $a;
    }

    public function getAllUserNotification($userId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId',$userId);

        $a = SPODAGORA_BOL_AgoraRoomUserNotificationDao::getInstance()->findListByExample($ex);

        return $a;
    }

    public function getSearchResult($roomId, $search_string, $userId)
    {
        //$n = 10;
        
        $ex = new OW_Example();
        $ex->andFieldEqual('entityId', $roomId);
        $ex->andFieldLike('comment', '%'.$search_string.'%');

        if(!empty($userId) && $userId != -1)
            $ex->andFieldEqual('ownerId', $userId);

        //$ex->setLimitClause($page*$n, $n);

        return SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->findListByExample($ex);
    }

    public function getAgoraFriendship($users)
    {
        if(!count($users))
            return null;

        $user_join = implode(",", $users);
        $sql = "SELECT userId, friendId FROM ow_friends_friendship where userId in (".$user_join.") and friendId in (".$user_join.") ORDER BY userId;";

        $dbo = OW::getDbo();
        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_AgoraRoomFriendshipContract');
    }

    public function getSubscribedNotificationUsersForRoom($roomId)
    {
        $dbo = OW::getDbo();
        $sql = "SELECT userId FROM ow_spod_agora_room_user_notification WHERE roomId = " . $roomId;
        return $dbo->queryForList($sql);
    }

    public function getAllLevesCommentsFromAgoraId($roomId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('entityId', $roomId);

        return SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->findListByExample($ex);
    }

    public function getAgoraSuggestedDataset($roomId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('agoraRoomId', $roomId);

        return SPODAGORA_BOL_AgoraRoomSuggestionDao::getInstance()->findListByExample($ex);
    }
}
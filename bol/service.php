<?php

class SPODAGORA_BOL_Service
{

    /**
     * Singleton instance.
     *
     * @var AGORA_BOL_Service
     */
    private static $classInstance;
    private $result_per_page = 10;

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
                               $comment, $level, $sentiment, $hast_tags)
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

        foreach ($hast_tags as $ht)
        {
            $h_t = new SPODAGORA_BOL_AgoraRoomHashtag();
            $h_t->roomId = $entityId;
            $h_t->commentId = $c->id;
            $h_t->hashtag = $ht;
            SPODAGORA_BOL_AgoraRoomHashtagDao::getInstance()->save($h_t);
        }

        return $c;
    }

    public function deleteComment($comment)
    {
        $dbo = OW::getDbo();
        $comments = [$comment];
        $agora = $this->getAgoraById($comments[0]->entityId);

        $ex = new OW_Example();
        $ex->andFieldEqual('parentId', $comment->id);
        $nested_comment = SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->findListByExample($ex);

        $comments = array_merge($comments, $nested_comment);

        foreach ($comments as $comment)
        {
            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($comment->id, 'agora');
            if(!empty($datalet))
            {
                //Delete Association
                $sql = "DELETE FROM ow_ode_datalet_post WHERE postId = {$comment->id} AND dataletId = {$datalet['dataletId']} AND plugin = 'agora'; ";
                $dbo->query($sql);
                //Delete Datalet
                $sql = "DELETE FROM ow_ode_datalet WHERE id = {$datalet['dataletId']}; ";
                $this->subAgoraRoomStat($comment->entityId, 'opendata');
                $dbo->query($sql);
                //Delete datalet node from datalet graph
                $datalet_graph = json_decode('['.rtrim($agora->datalet_graph, ",").']');
                foreach ($datalet_graph as $key => $node)
                {
                    if($node->comment_id == $comment->id)
                    {
                        unset($datalet_graph[$key]);
                        break;
                    }
                }
                $node_number = count($datalet_graph);
                $datalet_graph = json_encode(array_values($datalet_graph));
                $agora->datalet_graph = $node_number === 0 ? '' : (substr($datalet_graph, 1, strlen($datalet_graph)-2).",");
                SPODAGORA_BOL_AgoraRoomDao::getInstance()->save($agora);
            }

            //Delete comment hashtag
            $ex = new OW_Example();
            $ex->andFieldEqual('commentId', $comment->id);
            SPODAGORA_BOL_AgoraRoomHashtagDao::getInstance()->deleteByExample($ex);

            //Delete comment
            $sql = "DELETE FROM ow_spod_agora_room_comment WHERE id = {$comment->id}; ";
            $this->subAgoraRoomStat($comment->entityId, 'comments');
            $dbo->query($sql);
        }
    }

    public function editComment($comment, $comment_text, $hast_tags)
    {
        //Delete comment hashtag
        $ex = new OW_Example();
        $ex->andFieldEqual('commentId', $comment->id);
        SPODAGORA_BOL_AgoraRoomHashtagDao::getInstance()->deleteByExample($ex);

        // Insert new ht
        foreach ($hast_tags as $ht)
        {
            $h_t = new SPODAGORA_BOL_AgoraRoomHashtag();
            $h_t->roomId = $comment->entityId;
            $h_t->commentId = $comment->id;
            $h_t->hashtag = $ht;
            SPODAGORA_BOL_AgoraRoomHashtagDao::getInstance()->save($h_t);
        }

        // Edit comment
        $comment->comment = $comment_text;
        SPODAGORA_BOL_AgoraRoomCommentDao::getInstance()->save($comment);
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

    public function subAgoraRoomStat($agoraId, $field)
    {
        $sql = "UPDATE ".OW_DB_PREFIX."spod_agora_room SET {$field} = {$field} - 1 WHERE id = {$agoraId};";
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

    public function removeAgora($roomId)
    {
        // delete all comments, datalets, datalet-post association
        $this->deleteAgoraComments($roomId);

        // delete all room hashtags
        $ex = new OW_Example();
        $ex->andFieldEqual('roomId', $roomId);
        SPODAGORA_BOL_AgoraRoomHashtagDao::getInstance()->deleteByExample($ex);

        // delete room
        SPODAGORA_BOL_AgoraRoomDao::getInstance()->deleteById($roomId);
    }

    public function editAgora($roomId, $title, $body)
    {
        $agora = $this->getAgoraById($roomId);
        $agora->subject = $title;
        $agora->body = $body;
        SPODAGORA_BOL_AgoraRoomDao::getInstance()->save($agora);
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
                            (SELECT dataletId, postId FROM ow_ode_datalet_post WHERE plugin = 'agora') AS W ON K.id = W.postId ) 
                            AS F LEFT JOIN ow_ode_datalet 
                            AS J ON F.dataletId = J.id
                
                where level = 0 and entityId = {$roomId}
                order by F.timestamp asc;";

        $dbo = OW::getDbo();

        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_CommentContract');
    }

    public function getCommentListPaged($roomId, $last_id=10e10)
    {
        $sql = "SELECT * FROM (SELECT F.id, F.entityId, F.ownerId, F.comment, F.level, F.sentiment, F.timestamp, F.total_comment,
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
                            (SELECT dataletId, postId FROM ow_ode_datalet_post WHERE plugin = 'agora') AS W ON K.id = W.postId ) 
                            AS F LEFT JOIN ow_ode_datalet 
                            AS J ON F.dataletId = J.id
                
                where level = 0 and entityId = {$roomId} and F.id < {$last_id}
                order by F.timestamp DESC
                limit {$this->result_per_page}) as K
                ORDER BY K.timestamp ASC;";

        $dbo = OW::getDbo();

        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_CommentContract');
    }

    public function getCommentListMissing($roomId, $last_id, $comment_id)
    {
        $sql = "SELECT * FROM (SELECT F.id, F.entityId, F.ownerId, F.comment, F.level, F.sentiment, F.timestamp, F.total_comment,
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
                            (SELECT dataletId, postId FROM ow_ode_datalet_post WHERE plugin = 'agora') AS W ON K.id = W.postId ) 
                            AS F LEFT JOIN ow_ode_datalet 
                            AS J ON F.dataletId = J.id
                
                where level = 0 and entityId = {$roomId} and F.id < {$last_id} and F.id >= {$comment_id}
                order by F.timestamp DESC) as K
                ORDER BY K.timestamp ASC;";

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

    public function getUnreadCommentNumber($roomId, $userId)
    {
        $dbo = OW::getDbo();

        $sql = "SELECT * 
                FROM  ow_spod_agora_room_comment
                WHERE ow_spod_agora_room_comment.timeStamp > 
                    (SELECT IFNULL( (SELECT last_access
                     FROM ow_spod_agora_room_user_access 
                     WHERE userId = {$userId} and roomId = {$roomId}), '1990-01-01 00:00:00') as last_access)  
                AND   ow_spod_agora_room_comment.entityId = {$roomId} AND ow_spod_agora_room_comment.ownerId != {$userId}
                ORDER BY timestamp DESC";

        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_AgoraRoomComment');
    }

    public function getNestedComment($room_id, $parent_id, $level)
    {
        $sql = "SELECT T.id, T.entityId, T.ownerId, T.comment, T.level, T.sentiment, T.timestamp,
                       ow_ode_datalet.component, ow_ode_datalet.data, ow_ode_datalet.fields, ow_ode_datalet.params 
                FROM (ow_spod_agora_room_comment as T LEFT JOIN (SELECT dataletId, postId FROM ow_ode_datalet_post WHERE plugin = 'agora') as T1 on T.id = T1.postId) LEFT JOIN ow_ode_datalet ON T1.dataletId = ow_ode_datalet.id
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

        return SPODAGORA_BOL_AgoraRoomUserNotificationDao::getInstance()->findObjectByExample($ex);
    }

    public function getAllUserNotification($userId)
    {
        $sql = "select roomId from ow_spod_agora_room_user_notification where userId = ".$userId.";";
        $dbo = OW::getDbo();

        return $dbo->queryForColumnList($sql);
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

    public function getRoomSentiments($roomId)
    {
        $dbo = OW::getDbo();
        $sql = "SELECT count(sentiment) as tot, sentiment FROM ow_spod_agora_room_comment where entityId = ". $roomId." group by sentiment order by sentiment;";
        return $dbo->queryForList($sql);
    }

    public function getRoomHashtag($roomId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('roomId', $roomId);

        return SPODAGORA_BOL_AgoraRoomHashtagDao::getInstance()->findListByExample($ex);
    }

    public function getCommentByParentId($parentId)
    {
        $dbo = OW::getDbo();
        $sql = "SELECT T.id, T.entityId, T.ownerId, T.comment, T.level, T.sentiment, T.timestamp,
                       ow_ode_datalet.component, ow_ode_datalet.fields, ow_ode_datalet.params, username 
                FROM (ow_spod_agora_room_comment as T LEFT JOIN ow_ode_datalet_post
                    ON T.id = ow_ode_datalet_post.postId) LEFT JOIN ow_ode_datalet ON dataletId = ow_ode_datalet.id LEFT JOIN ow_base_user ON T.ownerId = ow_base_user.id
                WHERE T.parentId =".$parentId." ORDER BY T.id ASC;";

        return $dbo->queryForObjectList($sql,'SPODAGORA_BOL_CommentContract');
    }

    //Utils
    private function deleteAgoraComments($agoraId)
    {
        $dbo = OW::getDbo();
        $all_level_comments = $this->getAllLevesCommentsFromAgoraId($agoraId);
        foreach ($all_level_comments as $comment)
        {
            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($comment->id, 'agora');
            if(!empty($datalet))
            {
                //Delete Association
                $sql = "DELETE FROM ow_ode_datalet_post WHERE postId = {$comment->id} AND dataletId = {$datalet['id']} AND plugin = 'agora'; ";
                $dbo->query($sql);
                //Delete Datalet
                $sql = "DELETE FROM ow_ode_datalet WHERE id = {$datalet['id']}; ";
                $dbo->query($sql);
            }
            //Delete comment
            $sql = "DELETE FROM ow_spod_agora_room_comment WHERE id = {$comment->id}; ";
            $dbo->query($sql);
        }

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Utente
 * Date: 20/02/2017
 * Time: 11.18
 */

class SPODAGORA_CTRL_Importer extends OW_ActionController
{
    private $entityId = 0;

    public function index(array $params)
    {
        $roomId = $params['roomId'];

        $room = new stdClass();
        $room->id = $roomId;
        $room->createStamp = 0;
        $room->userId = 0;
        $room->message = "ROOM ".$roomId;
        $room->notCreateMessage = true;

        $publicRoom = SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($room->id);
        $this->entityId = $this->createAgora($publicRoom->ownerId, $publicRoom->subject, $publicRoom->body);
        $this->exportRoom($room, 0, $this->entityId);
        exit;
    }

    private function createAgora($owner, $subject, $body)
    {
        return SPODAGORA_BOL_Service::getInstance()->addAgoraRoom($owner, $subject, $body);
    }

    private function exportRoom($curr_comment, $level, $father)
    {
        $sentiment = SPODPUBLIC_BOL_Service::getInstance()->getCommentSentiment($curr_comment->id);


        if(!isset($curr_comment->notCreateMessage))
        {
            $computed_level = $level > 1 ? 1 : $level - 1;
            $c = SPODAGORA_BOL_Service::getInstance()->addCommentWithTimestamp($this->entityId, $father, $curr_comment->userId,
                $curr_comment->message, $computed_level, isset( $sentiment->sentiment) ? $sentiment->sentiment : 0, date("Y-m-d H:i:s", $curr_comment->createStamp));


            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($curr_comment->id, "public-room");
            if (count($datalet) > 0) {
                $this->switchDatalet($curr_comment->id, $c->id, $datalet, $curr_comment->message);
                echo "datalet presente -> ";
            }else{
                echo "datalet non presente -> ";
            }
        }

        echo $curr_comment->id . ")"
            . $curr_comment->message
            . " livello " . $level
            . " padre " . $father
            . " user id " . $curr_comment->userId
            /*. " sentiment " . $sentiment->sentiment*/
            . " create stamp " . $curr_comment->createStamp
            . "<br/>";

        $comments = BOL_CommentService::getInstance()->findFullCommentList(($level == 0 ) ? SPODPUBLIC_BOL_Service::ENTITY_TYPE : SPODPUBLIC_BOL_Service::ENTITY_TYPE_COMMENT, $curr_comment->id);

        for ($i = 0; $i < count($comments); $i++)
            $this->exportRoom($comments[$i], $level + 1, ($level > 1) ? $father : (isset($c) ? $c->id : $father));

    }

    private function switchDatalet($idOldPost, $idNewPost, $datalet, $message)
    {
        $sql = "UPDATE ow_ode_datalet_post SET postId = {$idNewPost} where postId = {$idOldPost} and plugin = 'public-room';";
        $dbo = OW::getDbo();
        $dbo->query($sql);

        SPODAGORA_BOL_Service::getInstance()->addAgoraDataletNode($datalet, $message, $idNewPost, $this->entityId);
    }
}
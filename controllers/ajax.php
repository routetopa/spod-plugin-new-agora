<?php

require_once OW::getPluginManager()->getPlugin('spodnotification')->getRootDir()
    . 'lib/vendor/autoload.php';

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

class SPODAGORA_CTRL_Ajax extends OW_ActionController
{
    //Writer
    public function addComment()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if(SPODAGORA_CLASS_Tools::getInstance()->check_value(["entityId", "parentId", "comment", "level", "sentiment"]))
        {
            //Get hashtag
            $ht = SPODAGORA_CLASS_Tools::getInstance()->get_hashtag($_REQUEST['comment']);
            $mt = SPODAGORA_CLASS_Tools::getInstance()->get_mention($_REQUEST['comment']);

            // Change \n to <br> for correct visualization of new line in HTML
            $comment  = str_replace("\n", "<br/>", htmlentities($_REQUEST['comment']));
            $comment .= $_REQUEST["preview"];

            $c = SPODAGORA_BOL_Service::getInstance()->addComment($_REQUEST['entityId'],
                $_REQUEST['parentId'],
                OW::getUser()->getId(),
                $comment,
                $_REQUEST['level'],
                $_REQUEST['sentiment'],
                $ht);

            //Increment the comments number
            SPODAGORA_BOL_Service::getInstance()->addAgoraRoomStat($_REQUEST['entityId'], 'comments');

            $this->send_realtime_notification($c);

            /* ODE */
            if( ODE_CLASS_Helper::validateDatalet($_REQUEST['datalet']['component'], $_REQUEST['datalet']['params'], $_REQUEST['datalet']['fields']) )
            {
                //Increments the opendata number
                SPODAGORA_BOL_Service::getInstance()->addAgoraRoomStat($_REQUEST['entityId'], 'opendata');
                //Add a datalet node in the datalet graph
                SPODAGORA_BOL_Service::getInstance()->addAgoraDataletNode($_REQUEST['datalet'], $_REQUEST['comment'], $c->getId(), $_REQUEST['parentId'], $_REQUEST['entityId']);

                ODE_BOL_Service::getInstance()->addDatalet(
                    $_REQUEST['datalet']['component'],
                    $_REQUEST['datalet']['fields'],
                    OW::getUser()->getId(),
                    $_REQUEST['datalet']['params'],
                    $c->getId(),
                    $_REQUEST['plugin'],
                    $_REQUEST['datalet']['data']);
            }
            /* ODE */

            // SEND EMAIL TO SUBSCRIBED USERS
            //SPODAGORA_CLASS_MailNotification::getInstance()->sendEmailNotificationOnComment($_REQUEST['entityId'], $c->ownerId);
            // SEND EMAIL NOTIFICATION TO MENTIONED USERS
            //SPODAGORA_CLASS_MailNotification::getInstance()->sendEmailNotificationOnMention($_REQUEST['entityId'], $c->ownerId, $mt);

            if (!empty($c->id))
                echo '{"result":"ok", "post_id":"'.$c->id.'"}';
            else
                echo '{"result":"ko"}';
        }
        else
        {
            echo '{"result":"ko"}';
        }

        exit;
    }

    public function deleteComment()
    {
        if(OW::getUser()->isAdmin())
        {
            $comment = SPODAGORA_BOL_Service::getInstance()->getCommentById($_REQUEST['commentId']);

            SPODAGORA_BOL_Service::getInstance()->deleteComment($comment);
            echo '{"result":"ok", "comment_id":"' . $_REQUEST['commentId'] . '"}';
        }
        else
            echo '{"result":"ko"}';

        exit;
    }

    public function deleteUserComment()
    {
        $comment = SPODAGORA_BOL_Service::getInstance()->getCommentById($_REQUEST['commentId']);

        if(OW::getUser()->getId() == $comment->ownerId)
        {
            SPODAGORA_BOL_Service::getInstance()->deleteComment($comment);
            echo '{"result":"ok", "comment_id":"' . $_REQUEST['commentId'] . '"}';
        }
        else
            echo '{"result":"ko", "error":"it is not your comment"}';

        exit;
    }

    public function editUserComment()
    {
        $comment = SPODAGORA_BOL_Service::getInstance()->getCommentById($_REQUEST['commentId']);

        //Get hashtag
        $ht = SPODAGORA_CLASS_Tools::getInstance()->get_hashtag($_REQUEST['comment']);

        // Change \n to <br> for correct visualization of new line in HTML
        $comment_txt  = str_replace("\n", "<br/>", htmlentities($_REQUEST['comment']));

        if(OW::getUser()->getId() == $comment->ownerId)
        {
            SPODAGORA_BOL_Service::getInstance()->editComment($comment, $comment_txt, $ht);
            echo '{"result":"ok", "comment_id":"' . $_REQUEST['commentId'] . '"}';
        }
        else
            echo '{"result":"ko", "error":"it is not your comment"}';

        exit;
    }

    public function addAgoraRoom()
    {
        $id = SPODAGORA_BOL_Service::getInstance()->addAgoraRoom(OW::getUser()->getId(),
            $_REQUEST['subject'],
            $_REQUEST['body']);

        $html_cmp = new SPODAGORA_CMP_AgoraAddRoom($id, $_REQUEST['subject'], $_REQUEST['body']);
        $html = $html_cmp->render();

        echo json_encode(array("status"  => "ok",
            "id"      => $id,
            "subject" => $_REQUEST['subject'],
            "body"    => $_REQUEST['body'],
            "html"    => $html));
        exit;
    }

    public function addAgoraRoomSuggestion(){
        $id = SPODAGORA_BOL_Service::getInstance()->addAgoraRoomSuggestion($_REQUEST['room_id'],
            $_REQUEST['dataset'],
            $_REQUEST['comment']);

        echo json_encode(array("status"  => "ok",
            "id"         => $id,
            "room_id"    => $_REQUEST['room_id'],
            "dataset"    => $_REQUEST['dataset'],
            "comment"    => $_REQUEST['comment']));
        exit;
    }

    //Reader
    public function getNestedComment()
    {
        $nc = new SPODAGORA_CMP_NestedComment($_REQUEST['entity_id'],
                                              $_REQUEST['parent_id'],
                                              $_REQUEST['level']);
        echo $nc->render();

        exit;
    }

    public function getSearchResult(){
        $sr = new SPODAGORA_CMP_SearchResult($_REQUEST['entity_id'],
                                             $_REQUEST['search_string'],
                                             $_REQUEST['user_id']);

        echo $sr->render();
        exit;
    }

    public function getSiteMetaTags()
    {
        $list = array();
        try {

            if(empty($_REQUEST["url"]))
            {
                echo json_encode($list);
                exit;
            }

            $html = file_get_contents($_REQUEST["url"]);

            @libxml_use_internal_errors(true);
            $dom = new DomDocument();
            $dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            $query = '//*/meta[starts-with(@property, \'og:\')]';
            $result = $xpath->query($query);

            foreach ($result as $meta) {
                $property = $meta->getAttribute('property');
                $content = $meta->getAttribute('content');
                $property = str_replace('og:', '', $property);
                $list[$property] = $content;
            }

            if (!$list["title"]) {
                $node = $xpath->query('//title');
                if($node->length > 0)
                    $list["title"] = $node->item(0)->nodeValue;
            }
            if (!$list["description"]) {
                $node = $xpath->query('//p');
                if($node->length > 0)
                    $list["description"] = $node->item(0)->nodeValue;
            }
            if (!$list["image"]) {
                $node = $xpath->query('//img');
                if($node->length > 0)
                    $list["image"] = $node->item(0)->getAttribute('src');
            }
            if (!$list["url"]) {
                $list["url"] = $_REQUEST["url"];
            }

            if (!$list["site_name"]) {
                $parse = parse_url($_REQUEST["url"]);
                $list["site_name"] = $parse['host'];
            }

        }catch (Exception $e){}
        finally {
            echo json_encode($list);
            exit;
        }
    }

    public function handleUserNotification()
    {
        if($_REQUEST['addUserNotification'] == "true")
            SPODAGORA_BOL_Service::getInstance()->addUserNotification($_REQUEST['roomId'], OW::getUser()->getId());
        else
            SPODAGORA_BOL_Service::getInstance()->removeUserNotification($_REQUEST['roomId'], OW::getUser()->getId());

        echo json_encode(array("status"  => "ok"));
        exit;
    }

    public function getCommentPage()
    {
        $cp = new SPODAGORA_CMP_AgoraMainComment($_REQUEST['entity_id'],
                                                 $_REQUEST['last_comment_id']);

        echo $cp->render();
        exit;
    }

    public function getMissingComment()
    {
        $cp = new SPODAGORA_CMP_AgoraMainComment($_REQUEST['entity_id'],
                                                 $_REQUEST['last_comment_id'],
                                                 $_REQUEST['comment_id']);

        echo $cp->render();
        exit;
    }

    //Realtime
    private function send_realtime_notification($comment)
    {
        try
        {
            $client = new Client(new Version1X('http://localhost:3000'));
            $client->initialize();

            $client->emit('realtime_notification',
                ['plugin' => 'agora',
                'room_id' => $_REQUEST['entityId'],
                'comment' => $comment->comment,
                'message_id' => $comment->id,
                'parent_id' => $_REQUEST['parentId'],
                'user_id' => OW::getUser()->getId(),
                'user_display_name' => $_REQUEST['username'],
                'user_avatar' => $_REQUEST['user_avatar_src'],
                'user_avatar_css' => $_REQUEST['user_avatar_css'],
                'user_avatar_initial' => $_REQUEST['user_avatar_initial'],
                'user_url' => $_REQUEST['user_url'],
                'comment_level' => $_REQUEST['level'],
                'sentiment' => $_REQUEST['sentiment'],
                'component' => $_REQUEST['datalet']['component'],
                'params' => $_REQUEST['datalet']['params'],
                'fields' => $_REQUEST['datalet']['fields']]);

            $client->close();
        }
        catch(Exception $e)
        {}
    }



}
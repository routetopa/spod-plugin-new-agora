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

        if($this->check_value(array("entityId", "parentId", "comment", "level", "sentiment")))
        {
            // Change \n to <br> for correct visualization of new line in HTML
            $comment  = str_replace("\n", "<br/>", strip_tags($_REQUEST['comment']));
            $comment .= $_REQUEST["preview"];

            $c = SPODAGORA_BOL_Service::getInstance()->addComment($_REQUEST['entityId'],
                $_REQUEST['parentId'],
                OW::getUser()->getId(),
                $comment,
                $_REQUEST['level'],
                $_REQUEST['sentiment']);

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
            SPODAGORA_CLASS_MailNotification::getInstance()->sendEmailNotificationProcess($_REQUEST['entityId']);

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

    public function addAgoraRoom()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        $id = SPODAGORA_BOL_Service::getInstance()->addAgoraRoom(OW::getUser()->getId(),
            $clean['subject'],
            $clean['body']);

        echo json_encode(array("status"  => "ok",
            "id"      => $id,
            "subject" => $clean['subject'],
            "body"    => $clean['body']));
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
        }catch (Exception $e){}
        finally {
            echo json_encode($list);
            exit;
        }

    }

    //Realtime
    public function handleUserNotification()
    {
        if($_REQUEST['addUserNotification'] == "true")
            SPODAGORA_BOL_Service::getInstance()->addUserNotification($_REQUEST['roomId'], OW::getUser()->getId());
        else
            SPODAGORA_BOL_Service::getInstance()->removeUserNotification($_REQUEST['roomId'], OW::getUser()->getId());

        echo json_encode(array("status"  => "ok"));
        exit;
    }

    private function send_realtime_notification($comment)
    {
        try
        {
            $client = new Client(new Version1X('http://localhost:3000'));
            $client->initialize();

            $client->emit('realtime_notification',
                ['plugin' => 'spodpublic',
                'room_id' => $_REQUEST['entityId'],
                'comment' => $comment->comment,
                'message_id' => $comment->id,
                'parent_id' => $_REQUEST['parentId'],
                'user_id' => OW::getUser()->getId(),
                'user_display_name' => $_REQUEST['username'],
                'user_avatar' => $_REQUEST['user_avatar_src'],
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

    //Utils
    private function check_value($params)
    {
        foreach ($params as $var)
        {
            if(!isset($_REQUEST[$var]))
                return false;
        }

        return true;
    }



}
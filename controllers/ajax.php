<?php

require_once OW::getPluginManager()->getPlugin('spodnotification')->getRootDir()
    . 'lib/vendor/autoload.php';

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

class SPODAGORA_CTRL_Ajax extends OW_ActionController
{
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
            $_REQUEST['comment'] = str_replace("\n", "<br/>", $_REQUEST['comment']);

            $c = SPODAGORA_BOL_Service::getInstance()->addComment($_REQUEST['entityId'],
                                                                  $_REQUEST['parentId'],
                                                                  OW::getUser()->getId(),
                                                                  $_REQUEST['comment'],
                                                                  $_REQUEST['level'],
                                                                  $_REQUEST['sentiment']);

            $this->send_realtime_notification($c);


            /* ODE */
            if( ODE_CLASS_Helper::validateDatalet($_REQUEST['datalet']['component'], $_REQUEST['datalet']['params'], $_REQUEST['datalet']['fields']) )
            {
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

    private function send_realtime_notification($comment)
    {
        try
        {
            $client = new Client(new Version1X('http://localhost:3000'));
            $client->initialize();

            $client->emit('realtime_notification',
                ['plugin' => 'spodpublic',
                'room_id' => $_REQUEST['entityId'],
                'comment' => $_REQUEST['comment'],
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

    private function check_value($params)
    {
        foreach ($params as $var)
        {
            if(!isset($_REQUEST[$var]))
                return false;
        }

        return true;
    }

    /*private function check_empty_value(...$params)
    {
        foreach ($params as $var)
        {
            if(empty($var))
                return false;
        }

        return true;
    }*/
}
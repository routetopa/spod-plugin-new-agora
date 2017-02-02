<?php

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
            $c = SPODAGORA_BOL_Service::getInstance()->addComment($_REQUEST['entityId'],
                                                                  $_REQUEST['parentId'],
                                                                  OW::getUser()->getId(),
                                                                  $_REQUEST['comment'],
                                                                  $_REQUEST['level'],
                                                                  $_REQUEST['sentiment']);

            if (!empty($c->id))
                echo '{"result":"ok"}';
            else
                echo '{"result":"ko"}';
        }
        else
        {
            echo '{"result":"ko"}';
        }

        exit;
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
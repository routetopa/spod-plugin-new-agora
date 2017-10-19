<?php
class SPODAGORA_CMP_AgoraLogIn extends OW_Component
{
    public function __construct()
    {
        $this->assign('url', OW_URL_HOME . 'oauth2/login');
    }
}
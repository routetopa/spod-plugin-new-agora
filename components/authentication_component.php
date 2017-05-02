<?php

class SPODAGORA_CMP_AuthenticationComponent extends BASE_CMP_Comments
{
    public function __construct()
    {
        $router = OW_Router::getInstance();
        $base_url = $router->getBaseUrl();

        $this->assign("url_password_reset", "{$base_url}openid/index.php/password_reset");

        $room_url = urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

        if ($plugin_oic = $router->getRoute('openidconnect_login')) {
            $this->assign("openid_enabled", "enabled");
            $this->assign("url_redirect_success", $base_url . $plugin_oic->generateUri() .'?back-uri=' . $room_url);
            $this->assign("url_redirect_failure", "{$base_url}openid/index.php/password_reset");
            $this->assign("url_openid_login", "{$base_url}openid/index.php/login");
            $this->assign("url_openid_signup", "{$base_url}openid/index.php/signin");
        } else {
            $this->assign("openid_enabled", "disabled");
        }

        if ( OW::getPluginManager()->isPluginActive("fbconnect") ) {
            $this->assign("fbconnect_enabled", "enabled");
        } else {
            $this->assign("fbconnect_enabled", "disabled");
        }
    }
}
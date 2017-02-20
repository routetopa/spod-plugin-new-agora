<?php

class SPODAGORA_CTRL_AgoraMain extends OW_ActionController
{
    private $field;

    public function index()
    {
        //Check if user can view this page
        /*$preference = BOL_PreferenceService::getInstance()->findPreference('agora_is_visible_not_logged');
        $is_visible_pref = empty($preference) ? "false" : $preference->defaultValue;

        if ( !$is_visible_pref && !OW::getUser()->isAuthenticated())
        {
            throw new AuthenticateException();
        }
        else
        {
            if(!OW::getUser()->isAuthenticated() && OW::getPluginManager()->isPluginActive('openidconnect'))
            {
                $this->addComponent('authentication_component', new SPODPUBLIC_CMP_AuthenticationComponent());
            }
        }*/

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticUrl() . 'css/agora_main.css');
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/general.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'masonry.pkgd.min.js', 'text/javascript');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_main.js', 'text/javascript');

        $agora = SPODAGORA_BOL_Service::getInstance()->getAgora();
        $timeSortedAgora = $agora;

        $max = $this->getMaxAndSetUsername($agora);

        $this->field = "opendata";
        usort($agora, array($this, "compare"));
        $this->assign('openDataMedian', $this->median($agora, 'opendata'));

        $this->field = "comments";
        usort($agora, array($this, "compare"));
        $this->assign('commentsMedian', $this->median($agora, 'comments'));

        $this->assign('components_url', SPODPR_COMPONENTS_URL);
        $this->assign('rooms', $this->setColor($timeSortedAgora, $max));

        $this->assign('userPublicRooms', SPODAGORA_BOL_Service::getInstance()->getAgoraByOwner(OW::getUser()->getId()));

        $this->addComponent('main_menu', new BASE_CMP_MainMenu());
        $this->addComponent('console', new BASE_CMP_Console());

        $this->assign('isCreationAuthorizated', OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), 'spodpublic', 'create_room'));

        $this->assign('ow_url_home', OW_URL_HOME);

        $js = UTIL_JsGenerator::composeJsString('ODE.currentUsername = {$current_username}',
            array('current_username' => BOL_UserService::getInstance()->getUserName(OW::getUser()->getId())));
        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript('AGORAMAIN.init();');
    }

    private function setColor($a, $max)
    {
        $max = $max == 0 ? 1 : $max;

        foreach($a as $item)
        {
            $step = floor(((100*$item->views)/$max)/5);
            $item->color      = $this->getColor($step);
            $item->colorStep  = ($step > 19) ? "many" : "few";
            $item->timestamp  = date('j F Y H:i',strtotime($item->timestamp));
        }

        return $a;
    }

    private function median($a, $value)
    {
        $count = count($a);

        if ($count == 0) return;

        if ($count%2) {
            return $a[(($count+1)/2) - 1]->$value;
        } else {
            return ($a[$count/2]->$value + $a[$count/2-1]->$value) / 2;
        }
    }

    private function compare($a, $b)
    {
        $field = $this->field;

        if($a->$field == $b->$field) return 0;
        return ($a->$field < $b->$field) ? -1:1;
    }

    private function getMaxAndSetUsername($a)
    {
        $max = 0;
        foreach($a as $item)
        {
            $item->owner = BOL_UserService::getInstance()->getUserName($item->ownerId);

            if($item->views > $max)
                $max = $item->views;
        }

        return $max;
    }

    private function getColor($step)
    {
        //$color = array('2C29FF','2E29FC','3029FA','3229F8','342AF6','362AF4','382AF2','3A2AF0','3C2BEE','3E2BEC','402BEA','422BE8','442CE6','462CE4','482CE2','4A2CE0','4C2DDE','4E2DDC','502DDA','522DD8','542ED6','562ED4','582ED2','5A2ED0','5C2FCE','5E2FCC','602FCA','6230C8','6430C6','6630C4','6830C2','6A31C0','6C31BE','6E31BC','7031BA','7232B8','7432B6','7632B4','7832B2','7A33B0','7C33AE','7E33AC','8033AA','8234A8','8434A6','8634A4','8834A2','8A35A0','8C359E','8E359C','90369A','923698','943696','963694','983792','9A3790','9C378E','9E378C','A0388A','A23888','A43886','A63884','A83982','AA3980','AC397E','AE397C','B03A7A','B23A78','B43A76','B63A74','B83B72','BA3B70','BC3B6E','BE3B6C','C03C6A','C23C68','C43C66','C63D64','C83D62','CA3D60','CC3D5E','CE3E5C','D03E5A','D23E58','D43E56','D63F54','D83F52','DA3F50','DC3F4E','DE404C','E0404A','E24048','E44046','E64144','E84142','EA4140','EC413E','EE423C','F0423A','F24238','F44336');
        $color = array('0D47A1', '1565C0', '1976D2', '1E88E5', '2196F3', '42A5F5', '64B5F6', '90CAF9', 'BBDEFB', 'E3F2FD', 'FFEBEE', 'FFCDD2', 'EF9A9A', 'E57373', 'EF5350', 'F44336', 'E53935', 'D32F2F', 'C62828', 'B71C1C');
        return ($step == count($color)) ? $color[$step-1] : $color[$step];
    }

}
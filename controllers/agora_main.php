<?php

class SPODAGORA_CTRL_AgoraMain extends OW_ActionController
{
    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/main.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_main.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_main_new.css');

        $raw_agora = SPODAGORA_BOL_Service::getInstance()->getAgora();
        $this->assign('agoras', $this->process_agora($raw_agora));

        $this->initializeJS();
    }

    private function initializeJS()
    {
        OW::getDocument()->addOnloadScript('AGORAMAIN.init();');
    }

    private function process_agora($agoras)
    {
        $users_id = array_unique(array_map(function($agoras) { return $agoras->ownerId;}, $agoras));
        $avatars  = BOL_AvatarService::getInstance()->getDataForUserAvatars($users_id);

        //maxView, maxComments, maxOpendata
        $maxStat = SPODAGORA_BOL_Service::getInstance()->getMaxAgoraStat();

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($agoras as &$agora)
        {
            $agora->stat = array("views" => ($agora->views*100/$maxStat["maxView"]), "comments" => ($agora->comments*100/$maxStat["maxComments"]), "opendata" => ($agora->opendata*100/$maxStat["maxOpendata"]));
            $agora->timestamp = $this->process_timestamp($agora->timestamp, $today, $yesterday);
            $agora->avatar = $avatars[$agora->ownerId];
        }

        return $agoras;
    }

    private function process_timestamp($timestamp, $today, $yesterday)
    {
        $date = date('Ymd', strtotime($timestamp));

        if($date == $today)
            return date('H:i', strtotime($timestamp));

        if($date == $yesterday)
            return "yesterday " . date('H:i', strtotime($timestamp));

        return date('H:i m/d', strtotime($timestamp));
    }
}
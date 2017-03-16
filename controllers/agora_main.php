<?php

class SPODAGORA_CTRL_AgoraMain extends OW_ActionController
{

    private $COLORS = array("#FFD180", "#FFAB40", "#FF9100", "#FF6D00");

    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/main.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_main.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_main_new.css');

        OW::getLanguage()->addKeyForJs('spodagora', 'just_now');

        $raw_agora = SPODAGORA_BOL_Service::getInstance()->getAgora();
        $this->assign('agoras', $this->process_agora($raw_agora));

        $this->initializeJS($raw_agora[0]);
    }

    private function initializeJS($first_agora)
    {
        OW::getDocument()->addOnloadScript('AGORAMAIN.init('.$first_agora->id.');');
    }

    private function process_agora($agoras)
    {
        //maxView, maxComments, maxOpendata
        $maxStat = SPODAGORA_BOL_Service::getInstance()->getMaxAgoraStat();

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($agoras as &$agora)
        {
            $comments = SPODAGORA_BOL_Service::getInstance()->getAllLevesCommentsFromAgoraId($agora->id);
            $users_id = array_diff(array_unique(array_map(function($comments) { return $comments->ownerId; }, $comments)), [$agora->ownerId]);
            $avatars  = BOL_AvatarService::getInstance()->getDataForUserAvatars($users_id);


            $views_prctg    = ($agora->views*100/$maxStat["maxView"]);
            $comments_prctg = ($agora->comments*100/$maxStat["maxComments"]);
            $opendata_prctg = ($agora->opendata*100/$maxStat["maxOpendata"]);
            $agora->stat = array("views" => $views_prctg, "viewsColor" => $this->COLORS[(int)($views_prctg/25.1)],
                                 "comments" => $comments_prctg, "commentsColor" => $this->COLORS[(int)($comments_prctg/25.1)],
                                 "opendata" => $opendata_prctg, "opendataColor" => $this->COLORS[(int)($opendata_prctg/25.1)]);
            $agora->timestamp = $this->process_timestamp($agora->timestamp, $today, $yesterday);
            $agora->avatars = $avatars;
            $agora->owner_avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($agora->ownerId));
            $agora->suggestions = SPODAGORA_BOL_Service::getInstance()->getAgoraSuggestedDataset($agora->id);
        }

        return $agoras;
    }

    private function process_timestamp($timestamp, $today, $yesterday)
    {
        $date = date('Ymd', strtotime($timestamp));

        if($date == $today)
            return date('H:i', strtotime($timestamp));

        if($date == $yesterday)
            return OW::getLanguage()->text('spodagora', 'yesterday'). " " . date('H:i', strtotime($timestamp));

        return date('H:i m/d', strtotime($timestamp));
    }
}
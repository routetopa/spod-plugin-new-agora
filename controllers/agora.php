<?php

class SPODAGORA_CTRL_Agora extends OW_ActionController
{

    private $agora;
    private $userId;
    private $avatars;
    private $agoraId;
    private $users_id;

    public function index(array $params)
    {
        //Check if user can view this page
        $preference = BOL_PreferenceService::getInstance()->findPreference('agora_is_visible_not_logged');
        $is_visible_pref = empty($preference) ? "false" : $preference->defaultValue;

        if ( !$is_visible_pref && !OW::getUser()->isAuthenticated())
        {
            throw new AuthenticateException();
        }
        else
        {
            if(!OW::getUser()->isAuthenticated() && OW::getPluginManager()->isPluginActive('openidconnect'))
            {
                $this->addComponent('authentication_component', new SPODAGORA_CMP_AuthenticationComponent());
            }
        }

        $this->agoraId = $params['agora_id'];
        $this->agora = SPODAGORA_BOL_Service::getInstance()->getAgoraById($this->agoraId);
        $this->userId = OW::getUser()->getId();

        // AVATARS
        $all_level_comments = SPODAGORA_BOL_Service::getInstance()->getAllLevesCommentsFromAgoraId($this->agoraId);
        //$this->users_id = array_unique($this->array_push_return(array_map(function($comments) { return $comments->ownerId; }, $all_level_comments), $this->agora->ownerId) );
        $this->users_id = array_unique(array_merge(array_map(function($comments) { return $comments->ownerId; }, $all_level_comments), [$this->agora->ownerId, $this->userId]));

        $this->avatars  = SPODAGORA_CLASS_Tools::getInstance()->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars($this->users_id));
        $this->assign('avatars', $this->avatars);

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/main.html');

        OW::getDocument()->setTitle($this->agora->subject);
        OW::getDocument()->setDescription($this->agora->body);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_room.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agoraJs.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'autogrow.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'socket_1_7_3.io.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'jquery.cssemoticons.min.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'jquery.cssemoticons.css');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_room.css');

        OW::getDocument()->addScript('https://d3js.org/d3.v4.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'd3-tip.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'liquidFillGauge.js');

        if(OW::getUser()->isAdmin())
        {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_admin.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_admin.css');
        }

        OW::getLanguage()->addKeyForJs('spodagora', 'c_just_now');
        OW::getLanguage()->addKeyForJs('spodagora', 'c_reply');
        OW::getLanguage()->addKeyForJs('spodagora', 't_delete');
        OW::getLanguage()->addKeyForJs('spodagora', 't_modify');
        OW::getLanguage()->addKeyForJs('spodagora', 'g_datalets');
        OW::getLanguage()->addKeyForJs('spodagora', 'g_datasets');
        OW::getLanguage()->addKeyForJs('spodagora', 'g_time');

        SPODAGORA_BOL_Service::getInstance()->addAgoraRoomStat($this->agoraId, 'views');
        $raw_comments = SPODAGORA_BOL_Service::getInstance()->getCommentList($this->agoraId);
        $this->assign('comments', $this->process_comment($raw_comments));

        $raw_unread_comments = SPODAGORA_BOL_Service::getInstance()->getUnreadComment($this->agoraId, $this->userId);
        $this->assign('unread_comments_count', count($raw_unread_comments));
        $this->assign('unread_comments', $this->process_unread_comment($raw_unread_comments));

        $notification = SPODAGORA_BOL_Service::getInstance()->getUserNotification($this->agoraId, OW::getUser()->getId());
        $this->assign('user_notification', empty($notification) ? '' : 'checked');

        // AGORA
        $this->assign('agora', $this->agora);

        // IS AUTH
        $this->assign('isAuth', OW::getUser()->isAuthenticated());

        $this->initializeJS();
    }

    private function process_unread_comment($unread_commnets)
    {
        $max_day        = 7;
        $today          = date_create('today');
        $unread_section = array();

        for($i = 0; $i < $max_day; $i++)
            $unread_section[OW::getLanguage()->text('spodagora', date_create('today - '.$i.' day')->format('l'))] = array();

        foreach ($unread_commnets as &$comment)
        {
            if(date_diff($today, date_create($comment->timestamp))->d > 7)
                break;

            $comment->comment         = strip_tags($comment->comment);
            $comment->username        = $this->avatars[$comment->ownerId]["title"];
            $comment->owner_url       = $this->avatars[$comment->ownerId]["url"];
            $comment->avatar_url      = $this->avatars[$comment->ownerId]["src"];
            $comment->avatar_css     = $this->avatars[$comment->ownerId]["css"];
            $comment->avatar_initial = $this->avatars[$comment->ownerId]["initial"];
            $section                  = OW::getLanguage()->text('spodagora', date('l', strtotime($comment->timestamp)));
            $comment->timestamp       = date('H:i', strtotime($comment->timestamp));
            $comment->sentiment_class = $comment->sentiment == 0 ? 'neutral' : ($comment->sentiment == 1 ? 'satisfied' : 'dissatisfied');

            array_push($unread_section[$section], $comment);
        }

        return $unread_section;
    }

    private function initializeJS()
    {
        $avatars          = $this->avatars;
        $sentiments       = SPODAGORA_BOL_Service::getInstance()->getRoomSentiments($this->agoraId);
        $sentiments_count = ( (empty($sentiments[0]['tot']) ? $sentiments[0]['tot'] = 0 : $sentiments[0]['tot'])  +
                              (empty($sentiments[1]['tot']) ? $sentiments[1]['tot'] = 0 : $sentiments[1]['tot'])  +
                              (empty($sentiments[2]['tot']) ? $sentiments[2]['tot'] = 0 : $sentiments[2]['tot']) );

        if(empty($avatars[$this->userId]))
        {
            $avatars = SPODAGORA_CLASS_Tools::getInstance()->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars(array($this->userId)));
        }

        $js = UTIL_JsGenerator::composeJsString('
            AGORA.roomId = {$roomId}
            AGORA.agora_comment_endpoint = {$agora_comment_endpoint}
            AGORA.agora_static_resource_url = {$agora_static_resource_url}
            AGORA.username = {$username}
            AGORA.user_url = {$user_url}
            AGORA.user_avatar_src = {$user_avatar_src}
            AGORA.user_avatar_css = {$user_avatar_css}
            AGORA.user_avatar_initial = {$user_avatar_initial}
            AGORA.user_id = {$user_id}
            AGORA.agora_nested_comment_endpoint = {$agora_nested_comment_endpoint}
            AGORA.user_notification_url = {$user_notification_url} 
            AGORA.datalet_graph = {$datalet_graph}
            AGORA.sat_prctg = {$sat_prctg}
            AGORA.unsat_prctg = {$unsat_prctg}
            AGORA.search_url = {$search_url}
            AGORA.user_friendship = {$user_friendship}
            AGORA.users_avatar = {$users_avatar}
            AGORA.get_site_tag_endpoint = {$get_site_tag_endpoint}
            AGORA.delete_user_comment_endpoint = {$delete_user_comment_endpoint}
            AGORA.edit_user_comment_endpoint = {$edit_user_comment_endpoint}
         ', array(
            'roomId' => $this->agoraId,
            'agora_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'addComment'),
            'agora_static_resource_url' =>  OW::getPluginManager()->getPlugin('spodagora')->getStaticUrl(),
            'username' => $avatars[$this->userId]["title"],
            'user_url' => $avatars[$this->userId]["url"],
            'user_avatar_src' => $avatars[$this->userId]["src"],
            'user_avatar_css' => $avatars[$this->userId]["css"],
            'user_avatar_initial' => $avatars[$this->userId]["initial"],
            'user_id' => $this->userId,
            'agora_nested_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'getNestedComment'),
            'user_notification_url' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'handleUserNotification'),
            'datalet_graph' => $this->agora->datalet_graph,
            'sat_prctg' => ($sentiments[1]['tot']*100)/($sentiments_count == 0 ? 1 : $sentiments_count),
            'unsat_prctg' => ($sentiments[2]['tot']*100)/($sentiments_count == 0 ? 1 : $sentiments_count),
            'search_url' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'getSearchResult'),
            'user_friendship' => SPODAGORA_BOL_Service::getInstance()->getAgoraFriendship($this->users_id),
            'users_avatar' => $this->avatars,
            'get_site_tag_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'getSiteMetaTags'),
            'delete_user_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'deleteUserComment'),
            'edit_user_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'editUserComment')
        ));

        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript('AGORA.init();');

        if(OW::getUser()->isAdmin())
        {
            $js = UTIL_JsGenerator::composeJsString('
            AGORA.delete_comment_endpoint = {$delete_comment_endpoint}
            ',array(
                'delete_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'deleteComment')
            ));

            OW::getDocument()->addOnloadScript($js);
        }
    }

    private function process_comment(&$comments)
    {
        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username       = $this->avatars[$comment->ownerId]["title"];
            $comment->owner_url      = $this->avatars[$comment->ownerId]["url"];
            $comment->avatar_url     = $this->avatars[$comment->ownerId]["src"];
            $comment->avatar_css     = $this->avatars[$comment->ownerId]["css"];
            $comment->avatar_initial = $this->avatars[$comment->ownerId]["initial"];
            $comment->total_comment  = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp      = SPODAGORA_CLASS_Tools::getInstance()->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class      = $this->userId == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';

            switch ($comment->sentiment)
            {
                case 0 : $comment->sentiment_class = 'neutral'; break;
                case 1 : $comment->sentiment_class = 'satisfied'; break;
                case 2 : $comment->sentiment_class = 'dissatisfied'; break;
            }

            if (!empty($comment->component)) {
                $comment->datalet_class  = 'agora_fullsize_datalet';
                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'. $comment->component . '",
                                                                    ' . $comment->params . ',
                                                                    ['. $comment->fields . '],
                                                                    undefined,
                                                                    "agora_datalet_placeholder_' . $comment->id . '");');
            }

        }

        return $comments;
    }

}
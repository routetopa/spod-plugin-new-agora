<?php

class SPODAGORA_CTRL_Agora extends OW_ActionController
{
    public function index()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/empty.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_room.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agoraJs.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'autogrow.min.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_room.css');


        $raw_comments = SPODAGORA_BOL_Service::getInstance()->getCommentList(0);
        $this->assign('comments', $this->process_comment($raw_comments));

        $this->initializeJS();
    }

    private function initializeJS()
    {
        $user_id = OW::getUser()->getId();
        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user_id));

        $js = UTIL_JsGenerator::composeJsString('
            AGORA.roomId = {$roomId}
            AGORA.agora_comment_endpoint = {$agora_comment_endpoint}
            AGORA.agora_static_resource_url = {$agora_static_resource_url}
            AGORA.username = {$username}
            AGORA.user_url = {$user_url}
            AGORA.user_avatar_src = {$user_avatar_src}
         ', array(
            'roomId' => 0,
            'agora_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'addComment'),
            'agora_static_resource_url' =>  OW::getPluginManager()->getPlugin('spodagora')->getStaticUrl(),
            'username' => $avatar[$user_id]["title"],
            'user_url' => $avatar[$user_id]["url"],
            'user_avatar_src' => $avatar[$user_id]["src"]
        ));

        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript('AGORA.init();');
    }

    private function process_comment(&$comments)
    {
        $users_ids = array_map(function($comments) { return $comments->ownerId;}, $comments);
        $user_id   = OW::getUser()->getId();
        $avatars   = BOL_AvatarService::getInstance()->getDataForUserAvatars($users_ids);
        $this->assign('avatars', $avatars);

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username      = $avatars[$comment->ownerId]["title"];
            $comment->owner_url     = $avatars[$comment->ownerId]["url"];
            $comment->avatar_url    = $avatars[$comment->ownerId]["src"];
            $comment->total_comment = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp     = $this->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class     = $user_id == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';
        }

        return $comments;
    }

    private function process_timestamp($timestamp, $today, $yesterday)
    {
        $date = date('Ymd', strtotime($timestamp));

        if($date == $today)
            return date('H:i', strtotime($timestamp));

        if($date == $yesterday)
            return "ieri " . date('H:i', strtotime($timestamp));

        return date('H:i m/d', strtotime($timestamp));
    }

}
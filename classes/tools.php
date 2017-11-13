<?php

class SPODAGORA_CLASS_Tools extends OW_Component
{
    private $avatar_colors = ['avatar_pink', 'avatar_purple', 'avatar_deeppurple', 'avatar_indigo',
                                 'avatar_lightblue', 'avatar_teal', 'avatar_lightgreen', 'avatar_lime',
                                 'avatar_yellow', 'avatar_amber', 'avatar_deeporange',
                                 'avatar_brown', 'avatar_grey', 'avatar_bluegrey'];

    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
            self::$classInstance = new self();

        return self::$classInstance;
    }

    public function process_timestamp($timestamp, $today, $yesterday)
    {
        $date = date('Ymd', strtotime($timestamp));

        if($date == $today)
            return date('H:i', strtotime($timestamp));

        if($date == $yesterday)
            return OW::getLanguage()->text('spodagora', 'yesterday'). " " . date('H:i', strtotime($timestamp));

        return date('H:i m/d', strtotime($timestamp));
    }

    public function array_push_return($array, $val)
    {
        array_push($array, $val);
        return $array;
    }

    public function check_value($params)
    {
        foreach ($params as $var)
        {
            if(!isset($_REQUEST[$var]))
                return false;
        }

        return true;
    }

    public function get_hashtag($str)
    {
        preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $str, $matches);
        return array_unique($matches[2]);
    }

    public function get_mention($str)
    {
        preg_match_all('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', $str, $matches);
        return array_unique($matches[2]);
    }

    public function process_avatar($avatars)
    {
        if (empty($avatars))
            return;

        foreach ($avatars as &$avatar)
        {
            if(strpos( $avatar['src'], 'no-avatar'))
            {
                $avatar['css'] = 'no_img ' . $this->avatar_colors[$avatar["userId"] % count($this->avatar_colors)];
                $avatar['initial'] = strtoupper($avatar['title'][0]);
                $avatar['src'] = '';
            }
            else
            {
                $avatar['css'] = '';
                $avatar['initial'] = '';
            }

        }

        return $avatars;
    }

    public function process_comment(&$comments, $userId)
    {
        $users_ids      = array_map(function($comments) { return $comments->ownerId;}, $comments);
        $avatars        = $this->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars($users_ids));

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username       = $avatars[$comment->ownerId]["title"];
            $comment->owner_url      = $avatars[$comment->ownerId]["url"];
            $comment->avatar_url     = $avatars[$comment->ownerId]["src"];
            $comment->avatar_css     = $avatars[$comment->ownerId]["css"];
            $comment->avatar_initial = $avatars[$comment->ownerId]["initial"];
            $comment->total_comment  = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp      = SPODAGORA_CLASS_Tools::getInstance()->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class      = $userId == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';

            switch ($comment->sentiment)
            {
                case 0 : $comment->sentiment_class = 'neutral'; break;
                case 1 : $comment->sentiment_class = 'satisfied'; break;
                case 2 : $comment->sentiment_class = 'dissatisfied'; break;
            }

            if (!empty($comment->component)) {
                $comment->datalet_class  = 'agora_fullsize_datalet';

                $comment->data = empty($comment->data) ? "''" : $comment->data;

                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'. $comment->component . '",
                                                                    ' . $comment->params . ',
                                                                    ['. $comment->fields . '],
                                                                    ' . $comment->data . ',
                                                                    "agora_datalet_placeholder_' . $comment->id . '");');
            }

        }

        return $comments;
    }

    public function process_comment_include_datalet(&$comments, $user_id)
    {
        $users_ids      = array_map(function($comments) { return $comments->ownerId;}, $comments);
        $avatars        = $this->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars($users_ids));

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username       = $avatars[$comment->ownerId]["title"];
            $comment->owner_url      = $avatars[$comment->ownerId]["url"];
            $comment->avatar_url     = $avatars[$comment->ownerId]["src"];
            $comment->avatar_css     = $avatars[$comment->ownerId]["css"];
            $comment->avatar_initial = $avatars[$comment->ownerId]["initial"];
            $comment->total_comment  = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp      = SPODAGORA_CLASS_Tools::getInstance()->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class       = $user_id == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';
            $comment->sentiment_class = $comment->sentiment == 0 ? 'neutral' : ($comment->sentiment == 1 ? 'satisfied' : 'dissatisfied');
            $comment->datalet_class   = '';
            $comment->datalet_html    = '';

            if (isset($comment->component)) {
                $comment->datalet_class = 'agora_fullsize_datalet';
                $comment->datalet_html  = $this->create_datalet_code($comment);
            }else{
                $comment->component = '';
            }

        }

        return $comments;
    }

    public function get_avatar_data($userId)
    {
        if(empty($_REQUEST['username']))
        {
            $avatars = $this->process_avatar(BOL_AvatarService::getInstance()->getDataForUserAvatars([$userId]));

            return [
                'username' => $avatars[$userId]['title'],
                'user_avatar_src' => $avatars[$userId]['src'],
                'user_avatar_css' => $avatars[$userId]['css'],
                'user_avatar_initial' =>  $avatars[$userId]['initial'],
                'user_url' => $avatars[$userId]['url']
            ];
        }

        return [
                'username' => $_REQUEST['username'],
                'user_avatar_src' => $_REQUEST['user_avatar_src'],
                'user_avatar_css' => $_REQUEST['user_avatar_css'],
                'user_avatar_initial' =>  $_REQUEST['user_avatar_initial'],
                'user_url' => $_REQUEST['user_url']
        ];
    }

    public function sendEmailNotificationOnComment($room_id, $avatar, $comment, $datalet_id)
    {
        $room = SPODAGORA_BOL_Service::getInstance()->getAgoraById($room_id);

        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_text.html';

        $mail_html = $this->getEmailContentHtml($room_id, $avatar, $room->subject, $room->body, $template_html, 'email_new_comment', $comment, $datalet_id);
        $mail_text = $this->getEmailContentText($room_id, $room->subject, $template_txt);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    public function sendEmailNotificationOnMention($room_id, $avatar, $comment, $datalet_id)
    {
        $room = SPODAGORA_BOL_Service::getInstance()->getAgoraById($room_id);

        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_text.html';

        $mail_html = $this->getEmailContentHtml($room_id, $avatar, $room->subject, $room->body, $template_html, 'email_mention', $comment, $datalet_id);
        $mail_text = $this->getEmailContentText($room_id, $room->subject, $template_txt);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    public function sendEmailNotificationOnNewRoom ($subject, $body, $room_id, $avatar)
    {
        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_text.html';

        $mail_html = $this->getEmailContentHtml($room_id, $avatar, $subject, $body, $template_html, 'email_new_room', '', '');
        $mail_text = $this->getEmailContentText($room_id, $subject, $template_txt);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    public function sendEmailNotificationOnReply($room_id, $avatar, $comment, $datalet_id)
    {
        $room = SPODAGORA_BOL_Service::getInstance()->getAgoraById($room_id);

        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_text.html';

        $mail_html = $this->getEmailContentHtml($room_id, $avatar, $room->subject, $room->body, $template_html, 'email_reply', $comment, $datalet_id);
        $mail_text = $this->getEmailContentText($room_id, $room->subject, $template_txt);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    private function getEmailContentHtml($room_id, $avatar, $roomSubject, $roomBody, $template, $languageKey, $comment, $datalet_id)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);

        $this->assign('userName', "<b>" . $avatar['username'] . "</b>");
        $this->assign('comment', $comment);
        $this->assign('dataletId', $datalet_id);
        $this->assign('dataletUrl', "ow_plugins/ode/datalet_images/datalet_" . $datalet_id . ".png");
        $this->assign('agoraSubject', "<b><a href='" . OW::getRouter()->urlForRoute('spodagora.main') . "/" . $room_id . "'>" . $roomSubject . "</a></b>");
        $this->assign('agoraBody', $roomBody);
        $this->assign('agoraUrl', "agora/" . $room_id);
        $this->assign('notificationSubject', OW::getLanguage()->text('spodagora', $languageKey, array("user_name" => $avatar['username'], "agora_subject" => $roomSubject)));


        return parent::render();
    }

    private function getEmailContentText($room_id, $roomSubject, $template)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);

        $this->assign('agora', $roomSubject);
        $this->assign('url', OW::getRouter()->urlForRoute('spodagora.main') . "/" . $room_id);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    public function getUseIdFromUsernames($usernames)
    {
        $users_id = [];

        foreach ($usernames as $user)
        {
            $users_id[] = BOL_UserService::getInstance()->findByUsername($user)->id;
        }

        return $users_id;
    }

    private function create_datalet_code($comment)
    {
        $params = json_decode($comment->params);
        $html  = '';//"<link rel='import' href='".SPODPR_COMPONENTS_URL."datalets/{$comment->component}/{$comment->component}.html' />";
        $html .= "<{$comment->component} ";

        foreach ($params as $key => $value){
            $html .= $key."='".$this->htmlSpecialChar($value)."' ";
        }

        //CACHE
        $html .= " data='{$comment->data}'";
        $html .= " ></{$comment->component}>";

        return $html;
    }

    protected function htmlSpecialChar($string)
    {
        return str_replace("'","&#39;", $string);
    }

}
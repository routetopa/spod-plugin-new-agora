<?php

class SPODAGORA_CLASS_MailNotification extends OW_Component
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $api_key = '';

    public function sendEmailNotificationOnComment($room_id, $owner_id)
    {
        $preference = BOL_PreferenceService::getInstance()->findPreference('elastic_mail_api_key');
        $this->api_key = empty($preference) ? "" : $preference->defaultValue;

        $userService = BOL_UserService::getInstance();

        //GET ALL SUBSCRIBED USERS
        $users = SPODAGORA_BOL_Service::getInstance()->getSubscribedNotificationUsersForRoom($room_id);

        $room = SPODAGORA_BOL_Service::getInstance()->getAgoraById($room_id);
        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_text.html';
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);


        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($owner_id))[$owner_id];
        $elastic_url = 'https://api.elasticemail.com/v2/email/send';

        foreach($users as $user_id)
        {
            if($user_id["userId"] == $owner_id)
                continue;

            $user = $userService->findUserById($user_id["userId"]);

            if (empty($user))
                return false;

            $email = $user->email;

            try
            {
                /*$mail = OW::getMailer()->createMail()
                    ->addRecipientEmail($email)
                    ->setTextContent($this->getEmailCommentContentText($room_id, $room, $user->username, $template_txt, $time))
                    ->setHtmlContent($this->getEmailCommentContentHtml($room_id, $avatar, $room, $user->username, $template_html, $time))
                    ->setSubject(OW::getLanguage()->text('spodagora', 'email_subject') . "\"" . $room->subject . "\"");

                //OW::getMailer()->send($mail);
                BOL_MailService::getInstance()->send($mail);*/

                try{
                    $post = array('from' => 'webmaster@routetopa.eu',
                        'fromName' => 'SPOD',
                        'apikey' => $this->api_key,
                        'subject' => "Something interesting is happening in the Agora " . $room->subject,
                        'to' => $email,
                        'bodyHtml' => $this->getEmailCommentContentHtml($room_id, $avatar, $room, $user->username, $template_html, $time),
                        'bodyText' => $this->getEmailCommentContentText($room_id, $room, $user->username, $template_txt, $time),
                        'isTransactional' => false);

                    $ch = curl_init();
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $elastic_url,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $post,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HEADER => false,
                        CURLOPT_SSL_VERIFYPEER => false
                    ));

                    $result=curl_exec ($ch);
                    curl_close ($ch);

                }
                catch(Exception $ex){
                    echo $ex->getMessage();
                }

            }
            catch ( Exception $e )
            {
                //Skip invalid notification
            }

        }
    }

    private function getEmailCommentContentHtml($room_id, $avatar, $room, $user, $template, $time)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);


        //USER AVATAR FOR THE NEW MAIL
        $this->assign('user', $user);
        $this->assign('time', $time);
        $this->assign('avatar', $avatar);
        $this->assign('agora', "<b><a href='" . OW::getRouter()->urlForRoute('spodagora.main') . "/" . $room_id . "'>" . $room->subject . "</a></b>");

        return parent::render();
    }

    private function getEmailCommentContentText($room_id, $room, $user, $template, $time)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);

        $this->assign('user', $user);
        $this->assign('time', $time);
        $this->assign('agora', $room->subject);
        $this->assign('url', OW::getRouter()->urlForRoute('spodagora.main') . "/" . $room_id);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    public function sendEmailNotificationOnMention($room_id, $owner_id, $mention)
    {
        $userService = BOL_UserService::getInstance();


        $room = SPODAGORA_BOL_Service::getInstance()->getAgoraById($room_id);
        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_mention_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_mention_template_text.html';
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);


        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($owner_id))[$owner_id];
        $elastic_url = 'https://api.elasticemail.com/v2/email/send';

        foreach($mention as $mention)
        {
            $user = $userService->findByUsername($mention);

            if (empty($user))
                return false;

            $email = $user->email;

            try
            {
                /*$mail = OW::getMailer()->createMail()
                    ->addRecipientEmail($email)
                    ->setTextContent($this->getEmailMentionContentText($room_id, $room, $user->username, $template_txt, $time))
                    ->setHtmlContent($this->getEmailMentionContentHtml($room_id, $avatar, $room, $user->username, $template_html, $time))
                    ->setSubject(OW::getLanguage()->text('spodagora', 'email_subject') . "\"" . $room->subject . "\"");

                //OW::getMailer()->send($mail);
                BOL_MailService::getInstance()->send($mail);*/

                $post = array('from' => 'webmaster@routetopa.eu',
                    'fromName' => 'SPOD',
                    'apikey' => $this->api_key,
                    'subject' => "Something interesting is happening in the Agora " . $room->subject,
                    'to' => $email,
                    'bodyHtml' => $this->getEmailMentionContentHtml($room_id, $avatar, $room, $user->username, $template_html, $time),
                    'bodyText' => $this->getEmailMentionContentText($room_id, $room, $user->username, $template_txt, $time),
                    'isTransactional' => false);

                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $elastic_url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $post,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ));

                $result=curl_exec ($ch);
                curl_close ($ch);

            }
            catch ( Exception $e )
            {
                //Skip invalid notification
            }

        }
    }

    private function getEmailMentionContentText($room_id, $room, $user, $template, $time)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);

        $this->assign('user', $user);
        $this->assign('time', $time);
        $this->assign('agora', $room->subject);
        $this->assign('url', OW::getRouter()->urlForRoute('spodagora.main') . "/" . $room_id);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    private function getEmailMentionContentHtml($room_id, $avatar, $room, $user, $template, $time)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);


        //USER AVATAR FOR THE NEW MAIL
        $this->assign('user', $user);
        $this->assign('time', $time);
        $this->assign('avatar', $avatar);
        $this->assign('agora', "<b><a href='" . OW::getRouter()->urlForRoute('spodagora.main') . "/" . $room_id . "'>" . $room->subject . "</a></b>");

        return parent::render();
    }

}
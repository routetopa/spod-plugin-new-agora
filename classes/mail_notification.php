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

    public function sendEmailNotificationProcess($room_id, $owner_id)
    {
        $userService = BOL_UserService::getInstance();

        //GET ALL SUBSCRIBED USERS
        $users = SPODAGORA_BOL_Service::getInstance()->getSubscribedNotificationUsersForRoom($room_id);

        $room = SPODAGORA_BOL_Service::getInstance()->getAgoraById($room_id);
        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_text.html';
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);


        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($owner_id))[$owner_id];

        foreach($users as $user_id)
        {
            $user = $userService->findUserById($user_id["userId"]);

            if (empty($user))
                return false;

            $email = $user->email;

            try
            {
                $mail = OW::getMailer()->createMail()
                    ->addRecipientEmail($email)
                    ->setTextContent($this->getEmailContentText($room_id, $room, $user->username, $template_txt, $time))
                    ->setHtmlContent($this->getEmailContentHtml($room_id, $avatar, $room, $user->username, $template_html, $time))
                    ->setSubject(OW::getLanguage()->text('spodagora', 'email_subject') . "\"" . $room->subject . "\"");

                OW::getMailer()->send($mail);
            }
            catch ( Exception $e )
            {
                //Skip invalid notification
            }

        }
    }

    private function getEmailContentHtml($room_id, $avatar, $room, $user, $template, $time)
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

    private function getEmailContentText($room_id, $room, $user, $template, $time)
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
}
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

    public function sendEmailNotificationProcess( $roomId )
    {

        //GET ALL SUBSCRIBED USERS
        $users = SPODAGORA_BOL_Service::getInstance()->getSubscribedNotificationUsersForRoom($roomId);

        foreach($users as $user){

            $userId = $user->userId;
            $userService = BOL_UserService::getInstance();
            $user = $userService->findUserById($userId);

            if ( empty($user) )
            {
                return false;
            }

            $email = $user->email;
            try
            {
                $mail = OW::getMailer()->createMail()
                    ->addRecipientEmail($email)
                    ->setTextContent($this->getEmailContentText($roomId))
                    ->setHtmlContent($this->getEmailContentHtml($roomId, $userId))
                    ->setSubject("Something interesting is happening on Agora");

                OW::getMailer()->send($mail);
            }
            catch ( Exception $e )
            {
                //Skip invalid notification
            }

        }
    }

    private function getEmailContentHtml($roomId, $userId)
    {
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);

        //SET EMAIL TEMPLETE
        $template = OW::getPluginManager()->getPlugin('spodpublic')->getCmpViewDir() . 'email_notification_template_html.html';
        $this->setTemplate($template);

        //USER AVATAR FOR THE NEW MAIL
        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()))[OW::getUser()->getId()];
        $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($userId));
        $this->assign('string', OW::getLanguage()->text('spodpublic', 'email_txt_comment') . " <b><a href=\"" .
            OW::getRouter()->urlForRoute('spodpublic.main')  . "/#!/" . $roomId . "\">" .
            SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($roomId)->subject . "</a></b>");
        $this->assign('avatar', $avatar);
        $this->assign('time', $time);

        return parent::render();

    }

    private function getEmailContentText($roomId)
    {
        $date = getdate();

        $template = OW::getPluginManager()->getPlugin('spodpublic')->getCmpViewDir() . 'email_notification_template_text.html';
        $this->setTemplate($template);

        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');
        $this->assign('string', " has commented on a discussion in the room <b>" . SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($roomId)->name . "</b>");

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }
}
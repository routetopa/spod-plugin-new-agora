<?php

define('_OW_', true);
define('DS', DIRECTORY_SEPARATOR);
define('OW_DIR_ROOT', '/var/www' . DS);
require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');


class SPODAGORA_CLASS_CliMailNotification extends OW_Component
{
    public function sendEmailNotificationOnComment($room_id, $owner_id)
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
        $elastic_url = 'https://api.elasticemail.com/v2/email/send';

        foreach($users as $user_id)
        {
            if($user_id["userId"] == $owner_id)
                continue;

            $user = $userService->findUserById($user_id["userId"]);

            if (empty($user))
                return false;

            $email = $user->email;

            try{

                //echo($email . "\n");

                $post = array('from' => 'webmaster@routetopa.eu',
                    'fromName' => 'SPOD',
                    'apikey' => 'c1e69cce-889e-4440-9e13-80151cdc6ef6',
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
                //echo $ex->getMessage();
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
                $post = array('from' => 'webmaster@routetopa.eu',
                    'fromName' => 'SPOD',
                    'apikey' => 'c1e69cce-889e-4440-9e13-80151cdc6ef6',
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

$_SERVER['HTTP_USER_AGENT'] = '';
$_SERVER['REQUEST_URI'] = '';

OW::getSession()->start();
$application = OW::getApplication();
$application->init();

$mailer = new SPODAGORA_CLASS_CliMailNotification();


if(count($argv) == 4)
{
    if(!empty($argv[1]) && !empty($argv[2]) && !empty($argv[3]))
    {
        $username = explode(",", $argv[3]);
        $mailer->sendEmailNotificationOnMention($argv[1], $argv[2], $username);
        //echo("SEND MENTION \n");
    }
}

if(count($argv) == 3)
{
    if(!empty($argv[1]) && !empty($argv[2]))
    {
        $mailer->sendEmailNotificationOnComment($argv[1], $argv[2]);
        //echo("SEND COMMENT \n");
    }
}
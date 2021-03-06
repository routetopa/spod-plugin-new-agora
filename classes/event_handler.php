<?php

class SPODAGORA_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    // Handle event and route
    public function init()
    {
        OW::getEventManager()->bind('spodnotification.collect_actions', array($this, 'onCollectNotificationActions'));
    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {

        $e->add(array(
            'section' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'action'  => SPODAGORA_CLASS_Const::PLUGIN_ACTION_ADD_COMMENT,
            'description' => OW::getLanguage()->text('spodagora', 'email_notifications_setting_new_comment'),
            'selected' => false,
            'sectionLabel' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
            ));

        $sub_actions = SPODNOTIFICATION_BOL_Service::getInstance()->isUserRegisteredForSubAction(OW::getUser()->getId(),
                                                                                                SPODAGORA_CLASS_Const::PLUGIN_NAME,
                                                                                                SPODAGORA_CLASS_Const::PLUGIN_ACTION_ADD_COMMENT,
                                                                                                SPODNOTIFICATION_CLASS_MailEventNotification::$TYPE);
        foreach ($sub_actions as $sub_action)
        {
            preg_match_all('!\d+!', $sub_action->action, $agora_id);
            $agora = SPODAGORA_BOL_Service::getInstance()->getAgoraById($agora_id[0][0]);

            $e->add(array(
                'section' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
                'action'  => $sub_action->action,
                'description' => OW::getLanguage()->text('spodagora', 'email_notifications_setting_new_comment_in_room', array("room" => $agora->subject)),
                'selected' => false,
                'sectionLabel' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
                'sectionIcon' => 'ow_ic_write',
                'sectionClass' => 'subAction',
                'parentAction' => $sub_action->parentAction
            ));
        }

        $e->add(array(
            'section' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'action'  => SPODAGORA_CLASS_Const::PLUGIN_ACTION_MENTION,
            'description' => OW::getLanguage()->text('spodagora', 'email_notifications_setting_mention'),
            'selected' => false,
            'sectionLabel' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));

        $e->add(array(
            'section' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'action'  => SPODAGORA_CLASS_Const::PLUGIN_ACTION_NEW_ROOM,
            'description' => OW::getLanguage()->text('spodagora', 'email_notifications_setting_new_room'),
            'selected' => false,
            'sectionLabel' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));

        $e->add(array(
            'section' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'action'  => SPODAGORA_CLASS_Const::PLUGIN_ACTION_REPLY,
            'description' => OW::getLanguage()->text('spodagora', 'email_notifications_setting_reply_my_post'),
            'selected' => false,
            'sectionLabel' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));

    }
}
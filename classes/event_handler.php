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
            'description' => 'New comment added',
            'selected' => false,
            'sectionLabel' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'sectionIcon' => 'ow_ic_write'
            ));

        $e->add(array(
            'section' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'action'  => SPODAGORA_CLASS_Const::PLUGIN_ACTION_MENTION,
            'description' => 'Mention',
            'selected' => false,
            'sectionLabel' => SPODAGORA_CLASS_Const::PLUGIN_NAME,
            'sectionIcon' => 'ow_ic_write'
        ));

    }
}
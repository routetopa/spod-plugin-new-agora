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
            'section' => 'agora',
            'action'  => 'new_agora',
            'description' => 'creazione nuova agora',
            'selected' => false,
            'sectionLabel' => 'Agora',
            'sectionIcon' => 'ow_ic_write'
        ));

        $e->add(array(
            'section' => 'agora',
            'action'  => 'agora_new_comment',
            'description' => 'nuovo commento',
            'selected' => false,
            'sectionLabel' => 'Agora',
            'sectionIcon' => 'ow_ic_write'
        ));

        $e->add(array(
            'section' => 'agora',
            'action'  => 'mention',
            'description' => 'mention',
            'selected' => false,
            'sectionLabel' => 'Agora',
            'sectionIcon' => 'ow_ic_write'
        ));

        $e->add(array(
            'section' => 'agora',
            'action'  => 'reply',
            'description' => 'reply',
            'selected' => false,
            'sectionLabel' => 'Agora',
            'sectionIcon' => 'ow_ic_write'
        ));
    }
}
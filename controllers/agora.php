<?php

class SPODAGORA_CTRL_Agora extends OW_ActionController
{
    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/empty.html');
    }
}
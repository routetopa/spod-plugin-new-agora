<?php

class SPODAGORA_CTRL_Agora extends OW_ActionController
{
    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/empty.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
    }
}
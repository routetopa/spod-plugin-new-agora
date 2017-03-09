<?php

class SPODAGORA_CTRL_NewAgora extends OW_ActionController
{
    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/main.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_main_new.css');
    }
}
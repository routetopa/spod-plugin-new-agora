<?php
class SPODAGORA_CMP_Helper extends OW_Component
{
    public function __construct()
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ode')->getStaticJsUrl() . 'helper.js');

        $this->assign("staticResourcesUrl", OW::getPluginManager()->getPlugin('spodagora')->getStaticUrl());
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
    }
}
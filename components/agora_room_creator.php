<?php

class SPODAGORA_CMP_AgoraRoomCreator extends OW_Component
{
    public function __construct()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'input-menu.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'input-menu.css');

        $form = new Form('AgoraRoomCreatorForm');

        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);

        $subject = new TextField('subject');
        $subject->setRequired(true);

        $body = new TextField('body');
        $body->setRequired(true);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('spodagora', 'submit_button'));

        $form->addElement($subject);
        $form->addElement($body);
        $form->addElement($submit);

        $form->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'addAgoraRoom')) );

        $this->addForm($form);

        $js = UTIL_JsGenerator::composeJsString('
            owForms["AgoraRoomCreatorForm"].bind( "success", function( r )
            {
                AGORAMAIN.addNewRoom(r);

                if ( r.error )
                {
                   OW.error(r.error); return;
                }

                if ( r.message ) {
                    OW.info(r.message);
                }

        });', array());

        OW::getDocument()->addOnloadScript( $js );

    }
}
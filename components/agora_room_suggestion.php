<?php

class SPODAGORA_CMP_AgoraRoomSuggestion extends OW_Component
{
    public function __construct($room)
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'input-menu.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'input-menu.css');

        $form = new Form('AgoraRoomSuggestionForm');

        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);

        $room_id = new HiddenField('room_id');
        $room_id->setValue($room);

        $dataset = new TextField('dataset');
        $dataset->setRequired(true);

        $comment = new TextField('comment');
        $comment->setRequired(true);

        $submit = new Submit('submit');
        $submit->setValue('submit');

        $form->addElement($room_id);
        $form->addElement($dataset);
        $form->addElement($comment);
        $form->addElement($submit);

        $form->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'addAgoraRoomSuggestion')) );

        $this->addForm($form);

        $js = UTIL_JsGenerator::composeJsString('
            owForms["AgoraRoomSuggestionForm"].bind( "success", function( r )
            {
                AGORAMAIN.addSuggestion(r);

                if ( r.error )
                {
                   OW.error(r.error); return;
                }

                if ( r.message ) {
                    OW.info(r.message);
                }

        });', array());

        OW::getDocument()->addOnloadScript($js);

    }
}
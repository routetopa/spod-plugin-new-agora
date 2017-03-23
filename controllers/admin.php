<?php

class SPODAGORA_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle("AGORA");
        $this->setPageHeading("AGORA");

        $this->assign('agora', SPODAGORA_BOL_Service::getInstance()->getAgora());

        $deleteUrl = OW::getRouter()->urlFor(__CLASS__, 'delete');
        $this->assign('deleteUrl', $deleteUrl);

        $editUrl = OW::getRouter()->urlFor(__CLASS__, 'edit');
        $this->assign('editUrl', $editUrl);

        $form = new Form('settings');
        $this->addForm($form);

        /* IsVisible */
        $is_visible = new CheckboxField('isVisible');
        $preference = BOL_PreferenceService::getInstance()->findPreference('agora_is_visible_not_logged');
        $is_visible_pref = empty($preference) ? "0" : $preference->defaultValue;
        $is_visible->setValue($is_visible_pref);
        $form->addElement($is_visible);

        $submit = new Submit('add');
        $submit->setValue('SAVE');
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            /* ode_deep_url */
            $preference = BOL_PreferenceService::getInstance()->findPreference('agora_is_visible_not_logged');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'agora_is_visible_not_logged';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['isVisible'];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);
        }

    }

    public function delete()
    {
        if ( isset($_REQUEST['id']))
        {
            $id = $_REQUEST['id'];
            SPODAGORA_BOL_Service::getInstance()->removeAgora($id);
        }

        $this->redirect(OW::getRouter()->urlForRoute('agora-settings'));
    }

    public function edit()
    {
        if ( isset($_REQUEST['id']))
        {
            $id = $_REQUEST['id'];
            $title = $_REQUEST['title'];
            $body = $_REQUEST['body'];
            SPODAGORA_BOL_Service::getInstance()->editAgora($id, $title, $body);
        }

        $this->redirect(OW::getRouter()->urlForRoute('agora-settings'));
    }
}
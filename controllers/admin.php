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

        /* SPLOD IsVisible */
        $SPLOD_visible = new CheckboxField('SPLOD_visible');
        $preference = BOL_PreferenceService::getInstance()->findPreference('splod_is_visible_agora');
        $splod_pref = empty($preference) ? "0" : $preference->defaultValue;
        $SPLOD_visible->setValue($splod_pref);
        $form->addElement($SPLOD_visible);

        /* MAPLET IsVisible */
        $Maplet_visible = new CheckboxField('Maplet_visible');
        $preference = BOL_PreferenceService::getInstance()->findPreference('maplet_is_visible_agora');
        $maplet_pref = empty($preference) ? "0" : $preference->defaultValue;
        $Maplet_visible->setValue($maplet_pref);
        $form->addElement($Maplet_visible);

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

            /* splod */
            $preference = BOL_PreferenceService::getInstance()->findPreference('splod_is_visible_agora');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'splod_is_visible_agora';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['SPLOD_visible'] ? $data['SPLOD_visible'] : 0;
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* maplet */
            $preference = BOL_PreferenceService::getInstance()->findPreference('maplet_is_visible_agora');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'maplet_is_visible_agora';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['Maplet_visible'] ? $data['Maplet_visible'] : 0;
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
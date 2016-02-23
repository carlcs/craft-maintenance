<?php
namespace Craft;

class MaintenanceWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Maintenance Announcements');
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('maintenance/widgets/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    public function getTitle()
    {
        return Craft::t($this->settings->title);
    }

    public function getBodyHtml()
    {
        $limit = $this->getSettings()->limit;
        $announcements = craft()->maintenance->getAnnouncements();

        return craft()->templates->render('maintenance/widgets/body', array(
            'announcements' => $announcements,
        ));
    }

    protected function defineSettings()
    {
        return array(
            'title' => array(AttributeType::Name, 'required' => true, 'default' => Craft::t('Maintenance Announcements')),
            'limit' => array(AttributeType::Number, 'min' => 0, 'default' => 5),
        );
    }
}

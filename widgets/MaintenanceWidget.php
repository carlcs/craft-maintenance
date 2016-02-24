<?php
namespace Craft;

class MaintenanceWidget extends BaseWidget
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the widget's name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Announcements');
    }

    /**
     * Returns the widget's title.
     *
     * @return string
     */
    public function getTitle()
    {
        return Craft::t($this->settings->title);
    }

    /**
     * Returns the path to the widget's SVG icon.
     *
     * @return string
     */
    public function getIconPath()
    {
        return craft()->path->getPluginsPath().'maintenance/resources/icon-announcement.svg';
    }

    /**
     * Returns the widget's settings model.
     *
     * @return BaseModel
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('maintenance/widgets/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    /**
     * Returns the widget's body HTML.
     *
     * @return string|false
     */
    public function getBodyHtml()
    {
        $limit = $this->getSettings()->limit;
        $announcements = craft()->maintenance->getAnnouncements();

        return craft()->templates->render('maintenance/widgets/body', array(
            'announcements' => $announcements,
        ));
    }

    // Protected Methods
    // =========================================================================

    /**
     * Defines the settings.
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'title' => array(AttributeType::Name, 'required' => true, 'default' => Craft::t('Announcements')),
            'limit' => array(AttributeType::Number, 'min' => 0, 'default' => 5),
        );
    }
}

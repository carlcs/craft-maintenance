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
        $statuses = craft()->plugins->getPlugin('maintenance')->announcementStatuses;

        // We don't show them the none and imminent status
        unset($statuses['none']);
        unset($statuses['imminent']);

        return craft()->templates->render('maintenance/widgets/settings', array(
            'settings' => $this->getSettings(),
            'statuses' => $statuses,
        ));
    }

    /**
     * Returns the widget's body HTML.
     *
     * @return string|false
     */
    public function getBodyHtml()
    {
        $announcements = craft()->maintenance->getAnnouncements();

        return craft()->templates->render('maintenance/widgets/body', array(
            'announcements' => $this->filterAnnouncements($announcements),
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
            'statuses' => array(AttributeType::Mixed),
            'simpleNote' => array(AttributeType::Bool),
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Filters announcements according to the widget's settings.
     *
     * @param array $announcements
     *
     * @return array
     */
    private function filterAnnouncements($announcements)
    {
        $settings = $this->getSettings();
        $statuses = $settings->statuses;

        if (!$statuses) {
            $statuses = array();
        }

        if (in_array('pending', $statuses)) {
            $statuses[] = 'imminent';
        }

        if ($settings->simpleNote) {
            $statuses[] = 'none';
        }

        $filtered = array();
        foreach ($announcements as $announcement) {
            if (in_array($announcement->getStatus(), $statuses)) {
                $filtered[] = $announcement;
            }
        }

        return $filtered;
    }
}

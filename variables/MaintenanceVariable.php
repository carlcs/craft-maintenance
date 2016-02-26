<?php
namespace Craft;

class MaintenanceVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns all stored announcements.
     *
     * @return array
     */
    public function getAnnouncements()
    {
        return craft()->maintenance->getAnnouncements();
    }

    /**
     * Returns a announcement by its ID.
     *
     * @param int $id
     *
     * @return AnnouncementModel|null
     */
    public function getAnnouncementById($id)
    {
        return craft()->maintenance->getAnnouncementById($id);
    }

    /**
     * Returns the currently active announcement.
     *
     * @param string $timeInAdvance
     *
     * @return AnnouncementModel|null
     */
    public function getAnnouncement($timeInAdvance = '0')
    {
        return craft()->maintenance_variables->getAnnouncement($timeInAdvance);
    }

    /**
     * Returns whether the CP is currently undergoing maintenance.
     *
     * @return bool
     */
    public function isCpMaintenance()
    {
        return craft()->maintenance_variables->isCpMaintenance();
    }

    /**
     * Returns whether the site is currently undergoing maintenance.
     *
     * @return bool
     */
    public function isSiteMaintenance()
    {
        return craft()->maintenance_variables->isSiteMaintenance();
    }

    /**
     * Returns the plugin's settings.
     *
     * @return array
     */
    public function getPluginSettings()
    {
        return craft()->maintenance->getPluginSettings();
    }

    /**
     * Returns whether there is a custom config.
     *
     * @return bool
     */
    public function hasConfigFile()
    {
        return craft()->maintenance->hasConfigFile();
    }

    /**
     * Returns the plugin's settings.
     *
     * @return array
     */
    public function getAnnouncementStatuses()
    {
        return craft()->plugins->getPlugin('maintenance')->announcementStatuses;
    }
}

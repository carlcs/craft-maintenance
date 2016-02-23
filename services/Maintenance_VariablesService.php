<?php
namespace Craft;

class Maintenance_VariablesService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    protected $announcement;

    // Public Methods
    // =========================================================================

    /**
     * Returns the currently active announcement.
     *
     * @param string $timeInAdvance
     *
     * @return AnnouncementModel|null
     */
    public function getAnnouncement($timeInAdvance)
    {
        return craft()->maintenance->getNextAnnouncement($timeInAdvance);
    }

    /**
     * Returns whether the CP is currently undergoing maintenance.
     *
     * @return bool
     */
    public function isCpMaintenance()
    {
        if (!$this->announcement) {
             $this->announcement = craft()->maintenance->getNextAnnouncement();
        }

        return $this->announcement ? (bool) $this->announcement->blockCp : false ;
    }

    /**
     * Returns whether the site is currently undergoing maintenance.
     *
     * @return bool
     */
    public function isSiteMaintenance()
    {
        if (!$this->announcement) {
             $this->announcement = craft()->maintenance->getNextAnnouncement();
        }

        return $this->announcement ? (bool) $this->announcement->blockSite : false ;
    }
}

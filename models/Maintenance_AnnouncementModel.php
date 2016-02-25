<?php
namespace Craft;

class Maintenance_AnnouncementModel extends BaseModel
{
    // Constants
    // =========================================================================

    const NONE       = 'none';
    const DISABLED   = 'disabled';
    const COMPLETED  = 'completed';
    const INPROGRESS = 'inprogress';
    const IMMINENT   = 'imminent';
    const PENDING    = 'pending';

    // Public Methods
    // =========================================================================

    /**
     * Returns the element's status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        $currentTime = DateTimeHelper::currentTimeStamp();
        $startDate   = ($this->startDate ? $this->startDate->getTimestamp() : null);
        $endDate     = ($this->endDate ? $this->endDate->getTimestamp() : null);

        $pluginSettings = craft()->plugins->getPlugin('maintenance')->pluginSettings;

        $interval = $pluginSettings['maintenanceImminent'];
        $interval = DateInterval::createFromDateString($interval);
        $secondsInAdvance = (new DateTime('@0'))->add($interval)->getTimeStamp();

        if (!$startDate) {
            return static::NONE;
        }

        if (!$this->blockCp && !$this->blockSite) {
            return static::DISABLED;
        } else {
            if ($startDate > $currentTime) {
                if ($startDate > $currentTime + $secondsInAdvance) {
                    return static::PENDING;
                } else {
                    return static::IMMINENT;
                }
            } else if ($startDate <= $currentTime && (!$endDate || $endDate > $currentTime)) {
                return static::INPROGRESS;
            } else if ($startDate <= $currentTime) {
                return static::COMPLETED;
            }
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Defines this model's attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'id'        => AttributeType::Number,
            'message'   => AttributeType::String,
            'startDate' => AttributeType::DateTime,
            'endDate'   => AttributeType::DateTime,
            'blockCp'   => array(AttributeType::Bool, 'default' => false),
            'blockSite' => array(AttributeType::Bool, 'default' => false),
        );
    }
}

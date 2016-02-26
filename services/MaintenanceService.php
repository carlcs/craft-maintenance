<?php
namespace Craft;

class MaintenanceService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    /**
     * Returns all announcements.
     *
     * @return array
     */
    public function getAnnouncements()
    {
        $announcementRecords = $this->_getAnnouncementRecords();

        if (count($announcementRecords) > 0) {
            return Maintenance_AnnouncementModel::populateModels($announcementRecords);
        }

        return array();
    }

    /**
     * Returns the next active announcement.
     *
     * @param string $timeInAdvance
     *
     * @return AnnouncementModel
     */
    public function getNextAnnouncement($timeInAdvance = '0')
    {
        $time = DateTimeHelper::currentUTCDateTime();
        $time->modify('+'.$timeInAdvance);

        $announcementRecord = craft()->db->createCommand()
            ->select('*')
            ->from('maintenance_announcements')
            ->where(
                array(
                    'and',
                    array(
                        'or',
                        'blockSite = 1',
                        'blockCp = 1',
                    ),
                    'startDate <= :time',
                    array(
                        'or',
                        'endDate >= :now',
                        'endDate IS NULL',
                    ),
                ),
                array(
                    ':now' => DateTimeHelper::formatTimeForDb(),
                    ':time' => DateTimeHelper::formatTimeForDb($time),
                )
            )
            ->order('startDate desc')
            ->queryRow();

        if ($announcementRecord) {
            return Maintenance_AnnouncementModel::populateModel($announcementRecord);
        }
    }

    /**
     * Returns a announcement by its ID.
     *
     * @param int $announcementId
     *
     * @return AnnouncementModel
     */
    public function getAnnouncementById($announcementId)
    {
        $announcementRecord = Maintenance_AnnouncementRecord::model()->findById($announcementId);

        if ($announcementRecord) {
            return Maintenance_AnnouncementModel::populateModel($announcementRecord);
        }
    }

    /**
     * Saves a announcement.
     *
     * @param AnnouncementModel $announcement
     *
     * @return bool
     */
    public function saveAnnouncement(Maintenance_AnnouncementModel $announcement)
    {
        $announcementRecord = $this->_getAnnouncementRecordById($announcement->id);

        $announcementRecord->message   = $announcement->message;
        $announcementRecord->startDate = $announcement->startDate;
        $announcementRecord->endDate   = $announcement->endDate;
        $announcementRecord->blockCp   = $announcement->blockCp;
        $announcementRecord->blockSite = $announcement->blockSite;

        if ($announcementRecord->validate()) {
            if ($announcementRecord->isNewRecord()) {
                $maxSortOrder = craft()->db->createCommand()
                    ->select('max(sortOrder)')
                    ->from('maintenance_announcements')
                    ->queryScalar();

                $announcementRecord->sortOrder = $maxSortOrder + 1;
            }

            $announcementRecord->save(false);

            // Now that we have a announcement ID, save it on the model
            if (!$announcement->id) {
                $announcement->id = $announcementRecord->id;
            }

            return true;
        } else {
            $announcement->addErrors($announcementRecord->getErrors());

            return false;
        }
    }

    /**
     * Deletes a announcement.
     *
     * @param int $announcementId
     *
     * @return bool
     */
    public function deleteAnnouncementById($announcementId)
    {
        craft()->db->createCommand()->delete('maintenance_announcements', array('id' => $announcementId));
        return true;
    }

    /**
     * Reorders announcements.
     *
     * @param array $announcementIds
     *
     * @return null
     */
    public function reorderAnnouncements($announcementIds)
    {
        foreach ($announcementIds as $order => $announcementId) {
            $data = array('sortOrder' => $order + 1);
            $condition = array('id' => $announcementId);
            craft()->db->createCommand()->update('maintenance_announcements', $data, $condition);
        }
    }

    /**
     * Returns the plugin's settings.
     *
     * @return bool
     */
    public function getPluginSettings()
    {
        // FIXME: make feature request so this ugly isn't necessary
        $items = array(
            'maintenanceUrls',
            'maintenanceIps',
            'maintenancePending',
            'maintenanceImminent',
        );

        // Is there a maintenance.php?
        if ($this->hasConfigFile()) {
            $settings = array();
            foreach ($items as $item) {
                $settings[$item] = craft()->config->get($item, 'maintenance');
            }
        } else {
            $plugin = craft()->plugins->getPlugin('maintenance');
            $settings = $plugin->getSettings();

            // We have no settings to merge from config.php
        }

        return $settings;
    }

    /**
     * Returns whether there is a custom config file.
     *
     * @return bool
     */
    public function hasConfigFile()
    {
        return (bool) IOHelper::fileExists(CRAFT_CONFIG_PATH.'maintenance.php');
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns a announcement's record.
     *
     * @param int $announcementId
     *
     * @return AnnouncementRecord
     */
    private function _getAnnouncementRecordById($announcementId = null)
    {
        if ($announcementId) {
            $announcementRecord = Maintenance_AnnouncementRecord::model()->findById($announcementId);

            if (!$announcementRecord) {
                throw new Exception(Craft::t('(Maintenance) No announcement exists with the ID “{id}”.', array('id' => $announcementId)));
            }
        } else {
            $announcementRecord = new Maintenance_AnnouncementRecord();
        }

        return $announcementRecord;
    }

    /**
     * Returns announcement records.
     *
     * @return array
     */
    private function _getAnnouncementRecords()
    {
        $criteria = array(
            'order' => 'sortOrder desc',
        );

        return Maintenance_AnnouncementRecord::model()->ordered()->findAll($criteria);
    }
}

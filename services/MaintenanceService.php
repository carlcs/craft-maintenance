<?php
namespace Craft;

class MaintenanceService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    /**
     * Returns all announcements.
     *
     * @param int $limit
     *
     * @return array
     */
    public function getAnnouncements($limit = null)
    {
        $announcementRecords = $this->_getAnnouncementRecords($limit);

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
                    'blockCp = 1',
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
     * @param int $limit
     *
     * @return array
     */
    private function _getAnnouncementRecords($limit)
    {
        $criteria = array(
            'order' => 'sortOrder desc',
        );

        if ($limit !== null) {
            $criteria = array_merge($criteria, array('limit' => $limit));
        }

        return Maintenance_AnnouncementRecord::model()->ordered()->findAll($criteria);
    }
}
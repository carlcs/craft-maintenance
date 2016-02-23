<?php
namespace Craft;

class MaintenanceController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Saves a new or existing announcement.
     *
     * @return null
     */
    public function actionSaveAnnouncement()
    {
        $this->requirePostRequest();

        $announcement = new Maintenance_AnnouncementModel();

        $announcement->id        = craft()->request->getPost('id');
        $announcement->message   = craft()->request->getPost('message');
        $announcement->startDate = (($startDate = craft()->request->getPost('startDate')) ? DateTime::createFromString($startDate, craft()->timezone) : null);
        $announcement->endDate   = (($endDate = craft()->request->getPost('endDate')) ? DateTime::createFromString($endDate, craft()->timezone) : null);
        $announcement->blockCp   = (bool) craft()->request->getPost('blockCp');
        $announcement->blockSite = (bool) craft()->request->getPost('blockSite');

        if (craft()->maintenance->saveAnnouncement($announcement)) {
            craft()->userSession->setNotice(Craft::t('Announcement saved.'));

            $this->redirectToPostedUrl();
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save announcement.'));
        }

        // Send the announcement back to the template
        craft()->urlManager->setRouteVariables(array(
            'announcement' => $announcement
        ));
    }

    /**
     * Deletes a announcement.
     *
     * @return null
     */
    public function actionDeleteAnnouncement()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $announcementId = JsonHelper::decode(craft()->request->getRequiredPost('id'));
        craft()->maintenance->deleteAnnouncementById($announcementId);

        $this->returnJson(array('success' => true));
    }

    /**
     * Updates the announcements sort order.
     *
     * @return null
     */
    public function actionReorderAnnouncements()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $announcementIds = JsonHelper::decode(craft()->request->getRequiredPost('ids'));
        $announcementIds = array_reverse($announcementIds);

        craft()->maintenance->reorderAnnouncements($announcementIds);

        $this->returnJson(array('success' => true));
    }
}

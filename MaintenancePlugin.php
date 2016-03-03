<?php
namespace Craft;

class MaintenancePlugin extends BasePlugin
{
    // Plugin Settings
    // =========================================================================

    public function getName()
    {
        return 'Maintenance';
    }

    public function getVersion()
    {
        return '1.2.2';
    }

    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    public function getDeveloper()
    {
        return 'carlcs';
    }

    public function getDeveloperUrl()
    {
        return 'https://github.com/carlcs';
    }

    public function getDocumentationUrl()
    {
        return 'https://github.com/carlcs/craft-maintenance';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://github.com/carlcs/craft-maintenance/raw/master/releases.json';
    }

    // Properties
    // =========================================================================

    public $pluginSettings;

    public $announcementStatuses;

    protected $announcement;

    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->pluginSettings = craft()->maintenance->getPluginSettings();

        $this->announcementStatuses = array(
            'none'       => array('label' => Craft::t('None'), 'icon' => 'light'),
            'disabled'   => array('label' => Craft::t('Disabled'), 'icon' => 'disabled'),
            'completed'  => array('label' => Craft::t('Completed'), 'icon' => 'green'),
            'inprogress' => array('label' => Craft::t('In progress'), 'icon' => 'red'),
            'imminent'   => array('label' => Craft::t('Pending'), 'icon' => 'orange'),
            'pending'    => array('label' => Craft::t('Pending'), 'icon' => 'orange'),
        );

        if (craft()->request->isCpRequest()) {
            craft()->templates->includeCssResource('maintenance/maintenance.css');
            craft()->templates->includeJsResource('maintenance/maintenance.js');
        }

        $this->initCpAccessControl();
        $this->initSiteAccessControl();
    }

    public function prepSettings($settings)
    {
        $settings['maintenanceUrls'] = array_filter(explode(' ', $settings['maintenanceUrls']));
        $settings['maintenanceIps'] = array_filter(explode(' ', $settings['maintenanceIps']));

        return $settings;
    }

    public function getSettingsUrl()
    {
        return 'settings/plugins/maintenance/index';
    }

    public function registerCpRoutes()
    {
        return array(
            'settings/plugins/maintenance/settings' => 'maintenance/settings/_settings',
            'settings/plugins/maintenance/index' => 'maintenance/settings/index',
            'settings/plugins/maintenance/new' => 'maintenance/settings/_edit',
            'settings/plugins/maintenance/(?P<announcementId>\d+)' => 'maintenance/settings/_edit',
        );
    }

    public function registerUserPermissions()
    {
        return array(
            'maintenanceSiteAccess' => array('label' => Craft::t('Access the site when frontend maintenance is in progress')),
            'maintenanceCPAccess' => array('label' => Craft::t('Close the “Maintenance in progress” overlay')),
            'maintenanceNoAnnouncements' => array('label' => Craft::t('Doesn’t get the “Maintenance in progress” overlay at all')),
        );
    }

    public function addTwigExtension()
    {
        Craft::import('plugins.maintenance.twigextensions.MaintenanceTwigExtension');
        return new MaintenanceTwigExtension();
    }

    public function getCpAlerts($path, $fetch)
    {
        if ($announcement = $this->announcement) {

            switch ($announcement->getStatus()) {
                case 'completed':
                    $message = Craft::t('Maintenance is complete.');
                    break;

                case 'inprogress':
                    $message = Craft::t('Maintenance in progress.');
                    break;

                case 'pending':
                    $date = $announcement->startDate;
                    $message = Craft::t('Maintenance will be carried out on {date}.', array('date' => '<span class="maintenanceBanner-date">'.$date->localeDate().' '.$date->localeTime().'</span>'));
                    break;

                case 'imminent':
                    craft()->templates->includeTranslations('Maintenance in progress.');

                    $currentTime = DateTimeHelper::currentTimeStamp();
                    $startDate = $announcement->startDate->getTimestamp();
                    $this->initCountdown($startDate - $currentTime);

                    $message = '<span class="hidden">'.Craft::t('Maintenance will be carried out in {minutes} minutes.', array('minutes' => '<span id="maintenancecountdown">some</span>')).'</span>';
                    break;
            }

            return array($message);
        }
    }

    // Protected Methods
    // =========================================================================

    protected function defineSettings()
    {
        return array(
            'maintenanceUrls'     => array(AttributeType::Mixed, 'default' => array('/legal-notice')),
            'maintenanceIps'      => array(AttributeType::Mixed, 'default' => array('127.0.0.1')),
            'maintenancePending'  => array(AttributeType::String, 'default' => '24 hours'),
            'maintenanceImminent' => array(AttributeType::String, 'default' => '60 minutes'),
        );
    }

    protected function initMaintenance()
    {
        if (!$this->announcement) {
            $timeInAdvance = $this->pluginSettings['maintenancePending'];
            $this->announcement = craft()->maintenance->getNextAnnouncement($timeInAdvance);
        }
    }

    protected function initSiteAccessControl()
    {
        if (craft()->request->isSiteRequest() && (!craft()->userSession->checkPermission('maintenanceSiteAccess'))) {
            if (!in_array(craft()->request->getIpAddress(), $this->pluginSettings['maintenanceIps'])) {
                $this->initMaintenance();

                if ($announcement = $this->announcement) {
                    if ($announcement->blockSite && $announcement->getStatus() === 'inprogress') {
                        $urlWhitelist = $this->pluginSettings['maintenanceUrls'];

                        if (!in_array(craft()->request->getUrl(), $urlWhitelist)) {
							throw new HttpException(503);
                        }
                    }
                }
            }
        }
    }

    protected function initCpAccessControl()
    {
        if (craft()->request->isCpRequest() && craft()->userSession->isLoggedIn()) {
            $this->initMaintenance();

            if ($announcement = $this->announcement) {
                if (!craft()->userSession->checkPermission('maintenanceNoAnnouncements') && $announcement->getStatus() === 'inprogress') {
                    craft()->templates->includeTranslations('Maintenance in progress.');

                    $date = $announcement->startDate;
                    $meta = Craft::t('started {date}', array('date' => '<span class="maintenanceOverlay-date">'.$date->uiTimestamp().'</span>'));

                    $message = StringHelper::parseMarkdown($announcement->message);
                    $maintenanceCPAccess = craft()->userSession->checkPermission('maintenanceCPAccess');
                    craft()->templates->includeJs('new Craft.MaintenanceModal('.JsonHelper::encode($announcement).','.JsonHelper::encode($message).','.JsonHelper::encode($meta).','.JsonHelper::encode($maintenanceCPAccess).');');
                }
            }
        }
    }

    protected function initCountdown($timeRemaining)
    {
        // FIXME: Check why Craft calls getCpAlerts twice
        static $fixme = 0;
        if ($fixme == 0) {
            $fixme++;
            craft()->templates->includeJs('new Craft.MaintenanceCountdown("maintenancecountdown",'.JsonHelper::encode($timeRemaining).');');
        }
    }
}

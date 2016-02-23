<?php
namespace Craft;

class MaintenanceTwigExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'Maintenance';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getGlobals()
    {
        return array(
            'isCpMaintenance' => craft()->maintenance_variables->isCpMaintenance(),
            'isSiteMaintenance' => craft()->maintenance_variables->isSiteMaintenance(),
        );
    }
}

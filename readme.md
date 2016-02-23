# Maintenance plugin for Craft CMS

![screenshot][1]

The plugin provides tools to help you do maintenance on your Craft CMS website:

- Display a “Maintenance in progress” overlay in the Control Panel when the site is undergoing scheduled maintenance
- Announce upcoming maintenance on the dashboard and with notification banners
- Use the dashboard widget to leave your client a message

## Installation

The plugin is available on Packagist and can be installed using Composer. You can also download the [latest release][2] and copy the files into craft/plugins/maintenance/.

```
$ composer require carlcs/craft-maintenance
```

## Maintenance Announcements

Set up maintenance announcements or plain messages from the plugin’s settings page. You can use markdown to add links or to format the message. To configure scheduled maintenance you have to set a start date in the announcement settings and enable “Backend Maintenance” and/or “Frontend Maintenance”.

Create a new “Maintenance Announcements” widget to display all notifications on the users’ Dashboards. Upcoming maintenance will also be announced with a banner notification.

## Maintenance Modes

When backend maintenance mode is active, users navigating the Control Panel will be shown a full screen “Maintenance in progress” overlay. It informs them about the undergoing maintenance, in order to prevent from useless data entry.

The frontend maintenance mode redirects all (frontend) requests to /503, your “service unavailable” page. You can exclude URLs from being redirected and whitelist visitor IP addresses in the plugin settings.

**Note:** the plugin doesn’t do “content freeze” in a way, that it actually *prevents* data from being saved or changed in the database. That being said, there are template variables provided and a plugins API.

## Settings

The plugin can be configured from a craft/config/maintenance.php config file or from Settings/Maintenance.

In Settings/Users you can assign user permissions to configure the  “Maintenance in progress” overlay or to grant individual user groups access while frontend maintenance is carried out.

## Template Variables

The plugin provides template variables to get the active announcement model, or to check whether the site is currently undergoing maintenance

#### `isCpMaintenance`

Returns whether the Control Panel is currently undergoing maintenance.

```twig
{{ isCpMaintenance ? 'Shop closed' : '<a href="/shop">Shop</a>' }}
```

#### `isSiteMaintenance`

Returns whether the site is currently undergoing maintenance.

```twig
{{ set reason = isSiteMaintenance ? 'scheduled' : 'unscheduled' }}
```

#### `getAnnouncement( timeInAdvance )`

Returns the latest, either currently active or soon to be activated maintenance announcement.

```twig
{% set announcement = craft.maintenance.getAnnouncement('2 hours') %}

{% if announcement and announcement.blockSite %}
    <span>{{ announcement.message }}</span>
{% endif %}
```

## Planned features

- Quick set up maintenance via environment variable


  [1]: https://github.com/carlcs/craft-maintenance/blob/master/resources/screenshot.png
  [2]: https://github.com/carlcs/craft-maintenance/releases/latest

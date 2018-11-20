# CHANGELOG
All notable changes to this project will be documented in this file.


## [2.5.7] - 2018-10-23
* Fix: Leads not tracking correctly with the new 1.6.0 api.
* Fix: Added support to set custom intervals for cron jobs.

## [2.5.6] - 2018-10-09
* Fix: Now uses IDX api version 1.6.0.

## [2.5.5] - 2018-08-02
* Fix: Dev feature impress_showcase_property_html filter fixed
* Fix: Cache option names updated so changes made on Leads and Searches page display immediately.
* Fix: Option set when API limit exceeded to prevent additional API calls until limit is reset.

## [2.5.4] - 2018-06-28
* Fix: Property widgets and shortcodes now have a default sort option that respects a saved link's sorting method.
* Fix: Fatal error on API exhaustion.

## [2.5.3] - 2018-05-24
* Fix: Javascript error with map search widget when using IMPress - IDX Middleware widget.
* Fix: Invalid regex in omnibar autocomplete API.

## [2.5.2] - 2018-05-18
* Fix: Fatal error on PHP < 5.5. Minimum required PHP version increased to 5.6.

## [2.5.1] - 2018-05-18
* New: Omnibar now supports address autocomplete by default.
* New: Middleware widgets are no longer individually imported. A single IMPress - IDX Dashboard Widget is available to use instead. Migration script will replace any old widgets in use upon upgrade.
* Fix: Gravity forms lead push failing when default labels of required fields are changed.
* Fix: Deprecated warning on PHP 7.2+ for Middlware widgets.

## [2.5.0] - 2018-04-19
* New: Display saved links in property showcase and carousel widgets and shortcodes!
* Fix: City links widget with property count uses single API call per MLS to get count.
* Fix: Uninstall hook moved to separate uninstall.php file and cleanup routines more thorough.

## [2.4.5] - 2018-03-14
* Fix: Yoast SEO noindex detection

## [2.4.4] - 2018-03-07
* New: Can now add password field to lead sign up widget and shortcode
* New: Now notifies you if Yoast SEO is causing your pages to not be indexed by search engines
* Fix: No longer loads multiple versions of Font Awesome
* Fix: City Links now show 0 when there are no properties in those cities
* Fix: Error when using Carousel shortcode with impress_carousel_property_html filter

## [2.4.3] - 2018-02-08
* New: Lead Sign Up widget button text is now customizable
* New: Lead Login widget can now have a password field
* New: Agent routing now possible with Gravity Forms
* Fix: Adding custom fields to Omnibar in Firefox no longer errors
* Fix: Removed cURL dependency

## [2.4.2] - 2018-01-24
* Fix: Permissions issue with adding/editing/deleting wrappers
* Fix: Update Carousel script to v2 for compatibility issues

## [2.4.1] - 2018-01-23
* New: For multi-user IDX accounts, now you can filter property widgets by agent.
* New: Multisite feature: Assign agents to sites on a network to have their agentHeaderID added to widgets and IDX url's for lead tracking.
* New: Developer feature: Added filters (impress_carousel_property_html and impress_carousel_property_html) to modify property widget/shortcode HTML output.
* Fix: Default photo URL updated to HTTPS.

## [2.4.0]
* New: Added option to display listing counts to city list widget and shortcode. Max 50 cities.
* New: Added signup date to lead management table.
* Fix: Resolved issued when searching for multi-part addresses.
* Fix: Omnibar scripts explicitly enqueued in footer.
* Fix: Issue with property carousel or showcase widgets displaying zeroes when there are no results.
* Fix: Add Shortcode button on non-standard wp_editor instances removed for compatibility issues.
* Fix: Wrapper CSS specificity increased so only intended elements are targeted to hide.
* Fix: Widget code cleanup.

## [2.3.5]
* Fix: Update additional deprecated URLs for SSL compatibility
* Fix: Improved messaging for API status codes

## [2.3.4]
* Fix: Cron schedule filter modified to prevent interference with other scheduled cron jobs
* Fix: Broken knowledge base links on settings page
* Fix: Load select2 script in footer to prevent conflict with custom select2 script packaged with Avada theme
* Fix: Deprecated URLs causing console errors in Admin Dashboard Edit pages

## [2.3.3]
* New: Added reCaptcha to lead signup widget to prevent spam signups
* Fix: Alignment of IDX logo icon

## [2.3.2]
* Fix: Omnibar with extra fields failing due to missing sort order

## [2.3.1]
* Fix: Omnibar extra fields UX enhancement reverted due to issues with form submission

## [2.3.0]
* New: Added interface for creating saved searches and lead saved searches
* New: Added option for omnibar default results sorting
* New: Implemented material design dialogs instead of browser native dialogs
* New: Added idx-wrapper-tags shortcode to shortcode UI
* New: Added filter for developers to modify IDX meta tags added to wrapper (h/t imforza)
* Fix: Multisite compatibility - Omnibar location list moved to site uploads folder
* Fix: Omnibar extra fields UX enhancement - fields names distinguished from values
* Fix: Gravity Forms lead import tooltip instruction clarified

## [2.2.1]
* Fix: Square feet showing truncated values in widgets and shortcodes
* Fix: Added email validation for lead management
* Fix: Lead management times are now offset based on WP timezone settings
* Fix: PHP notice in lead management UI
* Notice: Dropped support for PHP < 5.4

## [2.2.0]
* New: Added Lead Management interface so you can manage leads directly in WordPress
* New: Capture leads with integration with popular WordPress form plugins (Gravity Forms, Ninja Forms, Contact Form 7)
* New: Widgets updated to use selective refresh in WP > 4.5
* Tweak: Refactored plugin to remove IoC container that caused conflicts on some hosting platforms
* Tweak: Limit use of eval that caused false positive security warnings in some scanners
* Tweak: Removed historical as an option for property widgets as its no longer available in the API
* Tweak: Removed disallowed fields from Omnibar search
* Tweak: Removed ineffective Equity IP blacklist functionality

## [2.1.5]
* Added compatibility with custom user roles for IDX Pages.
* Removed protocol from widget src for SSL compatibility.
* Fixed error with Dashboard widget when lead has no last name.

## [2.1.4]
* Fixed issue with API URL in WP 4.6+
* Refactored register widget function to use core functions
* Added screen-reader-text CSS for themes without it

## [2.1.3]
* Fixed the warning issue for showcase shortcodes as well.
* Fixed disclaimer markup so it only displays if required.
* Fixed issue with saved links with no results in shortcodes and widgets.

## [2.1.2]
* An issue where warnings were displayed for some showcases and carousels has been fixed.

## [2.1.1]
* IDX Pages are now automatically updated when their URLs change allowing more seamless domain/subdomain changes for IDX accounts.
* Error handling has been added to the IMPress Lead Signup widget for duplicate accounts, blank fields, and invalid email addresses.
* An issue has been fixed where IMPress Carousel widgets and shortcodes displayed listing count was not being followed on smaller screens.
* Verbiage for the min price field has been updated for the omnibar to clarify Extra Fields must be enabled to display it.
* The styling was updated for the IDX icon where it was misaligned in the admin bar on the front end.
* Courtesies are now displayed on IMPress Showcases and Carousels when required by MLS rules.

## [2.1.0] - 2016-04-14
* Adds Min Price option for the IMPress Omnibar Widget and Shortcode.
* IMPress Showcase and Carousels now use the address for the image title attribute for better SEO.
* The Omnibar main input has been updated for better accessibility with screen readers.
* A Shortcode has been added to make regular WP pages a wrapper for incompatible plugins.
* A bug has been fixed where the omnibar was not updating properly from the Omnibar Settings page.
* The Omnibar Settings page has been updated for a better UX.
* A wrapper can now be applied to a specific IDX Page when editing IDX Pages.
* Wrappers are now more compatible with other plugins.
* Wrapper styling has been updated to hide common irrelevant meta data from IDX pages.
* A new Dashboard widget has been added for a convenient overview of Leads and Listings.
* Fixed an issue where IDX pages were not imported at all and caused slow downs on slower servers.

## [2.0.3] - 2016-03-25
* Fixed an issue where plugin review prompt could not be dismissed.

## [2.0.2] - 2016-03-11
* Fixed an issue where a saved links field was incorrectly displaying on Carousel and Showcase shortcodes for Lite accounts (This only works for Platinum accounts).
* Fixed a similar issue where saved links were displaying a UI to apply page level wrappers to them for Lite accounts.
* Incorporated an IoC Container into the plugin for a cleaner coding structure.
* Equity accounts now have ip blacklisting protection against scrapers stealing website content.
* Added option to open several IMPress widgets and shortcodes in a new window.
* Disable the IDX Broker Origin plugin if active to prevent conflicts.

## [2.0.1]
* Fixed a minor issue where a notice was displaying for users with error reporting enabled.

## [2.0.0]
* The IDX Broker plugin has been renamed as the IMPress plugin.
* Five new WordPress native IMPress widgets and shortcodes have been added to the plugin: lead login and signup widgets, a city links widget, and showcase and carousel widgets have been added. Take advantage of these new widgets to deliver the best experience for your website visitors.
* Each of these widgets has customization options right within WordPress including the ability to disable styling. Designers can use this to gain more control of the appearance of these widgets.
* IDX Pages can now have excerpts and thumbnails applied through the new IDX Pages link in the admin menu.
* Included several Customization Options for the Omnibar.
* The IDX Shortcode UI has been modified to have a better UX.
* The WordPress Help button is now available for learning about the plugin features.
* Lite accounts will have an Upgrade Account link available for easy upgrading to Platinum.
* Plugin links have been added to the admin bar at the top giving easy access to common pages for admins.
* Added composer, npm, and gulp support.

## [1.3.2]
* Fix an issue where upgrading resulted in a 500 error for clients with many saved links.
* Fix an issue with unexpected results with IDX pages switching between older and newer versions of the plugin.

## [1.3.1]
* Fixed an issue with duplicate start and stop tags for dynamic wrappers when Equity is installed.
* Fixed a bug where errors were being reported.
* Fixed an issue where the omnibar would not load or update on servers with strict restrictions.

## [1.3.0]
* Improved compatability with WP caching plugins fixing a bug that caused an API overage.
* Simplified the Admin UI.
* The IDX Shortcode UI has been redesigned and is now independent of the visual editor allowing shortcodes to be added in the text view of a page or post in addition to the visual view.
* IDX Pages and Wrappers are now stored as custom post types no longer cluttering the Pages section of WordPress. You can still link to IDX pages as before through Appearance > Menus or via shortcodes.
* Copying and Pasting the URL into Designs > Wrappers is no longer required for global wrappers. Creating a Global Wrapper page now automatically updates your IDX pages to use the new Global Wrapper Page via the API. Wrappers can also now be applied to a specific IDX page by editing a Wrapper and setting the Apply Wrapper to IDX Pages metabox before saving.
* Map Search Widget scripts are now only loaded when a map search widget is on the page speeding up the rest of your website.
* This plugin now has its own Top Menu. Say goodbye to searching for plugin preferences in the Settings Menu.
* The Admin page is now fully responsive allowing you easily to make WordPress changes from your mobile device.

## [1.2.4]
* Fixed bug where a Parse Error was displayed instead of a notification to upgrade the PHP version to higher than 5.2 when activating.
* Fixed an issue with shortcodes and widgets not working properly for some users.
* Added backwards compatability with legacy dynamic wrapper usage in themes.

## [1.2.3]
* Fixed bug with omnibar data not updating properly.

## [1.2.2]
* Restructured code and made it more WP4.3 and PSR compliant.
* Fixed bug with widgets not being cached properly.
* Fixed display issue with extra fields displaying on the regular Omnibar for a split second on load.
* Automatically update omnibar data once a day.
* Dropped support for PHP 5.2 as it is deprecated. For more information, see PHP supported versions: http://php.net/supported-versions.php

## [1.2.1]
* Updated code for compatibility with WP4.3 release.
* Improved acceptable Omnibar values.

## [1.2.0]
* Added two types of Omnibar Widgets to the plugin under Appearance > Widgets and as shortcodes. To refresh the cities, counties, and zipcodes for this widget, hit the Refresh Plugin Options button under the Settings tab of the IMPress for IDX Broker plugin. Cities, Counties, and Zipcodes are all active ones on the account.

## [1.1.7]
* Fix savedLink page tile issue.

## [1.1.6]
* Updated API calls to use the newest version of the IDX Broker API
* Fully tested to be compatible with WordPress v4.1

## [1.1.5]
* Fixed widget drag and drop bug
* Update plugin settings labels.

## [1.1.4]
* Fixed can't add tons of savelinks and systemlinks pages issue.

## [1.1.3]
* Added create dynamic wrapper page, clients can create a page for dynamic wrapper on the setting page.
* Fixed idx button on Multi site WP.

## [1.1.2]
* Fixed minor bug causing some Shortcodes to disappear

## [1.1.1]
* Changed MapQuest API version to v1.0
* Updated Leaflet version to v0.7.2

## [1.1.0]
* Fully tested to be compatible with WP v3.8.
* Fixed small bugs with certain characters in widget titles causing an error within the plugin.
* Added MapQuest map search library, clients now have the option between Bing or MapQuest for their map search widget.
* Added ability to hide IDX Broker widget titles by using keyword '!%hide_title!%' as the widget title inside the WordPress dashboard. This functionality will be extended by adding additional keywords in future revisions.
* Small bug fixes to IDX Broker shortcodes causing issues with multi-site installations.
* More detailed responses from the IDX Broker API if an error occurs.

## [1.0.9]
* Added helpful links to plugin. Fully tested to 3.6.1. Added Bing Map search library for users who add the map widget.

## [1.0.8]
* Remove Beta image from control panel. Remaining Beta text from readme.

## [1.0.7]
* Added support for php start and stop tags. Some users migrating from the original IDX Broker may have used an older method of adding the dynamic wrapper tags to their theme that was dependant on our original plugin. This update prevents errors when those users disable our original plugin.

## [1.0.6]
* Added shortcode functionality. The plugin now adds an IDX button to the Visual editor which allows you to add shortcodes for the various page links and widgets IDX Broker provides.

## [1.0.5]
* Cleaned up naming convention inconsistencies.

## [1.0.4]
* Additional CSS clean up; specific to certain installations/WP versions.

## [1.0.3]
* Cleaned up various CSS issues and removed excess button options from spec.

---
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

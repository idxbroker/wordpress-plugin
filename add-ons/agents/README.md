# IMPress Agents #
Author: Agent Evolution
Author URL: http://www.agentevolution.com/
**Contributors:** agentevolution, davebonds, chadajohnson, idxco

**Tags:** agents, employees, employee directory, agent directory, agents, idx broker, idx, impress

**Requires at least:** 4.3.0

**Requires PHP:** 5.4

**Tested up to:** 4.9.4

**Stable tag:** 1.1.4

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html


Employee Directory tailored for Real Estate Offices.

## Description ##

IMPress Agents provides you with a full employee directory, however it is ideal for Real Estate offices.

This plugin adds a custom post type for Employees with post meta fields for employee contact info. It uses included templates to display the contact info for single and archive pages, or these can be overridden in your theme.

Adds taxonomies for Offices and Job Types to show employees by location and/or job title. Or add custom taxonmies to categorize to your needs. If using WP 4.4+, you can add images to taxonomy terms and display an image for an office, job title, or an custom taxonomy term.

If using the [IMPress Listings](https://wordpress.org/plugins/wp-listings/) plugin, you can connect Employees to Listings. *Requires the [Posts 2 Posts](https://wordpress.org/plugins/posts-to-posts/) plugin*

*Coming soon: Import agent details from the IDX API. Add more details and have them connected to imported listings from your [IDX Broker](http://www.idxbroker.com/) account!*
*Coming soon: Add your favorite vendors!*

## Installation ##

1. Install from the WP Admin Dashboard – or download, then unzip, and upload the entire `impress-agents` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Begin adding employees to the directory.

## Frequently Asked Questions ##

### I have a support issue, where do I go? ###
Just post to the support forum here (https://wordpress.org/support/plugin/impress-agents/)

### Can I use this without an IDX account ###
Certainly! It functions as an employee directory, with or without an IDX account.

### How can I suggest improvements? ###
Just post to the support forum here on [wordpress.org](https://wordpress.org/support/plugin/impress-agents/) or developers can post to the [IMPress Agents Github repo](https://github.com/agentevolution/impress-agents).

## Screenshots ##

###1. WP Admin > Add New
###
[missing image]


###2. WP Admin > Employees
###
[missing image]


###3. WP Admin > Employee Taxonomies
###
[missing image]


###4. WP Admin > Settings
###
[missing image]


###5. Front End > Single Employee
###
[missing image]


###6. Front End > Employee Archive
###
[missing image]


###7. Front End > Employee Widget
###
[missing image]


###8. Front End > Connected Listings
###
[missing image]


## Changelog ##

### 1.1.4 ###
*Released 04.03.2018*
* Fix: Pagination issue
* Fix: Default sort order is now by last name meta field
* Fix: Changed Font Awesome handle to prevent conflicts

### 1.1.3 ###
*Released 12.22.2016*
* Added: Support for selective refresh of widgets
* Fix: Added sortable parameter when registering post type connections

### 1.1.2 ###
*Released 09.26.2016*
* Fix: Update npmcdn link with unpkg link for external scripts

### 1.1.1 ###
*Released 08.18.2016*
* Fix: Update for PHP7 compatibility
* Fix: Issue with imported agents changing to draft

### 1.1.0 ###
*Released 05.26.2016*
* Added: Posts 2 Posts functionality to connect to IMPress Listings
* Added: Order and Orderby parameters to Featured Agent widget and shortcode
* Added: Ability to show random agent in Featured Agent widget
* Added: Display link to listings on Archive page if Agent has listings
* Updated: Improved responsive CSS

### 1.0.2 ###
* Fix: Fatal error when calling is_plugin_active

### 1.0.1 ###
* Fix: Issue with imported agents creating duplicate images.
* Added: Automatic migration of posts from Genesis Agent Profiles to IMPress Agents

### 1.0.0 ###
* Initial public release

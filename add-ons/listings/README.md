# IMPress Listings #
**Author:** IDX Broker

**Author URL:** https://www.idxbroker.com/

**Contributors:** IDX, LLC

**Tags:** real estate, listings, property, properties, listing search, idx, idx broker, mls, agentpress

**Requires at least:** 4.0

**Tested up to:** 5.5.1

**Stable tag:** 2.6.1

**Requires PHP:** 5.6

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

## Description ##

Have full control of how your featured listings are presented on your real estate WordPress website.

IMPress Listings creates a fully responsive listing management system and works with nearly any WordPress theme. The plugin allows MLS integration through the use of the [IMPress for IDX Broker](https://wordpress.org/plugins/idx-broker-platinum/) plugin. IMPress Listings also allows for manual entry and management of listings directly within the WordPress interface.

### Demo ###
View a demo of the plugin in action: [IMPress Listings demo](http://impresslistings.com/)

> **Importing from your MLS**
> No more typing in listing information that you have already added to your MLS. With IMPress Listings and IMPress for IDX Broker, you can automatically import listing details.
>
> *IDX Broker subscription required.

### Default Taxonomies and Terms ###

* Status (Active, Sold, Pending, For Rent, Reduced, New)
* Property Types (Residential, Condo, Townhome, Commercial)
* Location
* Features

Use the taxonomy creation tool to create your own way of classifying listings (i.e. bedrooms, bathrooms, locations, price ranges) and use those taxonomies to allow users to search for listings. Then, reorder the taxonomies as needed.

Find listings by taxonomy using filters in the WordPress admin.

### Google My Business ###

Connect IMPress Listings to your verified Google My Business profile to generate and schedule timely posts, photos of your listings, and more.

The automatic scheduler can be used to create and share posts to highlight featured listings, open house announcements, recent sales, local expertise, and more.

Posts have the potential to bring leads and clients directly to your IDX-enabled website for more home search opportunities. Google My Business posts are archived on a weekly basis, so automating the process with the scheduler is an easy way to maintain your real estate business’s online presence.

* Automatic posting requires a verified Google My Business account with a verified location.

### Migrating from AgentPress Listings ###

If you're using the [AgentPress Listings](https://wordpress.org/plugins/agentpress-listings/) plugin, we've made it easy to switch to IMPress Listings. The plugin uses the same post type name and data fields (plus several more) so all your entered listing data will remain in place.

### Property Display ###

IMPress Listings automatically creates individual listings pages for your imported properties that can be managed in your WordPress dashboard. These pages can be linked, shared or searched from anywhere else on your site.

Choose to show or hide specific fields (pricing, square ft, etc.) with one click.

Supported property fields:
* Price
* Address
* Country
* MLS Number
* Year Built
* Floors
* Square Feet
* Lot Square Feet
* Bedrooms
* Bathrooms
* Half Baths
* Garage
* Pool
* Open House date and time
* Photo gallery
* Video or virtual tour
* Map
* and more!

### Contact Forms ###

Listing pages include a contact form for visitors to inquire about a property, or the form can be replaced with your own form plugin shortcode.

Save time by adding a contact form to all listings instead of one at a time.

### Flair for Developers ###

A lot of developer goodies came in WordPress 4.4, including API support. Developers looking to use the latest WordPress tools will love IMPress Listings.

We have built in WordPress API support for the listing post type and default taxonomies. This will allow skilled developers to create their own applications around listing content.

There is also support for taxonomy featured images. Assign an image for active properties, solds, featured listings, neighborhoods, or any other taxonomy you might create.

## Installation ##

1. Upload the entire `wp-listings` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Begin creating listings and listing taxonomies

### How to use the listings shortcode ###

### Basic usage ###
Just enter the following shortcode on any post or page

`[listings]`

### Advanced usage ###
The shortcode accepts the following parameters:

`id` = listing post id (accepts one or more id's), exclusive, cannot be combined with other parameters, except for columns
`limit` = limit the number of posts to show, defaults to all
`columns` = display output in columns, accepts values 2-6, default is 1 column
`taxonomy` = taxonomy to display (must be used with the term parameter)
`term` = term to display (must be used with the taxonomy parameter)

Example advanced usage:
`[listings taxonomy="status" term="active" limit="10" columns="3"]`
This will display all listings in the "Status" taxonomy, assigned to the "Active" term, limited to 10 listings, in 3 columns

## Frequently Asked Questions ##

### Can I import listings from my MLS? ###

Yes. With an IDX Broker subscription and adding their IMPress for IDX Broker plugin to your site, IMPress Listings can import featured properties from the MLS.

### I already use the AgentPress Listings plugin, can I use this plugin? ###

Yes. This plugin can be used instead of the AgentPress Listings plugin. It uses the same post type name and custom field names (along with several new fields), so the posts you've added using AgentPress, along with the associated meta data, will remain attached to the listing post. Just be sure to deactivate AgentPress before activating IMPress Listings.

### How can I create a custom single listing template? ###

Name your template file `single-listing-CUSTOM-NAME.php` (replace CUSTOM NAME with your own descriptive name). You can use the single-listing.php within the plugins /includes/views/ folder for a guide on how to display the post type data. You'll need to include the following block of text at the top of your custom template:
`/*
Single Listing Template: Test Template
Description: Give it a description to help identify
*/`

### How can I remove the default property status terms or property type terms? ###

It's possible to remove the default property status terms by using a filter in your theme or custom plugins. Here is an example for the status terms:

`/* Remove Default Status Terms from IMPress Listings */
add_filter( 'wp_listings_default_status_terms', 'custom_default_status_terms' );
function custom_default_status_terms() {
  $status_terms = array();
  return $status_terms;
}`

Here is an example for the property terms:

`/* Remove Default Property Terms from IMPress Listings */
add_filter( 'wp_listings_default_property_type_terms', 'custom_default_property_type_terms' );
function custom_default_property_type_terms() {
  $property_type_terms = array();
  return $property_type_terms;
}`

## Changelog ##

## 2.6.1 ##

## 2.6.0 ##
*Released 10-20-2020*
* New: Google My Business redesign
* Fix: jQuery error that could appear when attempting to import IDX Broker listings
* Fix: Replaced usage of ABSPATH for including files
* Fix: Resolves rest route registration warnings

## 2.5.3 ##
*Released 6-30-2020*
* Fix: Error handling compatibility issue during the listing import process when using PHP 7+
* Fix: Resolves issue that prevented use of a theme provided single-listing.php as the default listing post template

## 2.5.2 ##
*Released 6-17-2020*
* Fix: Resolves minor styling issue on Google My Business scheduling page

## 2.5.1 ##
*Released 6-11-2020*
* Feature: Automated posting to Google My Business (for IDX Broker users with a Platinum-level account)
* Fix: Resolves issue where listing posts would not display properly on the /listings/ page
* Fix: Custom listing post templates can be loaded from the current theme's directory
* Fix: Listing posts missing link to full IDX Broker details page if setting is enabled
* Fix: Resolves console error that could appear on some listing posts

## 2.5.0 ##
*Released 3-19-2020*
* Feature: Ability to import advanced field data with IDX Broker listings (requires an IDX Broker account and the IMPress for IDX Broker plugin installed, version 2.6.0 or higher)
* Feature: Layout templates have been added to listings posts

## 2.4.2 ##
*Released 12-23-2019*
* Fix: Resolved error preventing IDX listings from appearing in the import section

### 2.4.1 ###
*Released 10-10-2019*
* Update: Improved listing import functionality

### 2.4.0 ###
*Released 07-23-2019*
* Updated: New look for the Import IDX Listings page
* Feature: Better error reporting on listings that fail to import

### 2.3.3 ###
*Released 03-05-2019*
* Fix: IDX Broker listing import

### 2.3.2 ###
*Release 08-28-2018*
* Fix: Changed Status taxonomy REST base to listing-status to prevent collision with post status.

### 2.3.1 ###
*Released 03-25-17*
* Fix: Change font awesome handle to prevent collision with other loaded versions
* Fix: Change sold listings to use propStatus instead of archiveStatus
* Fix: Change listing importer to not append to status taxonomy when updating, only replace
* Fix: Added delay to masonry script on listing importer page to allow everything to load before initializing
* Fix: Added acres field in lieu of lot sq ft. Listing importer now respects this field.

### 2.3.0 ### 
*Released 05-25-17*
* Feature: Added option to set title/permalink for imported listings
* Feature: Added option to automatically import featured listings
* Feature: All featured listing images are now added to imported listings 
* Updated: Settings page now uses tabbed sections for better UX

### 2.2.2 ###
*Released 01-12-17*
* Fixed: Fatal error on single listing template
* Feature: Option to select default author for imported listings

### 2.2.1 ###
*Released 12-13-16*
* Feature: Option to add link to IDX Broker details page on imported listings
* Fixed: Imported IDX Broker listings use the proper status for sold listings

### 2.2.0 ###
*Released 10-20-16*
* Feature: Option to send default contact form entries to IDX middleware as a lead
* Feature: Delete all option to IDX imported listings
* Feature: Support for selective refresh for widgets in the customizer
* Fixed: Imported listings - Issue with price field being deleted on update
* Fixed: PHP warnings with some MLS disclaimers on imported listings

### 2.1.3 ###
*Released 09-13-16*
* Fixed: Imported listings - Image markup only included if it exists in disclaimer
* Fixed: Imported listings - Ensure price is pulled from listingPrice field

### 2.1.2 ###
*Released 08-18-16*
* Fixed: Added Google Maps API key field in Settings
* Fixed: Notice query arg showing on incorrect post types
* Updated: Recaptcha class for PHP7 compatibility

### 2.1.1 ###
*Released 06-02-16*
* Fixed: Imported listings will not reset taxonomy terms on update
* Fixed: MLS compliance photo adjacent courtesy display
* Fixed: Disclaimer and courtesy parsing due to IDX API change

### 2.1.0 ###
*Released: 04-21-16*
* Added: Global disclaimer
* Added: Currency symbol and currency code support
* Added: Meta field for county
* Added: Video field shortcode support
* Fixed: Imported listings reverting to draft
* Fixed: Text domain added/changed for better translation support
* Fixed: PHP warning for unset options
* Fixed; PHP error when importing but return is empty
* Fixed: Spelling for lot size field
* Fixed: Label on settings page float issue
* Fixed: Jetpack Related Posts not showing on non-listing post types
* Updated: Support Text mode on gallery editor
* Updated: Helper functions

### 2.0.3 ###
* Fix: Update Listing importer to account for API change
* Fix: Listing importer update to use wp_cron to reduce immediate server load
* Added: Lazy Load added to Listing importer to reduce load times with many property images

### 2.0.2 ###
* Added: Listing post type support added to Jetpack sitemap
* Added: Connected Agents with IMPress Agents output on single listings
* Added: Google Recaptcha support for default contact form
* Fix: HTML class output for statuses
* Fix: WP API undefined function call

### 2.0.1 ###
* Updated: WP API support
* Fix: Custom wrapper on single listing template
* Fix: Fatal error on some web hosts
* Fix: Some IDX listing import settings not being respected

### 2.0 ###
* Added: Listing importer for IDX Broker. Import your listings into WordPress! Import additional photos and data with [Equity](http://www.agentevolution.com/equity/).
* Added: listing_meta shortcode to output arbitrary listing meta data. e.g use listing_meta key="price" to output price.
* Added: Listing meta fields for lat/long, country, half bath, custom disclaimer, and others.
* Added: Auto-map feature for listings with lat/long available. Option available to turn this off on single listings.
* Added: Global option for default form shortcode.
* Added: Option for a custom HTML wrapper to allow better compatibility with more themes.
* Added: Support for WP core REST API. Listings and default taxonomy endpoints added. GET and POST supported. GET method returns supported listing meta data, filterable with wp_listings_allowed_api_meta_keys
* Added: Listings admin menu filtering for default taxonomies: Status, Property Types, Locations
* Added: Listings added to "At a Glance" Dashboard widget.
* Added: Filter for additional details meta boxes using wp_listings_additional_details_meta_boxes
* Added: Filter for imported listing photo gallery markup using wp_listings_imported_image_markup (Equity only)
* Added: Support for featured images for listing taxonomy terms. (WP 4.4+ required)
* Added: Checkbox to hide price on individual listings and optionally enter price placeholder.
* Added: Admin notice class.
* Added: Support for List or Excerpt view in Listings Admin.
* Added: Support for new heading hierarchy, post type and taxonomy labels in WP 4.4.
* Updated: Font Awesome version to 4.5.0
* Fix: Support taxonomy template overrides.
* Fix: Support shortcodes in video field.

### 1.2.3 ###
* Update single listing template to display IDX imported data

### 1.2.2 ###
* Update widgets to use PHP5 object constructors
* Added support for listings to Jetpack JSON Rest API

### 1.2.1 ###
* Fixed i18n in shortcode output h/t newlocalmedia

### 1.2 ###
* Added basic schema support to Single Listings Template (single-listing.php)
* Added basic anti-spam check to native contact form in single-listing.php
* Added support for Jetpack publicize and markdown editors
* Added Jetpack shortcode links in messages about shortcodes
* Added DNS Prefetch Support for scripts used on Single Listings Template
* Added translation to text strings in listings shortcode
* Minified CSS for better site performance, SCSS files included
* Updates to script calls to improve site performance
* Updated jQuery Validate to 1.13.1
* Updated to Font Awesome 4.3.0
* Updated .pot file
* Fixed WP 4.1 issue with photo gallery editor meta box

### 1.1.3 ###
* Set with_front on rewrite rules for taxonomies. h/t bhubbard
* Fix undefined index for default state

### 1.1.2 ###
* Set with_front parameter to false in rewrite rules
* Update for WP 4.0 compatibility
* Update Font Awesome version number and URL
* Compatibility with Equity theme framework

### 1.1.1 ###
* CSS fix for thumbnail overlays with shortcode and archive pages
* Fix for undefined variables in featured istings widget and single listing contact form
* Make default registered terms (status, property-type) filterable
* Make default taxonomy names and slugs translatable, improve translation

### 1.1 ###
* Add `[listings]` shortcode to output listings on any post or page
* Add ability to change permalink slug to prevent conflicts
* Rewrite backend settings fields options to simplify

### 1.0.8 ###
* Add function to flush rewrite rules on plugin deactivation

### 1.0.7 ###
* Add Genesis CPT archive settings support
* Remove widget list item margins affecting some themes

### 1.0.6 ###
* Add classes to search widget output for additional styling
* Add priority to author box removal action on Genesis HTML5 themes
* Update Font Awesome version number and use minified version
* Change default taxonomies to be hierarchical (except features)

### 1.0.5 ###
* Updated class output for listing status overlay to remove spaces and replace with hyphens
* Remove unused Categories column from admin page
* Remove faulty responsive video CSS. Use fitvids.js instead for videos on single listings

### 1.0.4 ###
* Add HTML classes for CSS layout compatibility with a number of various themes
* Rename translation template file

### 1.0.3 ###
* Fix for connected agents markup conditional function call

### 1.0.2 ###
* CSS fix for widget overlays on themes that absolutley positioned them

### 1.0.1 ###
* Added ability to select an image size in the Featured Listing widget. This allows you to set a custom image size in your theme.

### 1.0 ###
* Initial public release

## Credits ##

Uses code from the following plugins:

[Single Post Template](http://wordpress.org/plugins/single-post-template/) by Nathan Rice

[AgentPress Listings](http://wordpress.org/plugins/agentpress-listings/) by StudioPress

[AgentPress Listings Taxonomy Reorder](http://wordpress.org/plugins/agentpress-listings-taxonomy-reorder/) by Robert Iseley


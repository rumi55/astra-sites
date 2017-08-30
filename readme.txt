=== Astra Free Sites ===
Contributors: brainstormforce
Donate link: https://wpastra.com/pro/
Tags: demo, theme demos, one click import
Requires at least: 4.4
Tested up to: 4.8.1
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import Astra Free Sites with just one click.

== Description ==

This plugin is an add-on for the Astra WordPress Theme. It offers a library of ready sites that can be imported for your website easily. Here is how it works:

1. Browse through the library of ready sites right from your WordPress backend.
2. Pick a site you like.
3. Install required plugins in one click
4. Import the site data.
5. Done!

Use this imported site as a base for your project and don't waste time starting from scratch.

Some of the currently available sites for import:

https://sites.wpastra.com/gardener-free/
https://sites.wpastra.com/hotel-free/
https://sites.wpastra.com/agency-free/
https://sites.wpastra.com/restaurant-free/
https://sites.wpastra.com/construction-free/
https://sites.wpastra.com/makeup-artist-free/
https://sites.wpastra.com/electrician-free
https://sites.wpastra.com/law-free/

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/astra-sites` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Appearance->Astra->Astra Free Sites to select the page to be displayed as header and footer.

== Screenshots ==
1. Select the demo you want to import.
1. Install and activate the required plugins.
1. Import the demo.

== Changelog ==

v1.0.6 - 30-Aug-2017
* Fix: Elementor settings import.

v1.0.5 - 29-Aug-2017
* New: Added filter `astra_sites_api_args` for adding extra arguments in api call.
* Enhancement: Plugin name updated from `Astra Sites` with `Astra Free Sites`.
* Fix: PHP error while ignoring users.

v1.0.4 - 21-Aug-2017
* New: Added filter `astra_sites_api_params` for adding extra params in api call.
* New: Added filter `astra_sites_api_args` for adding extra arguments in api call.
* New: Added filter `astra_sites_category_hide_empty` for showing categories which are not set for any site.

v1.0.3 - 11-Aug-2017
* Fix: Avoided Astra users from site import process.

v1.0.2 - 09-Aug-2017
* Fix: Listing appropriate next and previous Astra sites.
* Enhancement: Listing Astra sites though AJAX API call.

v1.0.1 - 04-Aug-2017
* New: Added Elementor plugin options support.
* New: Added Customizer CSS support.
* Enhancement: Avoided Lite Plugin version if Pro version is Installed. Now added support for Beaver Builder Plugin (Lite Version).
* Enhancement: Astra sites API call validated before import.
* Enhancement: Site logo imported from Astra sites.
* Fix: Bug where widgets created with SiteOrigin plugin were not being imported.

v1.0.0
* Initial release


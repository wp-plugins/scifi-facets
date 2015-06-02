=== scifi Facets ===
Contributors: dimitrov.adrian
Tags: date archive, taxonomy, terms, facet, faceted, search
Requires at least: 3.7
Tested up to: 4.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

scifi Facets is simple facet widget which allow adding a widget
for faceted search or category/taxonomy and date browsing for all post types and all taxonomies.

Require PHP 5.3


== Screenshots ==

1. Widget settings screen
2. How it looks


== Installation ==

1. Visit 'Plugins > Add New'
2. Search for 'scifi facets'
3. Activate scifi facets from your Plugins page.
4. Visit Appearance > Widgets and add '(scifi Facets) ... 'widgets to some of your widget area.


== Changelog ==

= 0.6.1 =
* Added message for unsupported PHP

= 0.6 =
* Fixed: bug when themes using widgets before script enqueue hook
* Tweak: Animating arrow on time archive blocks

= 0.5 =
* Added order field
* Fixed highlight current month, if no month is active (date facets)
* Changed hook from 'scifi_list_orderby' to 'scifi_facets_list_orderby'

= 0.4 =
* Added date facet widget
* Separated ordering part from main taxonomy widget
* Code improvement
* Fixed possible PHP warnings

= 0.3 =
* Added query/permalink configuration
* Added option to exclude terms for taxonomies
* Added multi select widget
* Separated visibility rule (single, archive)
* Other code tweaks

= 0.2 =
* First public release

= 0.1 =
* Initial bump

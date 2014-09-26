=== scifi_facets ===
Contributors: dimitrov.adrian
Tags: taxonomy, terms, facet, faceted, search
Requires at least: 3.7
Tested up to: 4.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

scifi_facets is simple facet widget which allow adding a widget
for faceted search or category/taxonomy browsing for all post types and all taxonomies.


== TODO ==
* Posts counter
* Define AND/OR logic
* Time/Date pickers
* Number formatter


== DEVELOPERS ==
Plugin also define some filters and actions, currently available are:
* Alter ordering 'scifi_list_orderby' parameter $orderby
* Alter class of taxonomy term links 'scifi_facets_formatter_links_classes' parameters $term_classes and optionally $term
* Alter formatters 'scifi_facets_formatters' parameter available formatters
* Alter terms before processed 'scifi_facets_prepare_tax_terms' parameter $terms, $instance


== Screenshots ==

1. Widget settings screen
2. How it looks


== Installation ==

1. Visit 'Plugins > Add New'
2. Search for 'scifi facets'
3. Activate scifi facets from your Plugins page.
4. Visit Appearance > Widgets and add '(scifi) Taxonomy facets' to some of your widget area.


== Changelog ==

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


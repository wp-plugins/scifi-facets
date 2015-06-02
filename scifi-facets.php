<?php

/**
 * Plugin Name: scifi Facets
 * Plugin URI:  http://wordpress.org/extend/plugins/scifi-facets/
 * Description: Add widget for faceted search or category/taxonomy browsing
 * Author:      Adrian Dimitrov <dimitrov.adrian@gmail.com>
 * Author URI:  http://scifi.bg/opensource/
 * Version:     0.6.1
 * Text Domain: scifi-facets
 * Domain Path: /languages/
 */


/**
 * Check for PHP version
 */
if (version_compare(phpversion(), '5.3.0', '<')) {

  /**
   * Backend message.
   */
  function _scifi_facets_notices_php() {
    echo '<div class="error"><p>';
    printf(__('scifi Facets plugin requires PHP >= 5.3.0, but you have PHP %s. In order to get it work you need to upgrade the PHP to 5.3.0, contact your hosting provider for furture information.'), phpversion());
    echo '</p></div>';
  }
  add_action('admin_notices', '_scifi_facets_notices_php');

  return;
}


/**
 * Localize the plugin.
 */
add_action('plugins_loaded', function() {
  load_plugin_textdomain('scifi-facets', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');

});


/**
 * Do on widgets init
 */
add_action('widgets_init', function() {

  // This should be here because enqueueing is called from widget.
  wp_register_script('scifi-facets', plugins_url('scifi-facets.js', __FILE__), array('jquery'), NULL, TRUE);

  // Include widgets classes.
  require_once 'widget-taxonomy.php';
  require_once 'widget-time.php';
  require_once 'widget-ordering.php';
});


/**
 * Register facets JS and CSS
 */
add_action('wp_enqueue_scripts', function() {
  wp_enqueue_style('scifi-facets', plugins_url('scifi-facets.css', __FILE__), array());
});

<?php

/**
 * Plugin Name: scifi facets
 * Plugin URI:  http://wordpress.org/extend/plugins/scifi-facets/
 * Description: Add widget for faceted search or category/taxonomy browsing
 * Author:      Adrian Dimitrov <dimitrov.adrian@gmail.com>
 * Author URI:  http://scifi.bg/opensource/
 * Version:     0.2
 * Text Domain: scifi_facets
 * Domain Path: /languages/
 */

/**
 * Localize the plugin.
 */
add_action('plugins_loaded', function() {
  load_plugin_textdomain('scifi_facets', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
});

/**
 * Do on widgets init
 */
add_action('widgets_init', function() {

  // Include widgets classes.
  require 'widget.php';

  // Register the widgets
  register_widget('Widget_Scifi_Facets');

});

/**
 * Register facets JS and CSS
 */
add_action('wp_enqueue_scripts', function() {
  wp_register_script('scifi-facets', plugins_url('scifi-facets.js', __FILE__), array('jquery'), 0.2, TRUE);
  wp_enqueue_style('scifi-facets', plugins_url('scifi-facets.css', __FILE__), array(), 0.2);
});

/**
 * Register facets formatters
 */
add_filter('scifi_facets_formatters', function($formatters = array()) {
  $formatters['select'] = '_scifi_facets_formatters_select';
  $formatters['links']  = '_scifi_facets_formatters_links';
  $formatters['tags']   = '_scifi_facets_formatters_tags';
  return $formatters;
}, 5);

/**
 * Get available orderby criterias
 *
 * @return array
 */
function _scifi_list_orderby() {
  static $orderby = NULL;
  if ($orderby === NULL) {
    $orderby = array(
      'title'         => __('Title', 'scifi_facets'),
      'date'          => __('Date', 'scifi_facets'),
      'modified'      => __('Last update', 'scifi_facets'),
      'comment_count' => __('Comments', 'scifi_facets'),
    );
    $orderby = apply_filters('scifi_list_orderby', $orderby);
  }
  return $orderby;
}

/**
 * Facets widget formatter - select
 *
 * @param string $taxonomy_name
 * @param array $terms
 * @param array $active_terms
 * @param array $widget_instance_settings
 */
function _scifi_facets_formatters_select($taxonomy_name, $terms, $active_terms, $widget_instance_settings) {
  $taxonomy_object = get_taxonomy($taxonomy_name);
  echo '<select class="scifi-facets-select">';
  if ($widget_instance_settings['includeall'] == 'includeall') {
    $link = remove_query_arg($taxonomy_object->query_var);
    printf('<option value="%s">&lt;%s&gt;</option>', $link, __('All', 'scifi_facets'));
  }
  foreach ($terms as $term) {
    $selected = selected( in_array(urldecode($term->slug), $active_terms), TRUE, FALSE);
    $link = add_query_arg($taxonomy_object->query_var, $term->slug);
    printf('<option value="%s" %s>%s</option>', $link, $selected, $term->name);
  }
  echo '</select>';
}

/**
 * Facets widget formatter - links
 *
 * @param string $taxonomy_name
 * @param array $terms
 * @param array $active_terms
 * @param array $widget_instance_settings
 */
function _scifi_facets_formatters_links($taxonomy_name, $terms, $active_terms, $widget_instance_settings) {
  $taxonomy_object = get_taxonomy($taxonomy_name);
  echo '<ul class="menu">';

  if ($widget_instance_settings['includeall']) {
    $term_classes = array('scifi-facets-widgets-tax-facet', 'taxonomy-term-all');
    if (!$active_terms) {
      $term_classes[] = 'scifi-facets-widgets-tax-facet-current';
    }
    $link = empty($taxonomy_object->rewrite['slug']) ? FALSE : home_url($taxonomy_object->rewrite['slug']);
    if ($link === FALSE || $widget_instance_settings['useqvars']) {
      $link = remove_query_arg($taxonomy_object->query_var);
    }
    $term_classes = apply_filters('scifi_facets_formatter_links_classes', $term_classes);
    printf('<li><a class="%s" href="%s" rel="nofollow">%s</a></li>', implode(' ', $term_classes), $link, __('All', 'scifi_facets'));
  }

  foreach ($terms as $term) {
    $term_classes = array('scifi-facets-widgets-tax-facet', 'taxonomy-term-' . $term->term_id);
    if (in_array(urldecode($term->slug), $active_terms)) {
      $term_classes[] = 'scifi-facets-widgets-tax-facet-current';
    }
    $term_classes = apply_filters('scifi_facets_formatter_links_classes', $term_classes, $term);
    if ($widget_instance_settings['useqvars']) {
      $link = add_query_arg($taxonomy_object->query_var, $term->slug);
    }
    else {
      $link = get_term_link($term, $taxonomy_name);
    }
    // if ($widget_instance_settings['showcount'] == 'showcount') {
    //   $term->name = sprintf('%s (%s)', $term->name, $term->_post_count);
    // }
    printf('<li><a class="%s" href="%s" rel="nofollow">%s</a></li>', implode(' ', $term_classes), $link, $term->name);
  }
  echo '</ul>';
}

/**
 * Facets widget formatter - tags
 *
 * @param string $taxonomy_name
 * @param array $terms
 * @param array $active_terms
 * @param array $widget_instance_settings
 */
function _scifi_facets_formatters_tags($taxonomy_name, $terms, $active_terms, $widget_instance_settings) {
  $taxonomy_object = get_taxonomy($taxonomy_name);

  if ($active_terms) {
    echo '<ul class="scifi-facets-terms-tags-active">';
    foreach ($terms as $term_key => $term) {
      if (!in_array(urldecode($term->slug), $active_terms)) {
        continue;
      }
      if ($widget_instance_settings['useqvars']) {
        $a = $active_terms;
        $key = array_search(urldecode($term->slug), $a);
        if ($key !== FALSE) {
          unset($a[$key]);
          $a = array_filter($a);
        }
        if (count($a) > 0) {
          $link = add_query_arg($taxonomy_object->query_var, implode(',', $a));
        }
        else {
          $link = remove_query_arg($taxonomy_object->query_var);
        }
      }
      else {
        $link = get_term_link($term, $taxonomy_name);
      }
      $term_classes = array();
      printf('<li class="%s"><a href="%s" rel="nofollow">%s</a></li>', implode(' ', $term_classes), $link, $term->name);
    }
    echo '</ul>';
  }

  echo '<ul class="scifi-facets-terms-tags-inactive">';
  foreach ($terms as $term_key => $term) {
    if ($widget_instance_settings['useqvars']) {
      $a = $active_terms;
      $a[] = $term->slug;
      $a = array_filter($a);
      $link = add_query_arg($taxonomy_object->query_var, implode(',', $a));
    }
    else {
      $link = get_term_link($term, $taxonomy_name);
    }
    $term_classes = array();
    // if ($widget_instance_settings['showcount']) {
    //   $term->name = sprintf('%s (%s)', $term->name, $term->_post_count);
    // }
    printf('<li class="%s"><a href="%s" rel="nofollow">%s</a></li>', implode(' ', $term_classes), $link, $term->name);
  }
  echo '</ul>';
}

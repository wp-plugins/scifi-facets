<?php


/**
 * Get URL for current instance
 *
 * @param $widget_instance_settings
 *
 * @return string
 */
function _scifi_facets_taxonomy_url($widget_instance_settings) {
  if ($widget_instance_settings['urlbase'] == '{custom}') {
    return home_url($widget_instance_settings['urlbase_custom']);
  }
  elseif ($widget_instance_settings['urlbase']) {
    return get_post_type_archive_link($widget_instance_settings['urlbase']);
  }
  return $_SERVER['REQUEST_URI'];
}


/**
 * Facets widget formatter - select
 *
 * @param string $taxonomy_name
 * @param array $terms
 * @param array $active_terms
 * @param array $widget_instance_settings
 */
function _scifi_facets_taxonomy_formatters_select($taxonomy_name, $terms, $active_terms, $widget_instance_settings) {
  $taxonomy_object = get_taxonomy($taxonomy_name);
  echo '<select class="scifi-facets-select">';
  if ($widget_instance_settings['includeall'] == 'includeall') {
    if ($widget_instance_settings['usepermalinks'] == 'usepermalinks' || empty($taxonomy_object->rewrite['slug'])) {
      $link = home_url($taxonomy_object->rewrite['slug']);
    }
    else {
      $link = remove_query_arg($taxonomy_object->query_var);
    }
    printf('<option value="%s">&lt;%s&gt;</option>', $link, __('All', 'scifi-facets'));
  }
  foreach ($terms as $term) {
    $selected = selected( in_array(urldecode($term->slug), $active_terms), TRUE, FALSE);
    if ($widget_instance_settings['usepermalinks'] == 'usepermalinks') {
      $link = get_term_link($term, $taxonomy_name);
    }
    else {
      $link = add_query_arg($taxonomy_object->query_var, $term->slug, _scifi_facets_taxonomy_url($widget_instance_settings));
    }
    printf('<option value="%s" %s>%s</option>', $link, $selected, $term->name);
  }
  echo '</select>';
}


/**
 * Facets widget formatter - select multiple
 *
 * @param string $taxonomy_name
 * @param array $terms
 * @param array $active_terms
 * @param array $widget_instance_settings
 */
function _scifi_facets_taxonomy_formatters_select_multiple($taxonomy_name, $terms, $active_terms, $widget_instance_settings) {
  $taxonomy_object = get_taxonomy($taxonomy_name);
  printf('<select class="scifi-facets-select-multiple" multiple="multiple" data-scifi-facets-addurl="%s" data-scifi-facets-removeurl="%s">',
    add_query_arg($taxonomy_object->query_var, '#slug#', _scifi_facets_taxonomy_url($widget_instance_settings)),
    remove_query_arg($taxonomy_object->query_var));
  foreach ($terms as $term) {
    $selected = selected( in_array(urldecode($term->slug), $active_terms), TRUE, FALSE);
    printf('<option value="%s" %s>%s</option>', $term->slug, $selected, $term->name);
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
function _scifi_facets_taxonomy_formatters_links($taxonomy_name, $terms, $active_terms, $widget_instance_settings) {
  $taxonomy_object = get_taxonomy($taxonomy_name);
  echo '<ul class="menu">';

  if ($widget_instance_settings['includeall']) {
    $term_classes = array(
      'taxonomy-term-all',
    );
    if (!$active_terms) {
      $term_classes[] = 'scifi-facets-current';
    }
    if ($widget_instance_settings['usepermalinks'] == 'usepermalinks' || empty($taxonomy_object->rewrite['slug'])) {
      $link = home_url($taxonomy_object->rewrite['slug']);
    }
    else {
      $link = remove_query_arg($taxonomy_object->query_var);
    }
    $term_classes = apply_filters('scifi_facets_formatter_links_classes', $term_classes);
    printf('<li><a class="%s" href="%s" rel="nofollow">%s</a></li>', implode(' ', $term_classes), $link, __('All', 'scifi-facets'));
  }

  foreach ($terms as $term) {
    $term_classes = array(
      'taxonomy-term-' . $term->term_id,
    );
    if (in_array(urldecode($term->slug), $active_terms)) {
      $term_classes[] = 'scifi-facets-current';
    }
    $term_classes = apply_filters('scifi_facets_formatter_links_classes', $term_classes, $term);

    if ($widget_instance_settings['usepermalinks'] == 'usepermalinks') {
      $link = get_term_link($term, $taxonomy_name);
    }
    else {
      $link = add_query_arg($taxonomy_object->query_var, $term->slug, _scifi_facets_taxonomy_url($widget_instance_settings));
    }
    printf('<li><a class="%s" href="%s" rel="nofollow">%s</a></li>', implode(' ', $term_classes), $link, "{$term->name} ({$term->count})");
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
function _scifi_facets_taxonomy_formatters_tags($taxonomy_name, $terms, $active_terms, $widget_instance_settings) {
  $taxonomy_object = get_taxonomy($taxonomy_name);

  if ($active_terms) {
    echo '<ul class="scifi-facets-tags-active">';
    foreach ($terms as $term) {
      if (!in_array(urldecode($term->slug), $active_terms)) {
        continue;
      }
      $a = $active_terms;
      $key = array_search(urldecode($term->slug), $a);
      if ($key !== FALSE) {
        unset($a[$key]);
        $a = array_filter($a);
      }
      if (count($a) > 0) {
        $link = add_query_arg($taxonomy_object->query_var, implode(',', $a), _scifi_facets_taxonomy_url($widget_instance_settings));
      }
      else {
        $link = remove_query_arg($taxonomy_object->query_var);
      }
      printf('<li><a href="%s" rel="nofollow">%s</a></li>', $link, $term->name);
    }
    echo '</ul>';
  }

  echo '<ul class="scifi-facets-tags-inactive">';
  foreach ($terms as $term) {
    if (in_array(urldecode($term->slug), $active_terms)) {
      continue;
    }
    $a = $active_terms;
    $a[] = $term->slug;
    $a = array_filter($a);
    $link = add_query_arg($taxonomy_object->query_var, implode(',', $a), _scifi_facets_taxonomy_url($widget_instance_settings));
    printf('<li><a href="%s" rel="nofollow">%s</a></li>', $link, $term->name);
  }
  echo '</ul>';
}


/**
 * Facets Widget class
 */
class Widget_Scifi_Facets_Taxonomy extends WP_Widget {

  /**
   * Formatters
   */
  private $formatters = array();

  /**
   * Class constructor
   *
   * @see WP_Widget::__construct()
   */
  function __construct() {
    
    $widget_ops = array(
        'classname' => 'scifi-facets-widget',
        'description' => __('Taxonomy facets', 'scifi-facets'),
    );
    
    $this->formatters = apply_filters('scifi_facets_taxonomy_formatters', array(
      'links' => array(
        'name' => __('Links', 'scifi-facets'),
        'cb' => '_scifi_facets_taxonomy_formatters_links',
      ),
      'tags' => array(
        'name' => __('Tags', 'scifi-facets'),
        'cb' => '_scifi_facets_taxonomy_formatters_tags',
      ),
      'select' => array(
        'name' => __('Drop down', 'scifi-facets'),
        'cb' => '_scifi_facets_taxonomy_formatters_select',
      ),
      'select_multiple' => array(
        'name' => __('Multiple select', 'scifi-facets'),
        'cb' => '_scifi_facets_taxonomy_formatters_select_multiple',
      ),
    ));
    
    parent::__construct('scifi-facets-taxonomy-widget', sprintf('(scifi Facets) %s', __('Taxonomies', 'scifi-facets')), $widget_ops);
  }

  /**
   * Prepare and sanitize widget settings.
   *
   * @param array $settings
   *
   * @return array
   */
  private function prepare_settings($settings) {
    $defaults = array(
      'title' => '',
      'taxonomies' => array(),
      'taxonomies_exclude_terms' => array(),
      'format' => '',
      'includeall' => '',
      'connectwithquery' => '',
      'usepermalinks' => '',
      'showinsingulars' => '',
      'showinarchives' => '',
      'showtaxnames' => '',
      'dependsof' => '',
      'urlbase' => '',
      'urlbase_custom' => '',
      'order' => '',
    );
    $settings = array_merge($defaults, $settings);
    if (!is_array($settings['taxonomies'])) {
      $settings['taxonomies'] = $defaults['taxonomies'];
    }
    if (!is_array($settings['taxonomies_exclude_terms'])) {
      $settings['taxonomies_exclude_terms'] = $defaults['taxonomies_exclude_terms'];
    }
    return $settings;
  }

  /**
   * Get the all terms acording widget instance settings
   *
   * @param array $settings
   *
   * @return array
   */
  private function _get_the_terms($settings) {

    global $wpdb, $wp_query;

    $terms = array();

    if ($wp_query->request && $settings['connectwithquery'] == 'connectwithquery') {

      // Get current query
      $posts_querystr = preg_replace('#\bLIMIT\s*(?:\d+)\s*(?:\,\s*(?:\d+))\b#Ui', '', $wp_query->request);
      $posts_querystr = preg_replace('#\bSQL_CALC_FOUND_ROWS\b#Ui', '', $posts_querystr);

      // Prepare WHERE...IN first because in posts_querystr may appear some % chars.
      $where_querystr = 'wptt.taxonomy IN (' . implode(', ', array_fill(0, count($settings['taxonomies']), '%s')) . ')';
      $qparams = array($where_querystr);
      foreach ($settings['taxonomies'] as $tax_name) {
        $qparams[] = $tax_name;
      }
      $where_querystr = call_user_func_array(array($wpdb, 'prepare'), $qparams);

      // Main query
      $querystr = "
      SELECT
        *,
        COUNT(*) as count
        FROM (
        SELECT
          wpt.* ,
          wptt.taxonomy
        FROM ({$posts_querystr}) posts
        INNER JOIN {$wpdb->term_relationships} wptr ON posts.ID = wptr.object_id
        INNER JOIN {$wpdb->term_relationships} wptra ON wptr.object_id = wptra.object_id
        INNER JOIN {$wpdb->term_taxonomy} wptt ON wptra.term_taxonomy_id = wptt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} wpt ON wptt.term_id = wpt.term_id
        WHERE {$where_querystr}
        GROUP BY posts.id) p
      GROUP BY term_id
      ORDER BY taxonomy ASC, name ASC";

      foreach ($wpdb->get_results($querystr) as $term) {
        $terms[$term->taxonomy][] = $term;
      }
    }
    else {
      foreach ($settings['taxonomies'] as $tax_name) {
        foreach (get_terms($tax_name) as $term) {
          $terms[$tax_name][] = $term;
        }
      }
    }
    return $terms;
  }

  /**
   * Implements widget
   * Render callback for the widget
   *
   * @see WP_Widget::widget()
   *
   * @param array $args
   * @param array $instance
   *
   */
  function widget($args, $instance) {

    extract($args, EXTR_SKIP);

    global $wp_rewrite;
    $instance = $this->prepare_settings($instance);

    if (empty($wp_rewrite->permalink_structure)) {
      $settings['usepermalinks'] = '';
    }

    if (is_singular() && $instance['showinsingulars'] != 'showinsingulars') {
      return;
    }

    if (!is_singular() && $instance['showinarchives'] != 'showinarchives') {
      return;
    }

    if (empty($this->formatters[$instance['format']]) || !is_callable($this->formatters[$instance['format']]['cb'])) {
      return;
    }


    if ($instance['dependsof'] && ($dep_tax_object = get_taxonomy($instance['dependsof']))) {
      if (!get_query_var($dep_tax_object->query_var)) {
        return;
      }
    }

    if (empty($instance['taxonomies'])) {
      return;
    }

    wp_enqueue_script('scifi-facets');

    echo $before_widget;

    if (!empty($instance['title'])) {
      echo $before_title . $instance['title'] . $after_title;
    }

    $tax_terms = $this->_get_the_terms($instance);
    foreach ($instance['taxonomies'] as $tax_name) {
      if (!empty($tax_terms[$tax_name])) {

        $tax_object = get_taxonomy($tax_name);

        // Group wrapper.
        echo '<div class="scifi-facets-group scifi-facets-group-taxonomy scifi-facets-format-' . $instance['format'] . '" id="scifi-facets-taxonomy-group-' . $tax_name . '">';

        if ($instance['showtaxnames'] == 'showtaxnames' || $instance['format'] == 'tags') {
          echo '<div class="scifi-facets-group-title">' . $tax_object->label . '</div>';
        }

        // Terms wrapper.
        echo '<div class="scifi-facets-group-list">';

        if (is_single()) {
          $active_terms = array();
          $single_post_terms = get_the_terms(get_the_ID(), $tax_name);
          if ($single_post_terms) {
            foreach ($single_post_terms as $term) {
              $active_terms[] = urldecode($term->slug);
            }
          }
        }
        else {
          $active_terms = get_query_var($tax_object->query_var);
          if ($active_terms) {
            $active_terms = array_map('urldecode', array_map('trim', explode(',', $active_terms)));
          }
          else {
            $active_terms = array();
          }
        }

        $tax_terms[$tax_name] = apply_filters('scifi_facets_prepare_tax_terms', $tax_terms[$tax_name], $instance);

        // Exclude terms.
        if (!empty($instance['taxonomies_exclude_terms'][$tax_name])) {
          $excludes = array_map('trim', explode(',', $instance['taxonomies_exclude_terms'][$tax_name]));
          foreach($tax_terms[$tax_name] as $key => $term) {
            foreach ($excludes as $exclude_term) {
              if ((is_numeric($exclude_term) && $exclude_term == $term->term_id) || $exclude_term == urldecode($term->slug)) {
                unset($tax_terms[$tax_name][$key]);
                break;
              }
            }
          }
        }

        // Order elements.
        if ($instance['order']) {
          $order_cb = create_function('$a,$b', 'return $a->slug < $b->slug ? -1 : 1;');
          usort($tax_terms[$tax_name], $order_cb);
          if ($instance['order'] == 'desc') {
            $tax_terms[$tax_name] = array_reverse($tax_terms[$tax_name], TRUE);
          }
        }

        call_user_func($this->formatters[$instance['format']]['cb'], $tax_name, $tax_terms[$tax_name], $active_terms, $instance);

        // /terms wrapper
        echo '</div>';

        // /group wrapper
        echo '</div>';
      }
    }

    echo $after_widget;
  }

  /**
   * Implements update().
   *
   * @param array $new_instance
   * @param array $old_instance
   *
   * @return array $new_instance
   */
  function update($new_instance, $old_instance) {
    return $new_instance;
  }

  /**
   * Implements form().
   * Widget form settings here.
   *
   * @param array $instance
   *
   * @return void
   */
  function form($instance) {

    global $wp_rewrite;
    $instance = $this->prepare_settings($instance);
    ?>

    <p>
      <label for="<?php echo $this->get_field_id('title')?>">
        <?php _e('Title:', 'scifi-facets')?>
      </label>
      <input type="text"
             class="widefat"
             id="<?php echo $this->get_field_id('title')?>"
             name="<?php echo $this->get_field_name('title')?>"
             value="<?php echo esc_attr($instance['title'])?>"
        />
    </p>
    
    
    <fieldset class="widefat">
      <?php _e('Taxonomies:', 'scifi-facets')?>
      <ul style="max-height:200px;overflow:auto;">
        <?php foreach (get_taxonomies(array('public' => TRUE), 'objects') as $tax_object):?>
          <li>
            <input type="checkbox"
                   id="<?php echo $this->get_field_id('taxonomies') . '_' . $tax_object->name?>"
                   class="<?php echo $this->get_field_id('taxonomies')?>-cl"
                   name="<?php echo $this->get_field_name('taxonomies')?>[<?php echo esc_attr($tax_object->name)?>]"
                   value="<?php echo esc_attr($tax_object->name)?>"
              <?php checked(in_array($tax_object->name, $instance['taxonomies']))?>
              />
            <label for="<?php echo $this->get_field_id('taxonomies') . '_' . $tax_object->name?>">
              <?php printf('%s (%s)', $tax_object->label, $tax_object->name)?>
            </label>

            <p class="exclude-terms">
              <label id="<?php echo $this->get_field_id('taxonomies') . '_' . $tax_object->name?>_exclude">
                <?php printf(__('Exclude (%s) terms:', 'scifi-facets'), $tax_object->name)?>
              </label>
              <input type="text"
                     class="widefat"
                     id="<?php echo $this->get_field_id('taxonomies') . '_' . $tax_object->name?>_exclude"
                     name="<?php echo $this->get_field_name('taxonomies_exclude_terms')?>[<?php echo esc_attr($tax_object->name)?>]"
                     value="<?php echo empty($instance['taxonomies_exclude_terms'][$tax_object->name]) ? '' : esc_attr($instance['taxonomies_exclude_terms'][$tax_object->name])?>"
                />
                <small>
                  <?php _e('* Exclude terms is list of coma separated slugs or id.', 'scifi-facets')?>
                </small>
            </p>
          </li>
        <?php endforeach?>
      </ul>
    </fieldset>
    <script>
      (function($) {
        $(document).ready(function() {
          $('.<?php echo $this->get_field_id('taxonomies')?>-cl')
            .on('change', function() {
              if ($(this).is(':checked')) {
                $(this).closest('li').find('.exclude-terms').slideDown(100);
              }
              else {
                $(this).closest('li').find('.exclude-terms').slideUp(100);
              }
            })
            .trigger('change');
        });
      }(jQuery));
    </script>

    <p>
      <input type="checkbox"
             id="<?php echo $this->get_field_id('connectwithquery')?>"
             name="<?php echo $this->get_field_name('connectwithquery')?>"
             value="connectwithquery"
        <?php checked($instance['connectwithquery'], 'connectwithquery')?>
        />
      <label for="<?php echo $this->get_field_id('connectwithquery')?>">
        <?php _e('Connect with current page request query', 'scifi-facets')?>
      </label>
    </p>

    <p id="<?php echo $this->get_field_id('usepermalinks-wrapper')?>">
      <input type="checkbox"
             id="<?php echo $this->get_field_id('usepermalinks')?>"
             name="<?php echo $this->get_field_name('usepermalinks')?>"
             value="usepermalinks"
        <?php checked($instance['usepermalinks'], 'usepermalinks')?>
        <?php echo (empty($wp_rewrite->permalink_structure) ? 'disabled="disabled"' : '')?>
        />
      <label for="<?php echo $this->get_field_id('usepermalinks')?>">
        <?php _e('Use permalinks if available', 'scifi-facets')?>
      </label>
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('urlbase')?>">
        <?php _e('URL base when no permalinks available:', 'scifi-facets')?>
      </label>
      <select class="widefat" id="<?php echo $this->get_field_id('urlbase')?>" name="<?php echo $this->get_field_name('urlbase')?>">
        <option value="">&lt;<?php _e('Auto', 'scifi-facets')?>&gt;</option>
        <option value="{custom}" <?php selected($instance['urlbase'], '{custom}')?>>&lt;<?php _e('Custom')?>&gt;</option>
        <?php foreach (get_post_types('', 'names') as $post_type):?>
          <option value="<?php echo esc_attr($post_type)?>" <?php selected($instance['urlbase'], $post_type)?>>
            <?php printf(__('%s (post type)'), esc_attr($post_type))?>
          </option>
        <?php endforeach?>
      </select>
    </p>
    <p id="<?php echo $this->get_field_id('urlbase_custom_wrapper')?>">
      <input type="text"
             class="widefat"
             id="<?php echo $this->get_field_id('urlbase_custom')?>"
             name="<?php echo $this->get_field_name('urlbase_custom')?>"
             value="<?php echo esc_attr($instance['urlbase_custom'])?>"
        />
      <label for="<?php echo $this->get_field_id('urlbase_custom')?>">
        <small>
          <?php _e('Relative path to the site, without trailing slash.', 'scifi-facets')?>
        </small>
      </label>
    </p>
    <script>
      (function($) {
        $(document).ready(function() {
          $('#<?php echo $this->get_field_id('urlbase')?>')
            .change(function(event) {
              event.preventDefault();
              if ($(this).val() == '{custom}') {
                $('#<?php echo $this->get_field_id('urlbase_custom_wrapper')?>').slideDown(100);
              }
              else {
                $('#<?php echo $this->get_field_id('urlbase_custom_wrapper')?>').slideUp(100);
              }
            })
            .trigger('change');
        });
      }(jQuery));
    </script>

     <p>
      <label for="<?php echo $this->get_field_id('dependsof')?>">
        <?php _e('Show when next presented:', 'scifi-facets')?>
      </label>
      <select class="widefat" id="<?php echo $this->get_field_id('dependsof')?>" name="<?php echo $this->get_field_name('dependsof')?>">
        <option value="">&lt;<?php _e('None')?>&gt;</option>
        <?php foreach (get_taxonomies(array('public' => TRUE), 'objects') as $tax_object):?>
          <option value="<?php echo esc_attr($tax_object->name)?>" <?php selected($instance['dependsof'], esc_attr($tax_object->name))?>>
            <?php printf('%s (%s)', $tax_object->label, $tax_object->name)?>
          </option>
        <?php endforeach?>
      </select>
    </p>

    <p>
      <input type="checkbox"
             id="<?php echo $this->get_field_id('showinarchives')?>"
             name="<?php echo $this->get_field_name('showinarchives')?>"
             value="showinarchives"
        <?php checked($instance['showinarchives'], 'showinarchives')?>
        />
      <label for="<?php echo $this->get_field_id('showinarchives')?>">
        <?php _e('Show in archives', 'scifi-facets')?>
      </label>
    </p>

    <p>
      <input type="checkbox"
             id="<?php echo $this->get_field_id('showinsingulars')?>"
             name="<?php echo $this->get_field_name('showinsingulars')?>"
             value="showinsingulars"
        <?php checked($instance['showinsingulars'], 'showinsingulars')?>
        />
      <label for="<?php echo $this->get_field_id('showinsingulars')?>">
        <?php _e('Show in single post views', 'scifi-facets')?>
      </label>
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('format')?>">
        <?php _e('Format:', 'scifi-facets')?>
      </label>
      <select class="widefat" id="<?php echo $this->get_field_id('format')?>" name="<?php echo $this->get_field_name('format')?>">
        <?php foreach ($this->formatters as $formatter_id => $formatter_info):?>
          <option value="<?php echo $formatter_id?>" <?php selected($instance['format'], $formatter_id)?>>
            <?php echo $formatter_info['name']?>
          </option>
        <?php endforeach?>
      </select>
    </p>
    <script>
      (function($) {
        $(document).ready(function() {
          $('#<?php echo $this->get_field_id('format')?>')
            .on('change', function(event) {
              event.preventDefault();
              var val = $(this).val();
              if (val == 'tags' || val == 'select_multiple') {
                $('#<?php echo $this->get_field_id('includeall-wrapper')?>').hide();
                $('#<?php echo $this->get_field_id('usepermalinks-wrapper')?>').hide();
              }
              else {
                $('#<?php echo $this->get_field_id('includeall-wrapper')?>').show();
                $('#<?php echo $this->get_field_id('usepermalinks-wrapper')?>').show();
              }
            })
            .trigger('change');
        });
      }(jQuery));
    </script>

    <p>
      <input type="checkbox"
             id="<?php echo $this->get_field_id('showtaxnames')?>"
             name="<?php echo $this->get_field_name('showtaxnames')?>"
             value="showtaxnames"
        <?php checked($instance['showtaxnames'], 'showtaxnames')?>
        />
      <label for="<?php echo $this->get_field_id('showtaxnames')?>">
        <?php _e('Show taxonomy names', 'scifi-facets')?>
      </label>
    </p>

    <p id="<?php echo $this->get_field_id('includeall-wrapper')?>">
      <input type="checkbox"
             id="<?php echo $this->get_field_id('includeall')?>"
             name="<?php echo $this->get_field_name('includeall')?>"
             value="includeall"
        <?php checked($instance['includeall'], 'includeall')?>
        />
      <label for="<?php echo $this->get_field_id('includeall')?>">
        <?php _e('Show "show all" item', 'scifi-facets')?>
      </label>
    </p>
    
    <p>
      <label for="<?php echo $this->get_field_id('order')?>">
        <?php _e('Order:', 'scifi-facets')?>
      </label>
      <select class="widefat" id="<?php echo $this->get_field_id('order')?>" name="<?php echo $this->get_field_name('order')?>">
        <option value="">-<?php _e('none')?>-</option>
        <option value="asc" <?php selected($instance['order'], 'asc')?> > <?php _e('Asc', 'scifi-facets')?> </option>
        <option value="desc" <?php selected($instance['order'], 'desc')?> > <?php _e('Desc', 'scifi-facets')?> </option>
      </select>
    </p>

    <p>&nbsp;</p>
  <?php
  }

}


/**
 * Register the widgets
 */
register_widget('Widget_Scifi_Facets_Taxonomy');

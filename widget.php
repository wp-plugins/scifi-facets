<?php

/**
 * Facets Widget class
 */
class Widget_Scifi_Facets extends WP_Widget {

  private $formatters = array();

  /**
   * Class constructor
   *
   * @see WP_Widget::__construct()
   */
  function __construct() {
    $widget_ops = array(
        'classname' => 'scifi-facets-widget',
        'description' => __('Taxonomy facets', 'scifi_facets'),
    );
    $this->formatters = apply_filters('scifi_facets_formatters', array());
    parent::__construct('scifi-facets-widget', sprintf('(scifi) %s', __('Taxonomy facets', 'scifi_facets')), $widget_ops);
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
      'format' => '',
      'includeall' => '',
      // 'showcount' => '',
      'showorderby' => '',
      'showorder' => '',
      'connectwithquery' => '',
      'useqvars' => '',
      'showinarchiveonly' => '',
      'showtaxnames' => '',
      'dependsof' => '',
    );
    $settings = array_merge($defaults, $settings);
    if (!is_array($settings['taxonomies'])) {
      $settings['taxonomies'] = $defaults['taxonomies'];
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

    if ($settings['connectwithquery'] == 'connectwithquery') {

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
      // @TODO: add counting subquery
      $querystr = "
        SELECT DISTINCT
          wpt.* ,
          wptt.taxonomy
        FROM ({$posts_querystr}) posts
        INNER JOIN {$wpdb->term_relationships} wptr ON posts.ID = wptr.object_id
        INNER JOIN {$wpdb->term_relationships} wptra ON wptr.object_id = wptra.object_id
        INNER JOIN {$wpdb->term_taxonomy} wptt ON wptra.term_taxonomy_id = wptt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} wpt ON wptt.term_id = wpt.term_id
        WHERE {$where_querystr}
        ORDER BY wptt.taxonomy ASC, wpt.name ASC";

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

    $instance = $this->prepare_settings($instance);

    if (is_singular() && $instance['showinarchiveonly'] == 'showinarchiveonly') {
      return;
    }

    if (empty($this->formatters[$instance['format']])) {
      return;
    }

    if ($instance['dependsof'] && ($dep_tax_object = get_taxonomy($instance['dependsof']))) {
      if (!get_query_var($dep_tax_object->query_var)) {
        return;
      }
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
        echo '<div class="scifi-facets-terms-group scifi-facets-terms-format-' . $instance['format'] . '" id="scifi-facets-group-' . $tax_name . '">';

        if ($instance['showtaxnames'] == 'showtaxnames' || $instance['format'] == 'tags') {
          echo '<div class="scifi-facets-terms-title">' . $tax_object->label . '</div>';
        }

        // Terms wrapper.
        echo '<div class="scifi-facets-terms-list">';

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

        call_user_func($this->formatters[$instance['format']], $tax_name, $tax_terms[$tax_name], $active_terms, $instance);

        // /terms wrapper
        echo '</div>';

        // /group wrapper
        echo '</div>';
      }
    }

    if ($instance['showorderby'] || $instance['showorder']):?>
      <div class="scifi-facets-ordering">
      <?php if ($instance['showorderby']):?>
        <label for="<?php echo $this->get_field_id('orderby')?>">
          <?php _e('Order', 'scifi_facets')?>
        </label>
        <select id="<?php echo $this->get_field_id('orderby')?>" class="scifi-facets-orderby-selector" onchange="window.location.href=this.value">
          <option value="<?php echo esc_attr(remove_query_arg('orderby'))?>">
            &lt;<?php _e('Default')?>&gt;
          </option>
          <?php foreach (_scifi_list_orderby() as $orderby_key => $orderby_title):?>
          <option value="<?php echo esc_attr(add_query_arg('orderby', $orderby_key))?>" <?php selected(get_query_var('orderby'), $orderby_key)?>>
            <?php echo $orderby_title?>
          </option>
          <?php endforeach?>
        </select>
      <?php endif?>
      <?php
      if ($instance['showorder']) {
        if (strtolower(get_query_var('order')) == 'asc' || get_query_var('order') == '') {
          printf('<a class="scifi-facets-order-selector scifi-facets-order-selector-asc" rel="nofollow" href="%s"><span>%s</span></a>', esc_attr(add_query_arg('order', 'desc')), __('Asc', 'scifi_facets'));
        }
        else {
          printf('<a class="scifi-facets-order-selector scifi-facets-order-selector-desc" rel="nofollow" href="%s"><span>%s</span></a>', esc_attr(add_query_arg('order', 'asc')), __('Desc', 'scifi_facets'));
        }
      }
      ?>
      </div>
    <?php
    endif;
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
          <?php _e('Title:', 'scifi_facets')?>
        </label>
        <input  type="text" class="widefat" id="<?php echo $this->get_field_id('title')?>" name="<?php echo $this->get_field_name('title')?>" value="<?php echo esc_attr($instance['title'])?>" />
      </p>

      <p>
        <label for="<?php echo $this->get_field_id('format')?>">
          <?php _e('Format:', 'scifi_facets')?>
        </label>
        <select class="widefat" id="<?php echo $this->get_field_id('format')?>" name="<?php echo $this->get_field_name('format')?>">
          <?php foreach (array_keys($this->formatters) as $formatter):?>
          <option value="<?php echo $formatter?>" <?php selected($instance['format'], $formatter)?>>
            <?php echo $formatter?>
          </option>
          <?php endforeach?>
        </select>
      </p>

      <p>
        <input type="checkbox"
          id="<?php echo $this->get_field_id('showtaxnames')?>"
          name="<?php echo $this->get_field_name('showtaxnames')?>"
          value="showtaxnames"
          <?php checked($instance['showtaxnames'], 'showtaxnames')?>
          />
        <label for="<?php echo $this->get_field_id('showtaxnames')?>">
          <?php _e('Show taxonomy names', 'scifi_facets')?>
        </label>
      </p>

      <?php /*
      <p>
        <input type="checkbox"
               id="<?php echo $this->get_field_id('showcount')?>"
               name="<?php echo $this->get_field_name('showcount')?>"
               value="showcount"
          <?php checked($instance['showcount'], 'showcount')?>
          />
        <label for="<?php echo $this->get_field_id('showcount')?>">
          <?php _e('Show post count', 'scifi_facets')?>
        </label>
      </p>
      */ ?>

      <p>
        <input type="checkbox"
          id="<?php echo $this->get_field_id('includeall')?>"
          name="<?php echo $this->get_field_name('includeall')?>"
          value="includeall"
          <?php checked($instance['includeall'], 'includeall')?>
          />
        <label for="<?php echo $this->get_field_id('includeall')?>">
          <?php _e('Include all item', 'scifi_facets')?>
        </label>
      </p>

      <p>
        <input type="checkbox"
          id="<?php echo $this->get_field_id('useqvars')?>"
          name="<?php echo $this->get_field_name('useqvars')?>"
          value="useqvars"
          <?php checked($instance['useqvars'], 'useqvars')?>
          <?php echo (empty($wp_rewrite->permalink_structure) ? 'disabled="disabled"' : '')?>
          />
        <label for="<?php echo $this->get_field_id('useqvars')?>">
          <?php _e('Use query vars', 'scifi_facets')?>
        </label>
      </p>

      <p>
        <input type="checkbox"
          id="<?php echo $this->get_field_id('showinarchiveonly')?>"
          name="<?php echo $this->get_field_name('showinarchiveonly')?>"
          value="showinarchiveonly"
          <?php checked($instance['showinarchiveonly'], 'showinarchiveonly')?>
          />
        <label for="<?php echo $this->get_field_id('showinarchiveonly')?>">
          <?php _e('Show in archives only', 'scifi_facets')?>
        </label>
      </p>

      <p>
        <input type="checkbox"
          id="<?php echo $this->get_field_id('connectwithquery')?>"
          name="<?php echo $this->get_field_name('connectwithquery')?>"
          value="connectwithquery"
          <?php checked($instance['connectwithquery'], 'connectwithquery')?>
          />
        <label for="<?php echo $this->get_field_id('connectwithquery')?>">
          <?php _e('Connect with current page query', 'scifi_facets')?>
        </label>
      </p>

      <p>
        <input type="checkbox"
               id="<?php echo $this->get_field_id('showorderby')?>"
               name="<?php echo $this->get_field_name('showorderby')?>"
               value="showorderby"
          <?php checked($instance['showorderby'], 'showorderby')?>
          />
        <label for="<?php echo $this->get_field_id('showorderby')?>">
          <?php _e('Show order by selector', 'scifi_facets')?>
        </label>
      </p>

      <p>
        <input type="checkbox"
               id="<?php echo $this->get_field_id('showorder')?>"
               name="<?php echo $this->get_field_name('showorder')?>"
               value="showorder"
          <?php checked($instance['showorder'], 'showorder')?>
          />
        <label for="<?php echo $this->get_field_id('showorder')?>">
          <?php _e('Show order selector', 'scifi_facets')?>
        </label>
      </p>

      <p>
        <label for="<?php echo $this->get_field_id('dependsof')?>">
          <?php _e('Depends of:', 'scifi_facets')?>
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

      <fieldset class="widefat">
        <p><?php _e('Taxonomies:', 'scifi_facets')?></p>
        <ul style="max-height:200px;overflow:auto;">
        <?php foreach (get_taxonomies(array('public' => TRUE), 'objects') as $tax_object):?>
          <li>
            <input type="checkbox"
              id="<?php echo $this->get_field_id('taxonomies') . '_' . $tax_object->name?>"
              name="<?php echo $this->get_field_name('taxonomies')?>[]"
              value="<?php echo esc_attr($tax_object->name)?>"
              <?php checked(in_array($tax_object->name, $instance['taxonomies']))?>
              />
            <label for="<?php echo $this->get_field_id('taxonomies') . '_' . $tax_object->name?>">
              <?php printf('%s (%s)', $tax_object->label, $tax_object->name)?>
            </label>
          </li>
        <?php endforeach?>
        </ul>
      </fieldset>

  <?php
  }

}

<?php


/**
 * Build URL for time facet
 *
 * @param int $year
 * @param int $month
 * @param array $widget_instance
 *
 * @return string
 */
function _scifi_facets_time_url($year, $month, $widget_instance) {
  global $wp_rewrite;

  $url = '';

  if ($month) {
    $month = sprintf('%02d', $month);
  }

  if ( !empty($wp_rewrite->permalink_structure) && !empty($widget_instance['usepermalinks'])) {
    if (!empty($widget_instance['urlbase_custom'])) {
      $url = home_url($widget_instance['urlbase_custom']);
    }
    else {
      $post_type = empty($widget_instance['posttype']) ? get_query_var('post_type') : $widget_instance['posttype'];
      if ($post_type == 'post') {
        $url = home_url($wp_rewrite->front);
      }
      else {
        $url = get_post_type_archive_link($post_type);
      }
    }
    if (strpos($url, '%year%') !== FALSE || strpos($url, '%month%') !== FALSE || strpos($url, '%date%') !== FALSE) {
      $url = strtr($url, array('%year%' => $year, '%month%' => $month, '%date%' => $year . '/'. $month));
    }
    else {
      $url = $url . '/' . $year . '/' . $month;
    }
    $url = preg_replace('#[^\:]\/{2,}#', '/', trailingslashit($url));
  }
  else {
    $url = home_url(add_query_arg(array('year' => $year, 'month' => $month)));
  }
  return $url;
}


/**
 * Links lsit formatter callback
 */
function _scifi_facets_time_formatters_links($records, $active_year, $active_month, $widget_instance) {
  global $wp_locale;

  if ($widget_instance['includeall'] == 'includeall'):?>
  <div>
    <a class="scifi-facets-includeall" href="<?php echo _scifi_facets_time_url(NULL, NULL, $widget_instance)?>" rel="nofollow">
      <?php _e('All', 'scifi-facets')?>
    </a>
  </div>
  <?php
  endif;
  foreach ($records as $year => $months) {
  ?>
    <div class="scifi-facets-group-title">
      <?php echo $year?>
    </div>
    <div class="scifi-facets-group-list <?php echo ($active_year == $year ? 'scifi-facets-current-group' : '')?>">
      <ul class="scifi-facets-group-items">
      <?php foreach($months as $month => $posts_count):
        $classes = array();
        if ((int) $year == (int) $active_year && (int) $month == (int) $active_month) {
          $classes[] = 'scifi-facets-current';
        }
        ?>
        <li>
          <a href="<?php echo esc_attr(_scifi_facets_time_url($year, $month, $widget_instance))?>" rel="nofollow" class="<?php echo implode(' ', $classes)?>">
            <?php echo $wp_locale->get_month($month)?> <span class="scifi-facet-item-count"><?php echo $posts_count?></span>
          </a>
        </li>
        <?php endforeach?>
      </ul>
    </div>
  <?php
  }
}


/**
 * HTML select dropdown formatter callback
 */
function _scifi_facets_time_formatters_select($records, $active_year, $active_month, $widget_instance) {
  global $wp_locale;

  echo '<select class="scifi-facets-select">';
  if ($widget_instance['includeall'] == 'includeall') {
    printf('<option value="%s">&lt;%s&gt;</option>', _scifi_facets_time_url(NULL, NULL, $widget_instance), __('All', 'scifi-facets'));
  }

  foreach ($records as $year => $months) {
    echo '<optgroup label="' . $year . '">';
    foreach($months as $month => $posts_count) {
      $selected = selected( ($active_year || $active_month) && ($active_year ? $active_year == $year : TRUE) && ($active_month ? $active_month == $month : TRUE) , TRUE, FALSE);
      printf('<option value="%s" %s>%s %s (%s)</option>', esc_attr(_scifi_facets_time_url($year, $month, $widget_instance)), $selected, $wp_locale->get_month($month), $year, $posts_count);
    }
    echo '</optgroup>';
  }
  echo '</select>';
}


/**
 * Facets Widget class
 */
class Widget_Scifi_Facets_Time extends WP_Widget {

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
        'description' => __('Time facets', 'scifi-facets'),
    );
    $this->formatters = apply_filters('scifi_facets_time_formatters', array(
      'links' => array(
        'name' => __('Links', 'scifi-facets'),
        'cb' => '_scifi_facets_time_formatters_links',
      ),
      'select' => array(
        'name' => __('Select', 'scifi-facets'),
        'cb' => '_scifi_facets_time_formatters_select',
      ),
    ));
    parent::__construct('scifi-facets-time-widget', sprintf('(scifi Facets) %s', __('Time', 'scifi-facets')), $widget_ops);
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
      'posttype' => '',
      'usepermalinks' => '',
      'urlbase_custom' => '',
      'showinsingulars' => '',
      'showinarchives' => '',
      'format' => 'htmltime',
      'includeall' => '',
    );
    $settings = array_merge($defaults, $settings);
    return $settings;
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

    global $wp_rewrite, $wpdb;
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

    wp_enqueue_script('scifi-facets');

    echo $before_widget;

    if (!empty($instance['title'])) {
      echo $before_title . $instance['title'] . $after_title;
    }

    $current_year = get_query_var('year') ? get_query_var('year') : date('Y');
    $current_month = get_query_var('monthnum') ? get_query_var('monthnum') : date('m');

    $post_type = empty($instance['posttype']) ? get_query_var('post_type') : $instance['posttype'];

    $querystr = "
      SELECT
        YEAR(post_date) AS year,
        MONTH(post_date) AS month,
        count(ID) as posts_count
      FROM {$wpdb->posts}
      WHERE
          post_type = %s
          AND post_status = 'publish'
      GROUP BY
          YEAR(post_date),
          MONTH(post_date)
      ORDER BY
          post_date DESC";
    
    $rows = $wpdb->get_results($wpdb->prepare($querystr, $post_type));

    $records = array();
    foreach ($rows as $row) {
      $records[$row->year][$row->month] = $row->posts_count;
    }

    static $i = 0;
    $i_id = '';
    if ($i++) {
      $i_id = '-' + $i;
    }

    echo '<div class="scifi-facets-group scifi-facets-group-time scifi-facets-format-' . $instance['format'] . '" id="scifi-facets-time-group' . $i_id . '">';
    call_user_func($this->formatters[$instance['format']]['cb'], $records, $current_year, $current_month, $instance);
    echo '</div>';

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

    <p>
      <label for="<?php echo $this->get_field_id('posttype')?>">
        <?php _e('Post type:', 'scifi-facets')?>
      </label>
      <select class="widefat" id="<?php echo $this->get_field_id('posttype')?>" name="<?php echo $this->get_field_name('posttype')?>">
        <option value="">&lt;<?php _e('Auto', 'scifi-facets')?>&gt;</option>
        <?php foreach (get_post_types('', 'names') as $post_type):?>
          <option value="<?php echo esc_attr($post_type)?>" <?php selected($instance['posttype'], $post_type)?>>
            <?php echo esc_attr($post_type)?>
          </option>
        <?php endforeach?>
      </select>
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

    <p id="<?php echo $this->get_field_id('urlbase_custom_wrapper')?>">
      <label for="<?php echo $this->get_field_id('urlbase_custom')?>">
        <?php _e('Use custom URL base', 'scifi-facets')?>
      </label>
      <input type="text"
             class="widefat"
             id="<?php echo $this->get_field_id('urlbase_custom')?>"
             name="<?php echo $this->get_field_name('urlbase_custom')?>"
             value="<?php echo esc_attr($instance['urlbase_custom'])?>"
        />
      <small>
        <?php _e('Relative path to the site, without trailing slash.', 'scifi-facets')?>
      </small>
    </p>
    <script>
      (function($) {
        $(document).ready(function() {
          $('#<?php echo $this->get_field_id('usepermalinks')?>')
            .change(function(event) {
              event.preventDefault();
              if ($(this).is(':checked')) {
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

    <p>&nbsp;</p>
  <?php
  }

}


/**
 * Register the widgets
 */
register_widget('Widget_Scifi_Facets_Time');

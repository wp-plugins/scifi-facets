<?php


/**
 * Facets Widget class
 */
class Widget_Scifi_Facets_Ordering extends WP_Widget {

  private $order_options = array();

  /**
   * Class constructor
   *
   * @see WP_Widget::__construct()
   */
  function __construct() {

    $widget_ops = array(
        'classname' => 'scifi-facets-widget',
        'description' => __('Ordering of current view', 'scifi-facets'),
    );

    $this->order_options = apply_filters('scifi_list_orderby', array(
      'title'         => __('Title', 'scifi-facets'),
      'date'          => __('Date', 'scifi-facets'),
      'modified'      => __('Last update', 'scifi-facets'),
      'comment_count' => __('Comments', 'scifi-facets'),
    ));

    parent::__construct('scifi-facets-ordering-widget', sprintf('(scifi Facets) %s', __('Sort and ordering', 'scifi-facets')), $widget_ops);
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
      'showorderby' => '',
      'showorder' => '',
      'requireparams' => '',
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

    global $wp_rewrite;
    $instance = $this->prepare_settings($instance);

    if (is_singular()) {
      return;
    }

    if (!empty($instance['requireparams'])) {
      $required_params =  preg_split('#[\s,]+#', $instance['requireparams'], PREG_SPLIT_NO_EMPTY);
      foreach ($required_params as $key) {
        if (!get_query_var($key) && get_query_var($key) !== 0) {
          return;
        }
      }
    }

    wp_enqueue_script('scifi-facets');

    echo $before_widget;

    if (!empty($instance['title'])) {
      echo $before_title . $instance['title'] . $after_title;
    }

    if ($instance['showorderby'] || $instance['showorder']):?>
      <div class="scifi-facets-ordering">
      <?php if ($instance['showorderby']):?>
        <select id="<?php echo $this->get_field_id('orderby')?>" class="scifi-facets-orderby-selector" onchange="window.location.href=this.value">
          <option value="<?php echo esc_attr(remove_query_arg('orderby'))?>">
            &lt;<?php _e('Default')?>&gt;
          </option>
          <?php foreach ($this->order_options as $orderby_key => $orderby_title):?>
          <option value="<?php echo esc_attr(add_query_arg('orderby', $orderby_key))?>" <?php selected(get_query_var('orderby'), $orderby_key)?>>
            <?php echo $orderby_title?>
          </option>
          <?php endforeach?>
        </select>
      <?php endif?>
      <?php
      if ($instance['showorder']) {
        if (strtolower(get_query_var('order')) == 'asc' || get_query_var('order') == '') {
          printf('<a class="scifi-facets-order-selector scifi-facets-order-selector-asc" rel="nofollow" href="%s"><span>%s</span></a>', esc_attr(add_query_arg('order', 'desc')), __('Asc', 'scifi-facets'));
        }
        else {
          printf('<a class="scifi-facets-order-selector scifi-facets-order-selector-desc" rel="nofollow" href="%s"><span>%s</span></a>', esc_attr(add_query_arg('order', 'asc')), __('Desc', 'scifi-facets'));
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
      <input type="checkbox"
             id="<?php echo $this->get_field_id('showorderby')?>"
             name="<?php echo $this->get_field_name('showorderby')?>"
             value="showorderby"
        <?php checked($instance['showorderby'], 'showorderby')?>
        />
      <label for="<?php echo $this->get_field_id('showorderby')?>">
        <?php _e('Show "order by" selector', 'scifi-facets')?>
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
        <?php _e('Show "order" selector', 'scifi-facets')?>
      </label>
    </p>
    
    <p>
      <label for="<?php echo $this->get_field_id('requireparams')?>">
        <?php _e('Require params:', 'scifi-facets')?>
      </label>
      <input type="text"
             class="widefat"
             id="<?php echo $this->get_field_id('requireparams')?>"
             name="<?php echo $this->get_field_name('requireparams')?>"
             value="<?php echo esc_attr($instance['requireparams'])?>"
        />
      <small>
        <?php _e('Coma separated list of GET parameters that should be set for showing this widget.', 'scifi-facets')?>
      </small>
    </p>

    <p>&nbsp;</p>
  <?php
  }

}


/**
 * Register the widgets
 */
register_widget('Widget_Scifi_Facets_Ordering');

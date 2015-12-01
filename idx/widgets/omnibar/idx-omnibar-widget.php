<?php
namespace IDX\Widgets\Omnibar;

class IDX_Omnibar_Widget extends \WP_Widget
{
    public function __construct()
    {
        $widget_ops = array('classname' => 'IDX_Omnibar_Widget', 'description' => 'An Omnibar Search Widget for use with IDX WordPress Sites');
        parent::__construct('IDX_Omnibar_Widget', 'IMPress Omnibar Search', $widget_ops);
    }

    public $defaults = array(
        'title' => '',
        'styles' => 1,
        'extra' => 0,
    );

    public function form($instance)
    {
        $defaults = $this->defaults;
        $instance = wp_parse_args((array) $instance, $this->defaults);
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo esc_attr($title);?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo esc_attr($title);?>" /></label></p>
        <p>
            <label for="<?php echo $this->get_field_id('styles');?>"><?php _e('Default Styling?', 'idxbroker');?></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('styles');?>" name="<?php echo $this->get_field_name('styles')?>" value="1" <?php checked($instance['styles'], true);?>>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('extra');?>"><?php _e('Extra Fields?', 'idxbroker');?></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('extra');?>" name="<?php echo $this->get_field_name('extra')?>" value="1" <?php checked($instance['extra'], true);?>>
        </p>
    <?php
}

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['styles'] = (int) $new_instance['styles'];
        $instance['extra'] = (int) $new_instance['extra'];
        return $instance;
    }

    public function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);

        if (empty($instance)) {
            $instance = $this->defaults;
        }

        echo $before_widget;
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        $plugin_dir = plugins_url();

        //grab url from database set from get-locations.php
        $idx_url = get_option('idx-results-url');

        // Widget HTML:
        $create_omnibar = new Create_Omnibar;
        if (!empty($instance['extra'])) {
            echo $create_omnibar->idx_omnibar_extra($plugin_dir, $idx_url, $instance['styles']);
        } else {
            echo $create_omnibar->idx_omnibar_basic($plugin_dir, $idx_url, $instance['styles']);
        }
        echo $after_widget;
    }
}

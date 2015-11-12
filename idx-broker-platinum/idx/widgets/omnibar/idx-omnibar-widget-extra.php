<?php
namespace IDX\Widgets\Omnibar;

class IDX_Omnibar_Widget_Extra extends \WP_Widget
{
    public function __construct()
    {
        $widget_ops = array('classname' => 'IDX_Omnibar_Widget_Extra', 'description' => 'An Omnibar Search Widget with extra fields for use with IDX WordPress Sites');
        parent::__construct('IDX_Omnibar_Widget_Extra', 'IDX Omnibar With Extra Fields', $widget_ops);
    }
    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo esc_attr($title);?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo esc_attr($title);?>" /></label></p>
        <?php
}

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

    public function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);

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
        echo $create_omnibar->idx_omnibar_extra($plugin_dir, $idx_url);
        echo $after_widget;
    }
}

<?php
namespace IDX\Widgets;

class Impress_City_Links_Widget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {

        $this->idx_api = new \IDX\Idx_Api();

        parent::__construct(
            'impress_city_links', // Base ID
            'IMPress City Links', // Name
            array(
                'description' => __('Outputs a list of city links', 'idxbroker'),
                'classname' => 'impress-city-links-widget',
            )
        );
    }

    public $idx_api;
    public $defaults = array(
        'title' => 'Explore Cities',
        'city_list' => 'combinedActiveMLS',
        'mls' => '',
        'use_columns' => 0,
        'number_columns' => 4,
    );

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        extract($args);
        if (empty($instance)) {
            $instance = $this->defaults;
        }
        $title = $instance['title'];

        echo $before_widget;

        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        $idx_api = $this->idx_api;

        // For testing with demo data
        // if ( empty($instance['mls'] ) ) {
        //     $instance['mls'] = 'a000';
        // }

        if (empty($instance['mls'])) {
            echo 'Invalid MLS IDX ID. Email help@idxbroker.com to get your MLS IDX ID';
        } else {
            echo $idx_api->city_list_links($instance['city_list'], $instance['mls'], $instance['use_columns'], $instance['number_columns']);
        }

        echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['city_list'] = strip_tags($new_instance['city_list']);
        $instance['mls'] = strip_tags($new_instance['mls']);
        $instance['use_columns'] = (int) $new_instance['use_columns'];
        $instance['number_columns'] = (int) $new_instance['number_columns'];

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     * @uses IMPress_City_Links_Widget::city_list_options()
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {

        $idx_api = $this->idx_api;

        $defaults = $this->defaults;

        $instance = wp_parse_args((array) $instance, $defaults);

        ?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php esc_attr_e($instance['title']);?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('mls');?>">MLS to use for the city links: *required*</label>
			<select class="widefat" id="<?php echo $this->get_field_id('mls');?>" name="<?php echo $this->get_field_name('mls');?>">
				<?php $this->mls_options($instance);?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('city_list');?>">Select a city list:</label>
			<select class="widefat" id="<?php echo $this->get_field_id('city_list');?>" name="<?php echo $this->get_field_name('city_list')?>">
				<?php $this->city_list_options($instance);?>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['use_columns'], 1);?> id="<?php echo $this->get_field_id('use_columns');?>" name="<?php echo $this->get_field_name('use_columns');?>" value="1" />
			<label for="<?php echo $this->get_field_id('use_columns');?>">Split links into columns?</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number_columns');?>">Number of columns</label>
			<select class="widefat" id="<?php echo $this->get_field_id('number_columns');?>" name="<?php echo $this->get_field_name('number_columns');?>">
				<option <?php selected($instance['number_columns'], 2);?> value="2">2</option>
				<option <?php selected($instance['number_columns'], 3);?> value="3">3</option>
				<option <?php selected($instance['number_columns'], 4);?> value="4">4</option>
			</select>
		</p>
		<p>Don't have any city lists? Go create some in your <a href="http://middleware.idxbroker.com/mgmt/citycountyziplists.php">IDX dashboard.</a></p>
		<?php
}

    /**
     * Echos city list ids wrapped in option tags
     *
     * This is just a helper to keep the html clean
     *
     * @param var $instance
     */
    public function city_list_options($instance)
    {

        $lists = $this->idx_api->city_list_names();

        if (!is_array($lists)) {
            return;
        }

        foreach ($lists as $list) {

            // display the list id if no list name has been assigned
            $list_text = empty($list->name) ? $list->id : $list->name;

            echo '<option ', selected($instance['city_list'], $list->id, 0), ' value="', $list->id, '">', $list_text, '</option>';
        }
    }

    /**
     * Echos the approved mls names wrapped in option tags
     *
     * This is just a helper to keep the html clean
     *
     * @param var $instance
     */
    public function mls_options($instance)
    {

        $approved_mls = $this->idx_api->approved_mls();

        if (!is_array($approved_mls)) {
            return;
        }
        foreach ($approved_mls as $mls) {
            echo '<option ', selected($instance['mls'], $mls->id, 0), ' value="', $mls->id, '">', $mls->name, '</option>';
        }
    }
}

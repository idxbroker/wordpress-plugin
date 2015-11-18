<?php
namespace IDX\Widgets;

class IMPress_Lead_Login_Widget extends \WP_Widget
{
    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {

        $this->idx_api = new \IDX\Idx_Api();

        parent::__construct(
            'impress_lead_login', // Base ID
            __('IMPress Lead Login', 'idxbroker'), // Name
            array(
                'description' => __('Lead login form', 'idxbroker'),
                'classname' => 'impress-idx-login-widget',
            )
        );
    }

    public $idx_api;
    public $defaults = array(
        'title' => 'Account Login',
        'custom_text' => '',
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
        $custom_text = $instance['custom_text'];

        echo $before_widget;

        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        if (!empty($custom_text)) {
            echo '<p>', $custom_text, '</p>';
        }

        ?>
		<form action="<?php echo $this->idx_api->subdomain_url();?>ajax/userlogin.php" method="post" target="" name="leadLoginForm">
			<input type="hidden" name="action" value="login">
			<input type="hidden" name="loginWidget" value="true">
			<label for="bb-IDX-widgetEmail"><?php _e('Email Address:', 'idxbroker');?></label>
			<input id="bb-IDX-widgetEmail" type="text" name="email" placeholder="Enter your email address">
			<input id="bb-IDX-widgetPassword" type="hidden" name="password" value="">
			<input id="bb-IDX-widgetLeadLoginSubmit" type="submit" name="login" value="Log In">
		</form>
		<?php

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
        $instance['custom_text'] = htmlentities($new_instance['custom_text']);

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {

        $idx_api = $this->idx_api;

        $defaults = $this->defaults;

        $instance = wp_parse_args((array) $instance, $defaults);

        ?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:', 'idxbroker');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php esc_attr_e($instance['title']);?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('custom_text')?>"><?php _e('Custom Text', 'idxbroker');?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id('custom_text');?>" name="<?php echo $this->get_field_name('custom_text');?>" value="<?php esc_attr_e($instance['custom_text']);?>" rows="5"><?php esc_attr_e($instance['custom_text']);?></textarea>
		</p>
		<?php
}
}

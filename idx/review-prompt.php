<?php
namespace IDX;

class Review_Prompt
{

    public function __construct()
    {
        add_action('admin_init', array($this, 'check_timestamp'));
        add_action('wp_ajax_idx_dismiss_review_prompt', array($this, 'dismiss_prompt'));
        //remove dismiss option for testing
        // delete_option('idx_dismiss_review_prompt');
    }

    public static function set_timestamp()
    {
        //only continue if dismiss is not already set
        $dismiss = get_option('idx_dismiss_review_prompt');
        if($dismiss){
            return;
        }
        //set timestamp one week after
        $time = time() + 60*60*24*7;
        //shorter time for testing
        // $time = time();
        //set timestamp to prompt review
        update_option('idx_review_prompt_time', $time);
    }

    public function check_timestamp()
    {
        $timestamp = get_option('idx_review_prompt_time');
        $dismiss = get_option('idx_dismiss_review_prompt');
        //if timestamp is before now and dismiss is false display prompt
        if($timestamp < time() && ! $dismiss) {
            add_action('admin_notices', array($this, 'display_prompt'));
        }
    }

    public function display_prompt()
    {
        $review_url = 'https://wordpress.org/support/view/plugin-reviews/idx-broker-platinum';
        $dismiss_url = esc_url(wp_nonce_url(admin_url('?idx_dismiss_review_prompt=true'), 'idx_review_prompt_nonce'));

        echo '<div class="updated idx_review_prompt is-dismissible"><p>';
        echo 'Loving the updated IMPress for IDX Broker? If so, please leave us a review with your feedback!<br><br>';
        echo "<a href=\"$review_url\" class=\"button button-primary idx_accept_review_prompt\" target=\"_blank\">Review IMPress Now</a> ";
        echo "<a href=\"#\" class=\"idx_dismiss_review_prompt\">No Thanks</a>";
        echo '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        //dismiss via ajax
        wp_enqueue_script('idx_dismiss_review_prompt', plugins_url('/assets/js/idx-dismiss-review-prompt.min.js', dirname(__FILE__)), 'jquery');

    }

    public function dismiss_prompt()
    {
        if (!get_option('idx_dismiss_review_prompt')) {
            add_option('idx_dismiss_review_prompt');
        }
        update_option('idx_dismiss_review_prompt', true);
        wp_die();
    }

}

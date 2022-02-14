<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Migrate Posts from Genesis Agent Profiles to IMPress Agents
 * @since 1.1.0
*/
class IMPress_Agents_Migrate
{
	public function __construct()
	{
		$post_info = get_posts( array(
			'post_type' => 'aeprofiles',
			'nopaging'  => true
			)
		);
		if (empty($post_info)) {
			return;
		}
		$this->update_post_type($post_info);
	}

	public function update_post_type($post_info)
	{
		$meta_keys = array(
			'_agent_title' 		  => '_employee_title',
			'_agent_license' 	  => '_employee_license',
			'_agent_designations' => '_employee_designations',
			'_agent_phone' 		  => '_employee_phone',
			'_agent_mobile' 	  => '_employee_mobile',
			'_agent_email' 		  => '_employee_email',
			'_agent_website' 	  => '_employee_website',
			'_agent_address' 	  => '_employee_address',
			'_agent_city' 		  => '_employee_city',
			'_agent_state' 		  => '_employee_state',
			'_agent_zip' 		  => '_employee_zip',
			'_agent_facebook' 	  => '_employee_facebook',
			'_agent_twitter' 	  => '_employee_twitter',
			'_agent_linkedin' 	  => '_employee_linkedin',
			'_agent_googleplus'   => '_employee_googleplus',
			'_agent_pinterest' 	  => '_employee_pinterest',
			'_agent_youtube' 	  => '_employee_youtube',
			'_agent_instagram' 	  => '_employee_instagram'
			);

		foreach($post_info as $post) {
			foreach($meta_keys as $old_key => $new_key) {
				$old_value = get_post_meta($post->ID, $old_key, true);
				update_post_meta($post->ID, $new_key, $old_value);
			}
			set_post_type($post->ID, 'employee');
		}
	}
}

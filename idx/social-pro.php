<?php
/**
 * Social Pro.
 *
 * Creates new random posts from the Social Pro integration.
 *
 * @package IMPress_for_IDX_Broker
 */

namespace IDX;

/**
 * Class for Social Pro page routes.
 *
 * Supports GET and POST requests that return/set the Social Pro settings.
 */
class Social_Pro {
	/**
	 * Checks social pro status.
	 *
	 * @var string
	 */
	private $cron_hook;

	/**
	 * IDX Broker API object.
	 *
	 * @var IDX\IDX_Api
	 */
	private $idx_api;

	/**
	 * Settings associative array.
	 *
	 * @var array
	 */
	private $settings;


	/**
	 * Initialize member variables.
	 */
	public function __construct() {
		$this->cron_hook = 'idx_broker_create_social_pro_article';
		$this->idx_api   = new \IDX\Idx_Api();
		$this->settings  = $this->get_settings();
	}

	/**
	 * Initialize hooks.
	 */
	public function initialize_hooks() {
		add_action( $this->cron_hook, [ $this, 'add_article' ] );
		add_action( 'draft_to_publish', [ $this, 'draft_to_publish' ] );
	}

	/**
	 * Initiates cron task.
	 *
	 * @param bool $override Clear existing cron schedule.
	 * @return void
	 */
	public function setup_cron( $override = false ) {
		if ( $override ) {
			wp_clear_scheduled_hook( $this->cron_hook );
		}

		if ( ! wp_next_scheduled( $this->cron_hook ) ) {
			wp_schedule_single_event( $this->cron_timing(), $this->cron_hook );
		}
	}

	/**
	 * Cron task to add article.
	 *
	 * @return void
	 */
	public function add_article() {
		$this->create_post();
		$this->setup_cron();
	}

	/**
	 * Get the Social Pro settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$defaults = [
			'autopublish'  => 'autopublish',
			'post_day'     => 'tue',
			'post_type'    => 'post',
			'excluded_ids' => [],
			'author'       => get_current_user_id(),
			'categories'   => [],
		];
		$existing = get_option( 'idx_broker_social_pro_settings', [] );
		$settings = array_merge( $defaults, $existing );
		return $settings;
	}

	/**
	 * Creates the WordPress post.
	 *
	 * @return int|void Post ID created.
	 */
	public function create_post() {
		if ( ! $this->get_subscribed_status() ) {
			return;
		}
		$post_status = 'autopublish' === $this->settings['autopublish'] ? 'publish' : 'draft';
		$article     = $this->get_article();

		if ( is_wp_error( $article ) || ! isset( $article['title1'] ) ) {
			return;
		}

		$article['social_pro']     = true;
		$article['has_syndicated'] = false;

		$postarr = [
			'post_author'   => $this->settings['author'],
			'post_title'    => $this->choose_article_title( $article ),
			'post_content'  => $article['body'],
			'post_status'   => $post_status,
			'post_type'     => $this->settings['post_type'],
			'tags_input'    => $article['tags'],
			'post_category' => $this->category_id_array(),
			'meta_input'    => $article,
		];

		$id = wp_insert_post( $postarr );

		if ( 'publish' === $post_status ) {
			$this->syndicate( $id, $article );
		}
		return $id;
	}

	/**
	 * Get the social pro settings associative array.
	 *
	 * @return array|WP_Error
	 */
	public function get_article() {
		$params  = [
			'exludedIds' => $this->settings['excluded_ids'],
		];
		$article = $this->idx_api->idx_api(
			'generalInterestArticle',
			$this->idx_api->idx_api_get_apiversion(),
			'clients',
			$params,
			0,
			'PUT'
		);

		if ( is_wp_error( $article ) ) {
			return $this->convert_idx_api_error( $article );
		}

		$this->update_exluded_ids( $article['id'] );
		return $article;
	}
	/**
	 * Updates exlucded ids array.
	 *
	 * @param string|int $id ID to add to array.
	 * @return void
	 */
	public function update_exluded_ids( $id ) {
		$id       = (int) $id;
		$settings = get_option( 'idx_broker_social_pro_settings', [] );
		if ( ! isset( $settings['excluded_ids'] ) ) {
			$settings['excluded_ids'] = [ $id ];
		} else {
			$settings['excluded_ids'][] = $id;
		}

		update_option( 'idx_broker_social_pro_settings', $settings );
	}

	/**
	 * Updates excluded ids array.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Error|array|void
	 */
	public function syndicate( $post_id ) {
		$meta_fields = get_post_meta( $post_id );
		if (
			! isset( $meta_fields['social_pro'] ) ||
			! boolval( $meta_fields['social_pro'][0] ) ||
			! isset( $meta_fields['has_syndicated'] ) ||
			boolval( $meta_fields['has_syndicated'][0] )
		) {
			return;
		}
		if ( ! $this->get_subscribed_status() ) {
			return;
		}
		update_post_meta( $post_id, 'has_syndicated', true );
		$custom_url = rawurlencode( get_permalink( $post_id ) );
		$params     = [
			'customUrl' => $custom_url,
			'imageUrl'  => $meta_fields['image'][0],
			'title'     => get_the_title( $post_id ),
		];

		$data = $this->idx_api->idx_api(
			'socialPostSyndicate',
			$this->idx_api->idx_api_get_apiversion(),
			'clients',
			$params,
			0,
			'PUT'
		);

		return $data;
	}

	/**
	 * Callback when posts are published.
	 *
	 * @param Array $post Post data.
	 * @return void
	 */
	public function draft_to_publish( $post ) {
		$this->syndicate( $post->ID );
	}

	/**
	 * Checks if subscribed to Social Pro.
	 *
	 * @return string
	 */
	public function get_subscribed_status() {
		$status = $this->idx_api->idx_api( 'socialProStatus' );
		if ( is_wp_error( $status ) || ! $status ) {
			return false;
		}
		return 'enabled' === $status['socialProStatus'] ? true : false;
	}

	/**
	 * Grabs beta program info.
	 *
	 * @return array
	 */
	public function get_beta_status() {
		$status = $this->idx_api->idx_api( 'socialProStatus' );
		if ( is_wp_error( $status ) || ! $status || ! isset( $status['restrictedByBeta'] ) ) {
			return [
				'restrictedByBeta' => false,
			];
		}
		return [
			'restrictedByBeta' => $status['restrictedByBeta'],
			'optedInBeta'      => $status['optedInBeta'],
		];
	}

	/**
	 * Returns an array of selected category IDs.
	 *
	 * @return array
	 */
	private function category_id_array() {
		return array_map(
			function ( $category ) {
				return $category->term_id;
			},
			$this->settings['categories']
		);
	}

	/**
	 * Returns a random article title.
	 *
	 * @param  array $article Article array.
	 * @return string
	 */
	private function choose_article_title( $article ) {
		$titles = [];
		foreach ( $article as $key => $value ) {
			if ( strpos( $key, 'title' ) !== false && $value ) {
				$titles[] = $value;
			}
		}
		return $titles[ array_rand( $titles ) ];
	}

	/**
	 * Returns when to fire the next cron.
	 *
	 * @return int
	 */
	private function cron_timing() {
		$day = strtotime( 'next Tuesday' );
		switch ( $this->settings['post_day'] ) {
			case 'mon':
				$day = strtotime( 'next Monday' );
				break;
			case 'tue':
				$day = strtotime( 'next Tuesday' );
				break;
			case 'wed':
				$day = strtotime( 'next Wednesday' );
				break;
			case 'thur':
				$day = strtotime( 'next Thursday' );
				break;
			case 'fri':
				$day = strtotime( 'next Friday' );
				break;
			case 'sat':
				$day = strtotime( 'next Saturday' );
				break;
			case 'sun':
				$day = strtotime( 'next Sunday' );
				break;
		}

		$time = $day + ( 60 * 60 * 8 );
		return $time;
	}
}

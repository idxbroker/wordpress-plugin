<?php
namespace IDX;

use \Carbon\Carbon;
use \Exception;

/**
 * Dashboard_Widget class.
 */
class Dashboard_Widget {
	public function __construct() {
		$this->idx_api = new Idx_Api();

		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
		add_action( 'wp_ajax_idx_dashboard_leads', [ $this, 'leads_overview' ] );
		add_action( 'wp_ajax_idx_dashboard_listings', [ $this, 'listings_overview' ] );
		add_action( 'wp_ajax_side_overview_data', [ $this, 'side_overview_data' ] );
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Api_error
	 *
	 * @var mixed
	 * @access private
	 */
	private $api_error;

	/**
	 * Api_key_validator function.
	 *
	 * @access private
	 * @return void
	 */
	private function api_key_validator() {
		$system_links = $this->idx_api->idx_api_get_systemlinks();
		if ( is_wp_error( $system_links ) ) {
			$this->api_error = $system_links->get_error_message();
		}
	}

	/**
	 * Add_dashboard_widget function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_dashboard_widget() {
		add_meta_box( 'idx_dashboard_widget', 'IMPress for IDX Broker', array( $this, 'compile_dashboard_widget' ), 'dashboard', 'normal', 'high' );
	}

	/**
	 * Compile_dashboard_widget function.
	 *
	 * @access public
	 * @return void
	 */
	public function compile_dashboard_widget() {
		$this->api_key_validator();

		// API key is present and there are no errors.
		if ( get_option( 'idx_broker_apikey' ) && null === $this->api_error ) {
			$this->dashboard_widget_html();
			$this->load_scripts();
		}

		// API key is present and there is an error.
		if ( get_option( 'idx_broker_apikey' ) && $this->api_error ) {
			$allowed_html = [
				'a' => [
					'href'  => [],
					'title' => [],
				],
			];
			echo wp_kses( $this->api_error, $allowed_html );
		}

		// No key and no error (initial state of plugin after install but before API key is added).
		if ( ! get_option( 'idx_broker_apikey' ) && null === $this->api_error ) {
			echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=idx-broker#/guided-setup/welcome">Enter your IDX Broker API key to get started</a>';
		}
	}

	/**
	 * Dashboard_widget_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function dashboard_widget_html() {
		echo '<div class="widget-header">';
		echo '<button class="button leads" disabled="disabled">Lead Overview</button>';
		echo '<button class="button button-primary listings">Listing Overview</button>';
		echo '<div class="timeframe">';
		echo '<label class="week-day-label">Day</label>';
		echo '<div class="onoffswitch">';
		echo '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="timeframeswitch">';
		echo '<label class="onoffswitch-label" for="timeframeswitch">';
		echo '<span class="onoffswitch-inner"></span>';
		echo '<span class="onoffswitch-switch"></span>';
		echo '</label>';
		echo '</div>';
		echo '<label>Month</label>';
		echo '</div></div>';
		echo '<div class="idx-loader"></div>';
		echo '<div class="leads-overview"></div>';
		echo '<div class="listings-overview"></div>';
		echo '<div class="side-overview">';
		$this->side_overview();
		echo '</div>';
	}

	/**
	 * Leads_overview function.
	 *
	 * @access public
	 * @return void
	 */
	public function leads_overview() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'impress-dashboard-leads-nonce' ) ) {
			$interval  = sanitize_text_field( $_POST['timeframe'] );
			$timeframe = null;

			try {
				$leads = wp_json_encode( $this->leads_json( $timeframe, $interval ) );
				echo wp_kses_post( $leads );
				wp_die();
			} catch ( Exception $error ) {
				echo wp_kses_post( $error->getMessage() );
				wp_die();
			}
		}
		wp_die();
	}

	/**
	 * Listings_overview function.
	 *
	 * @access public
	 * @return void
	 */
	public function listings_overview() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'impress-dashboard-listings-nonce' ) ) {
			$interval = sanitize_text_field( $_POST['timeframe'] );
			try {
				$listings = wp_json_encode( $this->listings_json( $interval ) );
				echo wp_kses_post( $listings );
				wp_die();
			} catch ( Exception $error ) {
				echo wp_kses_post( $error->getMessage() );
				wp_die();
			}
		}
		wp_die();
	}

	/**
	 * Side_overview function.
	 *
	 * @access public
	 * @return void
	 */
	public function side_overview() {
		echo '<div class="new-leads">
					<p>New Leads</p>
					<ul>
						<!-- Placeholder list item, will be replaced with live data via ajax -->
						<li><div class="idx-dashboard-loader" style="display:block;"></div></li>
					</ul>
				</div>
				<div class="popular-listings">
					<p>Popular Listings</p>
					<ul>
						<!-- Placeholder list item, will be replaced with live data via ajax -->
						<li><div class="idx-dashboard-loader" style="display:block;"></div></li>
					</ul>
				</div>';
	}

	/**
	 * Side_overview_data function.
	 *
	 * @access public
	 * @return mixed
	 */
	public function side_overview_data() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'impress-dashboard-overview-nonce' ) ) {
			try {
				$leads = $this->new_leads();
			} catch ( Exception $error ) {
				$leads = '<li>' . $error->getMessage() . '</li>';
			}

			try {
				$listings = $this->popular_listings();
			} catch ( Exception $error ) {
				$listings = '<li>' . $error->getMessage() . '</li>';
			}

			echo wp_json_encode(
				[
					'data' => [
						'leads'    => $leads,
						'listings' => $listings,
					],
				]
			);
		}
		wp_die();
	}

	/**
	 * Load_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function load_scripts() {
		wp_enqueue_style( 'idx-dashboard-widget', IMPRESS_IDX_URL . 'assets/css/idx-dashboard-widget.min.css', [], '1.0' );
		wp_enqueue_script( 'google-charts', IMPRESS_IDX_URL . 'assets/js/loader.min.js', [], '1.0', false );
		wp_enqueue_script( 'idx-dashboard-widget', IMPRESS_IDX_URL . 'assets/js/idx-dashboard-widget.min.js', [], '1.0', false );
		wp_add_inline_script(
			'idx-dashboard-widget',
			'const idxDashboardNonces = ' . wp_json_encode(
				[
					'leadsNonce'    => wp_create_nonce( 'impress-dashboard-leads-nonce' ),
					'listingsNonce' => wp_create_nonce( 'impress-dashboard-listings-nonce' ),
					'overviewNonce' => wp_create_nonce( 'impress-dashboard-overview-nonce' ),
				]
			),
			'before'
		);
		wp_enqueue_style( 'font-awesome-5.8.2' );
	}

	/**
	 * Leads_json function.
	 *
	 * @access public
	 * @throws Exception - Exception.
	 * @param mixed $timeframe - Timeframe.
	 * @param mixed $interval - Interval.
	 * @return mixed
	 */
	public function leads_json( $timeframe, $interval ) {
		try {
			$interval_array = $this->get_interval_data( $timeframe, $interval );
		} catch ( Exception $e ) {
			throw new Exception( $e->getMessage() );
		}
		$interval_data = $interval_array['interval_data'];
		$min_max       = $interval_array['min_max'];

		if ( 'month' === $interval ) {
			$data = $this->leads_month_interval( $interval_data, $min_max );
		} elseif ( 'day' === $interval ) {
			$data = $this->leads_day_interval( $interval_data, $min_max );
		}

		return $data;
	}

	/**
	 * Listings_json function.
	 *
	 * @access public
	 * @param mixed $interval - Interval.
	 * @return mixed
	 */
	public function listings_json( $interval ) {
		if ( 'day' === $interval ) {
			// return one week of data.
			$timeframe = 24 * 7;

		} else {
			// return one month of data.
			$timeframe = 24 * 30;
		}

		$listings = $this->all_listings_numbers( $timeframe );
		return $listings;
	}

	/**
	 * New_leads function.
	 *
	 * @access public
	 * @throws Exception - Exception.
	 * @return mixed
	 */
	public function new_leads() {
		// order newest first.
		$leads_array = $this->idx_api->get_recent_leads( 'subscribeDate', 5 );
		// handle empty leads and listing arrays.
		if ( is_wp_error( $leads_array ) || empty( $leads_array ) ) {
			throw new Exception( 'No Leads Returned' );
		}

		$leads = '';

		// prepare leads for display.
		foreach ( $leads_array as $lead ) {
			// edit lead in MW link.
			$leads .= '<a href="https://middleware.idxbroker.com/mgmt/editlead.php?id=' . esc_attr( $lead->id ) . '" target="_blank">';
			$leads .= '<li><p class="lead-name">';
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$leads .= esc_html( $lead->firstName ) . ' ' . esc_html( $lead->lastName ) . '</p>';
			$leads .= '<p class="lead-email">' . esc_html( $lead->email ) . '</p><i class="fas fa-user"></i></li></a>';
		}

		return $leads;
	}

	/**
	 * Popular_listings function.
	 *
	 * @access public
	 * @throws Exception - Exception.
	 * @return mixed
	 */
	public function popular_listings() {
		$listings_array = $this->idx_api->get_featured_listings();
		if ( is_wp_error( $listings_array ) || empty( $listings_array ) ) {
			throw new Exception( 'No Listings Returned' );
		}

		$listings_array = $this->sort_listings_by_views( $listings_array );
		// only display 5 in order of most views first.
		$listings_array = array_slice( array_reverse( $listings_array ), 0, 5 );

		$listings = '';

		// prepare listings for display.
		foreach ( $listings_array as $listing ) {
			$listings .= '<a href="' . $listing['fullDetailsURL'] . '" target="_blank">';
			$listings .= '<li><p class="listing-address">' . $listing['address'] . '</p>';
			$listings .= '<p class="listing-views">' . $listing['viewCount'] . ' Views</p><i class="fas fa-external-link-alt"></i></li></a>';
		}

		return $listings;
	}

	/**
	 * Leads_month_interval function.
	 *
	 * @access public
	 * @param mixed $interval_data - Interval data.
	 * @param mixed $min_max - Min/max.
	 * @return mixed
	 */
	public function leads_month_interval( $interval_data, $min_max ) {
		$data          = array();
		$unique_months = array();
		// headers for chart.
		$data[] = array(
			'Month',
			'Registrations',
		);
		$min    = $min_max['min'];
		$max    = $min_max['max'];

		// create year then iterate over.
		// note that this is not necessarily a true 12 month year.
		$year = $this->create_year( $min, $max );

		// if lead capture month matches month of year, add to array.
		foreach ( $year as $month ) {
			foreach ( $interval_data as $data_month ) {
				$date           = $month['date'];
				$data_timestamp = $data_month['timestamp'];
				$timestamp      = $month['timestamp'];
				if ( date( 'M Y', $data_timestamp ) === $date ) {
					$unique_months[ $date ] = $date;
					$data[]                 = array(
						$date,
						$data_month['value'],
					);
				}
			}
			// if no lead was captured for the month, set month to 0.
			if ( ! isset( $unique_months[ $date ] ) ) {
				$data[] = array(
					$date,
					0,
				);
			}
		}
		return $data;
	}

	/**
	 * Leads_day_interval function.
	 *
	 * @access public
	 * @param mixed $interval_data - Interval data.
	 * @param mixed $min_max - Min/max.
	 * @return mixed
	 */
	public function leads_day_interval( $interval_data, $min_max ) {
		$data   = array();
		$data[] = array(
			'Day',
			'Registrations',
		);
		$min    = $min_max['min'];
		$max    = $min_max['max'];

		// create week from last 7 days to iterate over.
		$week        = $this->create_week( $min, $max );
		$unique_days = array();
		// if lead capture day matches day of week, add to array.
		foreach ( $interval_data as $data_day ) {
			foreach ( $week as $day ) {
				$date           = $day['date'];
				$data_timestamp = $data_day['timestamp'];
				$timestamp      = $day['timestamp'];

				if ( date( 'm-d', $data_timestamp ) === $date ) {
					if ( isset( $unique_days[ $date ] ) ) {
						$unique_days[ $date ] = array(
							$date,
							$unique_days[ $date ][1] + 1,
						);
					} else {
						$unique_days[ $date ] = array(
							$date,
							$data_day['value'],
						);
					}
				} else {
					if ( isset( $unique_days[ $date ] ) ) {
						// if already set, continue to next item in array.
						continue;
					} else {
						$unique_days[ $date ] = array(
							$date,
							0,
						);
					}
				}
			}
		}

		foreach ( $unique_days as $unique_day ) {
			$data[] = $unique_day;
		}
		return $data;
	}

	/**
	 * Create_year function.
	 *
	 * @access public
	 * @param mixed $min - Minimum.
	 * @param mixed $max - Maximum.
	 * @return mixed
	 */
	public function create_year( $min, $max ) {
		$year_array      = array();
		$month_timestamp = $min;
		for ( $i = 0; $i < 6; $i++ ) {
			$date                  = date( 'M Y', $month_timestamp );
			$carbon_object         = Carbon::createFromTimestamp( $month_timestamp );
			$carbon_object->month += 1;
			$next_month            = $carbon_object->timestamp;
			$year_array[]          = array(
				'date'      => $date,
				'value'     => 0,
				'timestamp' => $month_timestamp,
			);
			// move to next day.
			$month_timestamp = $next_month;
		}

		return $year_array;
	}

	/**
	 * Create_week function.
	 *
	 * @access public
	 * @param mixed $min - Minimum.
	 * @param mixed $max - Maximum.
	 * @return mixed
	 */
	public function create_week( $min, $max ) {
		$week_array = array();
		$day        = $min;
		for ( $i = 0; $i < 7; $i++ ) {
			$date         = date( 'm-d', $day );
			$week_array[] = array(
				'date'      => $date,
				'value'     => 0,
				'timestamp' => $day,
			);
			// move to next day.
			$day = $day + 60 * 60 * 24;
		}
		return $week_array;
	}

	/**
	 * Get_interval_data function.
	 *
	 * @access public
	 * @throws Exception - Exception.
	 * @param mixed $timeframe - Timeframe.
	 * @param mixed $interval - Interval.
	 * @return mixed
	 */
	public function get_interval_data( $timeframe, $interval ) {
		$leads_array = array();
		$min_max     = $this->min_max_intervals( $interval );

		$api_data = $this->idx_api->get_leads();
		// if no leads in API data, throw exception.
		if ( is_wp_error( $api_data ) || empty( $api_data ) ) {
			throw new Exception( 'No Leads Returned' );
		}

		foreach ( $api_data as $api_data_lead ) {
			// convert date to Carbon instance for easy parsing.
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$subscribe_date = Carbon::parse( $api_data_lead->subscribeDate )->timestamp;
			// if the subscribe date is before the min date, skip.
			if ( $subscribe_date < $min_max['min'] ) {
				continue;
			}
			// add entry with timestamp to leads_array.
			$leads_array[] = array(
				'timestamp' => $subscribe_date,
			);
		}

		$interval_data = $this->interval_data( $leads_array, $interval );

		// if no leads are within the timeframe, throw exception.
		if ( empty( $interval_data ) ) {
			throw new Exception( 'No Leads Returned' );
		}

		return compact( 'min_max', 'interval_data' );

	}

	/**
	 * Min_max_intervals function.
	 *
	 * @access public
	 * @param mixed $interval - Interval.
	 * @return mixed
	 */
	public function min_max_intervals( $interval ) {
		if ( 'month' === $interval ) {
			$min = Carbon::parse( '5 months ago' )->timestamp;
			$max = Carbon::now()->timestamp;
		} elseif ( 'day' === $interval ) {
			$min = Carbon::parse( '6 days ago' )->timestamp;
			$max = Carbon::now()->timestamp;
		}

		return compact( 'min', 'max' );
	}

	/**
	 * Feed in week or month. Example: $data, 'month'.
	 *
	 * @param mixed $data - Data.
	 * @param mixed $interval - Interval.
	 */
	public function interval_data( $data, $interval ) {
		$interval_data = array();
		foreach ( $data as $datum ) {
			$interval_number = Carbon::createFromTimestamp( $datum['timestamp'] )->$interval;
			if ( ! isset( $interval_data[ $interval_number ] ) ) {
				$interval_data[ $interval_number ] = array(
					'timestamp' => $datum['timestamp'],
					'value'     => 0,
				);
			}
			$interval_data[ $interval_number ]['value'] += 1;
		}

		return $interval_data;
	}

	/**
	 * All_listings_numbers function.
	 *
	 * @access public
	 * @param mixed $timeframe - Timeframe.
	 * @return mixed
	 */
	public function all_listings_numbers( $timeframe ) {

		$featured = $this->idx_api->get_featured_listings( 'featured', $timeframe );
		$archived = $this->idx_api->get_featured_listings( 'soldpending', $timeframe );
		// only idxStatus has sold/pending status, so copy that to propStatus.
		$archived     = $this->clone_idx_status_to_prop_status( $archived );
		$all_listings = array_merge( $featured, $archived );

		// find the number of listings per status for all account's listings.
		$listings_json = $this->get_listings_number( $all_listings, 'propStatus' );

		return $listings_json;

	}

	/**
	 * Get_listings_number function.
	 *
	 * @access public
	 * @throws Exception - Exception.
	 * @param mixed $listings - Listings.
	 * @param mixed $status_type - Status type.
	 * @return mixed
	 */
	public function get_listings_number( $listings, $status_type ) {
		// if no listings, throw exception.
		if ( empty( $listings ) ) {
			throw new Exception( 'No Listings Returned' );
		}

		// chart headers.
		$json_data = array(
			array( 'Listing Status', 'Count' ),
		);

		$listings_record = array();

		foreach ( $listings as $listing ) {
			$listing_status = $listing[ "$status_type" ];

			// if status entry is not yet set, add it.
			if ( ! isset( $listings_record[ $listing_status ] ) ) {
				$listings_record[ $listing_status ] = array( $listing_status, 1 );
			} else {
				// increase count of status.
				$listings_record[ $listing_status ][1] += 1;
			}
		}

		// prepare data for use with front end charts.
		foreach ( $listings_record as $status ) {
			$json_data[] = $status;
		}

		return $json_data;
	}

	/**
	 * Prepare sold/pending listings status.
	 *
	 * @param array $listings - Array of listings.
	 */
	public function clone_idx_status_to_prop_status( $listings ) {
		foreach ( $listings as $listing ) {
			$listing['propStatus'] = $listing['idxStatus'];
		}

		return $listings;
	}

	/**
	 * Sort_listings_by_views function.
	 *
	 * @access public
	 * @param mixed $listings - Listings.
	 * @return mixed
	 */
	public function sort_listings_by_views( $listings ) {
		usort(
			$listings,
			function( $a, $b ) {
				if ( (int) $a['viewCount'] === (int) $b['viewCount'] ) {
					return 0;
				}

				return ( (int) $a['viewCount'] < (int) $b['viewCount'] ) ? -1 : 1;
			}
		);

		return $listings;
	}
}

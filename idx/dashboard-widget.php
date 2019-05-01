<?php
namespace IDX;

use \Carbon\Carbon;
use \Exception;

/**
 * Dashboard_Widget class.
 *
 * @since 2.5.10
 */
class Dashboard_Widget {
	/**
	 * Begin constructor function
	 *
	 * @since 2.5.10
	 */
	public function __construct() {
		$this->idx_api = new Idx_Api();

		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		add_action( 'wp_ajax_idx_dashboard_leads', array( $this, 'leads_overview' ) );
		add_action( 'wp_ajax_idx_dashboard_listings', array( $this, 'listings_overview' ) );
	}

	/**
	 * Begin idx_api
	 *
	 * @var mixed
	 * @access public
	 * @since 2.5.10
	 */
	public $idx_api;

	/**
	 * Begin api_error
	 *
	 * @var mixed
	 * @access private
	 * @since 2.5.10
	 */
	private $api_error;

	/**
	 * Begin api_key_validator function.
	 *
	 * @access private
	 * @since 2.5.10
	 */
	private function api_key_validator() {
		$system_links = $this->idx_api->idx_api_get_systemlinks();
		if ( is_wp_error( $system_links ) ) {
			$this->api_error = $system_links->get_error_message();
		}
	}

	/**
	 * Begin creating add_dashboard_widget function.
	 *
	 * @access public
	 * @since 2.5.10
	 */
	public function add_dashboard_widget() {
		add_meta_box( 'idx_dashboard_widget', 'IMPress for IDX Broker', array( $this, 'compile_dashboard_widget' ), 'dashboard', 'normal', 'high' );
	}

	/**
	 * Begin creating compile_dashboard_widget function.
	 *
	 * @access public
	 * @since 2.5.10
	 */
	public function compile_dashboard_widget() {
		$this->api_key_validator();

		// API key is present and there are no errors.
		if ( get_option( 'idx_broker_apikey' ) && null === $this->api_error ) {
			echo esc_html( $this->dashboard_widget_html() );
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
			echo esc_html( '<a href="' . esc_url( admin_url() ) . 'admin.php?page=idx-broker">Enter your IDX Broker API key to get started</a>' );
		}
	}

	/**
	 * Begin dashboard_widget_html function.
	 *
	 * @access public
	 * @since 2.5.10
	 * @return html output
	 */
	public function dashboard_widget_html() {
		$output  = '<div class="widget-header">';
		$output .= '<button class="button leads" disabled="disabled">Lead Overview</button>';
		$output .= '<button class="button button-primary listings">Listing Overview</button>';
		$output .= '<div class="timeframe">';
		$output .= '<label class="week-day-label">Day</label>';
		$output .= '<div class="onoffswitch">';
		$output .= '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="timeframeswitch">';
		$output .= '<label class="onoffswitch-label" for="timeframeswitch">';
		$output .= '<span class="onoffswitch-inner"></span>';
		$output .= '<span class="onoffswitch-switch"></span>';
		$output .= '</label>';
		$output .= '</div>';
		$output .= '<label>Month</label>';
		$output .= '</div></div>';
		$output .= '<div class="idx-loader"></div>';
		$output .= '<div class="leads-overview"></div>';
		$output .= '<div class="listings-overview"></div>';
		$output .= '<div class="side-overview">' . $this->side_overview() . '</div>';
		return esc_html( $output );
	}

	/**
	 * Begin creating leads_overview function.
	 *
	 * @access public
	 * @since 2.5.10
	 */
	public function leads_overview() {
		$interval  = sanitize_text_field( $_POST['timeframe'] );
		$timeframe = null;

		try {
			$leads = wp_json_encode( $this->leads_json( $timeframe, $interval ) );
			echo esc_html( $leads );
			wp_die();
		} catch ( Exception $error ) {
			echo esc_html( $error->getMessage() );
			wp_die();
		}
	}

	/**
	 * Begin creating listings_overview function.
	 *
	 * @access public
	 * @since 2.5.10
	 */
	public function listings_overview() {
		$interval = sanitize_text_field( $_POST['timeframe'] );

		try {
			$listings = json_encode( $this->listings_json( $interval ) );
			echo esc_html( $listings );
			wp_die();
		} catch ( Exception $error ) {
			echo esc_html( $error->getMessage() );
			wp_die();
		}
	}

	/**
	 * Begin creatingside_overview function.
	 *
	 * @access public
	 * @since 2.5.10
	 * @return html output
	 */
	public function side_overview() {
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
		$output  = '<div class="new-leads"><p>New Leads</p>';
		$output .= '<ul>' . $leads . '</ul></div>';
		$output .= '<div class="popular-listings"><p>Popular Listings</p>';
		$output .= '<ul>' . $listings . '</ul></div>';
		return esc_html( $output );
	}

	/**
	 * Begin creating load_scripts function.
	 *
	 * @access public
	 * @since 2.5.10
	 */
	public function load_scripts() {
		wp_enqueue_style( 'idx-dashboard-widget', plugins_url( '/assets/css/idx-dashboard-widget.css', dirname( __FILE__ ) ) );
		wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js' );
		wp_enqueue_script( 'idx-dashboard-widget', plugins_url( '/assets/js/idx-dashboard-widget.min.js', dirname( __FILE__ ) ) );
		wp_enqueue_style( 'font-awesome-4.7.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0' );
	}

	/**
	 * Begin creating leads_json function.
	 *
	 * @access public
	 * @param mixed $timeframe using time stamp.
	 * @param mixed $interval number per return.
	 * @since 2.5.10
	 * @throws Exception $e error when interval can't be met.
	 * @return array lead data
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
	 * Begin creating listings_json function.
	 *
	 * @access public
	 * @param mixed $interval of days.
	 * @since 2.5.10
	 * @return array of listings.
	 */
	public function listings_json( $interval ) {
		$data = array();
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
	 * Begin creating new_leads function.
	 *
	 * @access public
	 * @since 2.5.10
	 * @throws Exception $e new message if no leads are returned.
	 * @return array of leads.
	 */
	public function new_leads() {
		// order newest first.
		$leads_array = $this->idx_api->get_leads();
		// handle empty leads and listing arrays.
		if ( is_wp_error( $leads_array ) || empty( $leads_array ) ) {
			throw new Exception( 'No Leads Returned' );
		}

		$leads_array = array_slice( array_reverse( $leads_array ), 0, 5 );

		$leads = '';

		// prepare leads for display.
		foreach ( $leads_array as $lead ) {
			// edit lead in MW link.
			$leads .= '<a href="https://middleware.idxbroker.com/mgmt/editlead.php?id=' . $lead->id . '" target="_blank">';
			$leads .= '<li><p class="lead-name">';
			$leads .= $lead->firstName . ' ' . $lead->lastName . '</p>'; // firstName and lastName are not in valid snake_case format.
			$leads .= '<p class="lead-email">' . $lead->email . '</p><i class="fa fa-user"></i></li></a>';
		}

		return $leads;
	}

	/**
	 * Begin creating popular_listings function.
	 *
	 * @access public
	 * @since 2.5.10
	 * @throws Exception $e if no listings are returned.
	 * @return array of listings
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
			$listings .= '<p class="listing-views">' . $listing['viewCount'] . ' Views</p><i class="fa fa-external-link"></i></li></a>';
		}

		return $listings;
	}

	/**
	 * Begin creating leads_month_interval function.
	 *
	 * @access public
	 * @param mixed $interval_data intervals.
	 * @param mixed $min_max min to max.
	 * @since 2.5.10
	 * @return array of lead data
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

		// create year then iterate over
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
	 * Begin creating leads_day_interval function.
	 *
	 * @access public
	 * @param mixed $interval_data interval data.
	 * @param mixed $min_max min and max.
	 * @since 2.5.10
	 * @return array lead data.
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
	 * Begin creating create_year function.
	 *
	 * @access public
	 * @param mixed $min minimum.
	 * @param mixed $max maximum.
	 * @since 2.5.10
	 * @return array year array
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
	 * Begin creating create_week function.
	 *
	 * @access public
	 * @param mixed $min minimum.
	 * @param mixed $max maximum.
	 * @since 2.5.10
	 * @return array week array.
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
	 * Begin creating get_interval_data function.
	 *
	 * @access public
	 * @param mixed $timeframe timeframe range.
	 * @param mixed $interval of dates.
	 * @since 2.5.10
	 * @throws Exception $e when no leads are returned.
	 * @return mixed min/max interval data.
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
			$subscribe_date = Carbon::parse( $api_data_lead->subscribeDate )->timestamp; // subscribeDate is not in valid snake_case format.
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
	 * Begin creating min_max_intervals function.
	 *
	 * @access public
	 * @param mixed $interval interval.
	 * @since 2.5.10
	 * @return mixed min/max values.
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
	 * Begin creating interval data function
	 * Feed in week or month. Example: $data, 'month'
	 *
	 * @since 2.5.10
	 * @param array $data array of lead data.
	 * @param mixed $interval to chunk leads by.
	 * @return array if interval data.
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
	 * Begin creating all_listings_numbers function.
	 *
	 * @access public
	 * @param mixed $timeframe is the timestamp range.
	 * @since 2.5.10
	 * @return string lisings json data.
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
	 * Begin creating get_listings_number function.
	 *
	 * @access public
	 * @param mixed $listings json string of listing data.
	 * @param mixed $status_type is the status of the listing.
	 * @since 2.5.10
	 * @throws Exception $e if no listings are returned.
	 * @return json data.
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

	// prepare sold/pending listings status.
	/**
	 * Begin creating clone_idx_status_to_prop_status
	 *
	 * @since 2.5.10
	 * @param mixed $listings listing data.
	 * @return mixed listing statuses.
	 */
	public function clone_idx_status_to_prop_status( $listings ) {
		foreach ( $listings as $listing ) {
			$listing['propStatus'] = $listing['idxStatus'];
		}

		return $listings;
	}

	/**
	 * Begin creating sort_listings_by_views function.
	 *
	 * @access public
	 * @param mixed $listings is the listing data.
	 * @since 2.5.10
	 * @return array of listing data.
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

<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * This widget displays a featured employee.
 *
 * @since 0.9.0
 * @author Agent Evolution
 */
class IMPress_Agents_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'featured-employee', 'description' => __( 'Display a featured employee or employees contact info.', 'impress_agents' ), 'customize_selective_refresh' => true );
		$control_ops = array( 'width' => 300, 'height' => 350 );
		parent::__construct( 'featured-employee', __( 'IMPress Agents', 'impress_agents' ), $widget_ops, $control_ops );
		add_shortcode( 'impress-agent', [ $this, 'impress_agent_shortcode' ] );
	}

	public function impress_agent_shortcode( $atts ) {
		extract(
			shortcode_atts(
				[
					'post_id'     => '',
					'title'       => '',
					'show_agent'  => 0,
					'show_number' => 1,
					'orderby'     => '',
					'order'       => '',
				],
				$atts
			)
		);

		$args = [
			'before_widget' => '<div class="box widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="widget-title">',
			'after_title'   => '</div>',
		];

		ob_start();
		$this->widget( array_merge( $atts, $args ), [] );
		$output = ob_get_clean();
		return $output;
	}

	public function widget( $args, $instance ) {

		global $post;

		/** defaults */
		$instance = wp_parse_args( $instance, array(
			'post_id'	=> '',
			'title' => '',
			'show_agent' => 0,
			'show_number' => 1,
			'orderby' => '',
			'order' => ''
		) );

		extract( $args );

		$post_id     = $instance['post_id'];
		$title       = $instance['title'];
		$orderby     = $instance['orderby'];
		$order       = $instance['order'];
		$show_agent  = $instance['show_agent'];
		$show_number = ( ! empty( $instance['show_number'] ) ) ? absint( $instance['show_number'] ) : 1;

		echo $before_widget;

		if ( $show_agent == 'show_all' ) {
			echo $before_title . apply_filters( 'widget_title', $title, $instance, $this->id_base) . $after_title;
			$query_args = array( 'post_type' => 'employee', 'posts_per_page' => - 1, 'orderby' => $orderby, 'order' => $order);

		} elseif ( $show_agent == 'show_random' ) {
			echo $before_title . apply_filters( 'widget_title', $title, $instance, $this->id_base ) . $after_title;
			$query_args = array( 'post_type' => 'employee', 'posts_per_page' => $show_number, 'orderby' => 'rand', 'order' => $order);

		} elseif ( ! empty( $instance['post_id'] ) ) {
			$post_id = explode( ',', $instance['post_id'] );
			echo $before_title . apply_filters( 'widget_title', $title, $instance, $this->id_base) . $after_title;
			$query_args = array( 'post_type' => 'employee', 'p' => $post_id[0], 'posts_per_page' => 1, 'orderby' => $orderby, 'order' => $order);

		}

		query_posts( $query_args );

		if ( have_posts() ) : while ( have_posts() ) : the_post();

				echo '<div ', esc_attr( post_class( 'widget-agent-wrap' ) ), '>';
				echo '<a href="' . esc_url( get_permalink() ) . '">', get_the_post_thumbnail( $post->ID, 'employee-thumbnail' ), '</a>';
				printf( '<div class="widget-agent-details"><a class="fn" href="%s">%s</a>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
				impa_employee_archive_details();

				if ( function_exists( '_p2p_init' ) && function_exists( 'agentpress_listings_init' ) || function_exists( '_p2p_init' ) && function_exists( 'wp_listings_init' ) ) {
					$has_listings = impa_has_listings( $post->ID );
					if ( ! empty( $has_listings ) ) {
						echo '<p><a class="agent-listings-link" href="' . esc_url( get_permalink() ) . '#agent-listings">View My Listings</a></p>';
					}
				}

				echo '</div>';

				echo '</div><!-- .widget-agent-wrap -->';

			endwhile;
		endif;
		wp_reset_query();

		echo $after_widget;

	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show_number'] = (int) $new_instance['show_number'];

		return $new_instance;
	}

	public function form( $instance ) {

		$instance = wp_parse_args( $instance, array(
			'post_id'     => '',
			'title'		  => 'Featured Employees',
			'show_agent'  => 'show_selected',
			'show_number' => 1,
			'orderby'     => 'menu_order',
			'order'       => 'ASC'
			) );
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr( $instance['title'] ); ?>" />
		</p>

		<?php
		echo '<p>';
		echo '<label for="' . esc_attr( $this->get_field_id( 'post_id' ) ) . '">Select an Employee:</label>';
		echo '<select id="' . esc_attr( $this->get_field_id( 'post_id' ) ) . '" name="' . $this->get_field_name( 'post_id' ) . '" class="widefat" style="width:100%;">';
			global $post;
			$args = array( 'post_type' => 'employee', 'posts_per_page'	=> -1);
			$agents = get_posts( $args);
			foreach( $agents as $post ) : setup_postdata( $post);
				echo '<option style="margin-left: 8px; padding-right:10px;" value="' . $post->ID . ',' . $post->post_title . '" ' . selected( $post->ID . ',' . $post->post_title, $instance['post_id'], false ) . '>' . $post->post_title . '</option>';
			endforeach;
		echo '</select>';
		echo '</p>';

		?>

		<p>
				<label for="<?php echo $this->get_field_id( 'show_agent' ); ?>"><?php esc_html_e( 'Show Agent', 'impress_agents' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_agent' ); ?>" name="<?php echo $this->get_field_name( 'show_agent' ); ?>">
					<option value="show_selected" <?php selected( 'show_selected', $instance['show_agent'] ); ?>><?php esc_html_e( 'Show Agent selected above', 'impress_agents' ); ?></option>
					<option value="show_random" <?php selected( 'show_random', $instance['show_agent'] ); ?>><?php esc_html_e( 'Show Random', 'impress_agents' ); ?></option>
					<option value="show_all" <?php selected( 'show_all', $instance['show_agent'] ); ?>><?php esc_html_e( 'Show All', 'impress_agents' ); ?></option>
				</select>
		</p>

		<hr>
		<p><?php esc_html_e( 'If Show Random selected: ', 'impress_agents' ); ?></p>

			<p>
				<label for="<?php echo $this->get_field_id( 'show_number' ); ?>"><?php esc_html_e( 'Max number of agents to show:' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'show_number' ); ?>" name="<?php echo $this->get_field_name( 'show_number' ); ?>" type="text" value="<?php echo $instance['show_number']; ?>" size="3" maxlength="2" />
			</p>

		<hr>
		<p><?php esc_html_e( 'If Show All selected: ', 'impress_agents' );?></p>
			<p>
				<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php esc_html_e( 'Order By', 'impress_agents' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
					<option value="date" <?php selected( 'date', $instance['orderby'] ); ?>><?php esc_html_e( 'Date', 'impress_agents' ); ?></option>
					<option value="title" <?php selected( 'title', $instance['orderby'] ); ?>><?php esc_html_e( 'Title', 'impress_agents' ); ?></option>
					<option value="menu_order" <?php selected( 'menu_order', $instance['orderby'] ); ?>><?php esc_html_e( 'Menu Order', 'impress_agents' ); ?></option>
					<option value="ID" <?php selected( 'ID', $instance['orderby'] ); ?>><?php esc_html_e( 'ID', 'impress_agents' ); ?></option>
					<option value="rand" <?php selected( 'rand', $instance['orderby'] ); ?>><?php esc_html_e( 'Random', 'impress_agents' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php esc_html_e( 'Sort Order', 'impress_agents' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
					<option value="DESC" <?php selected( 'DESC', $instance['order'] ); ?>><?php esc_html_e( 'Descending (3, 2, 1)', 'impress_agents' ); ?></option>
					<option value="ASC" <?php selected( 'ASC', $instance['order'] ); ?>><?php esc_html_e( 'Ascending (1, 2, 3)', 'impress_agents' ); ?></option>
				</select>
			</p>
		<?php
	}

}

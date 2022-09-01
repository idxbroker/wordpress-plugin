<?php
/**
 * This widget creates a search form which uses listings' taxonomy for search fields.
 *
 * @package WP Listings
 * @since 0.1.0
 */
class WP_Listings_Search_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'listings-search wp-listings-search', 'description' => __( 'Display listings search dropdown', 'wp-listings' ), 'customize_selective_refresh' => true );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'listings-search' );
		parent::__construct( 'listings-search', __( 'IMPress Listings - Search', 'wp-listings' ), $widget_ops, $control_ops );
		add_shortcode( 'impress-listings-search', [ $this, 'search_button_shortcode' ] );
	}

	public function search_button_shortcode( $atts ) {
		extract(
			shortcode_atts(
				[
					'title'       => '',
					'button_text' => __( 'Search Listings', 'wp-listings' ),
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

		$instance = wp_parse_args( (array) $instance, array(
			'title'			=> '',
			'button_text'	=> __( 'Search Listings', 'wp-listings' ),
		) );

		global $_wp_listings_taxonomies;

		$listings_taxonomies = $_wp_listings_taxonomies->get_taxonomies();

		extract( $args );

		echo $before_widget;

		if ( $instance['title'] ) echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

		echo '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/' ) ) . '" ><input type="hidden" value="" name="s" /><input type="hidden" value="listing" name="post_type" />';

		foreach ( $listings_taxonomies as $tax => $data ) {
			if ( ! isset( $instance[ $tax ] ) || ! $instance[ $tax ] ) {
				continue;
			}

			$terms = get_terms( $tax, array( 'orderby' => 'title', 'number' => 100, 'hierarchical' => false ) );
			if ( empty( $terms ) ) {
				continue;
			}

			$current = ! empty( $wp_query->query_vars[ $tax ] ) ? $wp_query->query_vars[ $tax ] : '';
			echo '<select name="' . esc_attr( $tax ) . '" id="' . esc_attr( $tax ) . '" class="wp-listings-taxonomy">';
			echo '<option value="" ' . selected( '' === $current, true, false ) . '>' . esc_html( $data['labels']['name'] ) . '</option>';
			foreach ( (array) $terms as $term ) {
				echo '<option value=' . esc_attr( $term->slug ) . ' ' . selected( $current, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
			}
			echo '</select>';
		}

		echo '<div class="btn-search"><button type="submit" class="searchsubmit"><i class="fas fa-search"></i><span class="button-text">' . esc_attr( $instance['button_text'] ) . '</span></button></div>';
		echo '<div class="clear"></div>
		</form>';

		echo $after_widget;

	}

	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	public function form( $instance ) {

		$instance = wp_parse_args(
			(array) $instance,
			[
				'title'       => '',
				'button_text' => __( 'Search Listings', 'wp-listings' ),
			]
		);

		global $_wp_listings_taxonomies;

		$listings_taxonomies = $_wp_listings_taxonomies->get_taxonomies();
		$new_widget = empty( $instance );

		printf( '<p><label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" style="%s" /></p>', esc_attr( $this->get_field_id( 'title' ) ), esc_html__( 'Title:', 'wp-listings' ), esc_attr( $this->get_field_id( 'title' ) ), esc_attr( $this->get_field_name( 'title' ) ), esc_attr( $instance['title'] ), 'width: 95%;' );
		?>
		<h5><?php esc_html_e( 'Include these taxonomies in the search widget', 'wp-listings' ); ?></h5>
		<?php
		foreach ( (array) $listings_taxonomies as $tax => $data ) {
			$terms = get_terms( $tax );
			if ( empty( $terms ) ) {
				continue;
			}

			$checked = isset( $instance[ $tax ] ) && $instance[ $tax ];

			printf( '<p><label><input id="%s" type="checkbox" name="%s" value="1" %s />%s</label></p>', esc_attr( $this->get_field_id( 'tax' ) ), esc_attr( $this->get_field_name( $tax ) ), checked( 1, $checked, 0 ), esc_html( $data['labels']['name'] ) );

		}

		printf( '<p><label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" style="%s" /></p>', esc_attr( $this->get_field_id( 'button_text' ) ), esc_html__( 'Button Text:', 'wp-listings' ), esc_attr( $this->get_field_id( 'button_text' ) ), esc_attr( $this->get_field_name( 'button_text' ) ), esc_attr( $instance['button_text'] ), 'width: 95%;' );
	}
}

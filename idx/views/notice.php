<?php

namespace IDX\Views;

use IDX;

// Outputs notice box for the admin_notice action
class Notice {
	// We don't want anyone instantiating this class
	private function __construct() {}

	// Outputs admin alert box
	public static function create_notice( $name, $message, $type, $url, $link_text ) {
		?>
		<div class="notice idx-notice is-dismissible notice-<?php echo esc_attr( $type ); ?>" data-name="<?php echo esc_attr( $name ); ?>">
			<p>
				<?php
				echo esc_html( $message );
				if ( '' !== $url && null !== $url ) {
					?>
					<a href="<?php echo esc_attr( $url ); ?>"><?php echo esc_html( $link_text ); ?></a>
					<?php
				}
				?>
			</p>
		</div>
		<?php
	}

	// Returns menu text with conditional alert <span>
	public static function menu_text_notice( $menu_text, $number ) {
		ob_start();

		echo esc_html( $menu_text );

		// If no notifications, don't show a notification icon
		if ( 0 === $number ) {
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}

		?>

		<span id="idx-menu-notice-item">
			<?php echo esc_html( $number ); ?>
		</span>

		<?php

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}

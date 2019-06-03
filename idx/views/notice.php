<?php

namespace IDX\Views;

use IDX;

/**
 * Outputs notice box for the admin_notice action
 *
 * @since 2.5.10
 */
class Notice {
	/**
	 * Begin __construct function.
	 * We don't want anyone instantiating this class.
	 *
	 * @since 2.5.10
	 */
	private function __construct() {}

	/**
	 * Begin create_notice function.
	 * Outputs admin alert box.
	 *
	 * @since 2.5.10
	 * @param text $name contains the name of the alert.
	 * @param text $message contains the message of the alert.
	 * @param text $type contains the type of alert.
	 * @param text $url contains the url for the alert.
	 * @param text $link_text contains the text for the link in the alert.
	 */
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

	/**
	 * Begin menu_text_notice function.
	 *
	 * @since 2.5.10
	 * @param text $menu_text contains the text for the menu.
	 * @param int  $number contains the number for the alert.
	 * @return html Return menu text with conditional alert <span>
	 */
	public static function menu_text_notice( $menu_text, $number ) {
		ob_start();

		echo esc_html( $menu_text );

		// If no notifications, don't show a notification icon.
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

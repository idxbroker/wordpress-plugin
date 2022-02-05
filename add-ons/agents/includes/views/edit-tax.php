<?php
$options = get_option( $this->settings_field );

if ( array_key_exists( $_REQUEST['id'], (array) $options ) ) {
	$taxonomy = stripslashes_deep( $options[ $_REQUEST['id'] ] );
} else {
	wp_die( esc_html__( "Nice try, partner. But that taxonomy doesn't exist or can't be edited. Click back and try again.", 'impress_agents' ) );
}
?>

<h2><?php esc_html_e( 'Edit Taxonomy', 'impress_agents' ); ?></h2>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;action=edit' ) ); ?>">
<?php wp_nonce_field( 'impress_agents-action_edit-taxonomy' ); ?>
<table class="form-table">

	<tr class="form-field">
		<th scope="row" valign="top"><label for="impress_agents_taxonomy[id]"><?php esc_html_e( 'ID', 'impress_agents' ); ?></label></th>
		<td>
		<input type="text" value="<?php echo esc_html( $_REQUEST['id'] ); ?>" size="40" disabled="disabled" />
		<input name="impress_agents_taxonomy[id]" id="impress_agents_taxonomy[id]" type="hidden" value="<?php echo esc_html( $_REQUEST['id'] ); ?>" size="40" />
		<p class="description"><?php esc_html_e( 'The unique ID used to register a taxonomy cannot be changed.', 'impress_agents' ); ?></p></td>
	</tr>

	<tr class="form-field">
		<th scope="row" valign="top"><label for="impress_agents_taxonomy[name]"><?php esc_html_e( 'Plural Name', 'impress_agents' ); ?></label></th>
		<td><input name="impress_agents_taxonomy[name]" id="impress_agents_taxonomy[name]" type="text" value="<?php echo esc_html( $taxonomy['labels']['name'] ); ?>" size="40" />
		<p class="description"><?php esc_html_e( 'Example: "Job Types" or "Offices"', 'impress_agents' ); ?></p></td>
	</tr>

	<tr class="form-field">
		<th scope="row" valign="top"><label for="impress_agents_taxonomy[singular_name]"><?php esc_html_e( 'Singular Name', 'impress_agents' ); ?></label></th>
		<td><input name="impress_agents_taxonomy[singular_name]" id="impress_agents_taxonomy[singular_name]" type="text" value="<?php echo esc_html( $taxonomy['labels']['singular_name'] ); ?>" size="40" />
		<p class="description"><?php esc_html_e( 'Example: "Job Type" or "Office"', 'impress_agents' ); ?></p></td>
	</tr>

</table>

<?php submit_button( __( 'Update', 'impress_agents' ) ); ?>

</form>

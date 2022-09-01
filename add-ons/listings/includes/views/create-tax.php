<h2><?php esc_html_e( 'Listing Taxonomies', 'wp-listings' ); ?></h2>

<div id="col-container">

	<div id="col-right">
	<div class="col-wrap">

		<h3><?php esc_html_e( 'Current Listing Taxonomies', 'wp-listings' ); ?></h3>
		<table class="widefat tag fixed" cellspacing="0">
			<thead>
			<tr>
			<th scope="col" class="manage-column column-slug"><?php esc_html_e( 'ID', 'wp-listings' ); ?></th>
			<th scope="col" class="manage-column column-singular-name"><?php esc_html_e( 'Singular Name', 'wp-listings' ); ?></th>
			<th scope="col" class="manage-column column-plural-name"><?php esc_html_e( 'Plural Name', 'wp-listings' ); ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
			<th scope="col" class="manage-column column-slug"><?php esc_html_e( 'ID', 'wp-listings' ); ?></th>
			<th scope="col" class="manage-column column-singular-name"><?php esc_html_e( 'Singular Name', 'wp-listings' ); ?></th>
			<th scope="col" class="manage-column column-plural-name"><?php esc_html_e( 'Plural Name', 'wp-listings' ); ?></th>
			</tr>
			</tfoot>

			<tbody id="the-list" class="list:tag">

				<?php
				$alt = true;

				$listing_taxonomies = array_merge( $this->property_features_taxonomy(), $this->listing_status_taxonomy(), $this->property_type_taxonomy(), $this->listing_location_taxonomy(), get_option( $this->settings_field ) );

				foreach ( (array) $listing_taxonomies as $id => $data ) :
					?>

				<tr
					<?php
					if ( $alt ) {
						echo 'class="alternate"';
						$alt = false;
					} else {
						$alt = true;
					}
					?>
				>
					<td class="slug column-slug">

					<?php if ( isset( $data['editable'] ) && 0 === $data['editable'] ) : ?>
						<?php echo '<strong>' . esc_html( $id ) . '</strong><br /><br />'; ?>
					<?php else : ?>
						<?php printf( '<a class="row-title" href="%s" title="Edit %s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;view=edit&amp;id=' . $id ) ), esc_attr( $id ), esc_html( $id ) ); ?>

						<br />

						<div class="row-actions">
							<span class="edit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;view=edit&amp;id=' . $id ) ); ?>"><?php esc_html_e( 'Edit', 'wp-listings' ); ?></a> | </span>
							<span class="delete"><a class="delete-tag" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;action=delete&amp;id=' . esc_html( $id ) ), 'wp_listings-action_delete-taxonomy' ) ); ?>"><?php esc_html_e( 'Delete', 'wp-listings' ); ?></a></span>
						</div>
					<?php endif; ?>

					</td>
					<td class="singular-name column-singular-name"><?php echo esc_html( $data['labels']['singular_name'] ); ?></td>
					<td class="plural-name column-plural-name"><?php echo esc_html( $data['labels']['name'] ); ?></td>
				</tr>

				<?php endforeach; ?>

			</tbody>
		</table>

	</div>
	</div><!-- /col-right -->

	<div id="col-left">
	<div class="col-wrap">

		<div class="form-wrap">
			<h3><?php esc_html_e( 'Add New Listing Taxonomy', 'wp-listings' ); ?></h3>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=register-taxonomies&amp;action=create' ) ); ?>">
			<?php wp_nonce_field( 'wp_listings-action_create-taxonomy' ); ?>

			<div class="form-field">
				<label for="taxonomy-id"><?php esc_html_e( 'ID', 'wp-listings' ); ?></label>
				<input name="wp_listings_taxonomy[id]" id="taxonomy-id" type="text" value="" size="40" maxlength="32" onkeypress="return /[a-z\-]/i.test(event.key)"/>
				<p><?php esc_html_e( 'A unique ID used to register the taxonomy (letters and dashes only, 32 character limit)', 'wp-listings' ); ?></p>
			</div>

			<div class="form-field form-required">
				<label for="taxonomy-name"><?php esc_html_e( 'Plural Name', 'wp-listings' ); ?></label>
				<input name="wp_listings_taxonomy[name]" id="taxonomy-name" type="text" value="" size="40" />
				<p><?php esc_html_e( 'Example: "Property Types" or "Locations"', 'wp-listings' ); ?></p>
			</div>

			<div class="form-field form-required">
				<label for="taxonomy-singular-name"><?php esc_html_e( 'Singular Name', 'wp-listings' ); ?></label>
				<input name="wp_listings_taxonomy[singular_name]" id="taxonomy-singular-name" type="text" value="" size="40" />
				<p><?php esc_html_e( 'Example: "Property Type" or "Location"', 'wp-listings' ); ?></p>
			</div>

			<?php submit_button( __( 'Add New Taxonomy', 'wp-listings' ), 'secondary' ); ?>
			</form>
		</div>

	</div>
	</div><!-- /col-left -->

</div><!-- /col-container -->

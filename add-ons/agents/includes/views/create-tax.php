<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<h2><?php esc_html_e( 'Employee Taxonomies', 'impress_agents' ); ?></h2>

<div id="col-container">

	<div id="col-right">
	<div class="col-wrap">

		<h3><?php esc_html_e( 'Current Employee Taxonomies', 'impress_agents' ); ?></h3>
		<table class="widefat tag fixed" cellspacing="0">
			<thead>
			<tr>
			<th scope="col" class="manage-column column-slug"><?php esc_html_e( 'ID', 'impress_agents' ); ?></th>
			<th scope="col" class="manage-column column-singular-name"><?php esc_html_e( 'Singular Name', 'impress_agents' ); ?></th>
			<th scope="col" class="manage-column column-plural-name"><?php esc_html_e( 'Plural Name', 'impress_agents' ); ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
			<th scope="col" class="manage-column column-slug"><?php esc_html_e( 'ID', 'impress_agents' ); ?></th>
			<th scope="col" class="manage-column column-singular-name"><?php esc_html_e( 'Singular Name', 'impress_agents' ); ?></th>
			<th scope="col" class="manage-column column-plural-name"><?php esc_html_e( 'Plural Name', 'impress_agents' ); ?></th>
			</tr>
			</tfoot>

			<tbody id="the-list" class="list:tag">

				<?php
				$alt = true;

				$employee_taxonomies = array_merge( $this->employee_job_type_taxonomy(), $this->employee_offices_taxonomy(), get_option( $this->settings_field ) );

				foreach ( (array) $employee_taxonomies as $id => $data ) :
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
						<?php printf( '<a class="row-title" href="%s" title="Edit %s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;view=edit&amp;id=' . $id ) ), esc_html( $id ), esc_html( $id ) ); ?>

						<br />

						<div class="row-actions">
							<span class="edit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;view=edit&amp;id=' . $id ) ); ?>"><?php esc_html_e( 'Edit', 'impress_agents' ); ?></a> | </span>
							<span class="delete"><a class="delete-tag" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;action=delete&amp;id=' . $id ) ), 'impress_agents-action_delete-taxonomy' ); ?>"><?php esc_html_e( 'Delete', 'impress_agents' ); ?></a></span>
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
			<h3><?php esc_html_e( 'Add New Employee Taxonomy', 'impress_agents' ); ?></h3>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=impress-agents-taxonomies&amp;action=create' ) ); ?>">
			<?php wp_nonce_field( 'impress_agents-action_create-taxonomy' ); ?>

			<div class="form-field">
				<label for="taxonomy-id"><?php esc_html_e( 'ID', 'impress_agents' ); ?></label>
				<input name="impress_agents_taxonomy[id]" id="taxonomy-id" type="text" value="" size="40" maxlength="32" onkeypress="return /[a-z\-]/i.test(event.key)" />
				<p><?php esc_html_e( 'A unique ID used to register the taxonomy (letters and dashes only, 32 character limit)', 'impress_agents' ); ?></p>
			</div>

			<div class="form-field form-required">
				<label for="taxonomy-name"><?php esc_html_e( 'Plural Name', 'impress_agents' ); ?></label>
				<input name="impress_agents_taxonomy[name]" id="taxonomy-name" type="text" value="" size="40" />
				<p><?php esc_html_e( 'Example: "Job Types" or "Offices"', 'impress_agents' ); ?></p>
			</div>

			<div class="form-field form-required">
				<label for="taxonomy-singular-name"><?php esc_html_e( 'Singular Name', 'impress_agents' ); ?></label>
				<input name="impress_agents_taxonomy[singular_name]" id="taxonomy-singular-name" type="text" value="" size="40" />
				<p><?php esc_html_e( 'Example: "Job Type" or "Office"', 'impress_agents' ); ?></p>
			</div>

			<?php submit_button( __( 'Add New Taxonomy', 'impress_agents' ), 'secondary' ); ?>
			</form>
		</div>

	</div>
	</div><!-- /col-left -->

</div><!-- /col-container -->

( function( blocks, element ) {
	var el = wp.element.createElement
	var ServerSideRender = wp.components.ServerSideRender
	var registerBlockType = wp.blocks.registerBlockType

	function Stars( { stars } ) {
		return el( 'div', { key: 'stars' },
			'★'.repeat( stars ),
			( ( stars * 2 ) % 2 ) ? '½' : '' );
	}

	blocks.registerBlockType( 'idx-broker-platinum/impress-lead-signup-block', {
		title: 'IMPress Lead Signup',
		icon: 'welcome-write-blog',
		category: 'widgets',

		attributes: {
			phone: {
				type: 'int'
			},
			styles: {
				type: 'int'
			},
			new_window: {
				type: 'int'
			},
			agent_id: {
				type: 'string'
			},
			password_field: {
				type: 'bool'
			},
			button_text: {
				type: 'string'
			}
		},

		edit: function( props ) {
			return el( ServerSideRender, {
				block: 'idx-broker-platinum/impress-lead-signup-block',
				attributes: props.attributes,
			} );
		},

		save: function() {
			// We don't want to save any HTML in post_content, as the value will be in postmeta
			return null;
		}
	} );
} )(
	window.wp.blocks,
	window.wp.element
);
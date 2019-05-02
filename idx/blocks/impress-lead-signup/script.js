( function( blocks, element ) {
	var el = wp.element.createElement
	var ServerSideRender = wp.components.ServerSideRender
	var InspectorControls = wp.editor.InspectorControls
	var TextControl = wp.components.TextControl
	var Checkbox = wp.components.Checkbox
	var registerBlockType = wp.blocks.registerBlockType

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
			return [
				el( ServerSideRender, {
					block: 'idx-broker-platinum/impress-lead-signup-block',
					attributes: props.attributes,
				} ),

				el( InspectorControls, {},
					el( TextControl, {
						label: 'Sign up button text:',
						value: props.attributes.button_text,
						onChange: ( value ) => { props.setAttributes( { button_text: value } ); },
					} )
				),

				// el( InspectorControls, {},
				// 	el( RadioControl, {
				// 		label: 'Foo1',
				// 		value: props.attributes.foo1,
				// 		onChange: ( value ) => { props.setAttributes( { foo1: value } ); },
				// 	} )
				// )
			]
		},

		/**
		 * 
		 * RadioControl
		label="User type"
		help="The type of the current user"
		selected={ option }
		options={ [
			{ label: 'Author', value: 'a' },
			{ label: 'Editor', value: 'e' },
		] }
		onChange={ ( option ) => { setState( { option } ) } }
		 */

		save: function() {
			// We don't want to save any HTML in post_content, as the value will be in postmeta
			return null;
		}
	} );
} )(
	window.wp.blocks,
	window.wp.element
);
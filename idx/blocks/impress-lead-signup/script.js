( function( blocks, element ) {
	var el = wp.element.createElement
	var ServerSideRender = wp.components.ServerSideRender
	var InspectorControls = wp.editor.InspectorControls
	var TextControl = wp.components.TextControl
	var Checkbox = wp.components.CheckboxControl
	var SelectControl = wp.components.SelectControl
	var registerBlockType = wp.blocks.registerBlockType
	var icon = el('i', {class: "fa fa-user-plus fa-2x"}, null )  

	blocks.registerBlockType( 'idx-broker-platinum/impress-lead-signup-block', {
		title: 'IMPress Lead Signup',
		icon: icon,
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
				type: 'string',
				default: 'Sign Up!'
			}
		},

		edit: function( props ) {
			return [
				el( ServerSideRender, {
					block: 'idx-broker-platinum/impress-lead-signup-block',
					attributes: props.attributes,
				} ),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Show phone number field?',
						value: props.attributes.phone,
						checked: (props.attributes.phone > 0 ? true : false),
						onChange: ( value ) => { props.setAttributes( { phone: (value > 0 ? 1 : 0 ) } ); },
					} )
				),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Default Styles?',
						value: props.attributes.styles,
						checked: (props.attributes.styles > 0 ? true : false) ,
						onChange: ( value ) => { props.setAttributes( { styles: (value > 0 ? 1 : 0 ) } ); },
					} )
				),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Open in a New Window?',
						value: props.attributes.new_window,
						checked: (props.attributes.new_window > 0 ? true : false),
						onChange: ( value ) => { props.setAttributes( { new_window: (value > 0 ? 1 : 0 ) } ); },
					} )
				),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Add password form field?',
						value: props.attributes.password_field,
						checked: (props.attributes.password_field ? true : false),
						onChange: ( value ) => { props.setAttributes( { password_field: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( TextControl, {
						label: 'Sign up button text:',
						value: props.attributes.button_text,
						onChange: ( value ) => { props.setAttributes( { button_text: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Route to Agent:',
						value: props.attributes.agent_id,
						options: lead_signup_agent_list.agents_list,
						onChange: ( value ) => { props.setAttributes( { agent_id: value } ); },
					} )
				),

			]
		},

		save: function(props) {
			return el(
				null,
				null,
				props.attributes.content
			);
		}

	} );
} )(
	window.wp.blocks,
	window.wp.element
);
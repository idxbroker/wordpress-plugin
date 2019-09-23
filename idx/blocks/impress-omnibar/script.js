( function( blocks, element ) {
	var el = wp.element.createElement
	var registerBlockType = wp.blocks.registerBlockType
	var ServerSideRender = wp.components.ServerSideRender
	var InspectorControls = wp.editor.InspectorControls
	var Checkbox = wp.components.CheckboxControl
	var icon = el('i', {class: "fa fa-search fa-2x"}, null )  

	function setCategory() {
		if (window.location.href.includes('wp-admin')) {
			return 'idx-category'
		} else {
			return 'widgets'
		}
	}

	blocks.registerBlockType( 'idx-broker-platinum/impress-omnibar-block', {
		title: 'IMPress Omnibar Search',
		icon: icon,
		category: setCategory(),

		attributes: {
			styles: {
				type: 'int',
				default: 1,
			},
			extra: {
				type: 'int',
				default: 0,
			},
			min_price: {
				type: 'int',
				default: 0,
			},
		},

		edit: function( props ) {
			return [
				// el( ServerSideRender, {
				// 	block: 'idx-broker-platinum/impress-omnibar-block',
				// 	attributes: props.attributes,
				// } ),
				el( "div", { 
					class: 'idx-block-placeholder-container',
				 }, el("img", {
					src: impress_omnibar_image_url
				})),
				
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
						label: 'Extra Fields?',
						value: props.attributes.extra,
						checked: (props.attributes.extra > 0 ? true : false),
						onChange: ( value ) => { props.setAttributes( { extra: (value > 0 ? 1 : 0 ) } ); },
					} )
				),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Include Min Price? (If Extra Fields is enabled)',
						value: props.attributes.min_price,
						checked: (props.attributes.min_price > 0 ? true : false),
						onChange: ( value ) => { props.setAttributes( { min_price: (value > 0 ? 1 : 0 ) } ); },
					} )
				),
			]
		},

		save: function(props) {
			return null
		}

	} );
} )(
	window.wp.blocks,
	window.wp.element
);


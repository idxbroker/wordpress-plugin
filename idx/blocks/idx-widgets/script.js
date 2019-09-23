( function( blocks, element ) {
	var el = wp.element.createElement
	var registerBlockType = wp.blocks.registerBlockType
	var InspectorControls = wp.editor.InspectorControls
	var SelectControl = wp.components.SelectControl
	var icon = el('i', {class: "fa fa-cog fa-2x"}, null )  

	function setCategory() {
		if (window.location.href.includes('wp-admin')) {
			return 'idx-category'
		} else {
			return 'widgets'
		}
	}

	blocks.registerBlockType( 'idx-broker-platinum/idx-widgets-block', {
		title: 'IDX Broker Widgets',
		icon: icon,
		category: setCategory(),

		attributes: {
			id: {
				type: 'string',
				default: null,
			},
		},

		edit: function( props ) {
			return [
				el( "div", {
					class: 'idx-block-placeholder-container',
				}, el("img", {
					src: idx_widget_block_image_url
				}), el("div", null, "")),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Select a Widget:',
						value: props.attributes.id,
						options: ( idx_widgets_list ? idx_widgets_list : [ { label: 'All', value: '' } ] ),
						onChange: ( value ) => { props.setAttributes( { id: value } ); },
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


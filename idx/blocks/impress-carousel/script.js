( function( blocks, element ) {
	var el = wp.element.createElement
	var registerBlockType = wp.blocks.registerBlockType
	var InspectorControls = wp.editor.InspectorControls
	var TextControl = wp.components.TextControl
	var Checkbox = wp.components.CheckboxControl
	var SelectControl = wp.components.SelectControl
 
	function setCategory() {
		if (window.location.href.includes('wp-admin')) {
			return 'idx-category'
		} else {
			return 'widgets'
		}
	}

	blocks.registerBlockType( 'idx-broker-platinum/impress-carousel-block', {
		title: 'IMPress Carousel',
		icon: 'admin-multisite',
		category: setCategory(),

		attributes: {
			max: {
				type: 'int',
				default: 15
			},
			display: {
				type: 'int',
				default: 3
			},
			autoplay: {
				type: 'int',
				default: 1
			},
			order: {
				type: 'string',
				default: 'default'
			},
			property_type: {
				type: 'string',
				default: 'featured'
			},
			styles: {
				type: 'int',
				default: 1,
			},
			new_window: {
				type: 'int',
				default: 0,
			},
			saved_link_id: {
				type: 'string',
				default: ''
			},
			agent_id: {
				type: 'string',
				default: ''
			}
		},

		edit: function( props ) {

			const propertiesToFeature = [{label: 'Featured', value: 'featured'}, {label: 'Sold/Pending', value: 'soldpending'}, {label: 'Supplemental', value: 'supplemental'}, {label: 'Use Saved Link', value: 'savedlinks'}];
			const sortOptions = [{label: 'Default', value: 'default'}, {label: 'Highest to Lowest Price', value: 'high-low'}, {label: 'Lowest to Highest Price', value: 'low-high'}];
		
			return [
				el( "div", { 
					class: 'idx-block-placeholder-container',
				 }, el("img", {
					src: impress_carousel_image_url
				}), el("div", null, "")),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Properties to Display:',
						value: props.attributes.property_type,
						options: propertiesToFeature,
						onChange: ( value ) => { props.setAttributes( { property_type: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Choose a saved link (if selected above):',
						value: props.attributes.saved_link_id,
						options: ( impress_carousel_saved_links ? impress_carousel_saved_links : [ { label: 'All', value: '' } ] ),
						onChange: ( value ) => { props.setAttributes( { saved_link_id: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Limit by Agent:',
						value: props.attributes.agent_id,
						options: ( impress_carousel_agent_list ? impress_carousel_agent_list: [ { label: 'All', value: '' } ] ),
						onChange: ( value ) => { props.setAttributes( { agent_id: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( TextControl, {
						label: 'Listings to show without scrolling:',
						value: props.attributes.display,
						type: 'number',
						onChange: ( value ) => { props.setAttributes( { display: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( TextControl, {
						label: 'Max number of listings to show:',
						value: props.attributes.max,
						type: 'number',
						onChange: ( value ) => { props.setAttributes( { max: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Sort Order:',
						value: props.attributes.order,
						options: sortOptions,
						onChange: ( value ) => { props.setAttributes( { order: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Autoplay?',
						value: props.attributes.autoplay,
						checked: (props.attributes.autoplay > 0 ? true : false),
						onChange: ( value ) => { props.setAttributes( { autoplay: (value > 0 ? 1 : 0 ) } ); },
					} )
				),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Open Listings in a New Window?',
						value: props.attributes.new_window,
						checked: (props.attributes.new_window > 0 ? true : false),
						onChange: ( value ) => { props.setAttributes( { new_window: (value > 0 ? 1 : 0 ) } ); },
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
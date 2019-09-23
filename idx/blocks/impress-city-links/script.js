( function( blocks, element ) {
	var el = wp.element.createElement
	var registerBlockType = wp.blocks.registerBlockType
	var ServerSideRender = wp.components.ServerSideRender
	var InspectorControls = wp.editor.InspectorControls
	var TextControl = wp.components.TextControl
	var Checkbox = wp.components.CheckboxControl
	var SelectControl = wp.components.SelectControl
	var icon = el('i', {class: "fa fa-link fa-2x"}, null )  

	function setCategory() {
		if (window.location.href.includes('wp-admin')) {
			return 'idx-category'
		} else {
			return 'widgets'
		}
	}

	blocks.registerBlockType( 'idx-broker-platinum/impress-city-links-block', {
		title: 'IMPress City Links',
		icon: icon,
		category: setCategory(),

		attributes: {
			mls: {
				type: 'string',
				default: 'a000',
			},
			city_list: {
				type: 'string',
				default: 'combinedActiveMLS',
			},
			use_columns: {
				type: 'int',
				default: 1,
			},
			number_columns: {
				type: 'int',
				default: 4,
			},
			styles: {
				type: 'int',
				default: 1,
			},
			show_count: {
				type: 'int',
				default: 0,
			},
			new_window: {
				type: 'int',
				default: 0,
			},
		},

		edit: function( props ) {
			const columnCountOptions = [{label: '2', value: '2'}, {label: '3', value: '3'}, {label: '4', value: '4'}];

			return [
				el( ServerSideRender, {
					block: 'idx-broker-platinum/impress-city-links-block',
					attributes: props.attributes,
				} ),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'MLS to use for city links:',
						value: props.attributes.mls,
						options: ( impress_city_links_mls_options ? impress_city_links_mls_options: [ { label: '-', value: '' } ] ),
						onChange: ( value ) => { props.setAttributes( { mls: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Select a city list:',
						value: props.attributes.city_list,
						options: ( impress_city_links_city_options ? impress_city_links_city_options: [ { label: '-', value: '' } ] ),
						onChange: ( value ) => { props.setAttributes( { city_list: value } ); },
					} )
				),

				el( InspectorControls, {},
					el( Checkbox, {
						label: 'Split links into columns?',
						value: props.attributes.use_columns,
						checked: (props.attributes.use_columns > 0 ? true : false),
						onChange: ( value ) => { props.setAttributes( { use_columns: value } ); },
					} )
				),
				
				el( InspectorControls, {},
					el( SelectControl, {
						label: 'Number of columns:',
						value: props.attributes.number_columns,
						options: columnCountOptions,
						onChange: ( value ) => { props.setAttributes( { number_columns: value } ); },
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
						label: 'Show Number of Listings for each city?',
						value: props.attributes.show_count,
						checked: (props.attributes.show_count > 0 ? true : false) ,
						onChange: ( value ) => { props.setAttributes( { show_count: (value > 0 ? 1 : 0 ) } ); },
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
					el("p", null, "Don't have any city lists? Go create some in your ", el("a", {
						href: "http://middleware.idxbroker.com/mgmt/citycountyziplists.php",
						target: "_blank"
					}, "IDX dashboard.")),
				)

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

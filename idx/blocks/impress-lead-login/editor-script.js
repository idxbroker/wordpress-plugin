( function( blocks, element ) {
	var el = element.createElement;

	function Stars( { stars } ) {
		return el( 'div', { key: 'stars' },
			'★'.repeat( stars ),
			( ( stars * 2 ) % 2 ) ? '½' : '' );
	}

	blocks.registerBlockType( 'idx-broker-platinum/impress-lead-login', {
		title: 'IMPress Lead Login',
		icon: 'admin-network',
		category: 'widgets',

		attributes: {
			stars: {
				type: 'int',
				meta: 'stars', // Store the value in postmeta
			}
		},

		edit: function( props ) {
			var stars = props.attributes.stars,
				children = [];

			function setStars( event ) {
				props.setAttributes( { stars: event.target.value } );
				event.preventDefault();
			}

			if ( stars ) {
				children.push( Stars( { stars: stars } ) );
			}
		
			children.push(
				el( 'h5', null, 'IMPress Lead Login' )
			);

			children.push(
				el( 'input', {
					key: 'stars-input',
					type: 'number',
					min: 0,
					max: 5,
					step: 0.5,
					value: stars,
					onChange: setStars } )
			);

			return el( 'div', { onSubmit: setStars }, children );
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



// wp.blocks.registerBlockType('idx-broker-platinum/impress-lead-login', {
// 	title: 'Impress Lead Login',
// 	icon: 'megaphone',
// 	category: "common",
// 	attributes: {
// 		content: {
// 			type: 'array',
// 			source: 'children',
// 			selector: 'p',
// 		},
// 	},
// 	edit: function() {
// 		var el = element.createElement;
// 		return el(
// 				'p',
// 				{ style: blockStyle },
// 				'Hello World, step 1 (from the editor).'
// 		);
// },
// save: function() {
// 	var el = element.createElement;
// 		return el(
// 				'p',
// 				{ style: blockStyle },
// 				'Hello World, step 1 (from the frontend).'
// 		);
// },

	// edit: function(props) {
	// 	return wp.element.createElement(wp.blocks.RichText, {
	// 		tagName: 'p',
	// 		className: props.className,
	// 		value: props.attributes.content,
	// 		onChange: function(newContent) {
	// 			props.setAttributes({content:newContent});
	// 		}
	// 	});
	// },

	// save: function(props) {
	// 	return wp.element.createElement('p', {
	// 		className: props.className,
// 	// 	}, props.attributes.content);
// 	// }
// });
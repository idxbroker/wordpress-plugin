jQuery(document).ready(function($) {

	var select = $('select.city-list-options');

	$(select).change(function() {
		var count = $(this).find(':selected').data('count');
		if ( count > 50 ) {
			$('p.show-count').hide();
			$('p.show-count input.checkbox').val(0);
			
		} else {
			$('p.show-count').show();
		}
			
	});
});

jQuery(document).ready(function() {
	jQuery( ".navigation a" ).smoothScroll();
	jQuery( ".iframe-wrap" ).fitVids();
});

jQuery(function( $ ){
	$('body').scrollspy({ target: '#listing-sidebar' })

	$(window).scroll(function () {
		if ($(document).scrollTop() > 1 ) {
			$('#listing-sidebar').addClass('expand');
		} else {
			$('#listing-sidebar').removeClass('expand');
		}
	});

});

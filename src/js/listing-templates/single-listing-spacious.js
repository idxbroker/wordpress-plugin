jQuery(document).ready(function() {
	jQuery( ".navigation a" ).smoothScroll();
	jQuery( ".iframe-wrap" ).fitVids();
});

jQuery(function( $ ){

	$(window).scroll(function () {
	  if ($(document).scrollTop() > 100 ) {
		$('.scroll').addClass('scroll-hide');
	  } else {
		$('.scroll').removeClass('scroll-hide');
	  }
	});
	
});

jQuery(function( $ ){
	$(window).scroll(function () {
		console.log('scrolling')
	  if ($(document).scrollTop() > 1 ) {
		$('.entry-title').addClass('shrink');
	  } else {
		$('.entry-title').removeClass('shrink');
	  }
	});
});

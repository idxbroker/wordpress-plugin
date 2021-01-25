<?php
/*
Single Listing Template: Solid
Description: left sidebar, icons
Version: 1.3.3

Changelog:

1.3.3 - Fix display issues in First Impression theme
1.3.2 - Fix issue with Google map not displaying
1.3.1 - Fix issue with contact form not displaying
1.3 - Updated with IMPress Agents support, currency support, and global disclaimer
1.2 - Update with new fields, auto-map, and more
1.1.7 - Update for Equity framework themes
1.1.6 - Remove top header on Curb Appeal Evolved theme
1.1.5 - Update call for font awesome icons
1.1.4 - Add additional classes for older Genesis themes
1.1.3 - Fix to remove post info on non-HTML5 themes
1.1.2 - Update to add priority to author box removal
1.1.1 - Update for conditional connected agents function call
1.1 - Update for error in XHTML/HTML5 hook
1.0 - Initial release

*/
add_filter( 'body_class', 'single_listing_class' );
function single_listing_class( $classes ) {
	$classes[] = 'listing-template-custom';

	return $classes;
}

add_action('wp_enqueue_scripts', 'enqueue_single_listing_scripts');
function enqueue_single_listing_scripts() {
	wp_register_style( 'wplistings-google-fonts', '//fonts.googleapis.com/css?family=Source+Sans+Pro:300,700|Rokkitt:400');
	wp_enqueue_style( 'font-awesome-5.8.2' );
	wp_enqueue_style( 'wplistings-google-fonts' );
	wp_register_script( 'fitvids', '//cdnjs.cloudflare.com/ajax/libs/fitvids/1.1.0/jquery.fitvids.js', array('jquery'), true, true );
	wp_register_script( 'smoothscroll', '//cdnjs.cloudflare.com/ajax/libs/jquery-smooth-scroll/1.4.13/jquery.smooth-scroll.min.js', array('jquery'), true, true );
	wp_register_script( 'bootstrap', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.1.1/js/bootstrap.min.js', array('jquery'), true, true );
	wp_enqueue_script( 'jquery-validate', array('jquery'), true, true );
	wp_enqueue_script( 'fitvids', array('jquery'), true, true );
	wp_enqueue_script( 'smoothscroll', array('jquery'), true, true );
	wp_enqueue_script( 'bootstrap', array('jquery'), true, true );
	wp_enqueue_script( 'wp-listings-single', array('jquery, jquery-ui-tabs', 'jquery-validate'), true, true );
}

// not using eqnueue, added in content area so it takes precedence over theme styles
function single_listing_style() { ?>
	<!-- Version 1.3.3 -->
	<script type="text/javascript">
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
	</script>

	<style>
	*, *:before, *:after {
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
	}
	html, body, div, span, applet, object, iframe,
	h1, h2, h3, h4, h5, h6, p, blockquote, pre,
	a, abbr, acronym, address, big, cite, code,
	del, dfn, em, img, ins, kbd, q, s, samp,
	small, strike, strong, sub, sup, tt, var,
	b, u, i, center,
	dl, dt, dd, ol, ul, li,
	fieldset, form, label, legend,
	table, caption, tbody, tfoot, thead, tr, th, td,
	article, aside, canvas, details, embed, 
	figure, figcaption, footer, header, hgroup, 
	menu, nav, output, ruby, section, summary,
	time, mark, audio, video {
		margin: 0;
		padding: 0;
		border: 0;
		font-size: 100%;
		font: inherit;
		vertical-align: baseline;
	}
	/* HTML5 display-role reset for older browsers */
	article, aside, details, figcaption, figure, 
	footer, header, hgroup, menu, nav, section {
		display: block;
	}
	body, br {
		line-height: 1;
	}
	ol,
	ul,
	li,
	.entry-content ul li {
		list-style: none;
	}
	.entry-content > *:not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.is-style-wide) {
		width: 100vw;
		margin: 0;
		max-width: none;
	}
	.entry-content .wplistings-single-listing .entry-header {
		max-width:none;
		padding: 0px;
	}
	.wplistings-single-listing .entry-header .entry-title::before {
		display:none;
	}
	.entry-content .wplistings-single-listing #listing-content {
		max-width:none;
	}
	.entry-content .wplistings-single-listing #listing-sidebar {
		margin-top:0;
	}
	@media only screen and (max-width: 480px) {
		.entry-content .wplistings-single-listing #listing-sidebar {
			margin-bottom:0;
		}
	}
	blockquote, q {
		quotes: none;
	}
	blockquote:before, blockquote:after,
	q:before, q:after {
		content: '';
		content: none;
	}
	table {
		border-collapse: collapse;
		border-spacing: 0;
	}
	html, html a {
		-webkit-font-smoothing: antialiased;
    	text-shadow: 1px 1px 1px rgba(0,0,0,0.004);
	}
	/* IE9 gradient */
	.gradient {
		filter: none;
	}
	
	/** Override theme defaults **/

	body,
	body.listing-template-custom,
	.site,
	.wrap,
	#wrap,
	#inner,
	#content,
	#content-sidebar-wrap,
	.full-width-content #content-sidebar-wrap,
	.site-inner .wrap,
	.content-sidebar-wrap,
	.single-listing .site-inner,
	.single-listing .content,
	.single-listing .hentry,
	.single-listing.full-width .hentry,
	.single-listing .entry,
	.single-listing .entry-content {
		position: relative;
		padding: 0;
		margin: 0;
		max-width: none;
		width: 100%;
		background: #fff;
		color: #666;
		font-family: "Source Sans Pro", "Calibri", "Droid Sans", "Avenir", Helvetica, Arial, sans-serif;
		font-size: 18px;
		font-weight: 300;
	}
	body.listing-template-custom .site-inner {
		padding: 0 !important;
		background-image: none;
	}
	.entry-content a,
	.entry-content a:link,
	.entry-content a:visited {
		color: #34619f;
		border-bottom: 1px solid #999;
		text-decoration: none;
	}
	.entry-content a:hover {
		color: #34619f;
		text-decoration: none;
		border-bottom: none;
	}
	.entry-content img {
		border: none;
	}
	.entry-content img a,
	.entry-content .agent-social-profiles a {
		border-bottom: none;
	}
	body.listing-template-custom p,
	body.listing-template-custom h1,
	body.listing-template-custom h2,
	body.listing-template-custom h3,
	body.listing-template-custom h4,
	body.listing-template-custom h5,
	body.listing-template-custom h6 {
		color: #666;
		font-family: "Rokkitt", serif;
		font-size: 18px;
		font-weight: 400;
		margin-bottom: 25px;
	}
	body.listing-template-custom h2 {
		font-size: 48px;
	}
	body.listing-template-custom h3 {
		font-size: 40px;
		border-bottom: 1px solid #ccc;
	}
	body.listing-template-custom h4 {
		font-size: 32px;
		border-bottom: 1px solid #ccc;
	}
	body.listing-template-custom h5,
	body.listing-template-custom h6 {
		font-size: 26px;
	}
	#listing-content h3,
	#listing-content h4 {
		margin: 25px 0;
	}
	body .entry-content p {
		color: #333;
		font-family: "Source Sans Pro", "Calibri", "Droid Sans", "Avenir", Helvetica, Arial, sans-serif;
		font-size: 18px;
		font-weight: 300;
		line-height: 30px;
		margin-bottom: 25px;
	}
	.site-inner,
	.entry-content ul,
	.entry-content ul li,
	.entry-header {
		margin: 0;
		padding: 0;
	}

	a,
	input:focus,
	input[type="button"],
	input[type="reset"],
	input[type="submit"],
	textarea:focus,
	#listing-sidebar .navigation li {
		-webkit-transition: all 0.25s ease-in-out;
		-moz-transition:    all 0.25s ease-in-out;
		-ms-transition:     all 0.25s ease-in-out;
		-o-transition:      all 0.25s ease-in-out;
		transition:         all 0.25s ease-in-out;
	}

	::-moz-selection {
		background-color: #34619f;
		color: #fff;
	}

	::selection {
		background-color: #34619f;
		color: #fff;
	}

	/** Header **/
	.listing-template header.entry-header {
		position: absolute;
		width: 100%;
		height: 80px;
		background: #fff;
		color: #333;
		margin: 0;
		display: block !important;
	}
	header h1.entry-title {
		color: #333;
		float: left;
		margin: 0;
		padding: 20px 25px;
		font-size: 44px;
		font-weight: 400;
		text-transform: none;
		line-height: 1;
		border: none;
	}
	header .listing-meta {
		float: right;
		padding: 10px 25px;
		color: #333;
		text-align: right;
	}
	header .listing-meta li {
		float: left;
		margin: 0 0 0 25px;
		line-height: 1;
	}
	.listing-price {
		position: relative;
		top: 0;
		left: 0;
		background: none;
		margin: 0;
		padding: 0;
		color: #fff;
		font-family: "Source Sans Pro", "Calibri", "Droid Sans", "Avenir", Helvetica, Arial, sans-serif;
		font-weight: 300;
		text-shadow: none;
		border-radius: 0;
	}
	header .listing-meta li.listing-price {
		color: #333;
		display: block;
		float: none;
		font-size: 34px;
		margin: 0 0 5px 25px;
	}
	header .listing-meta li span.label {
		font-weight: 400;
	}

	/** Content **/
	#listing-content {
		width: 65%;
		float: right;
		padding: 0;
		margin-top: 80px;
		background: #fff;
	}
	#listing-description,
	#listing-details,
	#listing-gallery,
	#listing-video,
	#listing-school-neighborhood,
	#listing-contact,
	#listing-map,
	#listing-disclaimer {
		overflow: hidden;
		padding: 0 35px;
		margin: 0 0 20px;
	}
	#listing-disclaimer,
	#listing-disclaimer p {
		font-size: 0.75em;
	}
	#listing-image {
		position: relative;
	}
	span.listing-open-house {
		position: absolute;
		top: 0;
		right: 0;
		padding: 10px;
		background: #be1d2c;
		background: rgba(190,29,44,.7);
		color: #fff;
	}
	.single-listing-image {
		width: 100%;
		height: auto;
		margin-bottom: 20px;
	}
	#listing-description .listing-full-details-link {
		line-height: normal;
	}

	/** Sidebar **/
	#listing-sidebar {
		position: fixed;
		width: 35%;
		height: 100%;
		top: 80px;
		float: left;
		padding: 10px 0;
		color: #fff;
		background: #363741;
		overflow: auto;
	}
	#listing-sidebar.expand {
		top: 0;
	}
	#listing-sidebar p {
		color: #fff;
		margin: 0;
		padding: 0;
		line-height: 22px;
	}
	#listing-sidebar .listing-info {
		margin: 0;
		padding: 0 25px;
	}
	#listing-sidebar .listing-info li {
		font-size: 18px;
		margin: 0;
		padding: 15px 5px;
		border-bottom: 1px solid #ccc;
		font-family: "Source Sans Pro", "Calibri", "Droid Sans", "Avenir", Helvetica, Arial, sans-serif;
	}
	#listing-sidebar .listing-info li span.label {
		width: 40%;
		display: inline-block;
		font-size: 16px;
		font-weight: 700;
		text-transform: uppercase;
		font-family: "Source Sans Pro", "Calibri", "Droid Sans", "Avenir", Helvetica, Arial, sans-serif;
	}
	#listing-sidebar .listing-info li.listing-price {
		font-size: 34px;
	}
	#listing-sidebar .listing-info li.listing-price span.label {
		font-size: 16px;
	}
	.listing-address,
	.listing-city-state-zip {
		display: inherit;
		text-align: left;
		margin: 0;
		padding: 0;
		color: #fff;
		font-family: "Source Sans Pro", "Calibri", "Droid Sans", "Avenir", Helvetica, Arial, sans-serif;
		text-shadow: none;
		font-size: 18px;
		font-weight: 300;
		line-height: 30px;
		text-transform: none;
	}
	#listing-sidebar li.listing-address p {
		display: inline-block;
	}
	
	/* Navigation */
	#listing-sidebar .navigation {
		position: relative;
		margin: 40px 0;
		padding: 0;
		background: #2876c9;
		border-top: 1px solid #6595DB;
	}

	#listing-sidebar .navigation li {
		display: block;
		font-family: "Rokkitt", serif;
		font-size: 24px;
		color: #fff;
		border-bottom: 1px solid #6595DB;
	}
	#listing-sidebar .navigation li a {
		background: #2876c9; /* Old browsers */
		background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzI4NzZjOSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMyNTY0YmMiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
		background: -moz-linear-gradient(top,  #2876c9 0%, #2564bc 100%); /* FF3.6+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#2876c9), color-stop(100%,#2564bc)); /* Chrome,Safari4+ */
		background: -webkit-linear-gradient(top,  #2876c9 0%,#2564bc 100%); /* Chrome10+,Safari5.1+ */
		background: -o-linear-gradient(top,  #2876c9 0%,#2564bc 100%); /* Opera 11.10+ */
		background: -ms-linear-gradient(top,  #2876c9 0%,#2564bc 100%); /* IE10+ */
		background: linear-gradient(to bottom,  #2876c9 0%,#2564bc 100%); /* W3C */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#2876c9', endColorstr='#2564bc',GradientType=0 ); /* IE6-8 */
	}
	#listing-sidebar .navigation li a:hover,
	#listing-sidebar .navigation li a:active,
	#listing-sidebar .navigation li.active a {
		background: #2876c9;
		text-shadow: -1px -1px 0 rgba(0,0,0,.5);
	}
	#listing-sidebar .navigation li a {
		color: #fff;
		border-bottom: none;
		text-decoration: none;
		display: block;
		padding: 20px;
		text-shadow: 1px 1px 0 rgba(0,0,0,.5);
	}
	#listing-sidebar .navigation li a i {
		font-size: 24px;
		margin-right: 25px;
		color: #fff;
	}


	/* Details tab */
	.listing-template .tagged-features {
		margin-bottom: 40px;
		overflow: hidden;
	}
	.listing-template .tagged-features li {
		float: left;
		width: 22.9%;
		margin: 0 2% 25px 0;
		list-style-type: none;
	}
	.listing-template .tagged-features li:before {
		color: #53c331;
		font-family: "Font Awesome\ 5 Free";
		font-weight: 900;
		font-size: 16px;
		content: "\f14a";
		top: 0;
	}

	.listing-template .tagged-features li a {
		text-decoration: none;
		border-bottom: none;
		margin-left: 15px;
	}

	/* Details table */
	table {
		border-collapse: collapse;
		border-spacing: 0;
		line-height: 2;
		margin-bottom: 40px;
		margin-bottom: 4rem;
		width: 100%;
	}

	tbody {
		border-bottom: 1px solid #ddd;
	}

	tbody.left,
	tbody.right {
		float: none !important;
	}

	th,
	td {
		text-align: left;
	}

	th {
		border-top: 1px solid #ddd;
		font-weight: bold;
		text-transform: uppercase;
	}

	td {
		border-top: 1px solid #ddd;
		padding: 6px 0;
		padding: 0.6rem 0;
	}

	/* Contact tab */
	#listing-agent,
	#contact-form {
		float: left;
		width: 48%;
		background: none;
	}
	#listing-agent {
		margin-right: 3.8%;
	}
	#listing-agent .connected-agents {
		background: none;
	}
	#listing-agent h5,
	#listing-agent p {
		font-size: 100%;
		margin: 0 0 10px;
		padding: 0;
	}
	#listing-agent h5 {
		font-size: 24px;
	}
	#listing-agent img {
		box-shadow: none;
		border: none;
	}

	/* Contact Form */
	#contact-form li {
		margin: 0 0 20px 0;
	}

	#contact-form label {
		margin: 0 0 10px 0;
		display: block;
	}

	#contact-form input,
	#contact-form input[type="text"]
	#contact-form select,
	#contact-form textarea {
		background-color: #fff;
		border: 1px solid #ddd;
		border-radius: 3px;
		box-shadow: 1px 1px 3px #eee inset;
		color: #999;
		font-size: 14px;
		padding: 16px;
		width: 100%;
		height: auto;
	}

	#contact-form input:focus,
	#contact-form textarea:focus {
		border: 1px solid #999;
		outline: none;
	}

	::-moz-placeholder {
		color: #999;
		opacity: 1;
	}

	::-webkit-input-placeholder {
		color: #999;
	}

	#contact-form button,
	#contact-form input[type="button"],
	#contact-form input[type="reset"],
	#contact-form input[type="submit"],
	#submit,
	#contact-form .button,
	#contact-form .entry-content .button {
		background: #34619f;
		border: none;
		box-shadow: none;
		color: #fff;
		cursor: pointer;
		padding: 16px 24px;
		text-transform: uppercase;
		text-decoration: none;
		text-shadow: none;
		width: auto;
	}

	#contact-form button:hover,
	#contact-form input:hover[type="button"],
	#contact-form input:hover[type="reset"],
	#contact-form input:hover[type="submit"],
	#submit,
	#contact-form .button:hover,
	#contact-form .entry-content .button:hover {
		background: #1A3E70;
	}

	#contact-form .entry-content .button:hover {
		color: #fff;
	}

	#contact-form .button {
		border-radius: 3px;
		display: inline-block;
	}

	/* back link */
	.listing-template a.link-main-site {
		padding: 0 5px;
		color: #fff;
		font-size: 12px;
		line-height: 20px;
		border-bottom: none;
	}

	/* Gallery */
	.gallery {
		margin-bottom: 20px;
	}

	.gallery a img {
		margin: 0;
	}

	.gallery-item {
		float: left;
		margin: 0 4px 4px 0;
		overflow: hidden;
		position: relative;
	}

	.gallery-columns-1 .gallery-item {
		max-width: 100%;
	}

	.gallery-columns-2 .gallery-item {
		max-width: 48%;
		max-width: -webkit-calc(50% - 4px);
		max-width:         calc(50% - 4px);
	}

	.gallery-columns-3 .gallery-item {
		max-width: 32%;
		max-width: -webkit-calc(33.3% - 4px);
		max-width:         calc(33.3% - 4px);
	}

	.gallery-columns-4 .gallery-item {
		max-width: 23%;
		max-width: -webkit-calc(25% - 4px);
		max-width:         calc(25% - 4px);
	}

	.gallery-columns-5 .gallery-item {
		max-width: 19%;
		max-width: -webkit-calc(20% - 4px);
		max-width:         calc(20% - 4px);
	}

	.gallery-columns-6 .gallery-item {
		max-width: 15%;
		max-width: -webkit-calc(16.7% - 4px);
		max-width:         calc(16.7% - 4px);
	}

	.gallery-columns-7 .gallery-item {
		max-width: 13%;
		max-width: -webkit-calc(14.28% - 4px);
		max-width:         calc(14.28% - 4px);
	}

	.gallery-columns-8 .gallery-item {
		max-width: 11%;
		max-width: -webkit-calc(12.5% - 4px);
		max-width:         calc(12.5% - 4px);
	}

	.gallery-columns-9 .gallery-item {
		max-width: 9%;
		max-width: -webkit-calc(11.1% - 4px);
		max-width:         calc(11.1% - 4px);
	}

	.gallery-columns-1 .gallery-item:nth-of-type(1n),
	.gallery-columns-2 .gallery-item:nth-of-type(2n),
	.gallery-columns-3 .gallery-item:nth-of-type(3n),
	.gallery-columns-4 .gallery-item:nth-of-type(4n),
	.gallery-columns-5 .gallery-item:nth-of-type(5n),
	.gallery-columns-6 .gallery-item:nth-of-type(6n),
	.gallery-columns-7 .gallery-item:nth-of-type(7n),
	.gallery-columns-8 .gallery-item:nth-of-type(8n),
	.gallery-columns-9 .gallery-item:nth-of-type(9n) {
		margin-right: 0;
	}

	.gallery-columns-1.gallery-size-medium figure.gallery-item:nth-of-type(1n+1),
	.gallery-columns-1.gallery-size-thumbnail figure.gallery-item:nth-of-type(1n+1),
	.gallery-columns-2.gallery-size-thumbnail figure.gallery-item:nth-of-type(2n+1),
	.gallery-columns-3.gallery-size-thumbnail figure.gallery-item:nth-of-type(3n+1) {
		clear: left;
	}

	.gallery-caption {
		background-color: rgba(0, 0, 0, 0.7);
		-webkit-box-sizing: border-box;
		-moz-box-sizing:    border-box;
		box-sizing:         border-box;
		color: #fff;
		font-size: 12px;
		line-height: 1.5;
		margin: 0;
		max-height: 50%;
		opacity: 0;
		padding: 6px 8px;
		position: absolute;
		bottom: 0;
		left: 0;
		text-align: left;
		width: 100%;
	}

	.gallery-caption:before {
		content: "";
		height: 100%;
		min-height: 49px;
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
	}

	.gallery-item:hover .gallery-caption {
		opacity: 1;
	}

	.gallery-columns-7 .gallery-caption,
	.gallery-columns-8 .gallery-caption,
	.gallery-columns-9 .gallery-caption {
		display: none;
	}

	/** Misc **/
	.backstretch {display: none !important;}
	#quicksearch-scrollspy {display: none !important;}
	/* hide admin bar */
	body.admin-bar {margin-top: -32px !important;}

	/* =Media Queries
	------------------------------------------------------------ */

	/** 
	for setting max-width on images without
	the max-width property being picked up by
	IE 8 and below. (Avoid IE8 max width bug)
	*/
	@media (max-width: 3000px) {
		img {
			max-width: 100%;
		}
	}

	@media (min-width: 1200px) {
		.wrap {
			width: 100%;
		}
	}

	@media only screen and (max-width: 1023px) {
		header {
			height: 70px;
		}
		header h1.entry-title {
			font-size: 32px;
		}
		header .listing-meta {
			font-size: 14px;
		}
		header .listing-meta li.listing-price {
			font-size: 24px;
		}
		#listing-content {
			margin-top: 70px;
			width: 60%;
		}
		#listing-sidebar {
			top: 70px;
			width: 40%;
		}
		#listing-sidebar .navigation li {
			font-size: 16px;
		}
		#listing-sidebar .navigation li a {
			padding: 15px;
		}
		#listing-sidebar .navigation li a i {
			font-size: 18px;
			margin-right: 15px;
		}
		.listing-template .tagged-features li {
			width: 45%;
		}
		#listing-agent,
		#contact-form {
			width: 100%;
		}
	}

	@media only screen and (max-width: 767px) {
		header {
			position: relative;
			height: auto;
			overflow: hidden;
		}
		header .listing-meta {
			display: none;
		}
		#listing-content {
			width: 65%;
			margin-top: 0;
		}
		#listing-sidebar {
			width: 35%;
			top: initial;
		}
		#listing-sidebar .listing-info {
			padding: 0 15px;
		}
		#listing-sidebar .listing-info li {
			font-size: 14px;
			padding: 10px 5px;
		}
		#listing-sidebar .listing-info li span.label {
			width: 100%;
			display: block;
			margin: 0 0 5px;
		}
		#listing-sidebar .listing-info li.listing-price {
			font-size: 24px;
		}
		.listing-template .tagged-features li {
			width: 100%;
		}
	}

	@media only screen and (max-width: 480px) {
		header {
			position: relative;
			height: auto;
		}
		header h1.entry-title {
			padding: 10px 25px;
		}
		header .listing-meta {
			display: block;
			clear: left;
			float: left;
			text-align: left;
			padding: 0 25px 10px;
		}
		header .listing-meta li,
		header .listing-meta li.listing-price {
			margin: 0 25px 5px 0;
		}
		span.listing-open-house {
			position: relative;
			display: block;
			padding: 5px;
			text-align: center;
		}
		#listing-content {
			width: 100%;
			float: none;
			margin: 0 0 80px;
		}
		#listing-sidebar,
		#listing-sidebar.expand {
			position: fixed;
			width: 100%;
			float: none;
			height: auto;
			padding: 0;
			left: 0;
			bottom: 0;
			top: inherit;
			z-index: 10;
		}
		#listing-sidebar .listing-info {
			display: none;
		}
		#listing-sidebar .navigation {
			margin: 0;
			padding: 0;
			overflow: hidden;
		}
		#listing-sidebar .navigation li {
			float: left;
			width: 33.3333333%;
			text-align: center;
			margin: 0;
			padding: 0;
			background: #2876c9;
			border-right: 1px solid #6595DB;
		}
		#listing-sidebar .navigation.items-4 li {
			width: 25%;
		}
		#listing-sidebar .navigation.items-5 li {
			width: 20%;
		}
		#listing-sidebar .navigation.items-6 li {
			width: 16.6666666%;
		}
		#listing-sidebar .navigation.items-7 li {
			width: 14.2845555%;
		}
		#listing-sidebar .navigation li a {
			background: #2876c9;
		}
		#listing-sidebar .navigation li a:hover,
		#listing-sidebar .navigation li.active a {
			background: #2564bc;
		}
		#listing-sidebar .navigation li a i {
			margin: 0;
			width: 100%;
		}
		#listing-sidebar .navigation li span.label {
			display: none;
		}
		.listing-template a.link-main-site {
			position: relative;
			display: block;
		}
	}

	</style>
	<?php 
}

function single_listing_post_content() {

	echo single_listing_style();

	global $post;
	$options = get_option('plugin_wp_listings_settings');

	?>

	<div class="entry-content wplistings-single-listing listing-template" data-spy="scroll" data-target="#listing-sidebar">

		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' );

			$listing_meta = sprintf( '<ul class="listing-meta">');

			if ( get_post_meta($post->ID, '_listing_hide_price', true) == 1 ) {
				$listing_meta .= (get_post_meta($post->ID, '_listing_price_alt', true)) ? sprintf( '<li class="listing-price">%s</li>', get_post_meta( $post->ID, '_listing_price_alt', true ) ) : '';
			} elseif(get_post_meta($post->ID, '_listing_price', true)) {
	 			$listing_meta .= sprintf( '<li class="listing-price">%s%s %s</li>', $options['wp_listings_currency_symbol'], get_post_meta( $post->ID, '_listing_price', true ), (isset($options['wp_listings_display_currency_code']) && $options['wp_listings_display_currency_code'] == 1) ? '<span class="currency-code">' . $options['wp_listings_currency_code'] . '</span>' : '' );
	 		}

			if ( '' != get_post_meta( $post->ID, '_listing_bedrooms', true ) ) {
				$listing_meta .= sprintf( '<li class="listing-bedrooms"><span class="label">Beds: </span>%s</li>', get_post_meta( $post->ID, '_listing_bedrooms', true ) );
			}

			if ( '' != get_post_meta( $post->ID, '_listing_bathrooms', true ) ) {
				$listing_meta .= sprintf( '<li class="listing-bathrooms"><span class="label">Baths: </span>%s</li>', get_post_meta( $post->ID, '_listing_bathrooms', true ) );
			}

			if ( '' != get_post_meta( $post->ID, '_listing_sqft', true ) ) {
				$listing_meta .= sprintf( '<li class="listing-sqft"><span class="label">Sq Ft: </span>%s</li>', get_post_meta( $post->ID, '_listing_sqft', true ) );
			}

			$listing_meta .= sprintf( '</ul>');

			echo $listing_meta;
			?>
		</header><!-- .entry-header -->


		<div id="listing-content" class="listing-data">

			<div id="listing-image">
				<?php // output featured iamge
				echo get_the_post_thumbnail( $post->ID, 'listings-full', array('class' => 'single-listing-image') );

				// open house info
				if ( '' != get_post_meta( $post->ID, '_listing_open_house', true ) ) {
					printf( '<span class="listing-open-house">Open House: %s</span>', get_post_meta( $post->ID, '_listing_open_house', true ) );
				}
				?>
			</div>

			<div id="listing-description">
				<h3>Description</h3>
				<?php
				the_content( __( 'View more <span class="meta-nav">&rarr;</span>', 'wp-listings' ) );
				echo ( get_post_meta( $post->ID, '_listing_featured_on', true ) ) ? '<p class="wp_listings_featured_on">' . get_post_meta( $post->ID, '_listing_featured_on', true ) . '</p>' : '';

				if ( class_exists( 'Idx_Broker_Plugin' ) && ! empty( $options['wp_listings_display_idx_link'] ) && get_post_meta( $post->ID, '_listing_details_url', true ) ) {
					echo '<a href="' . esc_attr( get_post_meta( $post->ID, '_listing_details_url', true ) ) . '" title="' . esc_attr( get_post_meta( $post->ID, '_listing_mls', true ) ) . '" class="listing-full-details-link">View full listing details</a>';
				}
				?>
			</div><!-- #listing-description -->

			<div id="listing-details">
				<h3>Listing Details</h3>
				<?php
				$details_instance = new WP_Listings();

				$pattern = '<tr class="wp_listings%s"><th class="label">%s</th><td>%s</td></tr>';

				echo '<table class="listing-details">';

				echo '<tbody class="left">';
				if ( get_post_meta($post->ID, '_listing_hide_price', true) == 1 ) {
					echo (get_post_meta($post->ID, '_listing_price_alt', true)) ? '<tr class="wp_listings_listing_price"><th class="label">' . __('Price:', 'wp-listings') . '</th><td>'.get_post_meta( $post->ID, '_listing_price_alt', true) .'</td></tr>' : '';
				} elseif(get_post_meta($post->ID, '_listing_price', true)) {
					echo '<tr class="wp_listings_listing_price"><th class="label">' . __('Price:', 'wp-listings') . '</th><td><span class="currency-symbol">' . $options['wp_listings_currency_symbol'] . '</span>';
					echo get_post_meta( $post->ID, '_listing_price', true) . ' ';
					echo (isset($options['wp_listings_display_currency_code']) && $options['wp_listings_display_currency_code'] == 1) ? '<span class="currency-code">' . $options['wp_listings_currency_code'] . '</span>' : '';
					echo '</td></tr>';
				}
				echo '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
				echo (get_post_meta($post->ID, '_listing_address', true)) ? '<tr class="wp_listings_listing_address"><th class="label">' . __('Address:', 'wp-listings') . '</th><td itemprop="streetAddress">'.get_post_meta( $post->ID, '_listing_address', true) .'</td></tr>' : '';
				echo (get_post_meta($post->ID, '_listing_city', true)) ? '<tr class="wp_listings_listing_city"><th class="label">' . __('City:', 'wp-listings') . '</th><td itemprop="addressLocality">'.get_post_meta( $post->ID, '_listing_city', true) .'</td></tr>' : '';
				echo (get_post_meta($post->ID, '_listing_state', true)) ? '<tr class="wp_listings_listing_state"><th class="label">' . __('State:', 'wp-listings') . '</th><td itemprop="addressRegion">'.get_post_meta( $post->ID, '_listing_state', true) .'</td></tr>' : '';
				echo (get_post_meta($post->ID, '_listing_zip', true)) ? '<tr class="wp_listings_listing_zip"><th class="label">' . __('Zip Code:', 'wp-listings') . '</th><td itemprop="postalCode">'.get_post_meta( $post->ID, '_listing_zip', true) .'</td></tr>' : '';
				echo (get_post_meta($post->ID, '_listing_subdivision', true)) ? '<tr class="wp_listings_listing_subdivision"><th class="label">' . __('Subdivision:', 'wp-listings') . '</th><td>'.get_post_meta( $post->ID, '_listing_subdivision', true) .'</td></tr>' : '';
				echo '</div>';
				echo (get_post_meta($post->ID, '_listing_mls', true)) ? '<tr class="wp_listings_listing_mls"><th class="label">MLS:</td><td>'.get_post_meta( $post->ID, '_listing_mls', true) .'</td></tr>' : '';
				echo '</tbody>';

				echo '<tbody class="right">';
				foreach ( (array) $details_instance->property_details['col2'] as $label => $key ) {
					$detail_value = esc_html( get_post_meta($post->ID, $key, true) );
					if ( ! empty( $detail_value ) ) {
						printf( $pattern, $key, esc_html( $label ), $detail_value );
					}
				}
				echo '</tbody>';

				echo '</table>';

				echo '<table class="listing-details extended">';
				echo '<tbody class="left">';
				foreach ( (array) $details_instance->extended_property_details['col1'] as $label => $key ) {
					$detail_value = esc_html( get_post_meta($post->ID, $key, true) );
					if ( ! empty( $detail_value ) ) {
						printf( $pattern, $key, esc_html( $label ), $detail_value );
					}
				}
				echo '</tbody>';
				echo '<tbody class="right">';
				foreach ( (array) $details_instance->extended_property_details['col2'] as $label => $key ) {
					$detail_value = esc_html( get_post_meta($post->ID, $key, true) );
					if ( ! empty( $detail_value ) ) {
						printf( $pattern, $key, esc_html( $label ), $detail_value );
					}
				}
				echo '</tbody>';
				echo '</table>';

				if ( isset( $options['wp_listings_display_advanced_fields'] ) && $options['wp_listings_display_advanced_fields'] ) {
					$adv_fields = generate_adv_field_list( $post );
					if ( count( $adv_fields ) ) {
						echo '<table class="listing-details advanced">';
						echo '<tbody class="left">';
						foreach ( $adv_fields['col1'] as $key => $value ) {
							if ( ! empty( $value ) ) {
								printf( $pattern, $key, esc_html( get_adv_field_display_name( $key ) . ':' ), $value );
							}
						}
						echo '</tbody>';

						echo '<tbody class="right">';
						foreach ( $adv_fields['col2'] as $key => $value ) {
							if ( ! empty( $value ) ) {
								printf( $pattern, $key, esc_html( get_adv_field_display_name( $key ) . ':'), $value );
							}
						}
						echo '</tbody>';
						echo '</table>';
					}
				}

				if(get_the_term_list( get_the_ID(), 'features', '<li>', '</li><li>', '</li>' ) != null) {
					echo '<h5>' . __('Tagged Features:', 'wp-listings') . '</h5><ul class="tagged-features">';
					echo get_the_term_list( get_the_ID(), 'features', '<li>', '</li><li>', '</li>' );
					echo '</ul><!-- .tagged-features -->';
				}

				if ( get_post_meta( $post->ID, '_listing_home_sum', true) != '' || get_post_meta( $post->ID, '_listing_kitchen_sum', true) != '' || get_post_meta( $post->ID, '_listing_living_room', true) != '' || get_post_meta( $post->ID, '_listing_master_suite', true) != '') { ?>
					<div class="additional-features">
						<h4>Additional Features</h4>
						<h6 class="label"><?php _e("Home Summary", 'wp-listings'); ?></h6>
						<p class="value"><?php echo do_shortcode(get_post_meta( $post->ID, '_listing_home_sum', true)); ?></p>
						<h6 class="label"><?php _e("Kitchen Summary", 'wp-listings'); ?></h6>
						<p class="value"><?php echo do_shortcode(get_post_meta( $post->ID, '_listing_kitchen_sum', true)); ?></p>
						<h6 class="label"><?php _e("Living Room", 'wp-listings'); ?></h6>
						<p class="value"><?php echo do_shortcode(get_post_meta( $post->ID, '_listing_living_room', true)); ?></p>
						<h6 class="label"><?php _e("Master Suite", 'wp-listings'); ?></h6>
						<p class="value"><?php echo do_shortcode(get_post_meta( $post->ID, '_listing_master_suite', true)); ?></p>
					</div><!-- .additional-features -->
				<?php
				}
				?>
				
			</div><!-- #listing-details -->

			<?php if (get_post_meta( $post->ID, '_listing_gallery', true) != '') { ?>
			<div id="listing-gallery">
				<h3>Photos</h3>
				<?php echo do_shortcode(get_post_meta( $post->ID, '_listing_gallery', true)); ?>
			</div><!-- #listing-gallery -->
			<?php } ?>

			<?php if (get_post_meta( $post->ID, '_listing_video', true) != '') { ?>
			<div id="listing-video">
				<h3>Video</h3>
				<div class="iframe-wrap">
				<?php echo do_shortcode(get_post_meta( $post->ID, '_listing_video', true)); ?>
				</div>
			</div><!-- #listing-video -->
			<?php } ?>

			<?php if (get_post_meta( $post->ID, '_listing_school_neighborhood', true) != '') { ?>
			<div id="listing-school-neighborhood">
				<h3>Schools &amp; Neighborhood</h3>
				<p>
				<?php echo do_shortcode(get_post_meta( $post->ID, '_listing_school_neighborhood', true)); ?>
				</p>
			</div><!-- #listing-school-neighborhood -->
			<?php } ?>


			<?php
				if (get_post_meta( $post->ID, '_listing_map', true) != '') {
				echo '<div id="listing-map"><h3>Location Map</h3>';
				echo do_shortcode(get_post_meta( $post->ID, '_listing_map', true) );
				echo '</div><!-- .listing-map -->';
				}

				elseif(get_post_meta( $post->ID, '_listing_latitude', true) && get_post_meta( $post->ID, '_listing_longitude', true) && get_post_meta( $post->ID, '_listing_automap', true) == 'y') {

					$map_info_content = sprintf('<p style="font-size: 14px; margin-bottom: 0;">%s<br />%s %s, %s</p>', get_post_meta( $post->ID, '_listing_address', true), get_post_meta( $post->ID, '_listing_city', true), get_post_meta( $post->ID, '_listing_state', true), get_post_meta( $post->ID, '_listing_zip', true));

					($options['wp_listings_gmaps_api_key']) ? $map_key = $options['wp_listings_gmaps_api_key'] : $map_key = '';

					echo '<script src="https://maps.googleapis.com/maps/api/js?key=' . $map_key . '"></script>
					<script>
						function initialize() {
							var mapCanvas = document.getElementById(\'map-canvas\');
							var myLatLng = new google.maps.LatLng(' . get_post_meta( $post->ID, '_listing_latitude', true) . ', ' . get_post_meta( $post->ID, '_listing_longitude', true) . ')
							var mapOptions = {
								center: myLatLng,
								zoom: 14,
								mapTypeId: google.maps.MapTypeId.ROADMAP
						    }

						    var marker = new google.maps.Marker({
							    position: myLatLng,
							    icon: \'//s3.amazonaws.com/ae-plugins/wp-listings/images/active.png\'
							});
							
							var infoContent = \' ' . $map_info_content . ' \';

							var infowindow = new google.maps.InfoWindow({
								content: infoContent
							});

						    var map = new google.maps.Map(mapCanvas, mapOptions);

						    marker.setMap(map);

						    infowindow.open(map, marker);
						}
						google.maps.event.addDomListener(window, \'load\', initialize);
					</script>
					';
					echo '<div id="listing-map"><h3>Location Map</h3><div id="map-canvas" style="width: 100%; height: 350px;"></div></div><!-- .listing-map -->';
				}
			?>

			<div id="listing-contact">
				<?php
					if (function_exists('_p2p_init') && function_exists('agent_profiles_init')) {
						echo'<div id="listing-agent">';
						aeprofiles_connected_agents_markup();
						echo '</div><!-- .listing-agent -->';
					} elseif (function_exists('_p2p_init') && function_exists('impress_agents_init') ) {
						echo'<div id="listing-agent">
						<div class="connected-agents">';
						impa_connected_agents_markup();
						echo '</div></div><!-- .listing-agent -->';
					}
				?>

				<div id="contact-form" <?php if(!function_exists('aeprofiles_connected_agents_markup')) { echo 'style="width: 100%;"'; }; ?>>
					<?php 
					$options = get_option('plugin_wp_listings_settings');
					if (get_post_meta( $post->ID, '_listing_contact_form', true) != '') {

						echo do_shortcode(get_post_meta( $post->ID, '_listing_contact_form', true) );

					} elseif (isset($options['wp_listings_default_form']) && $options['wp_listings_default_form'] != '') {
 
						echo do_shortcode($options['wp_listings_default_form']);

					} else {
						echo '<h4>Listing Inquiry</h4>';

						$nameError = '';
						$emailError = '';

						if(isset($_POST['submitted'])) {

						$url = get_permalink();
						$listing = get_the_title();

						if(trim($_POST['contactName']) === '') {
							$nameError = 'Please enter your name.';
							$hasError = true;
						} else {
							$name = trim($_POST['contactName']);
						}

						if(trim($_POST['email']) === '')  {
							$emailError = 'Please enter your email address.';
							$hasError = true;
						} else if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", trim($_POST['email']))) {
							$emailError = 'You entered an invalid email address.';
							$hasError = true;
						} else {
							$email = trim($_POST['email']);
						}

						$phone = trim($_POST['phone']);

						if(function_exists('stripslashes')) {
							$comments = stripslashes(trim($_POST['comments']));
						} else {
							$comments = trim($_POST['comments']);
						}


						if(!isset($hasError)) {
							$emailTo = get_the_author_meta( 'user_email', $post->post_author );
							if (!isset($emailTo) || ($emailTo == '') ){
								$emailTo = get_option('admin_email');
							}
							$subject = 'Listing Inquiry from '.$name;
							$body = "Name: $name \n\nEmail: $email \n\nPhone: $phone \n\nListing: $listing \n\nURL: $url \n\nComments: $comments";
							$headers = 'From: '.$name.' <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;

							wp_mail($emailTo, $subject, $body, $headers);
							$emailSent = true;
						}

					} ?>

					<?php if(isset($emailSent) && $emailSent == true) {	?>
						<div class="thanks">
							<a name="redirectTo"></a>
							<p>Thanks, your email was sent! We'll be in touch shortly.</p>
						</div>
					<?php } else { ?>
						<?php if(isset($hasError)) { ?>
							<a name="redirectTo"></a>
							<label class="error" name="redirectTo">Sorry, an error occured. Please try again.<label>
						<?php } ?>

						<form action="<?php the_permalink(); ?>#redirectTo" id="inquiry-form" method="post">
							<ul class="inquiry-form">
								<li class="contactName">
									<label for="contactName">Name: <span class="required">*</span></label>
									<input type="text" name="contactName" id="contactName" value="<?php if(isset($_POST['contactName'])) echo $_POST['contactName'];?>" class="required requiredField" />
									<?php if($nameError != '') { ?>
										<label class="error"><?=$nameError;?></label>
									<?php } ?>
								</li>

								<li class="contactEmail">
									<label for="email">Email: <span class="required">*</span></label>
									<input type="text" name="email" id="email" value="<?php if(isset($_POST['email']))  echo $_POST['email'];?>" class="required requiredField email" />
									<?php if($emailError != '') { ?>
										<label class="error"><?=$emailError;?></label>
									<?php } ?>
								</li>

								<li class="contactPhone">
									<label for="phone">Phone:</label>
									<input type="text" name="phone" id="phone" value="<?php if(isset($_POST['phone']))  echo $_POST['phone'];?>" />
								</li>

								<li class="contactComments"><label for="commentsText">Message:</label>
									<textarea name="comments" id="commentsText" rows="6" cols="20"><?php if(isset($_POST['comments'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['comments']); } else { echo $_POST['comments']; } } ?></textarea>
								</li>

								<li>
									<input id="submit" type="submit" value="Send Inquiry"></input>
								</li>
							</ul>
							<input type="hidden" name="submitted" id="submitted" value="true" />
						</form>
					<?php }

					}
					?>
				</div><!-- .contact-form -->
			</div><!-- #listing-contact -->

			<div id="listing-disclaimer">
			<?php
			if( get_post_meta($post->ID, '_listing_disclaimer', true) ) {
				echo '<p class="wp_listings_disclaimer">' . get_post_meta($post->ID, '_listing_disclaimer', true) . '</p>';
			} elseif ($options['wp_listings_global_disclaimer'] != '' && $options['wp_listings_global_disclaimer'] != null) {
				echo '<p class="wp_listings_disclaimer">' . $options['wp_listings_global_disclaimer'] . '</p>';
			}
			echo (get_post_meta($post->ID, '_listing_courtesy', true)) ? '<p class="wp_listings_courtesy">' . get_post_meta($post->ID, '_listing_courtesy', true) . '</p>' : '';
			?>
			</div><!-- #listing-disclaimer -->

		</div><!-- #listing-content .listing-data -->


		<div id="listing-sidebar" class="scrollpane">

			<?php

			// output listing meta data
			$listing_info = sprintf( '<ul class="listing-info">');

			if ( get_post_meta($post->ID, '_listing_hide_price', true) == 1 ) {
				$listing_info .= (get_post_meta($post->ID, '_listing_price_alt', true)) ? sprintf( '<li class="listing-price"><span class="label">Price: </span>%s</li>', get_post_meta( $post->ID, '_listing_price_alt', true ) ) : '';
			} elseif(get_post_meta($post->ID, '_listing_price', true)) {
	 			$listing_info .= sprintf( '<li class="listing-price"><span class="label">Price: </span>%s%s %s</li>', $options['wp_listings_currency_symbol'], get_post_meta( $post->ID, '_listing_price', true ), (isset($options['wp_listings_display_currency_code']) && $options['wp_listings_display_currency_code'] == 1) ? '<span class="currency-code">' . $options['wp_listings_currency_code'] . '</span>' : '' );
	 		}

			// output status
			// if ( '' != wp_listings_get_status() ) {
			// 	$listing_info .= sprintf( '<span class="listing-status %s">%s</span>', strtolower(wp_listings_get_status()), wp_listings_get_status() );
			// }

			$listing_info .= sprintf( '<li class="listing-address"><span class="label">Address: </span><p><span class="listing-address">%s</span><br />', wp_listings_get_address() );
			$listing_info .= sprintf( '<span class="listing-city-state-zip">%s, %s %s</span></p></li>', wp_listings_get_city(), wp_listings_get_state(), get_post_meta( $post->ID, '_listing_zip', true ) );

			// if ( '' != wp_listings_get_property_types() ) {
			// 	$listing_info .= sprintf( '<li class="listing-property-type"><span class="label">Property Type: </span>%s</li>', get_the_term_list( get_the_ID(), 'property-types', '', ', ', '' ) );
			// }

			// if ( '' != wp_listings_get_locations() ) {
			// 	$listing_info .= sprintf( '<li class="listing-location"><span class="label">Location: </span>%s</li>', get_the_term_list( get_the_ID(), 'locations', '', ', ', '' ) );
			// }

			if ( '' != get_post_meta( $post->ID, '_listing_bedrooms', true ) ) {
				$listing_info .= sprintf( '<li class="listing-bedrooms"><span class="label">Beds: </span>%s</li>', get_post_meta( $post->ID, '_listing_bedrooms', true ) );
			}

			if ( '' != get_post_meta( $post->ID, '_listing_bathrooms', true ) ) {
				$listing_info .= sprintf( '<li class="listing-bathrooms"><span class="label">Baths: </span>%s</li>', get_post_meta( $post->ID, '_listing_bathrooms', true ) );
			}

			if ( '' != get_post_meta( $post->ID, '_listing_sqft', true ) ) {
				$listing_info .= sprintf( '<li class="listing-sqft"><span class="label">Sq Ft: </span>%s</li>', get_post_meta( $post->ID, '_listing_sqft', true ) );
			}

			if ( '' != get_post_meta( $post->ID, '_listing_lot_sqft', true ) ) {
				$listing_info .= sprintf( '<li class="listing-lot-sqft"><span class="label">Lot Sq Ft: </span>%s</li>', get_post_meta( $post->ID, '_listing_lot_sqft', true ) );
			}

			$listing_info .= sprintf( '</ul>');

			echo $listing_info;

			?>

			<?php // listing navigation with counter for list item width
			$count = 0; // start counter at 0

			$listing_tabs = sprintf('<li><a href="#listing-description"><i class="fas fa-info fa-fw"></i><span class="label">Description</span></a></li>');
			$count++; // add 1 to counter 

			$listing_tabs .= sprintf('<li><a href="#listing-details"><i class="fas fa-list fa-fw"></i><span class="label">Details</span></a></li>');
			$count++; // add 1 to counter 

			if (get_post_meta( $post->ID, '_listing_gallery', true) != '') {
				$listing_tabs .= sprintf('<li><a href="#listing-gallery"><i class="fas fa-camera fa-fw"></i><span class="label">Photos</span></a></li>');
				$count++; // add 1 to counter 
			}

			if (get_post_meta( $post->ID, '_listing_video', true) != '') {
				$listing_tabs .= ('<li><a href="#listing-video"><i class="fab fa-youtube fa-fw"></i><span class="label">Video / Virtual Tour</span></a></li>');
				$count++; // add 1 to counter 
			}

			if (get_post_meta( $post->ID, '_listing_school_neighborhood', true) != '') {
				$listing_tabs .= ('<li><a href="#listing-school-neighborhood"><i class="fas fa-building-o fa-fw"></i><span class="label">Schools &amp; Neighborhood</span></a></li>');
				$count++; // add 1 to counter 
			}

			if (get_post_meta( $post->ID, '_listing_map', true) != '') {
				$listing_tabs .= ('<li><a href="#listing-map"><i class="fas fa-map-marker fa-fw"></i><span class="label">Map</span></a></li>');
				$count++; // add 1 to counter 
			}
			elseif(get_post_meta( $post->ID, '_listing_latitude', true) && get_post_meta( $post->ID, '_listing_longitude', true) && get_post_meta( $post->ID, '_listing_automap', true) == 'y') {
				$listing_tabs .= ('<li><a href="#listing-map"><i class="fas fa-map-marker fa-fw"></i><span class="label">Map</span></a></li>');
				$count++; // add 1 to counter 
			}


			$listing_tabs .= ('<li><a href="#listing-contact"><i class="fas fa-envelope fa-fw"></i><span class="label">Contact</span></a></li>');
			$count++; // add 1 to counter 

			printf('<ul class="nav navigation items-%s">%s</ul><!-- .navigation -->', $count, $listing_tabs);
			?>

			<a class="link-main-site" href="<?php echo home_url(); ?>">&larr; Back to main site</a>

		</div><!-- #listing-sidebar -->

	</div><!-- .entry-content -->

<?php
}

?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<?php
		// Start the Loop.
		while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class('entry-content wplistings-single-listing listing-template'); ?> role="main">

			<?php single_listing_post_content(); ?>

		</article><!-- #post-ID .listing-template -->

	<?php endwhile;	?>

<?php

wp_footer();

?>

</body>
</html>
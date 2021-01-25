<?php
/*
Single Listing Template: Spacious
Description: Large photo, white, blue, large type
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
	wp_register_style( 'wplistings-google-fonts', '//fonts.googleapis.com/css?family=Open+Sans:400,700|Libre+Baskerville:400,400italic');
	wp_enqueue_style( 'font-awesome-5.8.2' );
	wp_enqueue_style( 'wplistings-google-fonts' );
	//wp_register_script( 'modernizr', '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.1/modernizr.min.js', true, false );
	wp_register_script( 'fitvids', '//cdnjs.cloudflare.com/ajax/libs/fitvids/1.1.0/jquery.fitvids.js', array('jquery'), true, true );
	wp_register_script( 'smoothscroll', '//cdnjs.cloudflare.com/ajax/libs/jquery-smooth-scroll/1.4.13/jquery.smooth-scroll.min.js', array('jquery'), true, true );	
	//wp_enqueue_script( 'modernizr' );
	wp_enqueue_script( 'fitvids', array('jquery'), true, true );
	wp_enqueue_script( 'smoothscroll', array('jquery'), true, true );
	wp_enqueue_script( 'jquery-validate', array('jquery'), true, true );
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

		$(window).scroll(function () {
		  if ($(document).scrollTop() > 100 ) {
		    $('.scroll').addClass('scroll-hide');
		  } else {
		    $('.scroll').removeClass('scroll-hide');
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
		border: none;
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
	
	/** Override theme defaults **/

	body,
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
		background: none;
		color: #666;
		font-family: "Open Sans", sans-serif;
		font-size: 18px;
		font-weight: 300;
	}
	body.listing-template-custom .site-inner {
		padding: 0 !important;
		background-image: none;
	}
	html, body.listing-template-custom  {
		background: #efefef;
		background-image: -webkit-radial-gradient(top, circle cover, #fff, #b5b5b5 90%);
		background-image: -moz-radial-gradient(top, circle cover, #fff, #b5b5b5 90%);
		background-image: -o-radial-gradient(top, circle cover, #fff, #b5b5b5 90%);
		background-image: radial-gradient(top, circle cover, #fff, #b5b5b5 90%);
		background-attachment: fixed;
		height: 100%;
	}
	.entry-content a,
	.entry-content a:link,
	.entry-content a:visited {
		color: #000;
		border-bottom: 1px solid #999;
		text-decoration: none;
	}
	.entry-content a:hover {
		text-decoration: none;
		border-bottom: 1px solid #fff;
	}
	.entry-content img {
		border: none;
	}
	.entry-content img a,
	.entry-content .agent-social-profiles a {
		border-bottom: none;
	}
	body.listing-template-custom h1,
	body.listing-template-custom h2,
	body.listing-template-custom h3,
	body.listing-template-custom h4,
	body.listing-template-custom .entry-content h4,
	body.listing-template-custom h5,
	body.listing-template-custom h6 {
		color: #333;
		font-family: "Open Sans", sans-serif;
		font-size: 18px;
		font-weight: 700;
		line-height: 1;
		letter-spacing: -2px;
		margin-bottom: 25px;
		clear: both;
	}
	body.listing-template-custom h2 {
		font-size: 44px;
	}
	body.listing-template-custom h3 {
		font-size: 40px;
		text-align: center;
		border-bottom: 1px solid #b8b8b8;
	}
	body.listing-template-custom h4,
	body.listing-template-custom .entry-content h4 {
		font-size: 32px;
		text-align: center;
		border-bottom: 1px solid #b8b8b8;
	}
	body.listing-template-custom h5,
	body.listing-template-custom h6 {
		font-size: 26px;
	}
	body .entry-content p {
		color: #333;
		font-family: "Open Sans", sans-serif;
		font-size: 18px;
		line-height: 30px;
		margin-bottom: 25px;
	}
	.entry-content #listing-content p {
		letter-spacing: -1px;
	}
	.site-inner,
	.entry-content ul,
	.entry-content li,
	.entry-header {
		margin: 0;
		padding: 0;
	}
	.entry-content > *:not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.is-style-wide) {
		width: 100vw;
		margin: 0;
		max-width: none;
	}
	.entry-content .wplistings-single-listing .listing-leadin, 
	.entry-content .wplistings-single-listing .listing-image-wrap, 
	.entry-content .wplistings-single-listing #listing-content {
		max-width:none;
	}
	.entry-content .listing-leadin .entry-header {
		background-color:transparent;
	}
	.wplistings-single-listing .entry-header .entry-title::before {
		display:none;
	}
	#listing-description h3, #listing-details h3, #listing-gallery h3 {
		margin-top:0px;
	}
	#listing-description .listing-full-details-link {
		line-height: normal;
	}
	a,
	input:focus,
	input[type="button"],
	input[type="reset"],
	input[type="submit"],
	textarea:focus,
	.listing-template .ui-tabs .ui-tabs-nav li {
		-webkit-transition: all 0.25s ease-in-out;
		-moz-transition:    all 0.25s ease-in-out;
		-ms-transition:     all 0.25s ease-in-out;
		-o-transition:      all 0.25s ease-in-out;
		transition:         all 0.25s ease-in-out;
	}

	::-moz-selection {
		background-color: #fff;
		color: #111;
	}

	::selection {
		background-color: #fff;
		color: #111;
	}

	#quicksearch-scrollspy {
		display: none !important;
	}

	/** Lead in **/
	.listing-template .listing-leadin {
		min-height: 600px;
		height: 98vh;
		position: relative;
	}
	.listing-template .listing-leadin header {
		width: 80%;
		margin: 0 auto;
		display: block !important;
	}
	.listing-leadin h1.entry-title {
		margin: 0;
		padding: 24% 0 30px;
		font-size: 60px;
		font-weight: 700;
		letter-spacing: -4px;
		text-align: center;
		text-transform: none;
		width: 100%;
		color: #111;
		border: none;
	}
	.listing-leadin .listing-meta {
		width: 100%;
		padding: 10px 0;
		font-family: "Libre Baskerville", serif;
		font-size: 18px;
		line-height: 24px;
		text-align: center;
		border-top: 1px solid #b8b8b8;
		border-bottom: 1px solid #b8b8b8;
	}
	.listing-leadin .listing-meta li,
	.listing-leadin .listing-meta li.listing-price {
		position: relative;
		top: 0;
		left: 0;
		clear: none;
		display: inline-block;
		margin: 0 10px;
		color: #666;
		background: none;
		font-family: "Libre Baskerville", serif;
		font-size: inherit;
		font-weight: normal;
	}
	.listing-leadin .listing-meta li span.label {
		font-style: italic;
		text-transform: lowercase;
	}
	p.listing-open-house {
		width: 100%;
		padding: 10px 0;
		color: #666;
		font-family: "Libre Baskerville", serif;
		font-size: 18px;
		line-height: 24px;
		font-style: italic;
		text-align: center;
		border-bottom: 1px solid #b8b8b8;
	}
	.listing-leadin .scroll {
		position: absolute;
		width: 80px;
		height: 80px;
		padding: 13px;
		left: 30px;
		bottom: 30px;
		color: #777;
		font-size: 13px;
		font-weight: 700;
		text-transform: uppercase;
		text-align: center;
		opacity: 1;
		-webkit-transition: all .5s ease-in-out;
		-moz-transition:    all .5s ease-in-out;
		-ms-transition:     all .5s ease-in-out;
		-o-transition:      all .5s ease-in-out;
		transition:         all .5s ease-in-out;
		border: 2px solid #888;
		border-radius: 50%;
	}
	.listing-leadin .scroll i {
		display: block;
		font-size: 24px;
		font-weight: normal;
		margin-top: -5px;
		color: #666;
	}
	.listing-leadin .scroll.scroll-hide {
		opacity: 0;
	}

	/** Single listing top (image, meta) **/

	.listing-template .listing-image-wrap {
		position: relative;
		overflow: hidden;
		line-height: 0;
	}
	.listing-template .listing-image-wrap img {
		width: 100%;
		height: auto;
	}
	.listing-template .listing-image-wrap p {
		position: absolute;
		top: 40%;
		width: 100%;
		padding: 40px 15%;
		color: #333;
		background: rgba(255,255,255,0.65);
		font-family: "Libre Baskerville", serif;
		font-size: 22px;
		line-height: 32px;
		font-style: italic;
		text-align: center;
	}

	/** Listing navigation **/
	ul.navigation {
		width: 60%;
		margin: 110px auto 90px;
		padding: 20px 0px;
		overflow: hidden;
	}
	ul.navigation i {
		display: none;
	}
	ul.navigation li {
		width: 29.3333333%;
		margin: 0 2% 25px;
		display: inline-block;
	}
	ul.navigation.items-5 li:nth-of-type(4n) {
		margin: 0 2% 25px 16.3333333%;
	}
	ul.navigation.items-5 li:nth-of-type(5n) {
		margin: 0 16.3333333% 25px 2%;
	}
	ul.navigation.items-4 li:last-child,
	ul.navigation.items-7 li:last-child {
		margin: 0 auto;
		display: block;
	}
	.entry-content ul.navigation li a {
		display: block;
		padding: 13px 0;
		color: #333;
		font-size: 14px;
		font-weight: 700;
		text-transform: uppercase;
		text-align: center;
		letter-spacing: -1px;
		border: 2px solid #333;
	}
	.entry-content ul.navigation li a:hover {
		color: #111;
		background: #fff;
	}

	/** Listing content **/
	#listing-content .wrap {
		width: 60%;
		max-width: 1000px;
		padding-top: 30px;
		margin: 0 auto 60px;
		overflow: hidden;
	}

	/* Details tab */
	.listing-template .tagged-features {
		overflow: hidden;
		font-size: 14px;
		line-height: 18px;
		font-weight: 700;
		letter-spacing: -1px;
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
		background: none;
	}

	tbody {
	}

	tbody.left,
	tbody.right {
		float: none !important;
	}

	th,
	td {
		text-align: left;
		width: 50%;
	}

	table tr {
		background: none !important;
	}

	th {
		border-bottom: 1px solid #ddd;
		font-weight: bold;
		text-transform: none;
		letter-spacing: -1px;
	}

	td {
		border-bottom: 1px solid #ddd;
		padding: 6px 0;
		padding: 0.6rem 0;
	}

	/* Contact tab */
	#listing-agent,
	#contact-form {
		float: left;
		width: 48%;
		background: none;
		padding: 25px;
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
		font-family: "Open Sans", sans-serif;
		background: #666;
		border: none;
		box-shadow: none;
		color: #fff !important;
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
		background: #333;
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
		position: fixed;
		top: 0;
		left: 0;
		padding: 0 5px;
		color: #666;
		font-size: 12px;
		line-height: 20px;
		border-bottom: none;
	}

	/* Gallery */
	#listing-gallery p img {
		display: block;
		margin: auto;
	}
	#listing-gallery img {
		margin-bottom: 8px;
	}
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
	#listing-disclaimer,
	#listing-disclaimer p {
		font-size: 0.75em;
	}
	.backstretch {display: none !important;}

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
	@media only screen and (max-width: 1024px) {
		.listing-template .listing-leadin {
			/* hack for ios not supporting vh (viewport-height) */
			min-height: 500px;
			height: 500px;
		}
	}
	@media only screen and (max-width: 1023px) {
		.listing-template .listing-leadin {
			min-height: 600px;
			height: 500px;
		}
		ul.navigation {
			width: 70%;
			margin: 90px auto 70px;
		}
		ul.navigation li {
			width: 44%;
			margin: 0 3% 25px;
		}
		ul.navigation.items-4 li:last-child {
			margin: 0 3% 25px;
			display: inline-block;
		}
		ul.navigation.items-3 li:last-child,
		ul.navigation.items-5 li:last-child,
		ul.navigation.items-7 li:last-child {
			margin: 0 auto;
			display: block;
		}
	}
	@media only screen and (max-width: 767px) {
		#listing-content .wrap,
		ul.navigation {
			width: 90%;
		}
		ul.navigation {
			margin: 70px auto 50px;
		}
		.entry-content ul.navigation li a {
			font-size: 13px;
		}
		.listing-template .listing-image-wrap p {
			position: relative;
			background: rgba(255,255,255,0.2);
			font-size: 18px;
			line-height: 28px;
			border-bottom: 1px solid #ccc;
		}
		.listing-template .tagged-features li {
			width: 100%;
		}
		#listing-agent,
		#contact-form {
			width: 100%;
		}

	}

	@media only screen and (max-width: 480px) {
		h1,
		.listing-leadin h1.entry-title {
			font-size: 50px;
		}
		h2 {
			font-size: 38px;
		}
		h3 {
			font-size: 32px;
		}
		h4 {
			font-size: 24px
		}
		h5, h6 {
			font-size: 16px;
		}
		.entry-content ul.navigation {
			position: fixed;
			width: 100%;
			left: 0;
			bottom: 0;
			margin: 0;
			overflow: hidden;
			background: #fff;
			border-top: 1px solid #666;
			z-index: 10;
		}
		ul.navigation li {
			float: left;
			width: 33.3333333%;
			text-align: center;
			margin: 0 !important;
			padding: 0;
			border-right: 1px solid #666;
		}
		.navigation.items-4 li {
			width: 25%;
		}
		.navigation.items-5 li {
			width: 20%;
		}
		.navigation.items-6 li {
			width: 16.6666666%;
		}
		.navigation.items-7 li {
			width: 14.2845555%;
		}
		.entry-content ul.navigation span {
			display: none;
		}
		.entry-content ul.navigation i {
			width: 100%;
			margin: 0 auto;
			display: block;
		}
		.entry-content ul.navigation li a {
			padding: 14px;
			font-size: 18px;
			border: none;
		}
		.entry-content ul.navigation li a:hover,
		.entry-content ul.navigation li a.active {
			background: #efefef;
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

	<div class="entry-content wplistings-single-listing listing-template">

		<div class="listing-leadin">
			<header class="entry-header" style="padding:0px;">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<?php 
				// output listing meta data
				$listing_meta = sprintf( '<ul class="listing-meta">');

				if ( get_post_meta($post->ID, '_listing_hide_price', true) == 1 ) {
					$listing_meta .= (get_post_meta($post->ID, '_listing_price_alt', true)) ? sprintf( '<li class="listing-price">%s</li>', get_post_meta( $post->ID, '_listing_price_alt', true ) ) : '';
				} elseif(get_post_meta($post->ID, '_listing_price', true)) {
		 			$listing_meta .= sprintf( '<li class="listing-price">%s%s %s</li>', $options['wp_listings_currency_symbol'], get_post_meta( $post->ID, '_listing_price', true ), (isset($options['wp_listings_display_currency_code']) && $options['wp_listings_display_currency_code'] == 1) ? '<span class="currency-code">' . $options['wp_listings_currency_code'] . '</span>' : '' );
		 		}


				if ( '' != get_post_meta( $post->ID, '_listing_bedrooms', true ) ) {
					$listing_meta .= sprintf( '<li class="listing-bedrooms">%s <span class="label">Beds</span></li>', get_post_meta( $post->ID, '_listing_bedrooms', true ) );
				}

				if ( '' != get_post_meta( $post->ID, '_listing_bathrooms', true ) ) {
					$listing_meta .= sprintf( '<li class="listing-bathrooms">%s <span class="label">Baths</span></li>', get_post_meta( $post->ID, '_listing_bathrooms', true ) );
				}

				if ( '' != get_post_meta( $post->ID, '_listing_sqft', true ) ) {
					$listing_meta .= sprintf( '<li class="listing-sqft">%s <span class="label">Sq Ft</span></li>', get_post_meta( $post->ID, '_listing_sqft', true ) );
				}

				$listing_meta .= sprintf( '</ul>');

				echo $listing_meta;

				// open house info
				if ( '' != get_post_meta( $post->ID, '_listing_open_house', true ) ) {
					printf( '<p class="listing-open-house">Open House on %s</p>', get_post_meta( $post->ID, '_listing_open_house', true ) );
				}

				?>
			</header><!-- .entry-header -->

			<p class="scroll">
				Scroll
				<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="angle-double-down" class="svg-inline--fa fa-angle-double-down fa-w-10" role="img" viewBox="0 0 320 512" style="height:24px;width:48px;margin:auto;"><path fill="currentColor" d="M143 256.3L7 120.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0L313 86.3c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.4 9.5-24.6 9.5-34 .1zm34 192l136-136c9.4-9.4 9.4-24.6 0-33.9l-22.6-22.6c-9.4-9.4-24.6-9.4-33.9 0L160 352.1l-96.4-96.4c-9.4-9.4-24.6-9.4-33.9 0L7 278.3c-9.4 9.4-9.4 24.6 0 33.9l136 136c9.4 9.5 24.6 9.5 34 .1z"/></svg>
			</p>
		</div>

		<div class="listing-image-wrap" style="margin-top:0px;margin-bottom:0px;">	

			<?php // output featured iamge
			echo get_the_post_thumbnail( $post->ID, 'listings-full', array('class' => 'single-listing-image') ); ?>

			<?php the_excerpt(); ?>

		</div><!-- .listing-image-wrap -->

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


		<div id="listing-content" class="listing-data">

			<div id="listing-description" class="wrap">
				<h3>Description</h3>
				<?php
				the_content( __( 'View more <span class="meta-nav">&rarr;</span>', 'wp-listings' ) );
				echo ( get_post_meta( $post->ID, '_listing_featured_on', true ) ) ? '<p class="wp_listings_featured_on">' . get_post_meta( $post->ID, '_listing_featured_on', true ) . '</p>' : '';

				if ( class_exists( 'Idx_Broker_Plugin' ) && ! empty( $options['wp_listings_display_idx_link'] ) && get_post_meta( $post->ID, '_listing_details_url', true ) ) {
					echo '<a href="' . esc_attr( get_post_meta( $post->ID, '_listing_details_url', true ) ) . '" title="' . esc_attr( get_post_meta( $post->ID, '_listing_mls', true ) ) . '" class="listing-full-details-link">View full listing details</a>';
				}
				?>
			</div><!-- #listing-description -->

			<div id="listing-details" class="wrap">
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
			<div id="listing-gallery" class="wrap">
				<h3>Photos</h3>
				<?php echo do_shortcode(get_post_meta( $post->ID, '_listing_gallery', true)); ?>
			</div><!-- #listing-gallery -->
			<?php } ?>

			<?php if (get_post_meta( $post->ID, '_listing_video', true) != '') { ?>
			<div id="listing-video" class="wrap">
				<h3>Video</h3>
				<div class="iframe-wrap">
				<?php echo do_shortcode(get_post_meta( $post->ID, '_listing_video', true)); ?>
				</div>
			</div><!-- #listing-video -->
			<?php } ?>

			<?php if (get_post_meta( $post->ID, '_listing_school_neighborhood', true) != '') { ?>
			<div id="listing-school-neighborhood" class="wrap">
				<h3>Schools &amp; Neighborhood</h3>
				<p>
				<?php echo do_shortcode(get_post_meta( $post->ID, '_listing_school_neighborhood', true)); ?>
				</p>
			</div><!-- #listing-school-neighborhood -->
			<?php } ?>

			<?php if (get_post_meta( $post->ID, '_listing_map', true) != '') { ?>
			<div id="listing-map" class="wrap">
				<h3>Location Map</h3>
				<?php echo do_shortcode(get_post_meta( $post->ID, '_listing_map', true) ); ?>
			</div><!-- .listing-map -->
			<?php }

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

			<div id="listing-contact"  class="wrap">
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

			<div id="listing-disclaimer" class="wrap">
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

		<a class="link-main-site" href="<?php echo home_url(); ?>">&larr; Back to main site</a>

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
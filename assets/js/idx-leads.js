jQuery(document).ready(function($) {

	// add lead
	$('#add-lead').submit(function(e) {
		e.preventDefault();
		$('button.add-lead').hide();
		$('.mdl-spinner').addClass('is-active');

		var fields = $(this).serialize();
		var nonce = $('button.add-lead').data('nonce');
		var leadurl = IDXLeadAjax.leadurl;

		$.ajax({
			type: 'post',
			url: IDXLeadAjax.ajaxurl,
			data: {
				action: 'idx_lead_add',
				fields: fields,
				nonce: nonce
			},
			success: function( result ) {
				if( $.isNumeric( result ) ) {
					window.location.href = leadurl + result;
				}
			}
		});
		return false;
	});

	// edit lead
	$('#edit-lead').submit(function(e) {
		e.preventDefault();
		$('button.edit-lead').hide();
		$('.mdl-spinner').addClass('is-active');

		var fields = $(this).serialize();
		var nonce = $('button.edit-lead').data('nonce');
		var leadID = $('button.edit-lead').data('lead-id');

		$.ajax({
			type: 'post',
			url: IDXLeadAjax.ajaxurl,
			data: {
				action: 'idx_lead_edit',
				leadID: leadID,
				fields: fields,
				nonce: nonce
			},
			success: function( result ) {
				if( result == 'success' ) {
					window.location.reload(true);
				}
			}
		});
		return false;
	});

	// delete lead
	$(document).on( 'click', '.delete-lead', function() {
		var go_ahead = confirm("Are you sure you want to delete this lead?");
		var id = $(this).data('id');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.lead-row:first');

		if ( go_ahead === true ) {
			$('.mdl-spinner').addClass('is-active');

			$.ajax({
				type: 'post',
				url: IDXLeadAjax.ajaxurl,
				data: {
					action: 'idx_lead_delete',
					nonce: nonce,
					id: id
				},
				success: function( result ) {
					if( result == 'success' ) {
						post.fadeOut( function(){
							post.remove();
						});
					}
				}
			});

			$('.mdl-spinner').removeClass('is-active');
		}
		return false;
	});

	// delete lead note
	$(document).on( 'click', '.delete-note', function() {
		var go_ahead = confirm("Are you sure you want to delete this note?");
		var id = $(this).data('id');
		var noteid = $(this).data('noteid');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.note-row:first');

		if ( go_ahead === true ) {
			$('.mdl-spinner').addClass('is-active');

			$.ajax({
				type: 'post',
				url: IDXLeadAjax.ajaxurl,
				data: {
					action: 'idx_lead_note_delete',
					nonce: nonce,
					id: id,
					noteid: noteid
				},
				success: function( result ) {
					if( result == 'success' ) {
						post.fadeOut( function(){
							post.remove();
						});
					}
				}
			});

			$('.mdl-spinner').removeClass('is-active');
		}
		return false;
	});

	// delete lead saved property
	$(document).on( 'click', '.delete-property', function() {
		var go_ahead = confirm("Are you sure you want to delete this saved property?");
		var id = $(this).data('id');
		var spid = $(this).data('spid');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.property-row:first');

		if ( go_ahead === true ) {
			$('.mdl-spinner').addClass('is-active');

			$.ajax({
				type: 'post',
				url: IDXLeadAjax.ajaxurl,
				data: {
					action: 'idx_lead_property_delete',
					nonce: nonce,
					id: id,
					spid: spid
				},
				success: function( result ) {
					if( result == 'success' ) {
						post.fadeOut( function(){
							post.remove();
						});
					}
				}
			});

			$('.mdl-spinner').removeClass('is-active');
		}
		return false;
	});

	// delete lead saved search
	$(document).on( 'click', '.delete-search', function() {
		var go_ahead = confirm("Are you sure you want to delete this saved search?");
		var id = $(this).data('id');
		var ssid = $(this).data('ssid');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.search-row:first');

		if ( go_ahead === true ) {
			$('.mdl-spinner').addClass('is-active');

			$.ajax({
				type: 'post',
				url: IDXLeadAjax.ajaxurl,
				data: {
					action: 'idx_lead_search_delete',
					nonce: nonce,
					id: id,
					ssid: ssid
				},
				success: function( result ) {
					if( result == 'success' ) {
						post.fadeOut( function(){
							post.remove();
						});
					}
				}
			});

			$('.mdl-spinner').removeClass('is-active');
		}
		return false;
	});

	// delete all leads
	$(document).on( 'click', '.delete-selected', function() {
		var go_ahead = confirm("Are you sure you want to delete these leads?");
		var id = $(this).data('id');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.lead-row:first');

		if ( go_ahead === true ) {
			$.ajax({
				type: 'post',
				url: IDXLeadAjax.ajaxurl,
				data: {
					action: 'idx_lead_delete_all',
					nonce: nonce,
					id: id
				},
				success: function( result ) {
					if( result == 'success' ) {
						post.fadeOut( function(){
							post.remove();
						});
					}
				}
			});
		}
		return false;
	});

});
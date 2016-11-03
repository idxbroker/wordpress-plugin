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

	// add lead note
	$('form.add-lead-note').submit(function(e) {
		e.preventDefault();
		$('button.add-note').hide();
		$('.mdl-spinner').addClass('is-active');

		var note = $(this).serialize();
		var id = $('button.add-note').data('id');
		var nonce = $('button.add-note').data('nonce');
		var display_note = $('#note').val();
		
		$.ajax({
			type: 'post',
			url: IDXLeadAjax.ajaxurl,
			data: {
				action: 'idx_lead_note_add',
				note: note,
				id: id,
				nonce: nonce
			},
			success: function( result ) {
				if( $.isNumeric( result ) ) {
					var note_row = '<tr class="note-row"><td class="mdl-data-table__cell--non-numeric">Just Now</td><td class="mdl-data-table__cell--non-numeric note"><div class="render-note">' + display_note + '</div></td><td class="mdl-data-table__cell--non-numeric"><a href="#" id="delete-note-' + result + '" class="delete-note" data-id="' + id + '" data-noteid="' + result + '" data-nonce="' + nonce + '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-note-' + result + '">Delete Note</div></a></td></tr>';

					$('.mdl-data-table tbody').prepend( note_row );
					$('.mdl-spinner').removeClass('is-active');
					$('button.add-note').show();
					$('#note').val('');
					tb_remove();
				}
			}
		});
		return false;
	});

	// add lead property
	$('form.add-lead-property').submit(function(e) {
		e.preventDefault();
		$('button.add-property').hide();
		$('.mdl-spinner').addClass('is-active');

		var id = $('button.add-property').data('id');
		var nonce = $('button.add-property').data('nonce');

		var property_name = $('#propertyName').val();
		var idxid = $('#idxID').val();
		var listingid = $('#listingID').val();
		var updates = $('#receiveUpdates').val();

		var detailsurl = IDXLeadAjax.detailsurl;
		
		if(updates === 'on') {
			updates = 'y';
		} else {
			updates ='n';
		}

		$.ajax({
			type: 'post',
			url: IDXLeadAjax.ajaxurl,
			data: {
				action: 'idx_lead_property_add',
				id: id,
				property_name: property_name,
				idxid: idxid,
				listingid: listingid,
				updates: updates,
				nonce: nonce
			},
			success: function( result ) {
				if( $.isNumeric( result ) ) {
					var property_row = '<tr class="property-row"><td class="mdl-data-table__cell--non-numeric property"><a href="' + detailsurl + '/' + id + '/' + result + '">' + property_name + '<div class="mdl-tooltip" data-mdl-for="view-property-' + result + '">View Property</div></a></td><td class="mdl-data-table__cell--non-numeric">' + updates + '</td><td class="mdl-data-table__cell--non-numeric">Just Now</td><td class="mdl-data-table__cell--non-numeric"><a href="#" id="delete-property-' + result + '" class="delete-property" data-id="' + id + '" data-listingid="' + result + '" data-nonce="' + nonce + '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-property-' + result + '">Delete Note</div></a><a href="https://middleware.idxbroker.com/mgmt/addeditsavedprop.php?id=' + id + '&spid=' + result + '" id="edit-mw-' + result + '" target="_blank"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' + result + '">Edit Property in Middleware</div></a></td></tr>';

					$('.mdl-data-table tbody').prepend( property_row );
					$('.mdl-spinner').removeClass('is-active');
					$('button.add-property').show();
					$('#propertyName, #listingID').val('');
					tb_remove();
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

});
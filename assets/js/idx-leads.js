jQuery(document).ready(function($) {

	// Initialize datatables
	
	$('.mdl-data-table.leads').DataTable( {
		"pagingType": "full_numbers",
		"bSort": true,
		"bPaginate":true,
		"iDisplayLength": 10,
		"order": [[ 3, "desc" ]],
		"columnDefs": [
			{
				"targets": [ 0, 1, 2, 3, 4, 5, 6 ],
				"className": 'mdl-data-table__cell--non-numeric'
			},
			{
				"targets": [ 1, 2, 6 ],
				"orderable": false
			}
		],
		"dom": '<"table-filter"f>rt<"lead-table-controls mdl-shadow--2dp"lip>',
		"oLanguage": {
			"sSearch": ""
		},
		"sScrollX": "100%",
		"sScrollXInner": "98%"
	} );

	$('.mdl-data-table.lead-notes').DataTable( {
		"pagingType": "full_numbers",
		"bSort": true,
		"bPaginate":true,
		"iDisplayLength": 10,
		"order": [[ 0, "desc" ]],
		"columnDefs": [
			{
				"targets": [ 0, 1, 2 ],
				"className": 'mdl-data-table__cell--non-numeric'
			},
			{
				"targets": [ 1, 2 ],
				"orderable": false
			}
		],
		"dom": '<"table-filter"f>rt<"lead-table-controls mdl-shadow--2dp"lip>',
		"oLanguage": {
			"sSearch": ""
		},
		"sScrollX": "100%",
		"sScrollXInner": "98%"
	} );

	$('.mdl-data-table.lead-properties').DataTable( {
		"pagingType": "full_numbers",
		"bSort": true,
		"bPaginate":true,
		"iDisplayLength": 10,
		"order": [[ 2, "desc" ]],
		"columnDefs": [
			{
				"targets": [ 0, 1, 2, 3 ],
				"className": 'mdl-data-table__cell--non-numeric'
			},
			{
				"targets": [ 1, 3 ],
				"orderable": false
			}
		],
		"dom": '<"search"f>rt<"lead-table-controls mdl-shadow--2dp"lip>',
		"oLanguage": {
			"sSearch": ""
		},
		"sScrollX": "100%",
		"sScrollXInner": "98%"
	} );

	$('.mdl-data-table.lead-searches').DataTable( {
		"pagingType": "full_numbers",
		"bSort": true,
		"bPaginate":true,
		"iDisplayLength": 10,
		"order": [[ 2, "desc" ]],
		"columnDefs": [
			{
				"targets": [ 0, 1, 2, 3 ],
				"className": 'mdl-data-table__cell--non-numeric'
			},
			{
				"targets": [ 1, 3 ],
				"orderable": false
			}
		],
		"dom": '<"search"f>rt<"lead-table-controls mdl-shadow--2dp"lip>',
		"oLanguage": {
			"sSearch": ""
		},
		"sScrollX": "100%",
		"sScrollXInner": "98%"
	} );

	$('.mdl-data-table.lead-traffic').DataTable( {
		"pagingType": "full_numbers",
		"bSort": true,
		"bPaginate":true,
		"iDisplayLength": 10,
		"order": [[ 0, "desc" ]],
		"columnDefs": [
			{
				"targets": [ 0, 1, 2 ],
				"className": 'mdl-data-table__cell--non-numeric'
			}
		],
		"dom": '<"search"f>rt<"lead-table-controls mdl-shadow--2dp"lip>',
		"oLanguage": {
			"sSearch": ""
		},
		"sScrollX": "100%",
		"sScrollXInner": "98%"
	} );

	// add mdl classes to table length select
	$('.dataTables_length').addClass('mdl-selectfield');
	$('.dataTables_length select').addClass('mdl-selectfield__select');

	// add placeholder to table filter
	$('.dataTables_filter input').attr({
		placeholder: 'Search'
	});

	// add lead
	$('#add-lead').submit(function(e) {
		e.preventDefault();

		var firstname = $('#firstName').val();
		if(firstname === '') {
			$('#firstName').parent().addClass('is-dirty');
			$('#firstName').focus();
			$('#add-lead .error-incomplete').show();
			return false;
		}
		var lastname = $('#lastName').val();
		if(lastname === '') {
			$('#lastName').parent().addClass('is-dirty');
			$('#lastName').focus();
			$('#add-lead .error-incomplete').show();
			return false;
		}
		var email = $('#email').val();
		if(email === '') {
			$('#email').parent().addClass('is-dirty');
			$('#email').focus();
			$('#add-lead .error-incomplete').show();
			return false;
		}

		$('#add-lead .error-existing').hide();
		$('#add-lead .error-fail').hide();
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
				} else if (result === 'Lead already exists.') {
					$('#add-lead .error-existing').show();
					$('button.add-lead').show();
					$('.mdl-spinner').removeClass('is-active');
				} else {
					$('#add-lead .error-fail').show();
					$('button.add-lead').show();
					$('.mdl-spinner').removeClass('is-active');
				}
			}
		});
		return false;
	});

	// edit lead
	$('#edit-lead').submit(function(e) {
		e.preventDefault();

		var firstname = $('#firstName').val();
		if(firstname === '') {
			$('#firstName').parent().addClass('is-dirty');
			$('#firstName').focus();
			$('#edit-lead .error-incomplete').show();
			return false;
		}
		var lastname = $('#lastName').val();
		if(lastname === '') {
			$('#lastName').parent().addClass('is-dirty');
			$('#lastName').focus();
			$('#edit-lead .error-incomplete').show();
			return false;
		}
		var email = $('#email').val();
		if(email === '') {
			$('#email').parent().addClass('is-dirty');
			$('#email').focus();
			$('#edit-lead .error-incomplete').show();
			return false;
		}
		if(validateEmail(email) === false) {
			$('#email').parent().addClass('is-dirty');
			$('#email').focus();
			$('#edit-lead .error-invalid-email').show();
			return false;
		}

		$('#edit-lead .error-fail').hide();
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
				} else {
					$('#edit-lead .error-fail').show();
					$('button.edit-lead').show();
					$('.mdl-spinner').removeClass('is-active');
				}
			}
		});
		return false;
	});

	// add lead note
	$('form.add-lead-note').submit(function(e) {
		e.preventDefault();

		var notefield = $('.add-lead-note #note').val();

		if(!notefield) {
			$('.add-lead-note #note').parent().addClass('is-dirty');
			$('.add-lead-note #note').focus();
			$('.add-lead-note .error-incomplete').show();
			return false;
		} else {
			$('.add-lead-note .error-incomplete').hide();
			$('.add-lead-note .error-fail').hide();
		}

		$('button.add-note').hide();
		$('.mdl-spinner').addClass('is-active');

		var note = $(this).serialize();
		var id = $('button.add-note').data('id');
		var nonce = $('button.add-note').data('nonce');
		var display_note = $('form.add-lead-note #note').val();
		
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

					$('.mdl-data-table.lead-notes tbody').prepend( note_row );
					$('.mdl-spinner').removeClass('is-active');
					$('button.add-note').show();
					$('form.add-lead-note #note').val('');
					tb_remove();
				} else {
					$('.add-lead-note .error-fail').show();
				}
			}
		});
		return false;
	});

	// edit lead note
	$('a.edit-note').click( function() {
		var note = $(this).data('note');
		var noteid = $(this).data('noteid');

		$('button.edit-note').attr('data-noteid', noteid);
		$('form.edit-lead-note #note').focus();
		$('form.edit-lead-note #note').val(note);
	});

	$('form.edit-lead-note').submit(function(e) {
		e.preventDefault();

		var notefield = $('.edit-lead-note #note').val();

		if(!notefield) {
			$('.edit-lead-note #note').parent().addClass('is-dirty');
			$('.edit-lead-note #note').focus();
			$('.edit-lead-note .error-incomplete').show();
			return false;
		} else {
			$('.edit-lead-note .error-incomplete').hide();
			$('.edit-lead-note .error-fail').hide();
		}

		$('button.edit-note').hide();
		$('.mdl-spinner').addClass('is-active');

		var note = $(this).serialize();
		var id = $('button.edit-note').data('id');
		var noteid = $('button.edit-note').data('noteid');
		var nonce = $('button.edit-note').data('nonce');
		var display_note = $('form.edit-lead-note #note').val();
		
		$.ajax({
			type: 'post',
			url: IDXLeadAjax.ajaxurl,
			data: {
				action: 'idx_lead_note_edit',
				note: note,
				id: id,
				noteid: noteid,
				nonce: nonce
			},
			success: function( result ) {
				if( result == 'success' ) {
					var note_row = '<tr class="note-row note-id' + noteid + '"><td class="mdl-data-table__cell--non-numeric">Just Now</td><td class="mdl-data-table__cell--non-numeric note"><div class="render-note+' + noteid + '">' + display_note + '</div></td><td class="mdl-data-table__cell--non-numeric"><a href="#" id="delete-note-' + noteid + '" class="delete-note" data-id="' + id + '" data-noteid="' + noteid + '" data-nonce="' + nonce + '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-note-' + noteid + '">Delete Note</div></a></td></tr>';
					$('tr.note-id-' + noteid).remove();
					$('.mdl-data-table.lead-notes tbody').prepend( note_row );
					$('.mdl-spinner').removeClass('is-active');
					$('button.edit-note').show();
					$('form.edit-lead-note #note').val('');
					tb_remove();
				} else {
					$('.edit-lead-note .error-fail').show();
				}
			}
		});
		return false;
	});

	// add lead property
	$('form.add-lead-property').submit(function(e) {
		e.preventDefault();
		
		var id = $('button.add-property').data('id');
		var nonce = $('button.add-property').data('nonce');
		var property_name = $('.add-lead-property #propertyName').val();
		var idxid = $('.add-lead-property #idxID').val();
		var listingid = $('.add-lead-property #listingID').val();
		var detailsurl = IDXLeadAjax.detailsurl;

		if(!property_name) {
			$('.add-lead-property #propertyName').parent().addClass('is-dirty');
			$('.add-lead-property #propertyName').focus();
			$('.add-lead-property .error-incomplete').show();
			return false;
		} if(!idxid) {
			$('.add-lead-property #idxID').parent().addClass('is-dirty');
			$('.add-lead-property #idxID').focus();
			$('.add-lead-property .error-incomplete').show();
			return false;
		} if(!listingid) {
			$('.add-lead-property #listingID').parent().addClass('is-dirty');
			$('.add-lead-property #listingID').focus();
			$('.add-lead-property .error-incomplete').show();
			return false;
		} else {
			$('.add-lead-property .error-incomplete').hide();
			$('.add-lead-property .error-fail').hide();
		}

		$('button.add-property').hide();
		$('.mdl-spinner').addClass('is-active');

		if($('.add-lead-property #receiveUpdates-add').parent().hasClass('is-checked')) {
			updates = 'y';
			update_nice = 'Yes';
		} else {
			updates = 'n';
			update_nice = 'No';
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
					var property_row = '<tr class="property-row"><td class="mdl-data-table__cell--non-numeric property"><a href="' + detailsurl + '/' + id + '/' + result + '">' + property_name + '<div class="mdl-tooltip" data-mdl-for="view-property-' + result + '">View Property</div></a></td><td class="mdl-data-table__cell--non-numeric">' + update_nice + '</td><td class="mdl-data-table__cell--non-numeric">Just Now</td><td class="mdl-data-table__cell--non-numeric"><a href="#" id="delete-property-' + result + '" class="delete-property" data-id="' + id + '" data-listingid="' + result + '" data-nonce="' + nonce + '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-property-' + result + '">Delete Note</div></a><a href="https://middleware.idxbroker.com/mgmt/addeditsavedprop.php?id=' + id + '&spid=' + result + '" id="edit-mw-' + result + '" target="_blank"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' + result + '">Edit Property in Middleware</div></a></td></tr>';

					$('.mdl-data-table.lead-properties tbody').prepend( property_row );
					$('.mdl-spinner').removeClass('is-active');
					$('button.add-property').show();
					$('#propertyName, #listingID').val('');
					tb_remove();
				} else {
					$('.add-lead-property .error-fail').show();
				}
			}
		});
		return false;
	});

	// edit lead property
	$('a.edit-property').click( function() {
		var name = $(this).data('name');
		var spid = $(this).data('spid');
		var listingid = $(this).data('listingid');
		var idxid = $(this).data('idxid');
		var updates = $(this).data('updates');
		var nonce = $(this).data('nonce');

		$('button.edit-property').attr('data-spid', spid);
		$('button.edit-property').attr('data-nonce', nonce);

		$('form.edit-lead-property #propertyName').focus();
		$('form.edit-lead-property #propertyName').val(name);
		$('form.edit-lead-property #listingID').val(listingid);
		
		if(updates == 'y') {
			$('form.edit-lead-property #receiveUpdates-edit').prop('checked', true);
		}

		$('form.edit-lead-property #idxID option').each(function(){
			if($(this).val() == idxid) {
				$(this).attr('selected','selected');
			}
		});
	});

	$('form.edit-lead-property').submit(function(e) {
		e.preventDefault();

		$('button.edit-property').hide();
		$('.mdl-spinner').addClass('is-active');

		var id = $('button.edit-property').data('id');
		var name = $('form.edit-lead-property #propertyName').val();
		var listingid = $('form.edit-lead-property #listingID').val();
		var idxid = $('form.edit-lead-property #idxID').val();
		var spid = $('button.edit-property').data('spid');
		var nonce = $('button.edit-property').data('nonce');
		var detailsurl = IDXLeadAjax.detailsurl;
		
		if(!name) {
			$('.edit-lead-property #propertyName').parent().addClass('is-dirty');
			$('.edit-lead-property #propertyName').focus();
			$('.edit-lead-property .error-incomplete').show();
			return false;
		} if(!idxid) {
			$('.edit-lead-property #idxID').parent().addClass('is-dirty');
			$('.edit-lead-property #idxID').focus();
			$('.edit-lead-property .error-incomplete').show();
			return false;
		} if(!listingid) {
			$('.edit-lead-property #listingID').parent().addClass('is-dirty');
			$('.edit-lead-property #listingID').focus();
			$('.edit-lead-property .error-incomplete').show();
			return false;
		} else {
			$('.edit-lead-property .error-incomplete').hide();
			$('.edit-lead-property .error-fail').hide();
		}

		if($('.edit-lead-property #receiveUpdates-edit').parent().hasClass('is-checked')) {
			updates = 'y';
			update_nice = 'Yes';
		} else {
			updates = 'n';
			update_nice = 'No';
		}

		$.ajax({
			type: 'post',
			url: IDXLeadAjax.ajaxurl,
			data: {
				action: 'idx_lead_property_edit',
				id: id,
				name: name,
				listingid: listingid,
				idxid: idxid,
				spid: spid,
				updates: updates,
				nonce: nonce
			},
			success: function( result ) {
				if( result == 'success' ) {
					var property_row = '<tr class="property-row property-id' + spid + '"><td class="mdl-data-table__cell--non-numeric"><a href="' + detailsurl + '/' + idxid + '/' + listingid + '">' + name + '</a></td><td class="mdl-data-table__cell--non-numeric property">' + update_nice +'</td><td class="mdl-data-table__cell--non-numeric">Just Now</td><td class="mdl-data-table__cell--non-numeric"><a href="#" id="delete-property-' + spid + '" class="delete-property" data-id="' + id + '" data-spid="' + spid + '" data-nonce="' + nonce + '"><i class="material-icons md-18">delete</i><div class="mdl-tooltip" data-mdl-for="delete-property-' + spid + '">Delete Property</div></a><a href="https://middleware.idxbroker.com/mgmt/addeditsavedprop.php?id=' + id + '&spid=' + spid + '" id="edit-mw-' + spid + '" target="_blank"><i class="material-icons md-18">exit_to_app</i><div class="mdl-tooltip" data-mdl-for="edit-mw-' + spid + '">Edit Property in Middleware</div></a></td></tr>';
					$('tr.property-id-' + spid).remove();
					$('.mdl-data-table.lead-properties tbody').prepend( property_row );
					$('.mdl-spinner').removeClass('is-active');
					$('button.edit-property').show();
					tb_remove();
				} else {
					$('.edit-lead-property .error-fail').show();
				}
			}
		});
		return false;
	});

	// delete lead
	$(document).on( 'click', '.delete-lead', function() {
		var dialog = document.getElementById('dialog-lead-delete');
		dialogPolyfill.registerDialog(dialog);
		dialog.showModal();

		var id = $(this).data('id');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.lead-row:first');

		dialog.addEventListener('close', function (event) {
			if (dialog.returnValue == 'yes') {
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
		});
		return false;
	});

	// delete lead note
	$(document).on( 'click', '.delete-note', function() {
		var dialog = document.getElementById('dialog-lead-note-delete');
		dialogPolyfill.registerDialog(dialog);
		dialog.showModal();

		var id = $(this).data('id');
		var noteid = $(this).data('noteid');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.note-row:first');

		dialog.addEventListener('close', function (event) {
			if (dialog.returnValue == 'yes') {
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
		});
		
		return false;
	});

	// delete lead saved property
	$(document).on( 'click', '.delete-property', function() {
		var dialog = document.getElementById('dialog-lead-property-delete');
		dialogPolyfill.registerDialog(dialog);
		dialog.showModal();

		var id = $(this).data('id');
		var spid = $(this).data('spid');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.property-row:first');

		dialog.addEventListener('close', function (event) {
			if (dialog.returnValue == 'yes') {
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
		});
		return false;
	});

	// delete lead saved search
	$(document).on( 'click', '.delete-search', function() {
		var dialog = document.getElementById('dialog-lead-search-delete');
		dialogPolyfill.registerDialog(dialog);
		dialog.showModal();

		var id = $(this).data('id');
		var ssid = $(this).data('ssid');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.search-row:first');

		dialog.addEventListener('close', function (event) {
			if (dialog.returnValue == 'yes') {
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
		});
		return false;
	});

	// validate email
	function validateEmail(email) {
		var re = /\S+@\S+\.\S+/;
		return re.test(email);
	}

});
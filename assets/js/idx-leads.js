jQuery(document).ready(function($) {

	// add lead
	$('#add-lead').submit(function(e) {
		e.preventDefault();

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
		}
		return false;
	});

});
jQuery(document).ready(function($) {

	// Initialize datatables
	
	$('.mdl-data-table.searches').DataTable( {
		"pagingType": "full_numbers",
		"bSort": true,
		"bPaginate":true,
		"iDisplayLength": 10,
		"order": [[ 1, "desc" ]],
		"columnDefs": [
			{
				"targets": [ 0, 1, 2, 3 ],
				"className": 'mdl-data-table__cell--non-numeric'
			},
			{
				"targets": [ 3 ],
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

	// add mdl classes to table length select
	$('.dataTables_length').addClass('mdl-selectfield');
	$('.dataTables_length select').addClass('mdl-selectfield__select');

	// add placeholder to table filter
	$('.dataTables_filter input').attr({
		placeholder: 'Search'
	});

	// add search (saved link)
	$('#add-search').submit(function(e) {
		e.preventDefault();

		var ccz = $('#ccz').select2().find(':selected').data('ccz');
		var locations = $('#ccz').select2().val();
		var pt = $('#pt').val();
		var lp = $('#lp').val();
		var hp = $('#hp').val();
		var bd = $('#bd').val();
		var ba = $('#ba').val();
		var sqft = $('#sqft').val();
		var acres = $('#acres').val();
		var add = $('#add').val();

		var pageTitle = $('#pageTitle').val();
		if(pageTitle === '') {
			$('#pageTitle').parent().addClass('is-dirty');
			$('#pageTitle').focus();
			$('#add-search .error-incomplete').show();
			return false;
		}
		var linkTitle = $('#linkTitle').val();
		if(linkTitle === '') {
			$('#linkTitle').parent().addClass('is-dirty');
			$('#linkTitle').focus();
			$('#add-search .error-incomplete').show();
			return false;
		}

		$('#add-search .error-existing').hide();
		$('#add-search .error-fail').hide();
		$('button.add-search').hide();
		$('.mdl-spinner').addClass('is-active');

		var descriptionMeta = $('#descriptionMeta').val();
		var keywords = $('#keywords').val();
		var linkCopy = $('#linkCopy').val();
		var agentID = $('#agentID').val();

		if($('#useDescriptionMeta').parent().hasClass('is-checked')) {
			useDescriptionMeta = 'y';
		} else {
			useDescriptionMeta = 'n';
		}

		if($('#useKeywordsMeta').parent().hasClass('is-checked')) {
			useKeywordsMeta = 'y';
		} else {
			useKeywordsMeta = 'n';
		}

		if($('#featured').parent().hasClass('is-checked')) {
			featured = 'y';
		} else {
			featured = 'n';
		}

		var nonce = $('button.add-search').data('nonce');
		var searchesurl = IDXSearchAjax.searchesurl;

		$.ajax({
			type: 'post',
			url: IDXSearchAjax.ajaxurl,
			data: {
				action: 'idx_search_add',
				pt: pt,
				ccz: ccz,
				locations: locations,
				lp: lp,
				hp: hp,
				bd: bd,
				ba: ba,
				sqft: sqft,
				acres: acres,
				add: add,
				pageTitle: pageTitle,
				linkTitle: linkTitle,
				useDescriptionMeta: useDescriptionMeta,
				descriptionMeta: descriptionMeta,
				useKeywordsMeta: useKeywordsMeta,
				keywords: keywords,
				featured: featured,
				linkCopy: linkCopy,
				agentID: agentID,
				nonce: nonce
			},
			success: function( result ) {
				if( $.isNumeric( result ) ) {
					window.location.href = searchesurl;
				} else {
					$('#add-search .error-fail').show();
					$('button.add-search').show();
					$('.mdl-spinner').removeClass('is-active');
				}
			}
		});
		return false;
	});

	// add lead search
	$('#add-lead-search').submit(function(e) {
		e.preventDefault();

		var ccz = $('#ccz').select2().find(':selected').data('ccz');
		var locations = $('#ccz').select2().val();
		var pt = $('#pt').val();
		var lp = $('#lp').val();
		var hp = $('#hp').val();
		var bd = $('#bd').val();
		var ba = $('#ba').val();
		var sqft = $('#sqft').val();
		var acres = $('#acres').val();
		var add = $('#add').val();

		$('#add-lead-search .error-existing').hide();
		$('#add-lead-search .error-fail').hide();
		$('button.add-lead-search').hide();
		$('.mdl-spinner').addClass('is-active');

		if($('#receiveUpdates').parent().hasClass('is-checked')) {
			receiveUpdates = 'y';
		} else {
			receiveUpdates = 'n';
		}

		var leadID = $('#leadID').val();
		var searchName = $('#searchName').val();
		var receiveUpdates = $('#receiveUpdates').val();

		var nonce = $('button.add-lead-search').data('nonce');
		var leadurl = IDXSearchAjax.leadurl;

		$.ajax({
			type: 'post',
			url: IDXSearchAjax.ajaxurl,
			data: {
				action: 'idx_lead_search_add',
				pt: pt,
				ccz: ccz,
				locations: locations,
				lp: lp,
				hp: hp,
				bd: bd,
				ba: ba,
				sqft: sqft,
				acres: acres,
				add: add,
				searchName: searchName,
				receiveUpdates: receiveUpdates,
				leadID: leadID,
				nonce: nonce
			},
			success: function( result ) {
				if( $.isNumeric( result ) ) {
					window.location.href = leadurl + leadID + '#lead-searches';
					$('#lead-searches').delay(1000).addClass('is-active');
				} else {
					$('#add-search .error-fail').show();
					$('button.add-search').show();
					$('.mdl-spinner').removeClass('is-active');
				}
			}
		});
		return false;
	});

	// delete search
	$(document).on( 'click', '.delete-search', function() {
		var dialog = document.getElementById('dialog-search-delete');
		dialogPolyfill.registerDialog(dialog);
		dialog.showModal();

		var ssid = $(this).data('ssid');
		var nonce = $(this).data('nonce');
		var post = $(this).parents('.search-row:first');

		dialog.addEventListener('close', function (event) {
			if (dialog.returnValue == 'yes') {
				$('.mdl-spinner').addClass('is-active');

				$.ajax({
					type: 'post',
					url: IDXSearchAjax.ajaxurl,
					data: {
						action: 'idx_search_delete',
						nonce: nonce,
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
	
	// init select2 on ccz list
	$('#ccz').select2({
		placeholder: "Enter City, County, or Postal Code"
	});

	// Disable other optgroups after one has been selected
	$('#ccz').on('select2:select', function() {
		selectedOptGroup = $('#ccz').select2().find(':selected').parent('optgroup');
		otherOptGroups = $('#ccz').find('optgroup').not(selectedOptGroup);
		$(otherOptGroups).find('option').prop('disabled', true);
	});

});


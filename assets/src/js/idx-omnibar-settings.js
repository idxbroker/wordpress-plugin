window.addEventListener('DOMContentLoaded', function(){

    if(typeof loadOmnibarView !== 'undefined'){
        jQuery.post(
            ajaxurl, {
            'action': 'idx_preload_omnibar_settings_view'
            }, function(){window.location.reload();}
        );
    }

    jQuery('.select2').select2({
        maximumSelectionLength: 10,
        placeholder: 'Select Up to Ten Fields'
    });

    var saveButton = document.querySelectorAll('#save_changes')[0];
    var ajax_load = "<span class='ajax'></span>";

    if(typeof saveButton !== 'undefined'){
        saveButton.addEventListener('click', function(event){
        event.preventDefault();
        jQuery('.status').fadeIn('fast').html(ajax_load + 'Saving Settings...');
        updateOmnibarCurrentCcz();
        });
    }



    function updateOmnibarCurrentCcz(){
        if(typeof document.querySelectorAll('.city-list')[0] === 'undefined'){
            return;
        }
        var city = jQuery('.city-list select').val();
        var county = jQuery('.county-list select').val();
        var zipcode = jQuery('.zipcode-list select').val();
         jQuery.post(
                ajaxurl, {
                'action': 'idx_update_omnibar_current_ccz',
                'city-list': city,
                'county-list': county,
                'zipcode-list': zipcode
        }, function(){updateOmnibarCustomFields();});
    }


    function updateOmnibarCustomFields(){
        var customField = document.querySelectorAll('.omnibar-additional-custom-field')[0];
        if(customField.options === undefined){
            return;
        }
        var customFieldValues = [];
        for (var i = 0; i < customField.options.length; i++) {
            if(customField.options[i].selected){
                var idxID = customField.options[i].parentNode.classList[0];
                var value = customField.options[i].value;
                var fieldName = customField.options[i].label;
                var mlsPtID = customField.options[i].getAttribute('data-mlsptid');
                var fieldObject = {'idxID': idxID, 'value': value, 'mlsPtID': mlsPtID, 'name': fieldName};
                customFieldValues.push(fieldObject);
            }
        }
        mlsPtID = document.querySelectorAll('.omnibar-mlsPtID');
        var mlsPtIDs = [];
        for (var i = 0; i < mlsPtID.length; i++) {
                var idxID = mlsPtID[i].name;
                var value = mlsPtID[i].value;
                var mlsPtObject = {'idxID': idxID, 'mlsPtID': value};
                mlsPtIDs.push(mlsPtObject);
        }
        var placeholder = document.querySelectorAll('.omnibar-placeholder')[0].value;
         jQuery.post(
                ajaxurl, {
                'action': 'idx_update_omnibar_custom_fields',
                'fields': customFieldValues,
                'mlsPtIDs': mlsPtIDs,
                'placeholder': placeholder
        }, function(){window.location.reload();});
    }
});

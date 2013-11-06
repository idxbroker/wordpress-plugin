jQuery(document).ready(function(){
    
    jQuery('#web_services_error').css('display','block');
    
    // ajax loading gif
    var ajax_load = "<span class='ajax'></span>";
    
    // path to the admin ajax file
    var ajaxPath = jQuery('.update_idxlinks').attr('ajax');

    // url of the blog
    var blogUrl = jQuery('#blogUrl').attr('ajax');
    
    // when the save changes button is clicked
    
    jQuery('#save_changes').click(function(event){
    	jQuery('#action_mode').val('');
        // prevent the default action as we need to save the links to the db first.
        event.preventDefault();
        
        var apikey = jQuery('#idx_broker_apikey').val();
        var submit = true;
 
        if (apikey == '') {
        	jQuery('#idx_broker_apikey').focus();
            jQuery('#idx_broker_apikey').parents('li').css('background', '#FDB7B7');
            jQuery('#idx_broker_apikey_error').css('display', 'block');
            submit = false;
        } else {
            jQuery('#idx_broker_apikey').parents('li').css('background', 'none');
            jQuery('#idx_broker_apikey_error').css('display', 'none');
        }
        
        if(submit == true) {

        	var status = jQuery('.status');
            // give the user a pseudo status console so they know something is happening
            status.fadeIn('fast').html(ajax_load+'Saving Links...');

            // need to get the custom links from the form, if any
            if(jQuery('.systemLink').size() > 0 || jQuery('.savedLink').size() > 0) {
            	jQuery('[name=action]').val('idx_update_links');
            	params = jQuery('#idx_broker_options').serialize()+'&'+jQuery.param({ 'save_action': 'all_links' });
            	//params = params+''
            	jQuery.ajax({
    		  		type: "POST",
    		   		url: ajaxPath,
    		   		data: params,
    		   		success: function(data) {
            			jQuery('[name=action]').val('update');
            			status.fadeIn('slow').html(ajax_load+'Saving Options...');
            			jQuery('#idx_broker_options').submit();
    			  	}		   
    	 		});
            } else {
            	status.fadeIn('slow').html(ajax_load+'Saving Options...');
            	jQuery('#idx_broker_options').submit();
            }
        }
    });
    
    jQuery('#save_systemlinks').click(function(event) {
    	
   	 	var apikey = jQuery('#idx_broker_apikey').val();
        var submit = true;
 
        if (apikey == '') {
        	jQuery('#idx_broker_apikey').focus();
            jQuery('#idx_broker_apikey').parents('li').css('background', '#FDB7B7');
            jQuery('#idx_broker_apikey_error').css('display', 'block');
            submit = false;
        } else {
            jQuery('#idx_broker_apikey').parents('li').css('background', 'none');
            jQuery('#idx_broker_apikey_error').css('display', 'none');
        }
	   	if(submit == true) { 
	    	event.preventDefault();
	    	jQuery('[name=action]').val('idx_update_systemlinks');
	    	
	    	jQuery('#action_mode').val('systemlinks');
	    	
	    	var status = jQuery(this).siblings('.system_status');
	    	var ajax_url = jQuery('#save_changes').attr('ajax');
	    	var ajax_load = "<span class='ajax'></span>";
	    	params = jQuery('#idx_broker_options').serialize()+'&'+jQuery.param({ 'save_action': 'systemlinks' });
	    	status.fadeIn('fast').html(ajax_load+'Refreshing API...');
	    	jQuery.ajax({
		  		type: "POST",
		   		url: ajax_url,
		   		data: params,
		   		success: function(data) {
	    			status.fadeIn('fast').html(ajax_load+'Refreshing Links...');
	    			jQuery('[name=action]').val('update');
	    			jQuery('#idx_broker_options').submit();
			  	}		   
	 		});
	   	}
   });  
    jQuery('#save_savedlinks').click(function(event) {
    	
      	 var apikey = jQuery('#idx_broker_apikey').val();
         var submit = true;
    
           if (apikey == '') {
           	jQuery('#idx_broker_apikey').focus();
               jQuery('#idx_broker_apikey').parents('li').css('background', '#FDB7B7');
               jQuery('#idx_broker_apikey_error').css('display', 'block');
               submit = false;
           } else {
               jQuery('#idx_broker_apikey').parents('li').css('background', 'none');
               jQuery('#idx_broker_apikey_error').css('display', 'none');
           }
   	   	if(submit == true) { 
   	    	event.preventDefault();
   	    	jQuery('[name=action]').val('idx_update_savedlinks');
   	    	
   	    	jQuery('#action_mode').val('savedlinks');
   	    	
   	    	var status = jQuery(this).siblings('.saved_status');
   	    	var ajax_url = jQuery('#save_changes').attr('ajax');
   	    	var ajax_load = "<span class='ajax'></span>";
   	    	params = jQuery('#idx_broker_options').serialize()+'&'+jQuery.param({ 'save_action': 'savedlinks' });
   	    	status.fadeIn('fast').html(ajax_load+'Refreshing API...');
   	    	jQuery.ajax({
   		  		type: "POST",
   		   		url: ajax_url,
   		   		data: params,
   		   		success: function(data) {
   	    			status.fadeIn('fast').html(ajax_load+'Refreshing Links...');
   	    			jQuery('[name=action]').val('update');
   	    			jQuery('#idx_broker_options').submit();
   			  	}		   
   	 		});
   	   	}
      });  
    
    // select/deselect all link functionality
    
    jQuery('#idx_systemlink_group').click(function(event){
        jQuery('.idx_platinum_sl').attr('checked', jQuery(this).is(':checked'));
    });

    jQuery('#idx_savedlink_group').click(function(event){
        jQuery('.idx_platinum_sdl').attr('checked', jQuery(this).is(':checked'));
    });
    
    jQuery('#api_update').click(function(event) {
    	
    	 var apikey = jQuery('#idx_broker_apikey').val();
         var submit = true;
  
         if (apikey == '') {
         	jQuery('#idx_broker_apikey').focus();
             jQuery('#idx_broker_apikey').parents('li').css('background', '#FDB7B7');
             jQuery('#idx_broker_apikey_error').css('display', 'block');
             submit = false;
         } else {
             jQuery('#idx_broker_apikey').parents('li').css('background', 'none');
             jQuery('#idx_broker_apikey_error').css('display', 'none');
         }
    	if(submit == true) { 
	    	event.preventDefault();
	    	jQuery('[name=action]').val('idx_refresh_api');
	    	jQuery('#action_mode').val('refresh_mode');
	    	var status = jQuery(this).siblings('.refresh_status');
	    	var ajax_url = jQuery('#save_changes').attr('ajax');
	    	var ajax_load = "<span class='ajax'></span>";
	    	params = jQuery('#idx_broker_options').serialize();
	    	status.fadeIn('fast').html(ajax_load+'Refreshing API...');
	
	    	jQuery.ajax({
		  		type: "POST",
		   		url: ajax_url,
		   		data: params,
		   		success: function(data) {
	    			status.fadeIn('fast').html(ajax_load+'Refreshing Links...');
	    			jQuery('[name=action]').val('update');
	    			jQuery('#idx_broker_options').submit();
			  	}		   
	 		});
    	}
    });  
    
});
function systemlink_check() {
	var sel_count = 0;
	var total = jQuery('.systemLink').size();
	jQuery('.systemLink').each(function() {
		if( jQuery(this).is(':checked') ) {
    	  sel_count++; 
		}
	});
	
    if(total == sel_count) {
    	jQuery('input[name=idx_systemlink_group]').attr('checked', true);
    } else {
    	jQuery('input[name=idx_systemlink_group]').attr('checked', false);
    }
}

function savedlink_check() {
	var sel_count = 0;
	var total = jQuery('.savedLink').size();
	jQuery('.savedLink').each(function() {
		if( jQuery(this).is(':checked') ) {
    	  sel_count++; 
		}
	});
	
    if(total == sel_count) {
    	jQuery('input[name=idx_savedlink_group]').attr('checked', true);
    } else {
    	jQuery('input[name=idx_savedlink_group]').attr('checked', false);
    }
}
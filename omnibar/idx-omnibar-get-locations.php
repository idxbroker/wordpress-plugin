<?php
//Find Results URL
function get_base_url($array){
  foreach ((array)$array as $item){
    if(preg_match("/results/i", $item->url)){
      return($item->url);
    }
  }
}

  //grab responses and add JSON object container for easier parsing later
  $cities = '"cities" : '.json_encode(idx_api('cities/combinedActiveMLS'));
  $counties = ', "counties" : '.json_encode(idx_api('counties/combinedActiveMLS'));
  $zipcodes = ', "zipcodes" : '.json_encode(idx_api('zipcodes/combinedActiveMLS'));
  //location lists together
  $locations = 'idxOmnibar({'.$cities.$counties.$zipcodes.'})';

  //get base Url for client's results page for use on omnibar.js front end
  $systemLinksCall = idx_api_get_systemlinks();


  //test to confirm API call worked properly before updating JSON file etc.
  if($systemLinksCall){
    file_put_contents(dirname(dirname(__FILE__)) . '/js/locationlist.json', $locations);
    
    //update database with new results url
    update_option('idx-results-url', get_base_url($systemLinksCall));
    //If invalid API key, display error
  } else {
    echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
  }
  

<?php
namespace IDX\Omnibar;

class Get_Locations
{
    public function __construct()
    {
        $api = get_option('idx_broker_apikey');
        if (empty($api)) {
            return;
        } else {
            $this->initiate_get_locations();
        }
    }
    //Find Results URL
    private function get_base_url($array)
    {
        foreach ((array) $array as $item) {
            if (preg_match("/results/i", $item->url)) {
                return ($item->url);
            }
        }
    }

    private function initiate_get_locations()
    {

        //get base Url for client's results page for use on omnibar.js front end
        $idx_api = new \IDX\Idx_Api;
        $system_links_call = $idx_api->idx_api_get_systemlinks();

        //grab responses and add JSON object container for easier parsing later
        $cities = '"cities" : ' . json_encode($idx_api->idx_api('cities/combinedActiveMLS'));
        $counties = ', "counties" : ' . json_encode($idx_api->idx_api('counties/combinedActiveMLS'));
        $zipcodes = ', "zipcodes" : ' . json_encode($idx_api->idx_api('zipcodes/combinedActiveMLS'));
        //location lists together
        $locations = 'idxOmnibar({' . $cities . $counties . $zipcodes . '})';
        //test to confirm API call worked properly before updating JSON file etc.
        if ($system_links_call) {
            file_put_contents(dirname(dirname(dirname(__FILE__))) . '/assets/js/locationlist.json', $locations);

            //update database with new results url
            update_option('idx-results-url', $this->get_base_url($system_links_call));
            //If invalid API key, display error
        } else {
            echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
        }
    }

}

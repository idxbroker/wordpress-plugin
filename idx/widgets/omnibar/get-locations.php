<?php
namespace IDX\Widgets\Omnibar;

class Get_Locations
{
    public function __construct()
    {
        $api = get_option('idx_broker_apikey');
        if (empty($api)) {
            return;
        } else {
            $this->idx_api = new \IDX\Idx_Api();
            if (isset($this->idx_api->idx_api_get_systemlinks()->errors)) {
                return;
            }
            $this->initiate_get_locations();
        }
    }

    public $idx_api;

    /*
     * Custom Advanced Fields added via admin
     */
    //used in get_additional_fields function
    public function get_idxIDs($array)
    {
        $idxIDs = array();
        foreach ($array as $field) {
            $idxID = $field['idxID'];
            if (!in_array($idxID, $idxIDs)) {
                array_push($idxIDs, $idxID);
            }
        }
        return $idxIDs;
    }

    //used in get_additional_fields function
    public function fields_in_idxID($idxIDMatch, $fields)
    {
        $output = '';
        $first_run_for_idxID = true;
        for ($i = 0; $i < count($fields); $i++) {
            $field = $fields[$i];
            $idxID = $field['idxID'];
            $name = $field['value'];
            $mlsPtID = $field['mlsPtID'];
            $prefix = ', {"' . $name . '" : ';
            if ($first_run_for_idxID) {
                $prefix = '{"' . $name . '" : ';
            }
            if ($idxIDMatch === $idxID) {
                $first_run_for_idxID = false;
                $field_values = json_encode($this->idx_api->idx_api("searchfieldvalues/$idxID?mlsPtID=$mlsPtID&name=$name", $this->idx_api->idx_api_get_apiversion(), 'mls', array(), 86400));
                $output .= "$prefix $field_values }";
            }
        }
        return $output;
    }

    //used to retrieve all fields and create JSON objects by each idxID for each field
    public function get_additional_fields()
    {
        $fields = get_option('idx-omnibar-custom-fields');
        if (empty($fields)) {
            return;
        }
        $idxIDs = $this->get_idxIDs($fields);
        $output = '';
        foreach ($idxIDs as $idxID) {
            $fields_in_idxID = $this->fields_in_idxID($idxID, $fields);
            $output .= ", {\"$idxID\" : [ $fields_in_idxID ]}";
        }
        return $output;
    }

    //for display on the front end.
    public function create_custom_fields_key()
    {
        $custom_fields_key = array();
        $fields = get_option('idx-omnibar-custom-fields');
        if (empty($fields)) {
            return 'var customFieldsKey = {}; ';
        }
        foreach ($fields as $field) {
            $name = $field['value'];
            $mlsPtID = $field['mlsPtID'];
            $displayName = $field['name'];
            $custom_fields_key[$name] = $displayName;
        }
        return 'var customFieldsKey = ' . json_encode($custom_fields_key) . '; ';
    }

    public function get_cczs()
    {
        //Get correct CCZ List set in admin
        $omnibar_city = get_option('idx-omnibar-current-city-list');
        $omnibar_county = get_option('idx-omnibar-current-county-list');
        $omnibar_zipcode = get_option('idx-omnibar-current-zipcode-list');
        //If none is set yet, use cobinedActiveMLS
        if (!isset($omnibar_city)) {
            $omnibar_city = 'combinedActiveMLS';
            update_option('idx-omnibar-current-city-list', 'combinedActiveMLS');
        }
        if (!isset($omnibar_county)) {
            $omnibar_county = 'combinedActiveMLS';
            update_option('idx-omnibar-current-county-list', 'combinedActiveMLS');
        }
        if (!isset($omnibar_zipcode)) {
            $omnibar_zipcode = 'combinedActiveMLS';
            update_option('idx-omnibar-current-zipcode-list', 'combinedActiveMLS');
        }
        //grab responses for CCZs and add JSON object container for front end JavaScript
        $cities = '"cities" : ' . json_encode($this->idx_api->idx_api("cities/$omnibar_city", $this->idx_api->idx_api_get_apiversion()));
        $counties = ', "counties" : ' . json_encode($this->idx_api->idx_api("counties/$omnibar_county", $this->idx_api->idx_api_get_apiversion()));
        $zipcodes = ', "zipcodes" : ' . json_encode($this->idx_api->idx_api("zipcodes/$omnibar_zipcode", $this->idx_api->idx_api_get_apiversion()));
        return $cities . $counties . $zipcodes;
    }

    private function initiate_get_locations()
    {
        $cczs = $this->get_cczs();

        //location lists together
        $locations = 'idxOmnibar( [{"core" : {' . $cczs . '} }' . $this->get_additional_fields() . ']);';

        $output = $this->create_custom_fields_key() . $locations;

        $system_links_call = $this->idx_api->idx_api_get_systemlinks();

        $city_lists = $this->idx_api->city_list_names();
        $county_lists = $this->idx_api->county_list_names();
        $zipcode_lists = $this->idx_api->postalcode_list_names();

        //test to confirm API call worked properly before updating JSON file etc.
        if (!empty($system_links_call) && empty($system_links_call->errors)) {
            file_put_contents(dirname(dirname(dirname(dirname(__FILE__)))) . '/assets/js/locationlist.js', $output);

            //update database with new results url
            //get base Url for client's results page for use on omnibar.js front end
            update_option('idx-results-url', $this->idx_api->system_results_url());
            //Update city lists
            update_option('idx-omnibar-city-lists', $city_lists);
            update_option('idx-omnibar-county-lists', $county_lists);
            update_option('idx-omnibar-zipcode-lists', $zipcode_lists);

            //If invalid API key, display error
        } else {
            echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
        }
    }

}

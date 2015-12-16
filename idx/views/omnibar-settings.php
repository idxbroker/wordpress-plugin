<?php
namespace IDX\Views;

class Omnibar_Settings
{

    public function __construct()
    {

        $this->idx_api = new \IDX\Idx_Api();

        //preload via javascript if first load of view
        add_action('wp_ajax_idx_preload_omnibar_settings_view', array($this, 'idx_preload_omnibar_settings_view'));
        add_action('wp_ajax_idx_update_omnibar_current_ccz', array($this, 'idx_update_omnibar_current_ccz'));
        add_action('wp_ajax_idx_update_omnibar_custom_fields', array($this, 'idx_update_omnibar_custom_fields'));

    }

    public $idx_api;

    //preload view via javascript if first load of view to give user feedback of loading the page and decreased perceived page load time
    public function idx_omnibar_settings_interface()
    {
        //register omnibar settings script
        wp_register_script('idx-omnibar-settings', plugins_url('/assets/js/idx-omnibar-settings.min.js', dirname(dirname(__FILE__))), 'jquery');
        wp_enqueue_style('idx-omnibar-settings', plugins_url('/assets/css/idx-omnibar-settings.css', dirname(dirname(__FILE__))));
        if ($this->idx_api->get_transient('idx_approvedmls_cache') !== false) {
            $this->idx_preload_omnibar_settings_view();
        } else {
            echo "<div class=\"loading\" style=\"margin-top: 2rem; font-size: 1rem;\">Loading Omnibar Settings...</div><div class=\"idx-loader\"></div>";
            //tell JS to reload page when ready
            wp_localize_script('idx-omnibar-settings', 'loadOmnibarView', 'true');
        }
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js', 'jquery');

        return wp_enqueue_script('idx-omnibar-settings');
    }

    public function idx_preload_omnibar_settings_view()
    {
        global $api_error;
        $search_item = array('_', '-');
        $display_class = '';
        $savedlinks = '';
        $systemlinks = '';
        $check_sys_option = '';

        if (!$api_error) {
            $systemlinks = $this->idx_api->idx_api_get_systemlinks();
            if (is_wp_error($systemlinks)) {
                $api_error = $systemlinks->get_error_message();
                $systemlinks = '';
            }
            $savedlinks = $this->idx_api->idx_api_get_savedlinks();
            if (is_wp_error($savedlinks)) {
                $api_error = $savedlinks->get_error_message();
                $savedlinks = '';
            }
        }
        //if API error, display error and do not attempt to load the rest of the page
        if ($api_error) {
            return "<div class=\"error\">$api_error</div>";
        }
        //Shows which ccz list is currently being used by the omnibar
        $omnibar_cities = $this->idx_api->city_list_names();
        $omnibar_counties = $this->idx_api->county_list_names();
        $omnibar_zipcodes = $this->idx_api->postalcode_list_names();
        ?>
        <div class="wrap">
            <div class="">
                <div class="inside">
                    <form>
                        <div id="omnibar-ccz">
                            <h2>IMPressOmnibar Search Widget Settings</h2>
                            <a href="http://www.idxbroker.com" target="_blank" class="logo-link">
                                <div id="logo"></div>
                            </a>
                            <h3>City, County, and Postal Code Lists</h3>
                            <div class="help-text">
                                Choose which custom City, County, or Postal Code lists to use for the Omnibar. Only locations in these lists will return results.
                                <div>
                                    <i>Example: Combined Active MLS Cities List</i>
                                </div>
                            </div>

        <div class="city-list select-div"><label>City List:</label><select name="city-list">
        <?php
//Cities
        foreach ($omnibar_cities as $lists => $list) {
            foreach ($list as $list_option => $list_option_value) {
                if ($list_option === 'id') {
                    $id = $list_option_value;
                }
                if ($list_option === 'name') {
                    $name = $list_option_value;
                }
            }
            //create options for each list and select currently saved option in select by default
            $saved_list = $this->idx_saved_or_default_list(get_option('idx-omnibar-current-city-list'));
            echo "<option value=\"$id\"" . selected($id, $saved_list) . ">$name</option>";
        }
        echo "</select></div><div class=\"county-list select-div\"><label>County List:</label><select name=\"county-list\">";
        //Counties
        foreach ($omnibar_counties as $lists => $list) {
            foreach ($list as $list_option => $list_option_value) {
                if ($list_option === 'id') {
                    $id = $list_option_value;
                }
                if ($list_option === 'name') {
                    $name = $list_option_value;
                }
            }
            //create options for each list and select currently saved option in select by default
            $saved_list = $this->idx_saved_or_default_list(get_option('idx-omnibar-current-county-list'));
            echo "<option value=\"$id\"" . selected($id, $saved_list) . ">$name</option>";
        }
        echo "</select></div><div class=\"zipcode-list select-div\"><label>Postal Code List:</label><select name=\"zipcode-list\">";
        //Zipcodes
        foreach ($omnibar_zipcodes as $lists => $list) {
            foreach ($list as $list_option => $list_option_value) {
                if ($list_option === 'id') {
                    $id = $list_option_value;
                }
                if ($list_option === 'name') {
                    $name = $list_option_value;
                }
            }
            //create options for each list and select currently saved option in select by default
            $saved_list = $this->idx_saved_or_default_list(get_option('idx-omnibar-current-zipcode-list'));
            echo "<option value=\"$id\"" . selected($id, $saved_list) . ">$name</option>";
        }
        echo "</select></div></div>";
        //Advanced Fields:
        $all_mls_fields = $this->idx_omnibar_advanced_fields();

        //Default property type for each MLS
        echo "<h3>Property Type</h3><div class=\"idx-property-types\">";
        echo "<div class=\"help-text\">Choose the property type for Omnibar searches.</div>";
        ?>
        <div class="select-div">
            <label for="basic">Not Custom Field Searches:</label><select class="omnibar-mlsPtID" name="basic">
                <option value="sfr">Single Family Residential</option>
                <option value="com">Commercial</option>
                <option value="ld">Lots and Land</option>
                <option value="mfr">Multifamily Residential</option>
                <option value="rnt">Rentals</option>
            </select>
        </div>

        <?php

        foreach ($all_mls_fields[1] as $mls) {
            $mls_name = $mls['mls_name'];
            $idxID = $mls['idxID'];
            $property_types = json_decode($mls['property_types']);
            echo "<div class=\"select-div\"><label for=\"$idxID\">$mls_name:</label>";
            echo "<select class=\"omnibar-mlsPtID\" name=\"$idxID\">";
            foreach ($property_types as $property_type) {
                $mlsPtID = $property_type->mlsPtID;
                $mlsPropertyType = $property_type->mlsPropertyType;
                echo "<option value=\"$mlsPtID\"" . selected($mlsPtID, $this->idx_in_saved_array($mlsPtID, get_option('idx-default-property-types'), $idxID)) . ">$mlsPropertyType</option>";
            }
            echo "</select></div>";
        }

        //echo them as one select
        echo "<h3>Custom Fields</h3>";
        echo "<div class=\"help-text\">By default the omnibar searches by City, County, Postal Code, Address, or Listing ID. Add up to 10 custom fields to be used as well.<div><i>Examples: High School, Area, Subdivision</i></div></div>";
        echo "<select class=\"omnibar-additional-custom-field select2\" name=\"omnibar-additional-custom-field\" multiple=\"multiple\">";
        foreach ($all_mls_fields[0] as $mls) {
            $mls_name = $mls['mls_name'];
            $idxID = $mls['idxID'];
            echo "<optgroup label=\"$mls_name\" class=\"$idxID\">";
            $fields = json_decode($mls['field_names']);
            //make sure field names only appear once per MLS
            $unique_values = array();
            foreach ($fields as $field) {
                $name = $field->displayName;
                $value = $field->name;
                $mlsPtID = $field->mlsPtID;
                if (!in_array($value, $unique_values, true) && $name !== '') {
                    array_push($unique_values, $value);
                    echo "<option value=\"$value\"" . selected($value, $this->idx_in_saved_array($value, get_option('idx-omnibar-custom-fields'), $idxID)) . " data-mlsPtID=\"$mlsPtID\">$name</option>";
                }

            }
            echo "</optgroup>";
        }
        echo "</select>";

        $placeholder = get_option('idx-omnibar-placeholder');
        if (empty($placeholder)) {
            $placeholder = "City, Postal Code, Address, or Listing ID";
        }
        echo "<h3>Custom Placeholder</h3>";
        echo "<div class=\"help-text\">This is a placeholder for the main input of Omnibar widgets.<div><i>Examples: \"Search for Properties\" or \"Location, School, Address, or Listing ID\"</i></div></div>";
        echo "<input class=\"omnibar-placeholder\" type=\"text\" value=\"$placeholder\">";
        echo "</div>";
        echo <<<EOT
                        <div class="saveFooter">
                        <input type="submit" value="Save Changes" id="save_changes" class="button-primary update_idxlinks"  />
                        <span class="status"></span>
                        <input type="hidden" name="action_mode" id="action_mode" value="" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
EOT;

    }

    public function idx_in_saved_array($name, $array, $idxID)
    {
        if (empty($array)) {
            return false;
        }
        foreach ($array as $field) {
            if (in_array($name, $field) && in_array($idxID, $field)) {
                return $name;
            }
        }
    }
    public function idx_saved_or_default_list($list_name)
    {
        if (empty($list_name)) {
            return 'combinedActiveMLS';
        } else {
            return $list_name;
        }
    }

    //custom fields:
    public function idx_omnibar_advanced_fields()
    {

        //Grab all advanced field names for all MLS
        //grab all idxIDs for account
        $mls_list = $this->idx_api->approved_mls();
        $all_mls_fields = array();
        $all_mlsPtIDs = array();
        //grab all field names for each idxID
        foreach ($mls_list as $mls) {
            $idxID = $mls->id;
            $mls_name = $mls->name;
            $fields = json_encode($this->idx_api->idx_api("searchfields/$idxID", $this->idx_api->idx_api_get_apiversion(), 'mls', array(), 86400));
            $property_types = json_encode($this->idx_api->idx_api("propertytypes/$idxID", $this->idx_api->idx_api_get_apiversion(), 'mls', array(), 86400));
            $mls_object = new \IDX\Widgets\Omnibar\Advanced_Fields($idxID, $mls_name, $fields, $property_types);
            $mls_fields_object = $mls_object->return_fields();
            $mls_property_types_object = $mls_object->return_mlsPtIDs();
            //push all fieldnames for each MLS to array
            array_push($all_mls_fields, $mls_fields_object);
            array_push($all_mlsPtIDs, $mls_property_types_object);
        }
        return array(array_unique($all_mls_fields, SORT_REGULAR), $all_mlsPtIDs);
    }

    /** Update Saved CCZ Lists for Omnibar when Admin form is saved
     * @param void
     */
    public function idx_update_omnibar_current_ccz()
    {
        //Strip out HTML Special Characters before updating db to avoid security or formatting issues
        $city_list = htmlspecialchars($_POST['city-list']);
        $county_list = htmlspecialchars($_POST['county-list']);
        $zipcode_list = htmlspecialchars($_POST['zipcode-list']);
        update_option('idx-omnibar-current-city-list', $city_list);
        update_option('idx-omnibar-current-county-list', $county_list);
        update_option('idx-omnibar-current-zipcode-list', $zipcode_list);
        return wp_die();
    }

    public function idx_update_omnibar_custom_fields()
    {
        //Strip out HTML Special Characters before updating db to avoid security or formatting issues
        if (!empty($_POST['fields'])) {
            $fields = $_POST['fields'];
        } else {
            $fields = array();
        }
        update_option('idx-omnibar-custom-fields', $fields);
        update_option('idx-default-property-types', $_POST['mlsPtIDs']);
        update_option('idx-omnibar-placeholder', htmlspecialchars($_POST['placeholder']));
        new \IDX\Widgets\Omnibar\Get_Locations();
        return wp_die();
    }

}

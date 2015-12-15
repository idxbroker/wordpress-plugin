<?php
namespace IDX\Views;

class Omnibar_Settings {
    public function __constructor(){
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        //preload via javascript if first load of view
        add_action('wp_ajax_idx_preload_ccz_view', 'idx_load_ccz_view_content');
    }

    //preload view via javascript if first load of view to give user feedback of loading the page and decreased perceived page load time
    function idx_omnibar_settings_interface(){
        if(get_transient('idx_approvedmls_cache')){
            idx_load_ccz_view_content();
            idx_admin_scripts();
        } else {
            echo "<div class=\"loading\" style=\"margin-top: 2rem; font-size: 1rem;\">Loading Omnibar Settings...</div>";
            //load scripts and styles
            idx_admin_scripts();
            wp_localize_script('idxjs', 'loadCczView', 'true');
        }
    }

    function idx_load_ccz_view_content(){
        global $api_error;
        $search_item = array('_','-');
        $display_class = '';
        $savedlinks = '';
        $systemlinks = '';
        $check_sys_option = '';
        if (!current_user_can('manage_options'))  {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        if(!$api_error) {
            $systemlinks = idx_api_get_systemlinks();
            if( is_wp_error($systemlinks) ) {
                $api_error = $systemlinks->get_error_message();
                $systemlinks = '';
            }
            $savedlinks = idx_api_get_savedlinks();
            if(is_wp_error($savedlinks) ) {
                $api_error = $savedlinks->get_error_message();
                $savedlinks = '';
            }
        }
        //if API error, display error and do not attempt to load the rest of the page
        if($api_error){
            return "<div class=\"error\">$api_error</div>";
        }
        //Shows which ccz list is currently being used by the omnibar
        $omnibar_cities = get_option('idx-omnibar-city-lists');
        $omnibar_counties = get_option('idx-omnibar-county-lists');
        $omnibar_zipcodes = get_option('idx-omnibar-zipcode-lists');
        function idx_is_saved($id, $saved_id){
            if($id === $saved_id){
                return 'selected';
            } else {
                return '';
            }
        }
        function idx_in_saved_array($name, $array, $idxID){
            if(empty($array)){
                return FALSE;
            }
            foreach($array as $field){
                if(in_array($name, $field) && in_array($idxID, $field)){
                    return $name;
                }
            }
        }
        function idx_saved_or_default_list($list_name){
            if(empty($list_name)){
                return 'combinedActiveMLS';
            } else {
                return $list_name;
            }
        }
        echo "<div class=\"wrap\"><div class=\"\"><div class=\"inside\"><form>";
                echo "<div id=\"omnibar-ccz\"><h2><span>Omnibar Search Widget Settings <a href=\"http://support.idxbroker.com/customer/portal/articles/2081878-widget---wordpress-omnibar-search\" target=\"_blank\"><img class=\"help-icon\" src=\"".plugins_url('../images/helpIcon.svg', __FILE__)."\" alt=\"help\"></a></span></h2><a href=\"http://www.idxbroker.com\" target=\"_blank\" class=\"logo-link\"><div id=\"logo\"></div></a><h3>City, County, and Postal Code Lists</h3>";
                echo "<div class=\"help-text\">Choose which custom City, County, or Postal Code lists to use for the omnibar. Only locations in these lists will return results.<div><i>Example: Combined Active MLS Cities List</i></div></div>";
                echo "<div class=\"city-list select-div\"><label>City List:</label><select name=\"city-list\">";
                        foreach ($omnibar_cities as $lists => $list) {
                            foreach($list as $list_option => $list_option_value){
                                if($list_option === 'id'){
                                    $id = $list_option_value;
                                }
                                if($list_option === 'name') {
                                    $name = $list_option_value;
                                }
                            }
                            //create options for each list and select currently saved option in select by default
                            echo "<option value=\"$id\"".idx_is_saved($id, idx_saved_or_default_list(get_option('idx-omnibar-current-city-list'))).">$name</option>";
                        }
                    echo "</select></div><div class=\"county-list select-div\"><label>County List:</label><select name=\"county-list\">";
                        foreach ($omnibar_counties as $lists => $list) {
                            //create options for each list and select currently saved option in select by default
                            echo "<option value=\"$list\"".idx_is_saved($list, idx_saved_or_default_list(get_option('idx-omnibar-current-county-list'))).">$list</option>";
                        }
                    echo "</select></div><div class=\"zipcode-list select-div\"><label>Postal Code List:</label><select name=\"zipcode-list\">";
                        foreach ($omnibar_zipcodes as $lists => $list) {
                            //create options for each list and select currently saved option in select by default
                            echo "<option value=\"$list\"".idx_is_saved($list, idx_saved_or_default_list(get_option('idx-omnibar-current-zipcode-list'))).">$list</option>";
                        }
                    echo "</select></div></div>";
                //Advanced Fields:
                    $all_mls_fields = idx_omnibar_advanced_fields();
                //echo them as one select
                    echo "<h3>Custom Fields</h3>";
                    echo "<div class=\"help-text\">By default the omnibar searches by City, County, Postal Code, Address, or Listing ID. Add up to 10 custom fields to be used as well.<div><i>Examples: High School, Area, Subdivision</i></div></div>";
                    echo "<select class=\"omnibar-additional-custom-field select2\" name=\"omnibar-additional-custom-field\" multiple=\"multiple\">";
                    foreach($all_mls_fields[0] as $mls){
                        $mls_name = $mls['mls_name'];
                        $idxID = $mls['idxID'];
                        echo "<optgroup label=\"$mls_name\" class=\"$idxID\">";
                        $fields = json_decode($mls['field_names']);
                        //make sure field names only appear once per MLS
                        $unique_values = array();
                        foreach($fields as $field){
                            $name = $field->displayName;
                            $value = $field->name;
                            $mlsPtID = $field->mlsPtID;
                            if(! in_array($value, $unique_values, TRUE) && $name !== ''){
                                array_push($unique_values, $value);
                                echo "<option value=\"$value\"".idx_is_saved($value, idx_in_saved_array($value, get_option('idx-omnibar-custom-fields'), $idxID))." data-mlsPtID=\"$mlsPtID\">$name</option>";
                            }

                        }
                        echo "</optgroup>";
                    }
                    echo "</select>";
                    //Default property type for each MLS
                    echo "<h3>Default Property Type for Custom Field Searches</h3><div class=\"idx-property-types\">";
                    echo "<div class=\"help-text\">Choose the property type for when a user uses a value of a custom field such as a school.</div>";
                    foreach($all_mls_fields[1] as $mls){
                        $mls_name = $mls['mls_name'];
                        $idxID = $mls['idxID'];
                        $property_types = json_decode($mls['property_types']);
                        echo "<div class=\"select-div\"><label for=\"$idxID\">$mls_name:</label>";
                        echo "<select class=\"omnibar-mlsPtID\" name=\"$idxID\">";
                            foreach($property_types as $property_type){
                                $mlsPtID = $property_type->mlsPtID;
                                $mlsPropertyType = $property_type->mlsPropertyType;
                                echo "<option value=\"$mlsPtID\"".idx_is_saved($mlsPtID, idx_in_saved_array($mlsPtID, get_option('idx-default-property-types'), $idxID)).">$mlsPropertyType</option>";
                            }
                        echo "</select></div>";
                    }
                    $placeholder = get_option('idx-omnibar-placeholder');
                    if(empty($placeholder)){
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
}


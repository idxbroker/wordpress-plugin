<?php
namespace IDX;

class Blacklist {
    public function __construct(){
        //convert visitor IP to long format to match API response
        $this->visitor = ip2long($_SERVER['REMOTE_ADDR']);
        //for testing blacklist functionality:
        // $this->visitor = "1572208640";
        $this->idx_api = new Idx_Api();
        //load function after Equity is loaded
        add_action('init', array($this, 'pass_or_fail'));
    }

    //visitor IP address
    public $visitor;

    public $idx_api;

    public function in_blacklist($visitorIp){
        $blacklist = $this->idx_api->get_blacklist();
        //if api returns error, do not block visitor
        if(is_wp_error($blacklist)){
            return false;
        }
        //check if visitor ip is in blacklist
        return in_array($visitorIp, $blacklist);
    }

    public function pass_or_fail(){
        //only blacklist if Equity is being used
        if(!function_exists('equity')){
            return;
        }
        //redirect blocked IPs
        if($this->in_blacklist($this->visitor)){
            header("Location: http://middleware.idxbroker.com/docs/403.php");
            exit;
        }
    }
}

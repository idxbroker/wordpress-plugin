<?php
namespace IDX;

class Blacklist {
    public function __construct(){
        $this->visitor = ip2long($_SERVER['REMOTE_ADDR']);
        $this->idx_api = new Idx_Api();
        add_action('wp_loaded', array($this, 'pass_or_fail'));

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
        //redirect blocked IPs
        if($this->in_blacklist($this->visitor)){
            header("Location: http://middleware.idxbroker.com/docs/403.php");
        }
    }
}

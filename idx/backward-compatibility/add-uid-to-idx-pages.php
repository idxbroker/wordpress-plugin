<?php
namespace IDX\Backward_Compatibility;

class Add_Uid_To_Idx_Pages
{
    public $idx_api;

    public function __construct(\IDX\Idx_Api $idx_api)
    {
        $this->idx_api = $idx_api;

        if(!empty(get_option('idx_added_uid_to_idx_pages'))){
            $this->add_uid();
            return update_option('idx_added_uid_to_idx_pages', true);
        }
    }

    public function add_uid()
    {
        $idx_pages = get_posts(array('post_type' => 'idx_page', 'numberposts' => -1));
        foreach($idx_pages as $idx_page){
            $id = $idx_page->ID;
            $uid = $this->matched_idx_page($idx_page->post_name);
            if(empty($idx_page)){
                continue;
            }
            update_post_meta($id, $uid);
        }
    }

    public function matched_idx_page($url)
    {
        $saved_links = $this->idx_api->idx_api_get_savedlinks();
        $system_links = $this->idx_api->idx_api_get_systemlinks();

        if (!is_array($system_links) || !is_array($saved_links)) {
            return false;
        }

        $idx_pages = array_merge($saved_links, $system_links);

        var_dump($idx_pages);

        foreach($idx_pages as $idx_page){
            if($idx_page->url === $url){
                return $idx_page->uid;
            }
        }
        return false;
    }
}

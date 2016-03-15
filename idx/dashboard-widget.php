<?php
namespace IDX;

use \Carbon\Carbon;

class Dashboard_Widget {
    public function __construct(Idx_Api $idx_api)
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        $this->idx_api = $idx_api;
        
        var_dump(Carbon::parse('last week'));
        var_dump($this->hours_before_now('last week'));
    }

    public $idx_api;

    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'idx_dashboard_widget',
            'IMPress for IDX',
            array($this, 'compile_dashboard_widget')
        );
    }

    public function compile_dashboard_widget()
    {
        wp_register_script('idx-dashboard-widget', plugins_url('/assets/js/idx-dashboard-widget.min.js', dirname(__FILE__)));
        echo $this->dashboard_widget_html(
            $this->leads_overview(), 
            $this->listings_overview(), 
            $this->side_overview()
        );
        $this->load_scripts();
    }

    public function dashboard_widget_html($leads_overview, $listings_overview, $side_overview)
    {
        $output = '';
        $output .= $leads_overview . $listings_overview . $side_overview;
        $output .= '';
        return $output;
    }

    public function leads_overview()
    {
        $output = '<div class="leads-overview">';
        wp_localize_script('idx-dashboard-widget', 'leadsData', $this->leads_json());
        $output .= '</div>';
        return $output;
    }

    public function listings_overview()
    {
        $output = '<div class="listings-overview">';
        wp_localize_script('idx-dashboard-widget', 'listingsData', $this->listings_json());
        $output .= '</div>';
        return $output;
    }

    public function side_overview()
    {
        $output = '';
        return $output;
    }

    public function load_scripts()
    {
        wp_enqueue_script('google-charts', 'https://www.gstatic.com/charts/loader.js');
        wp_enqueue_script('idx-dashboard-widget');
    }

    public function leads_json($timeframe = null)
    {
        $leads_object;
        $api_data = $this->idx_api->get_leads($timeframe);
        foreach($api_data as $api_data_lead){
            //convert date to Carbon instance for easy parsing
            $subscribe_date = Carbon::parse($api_data_lead->subscribeDate);
            $leads_object[] = array(
                'month' => $subscribe_date->month
            );
        }
        return $leads_object;
    }

    public function listings_json()
    {
        $output = $this->idx_api->get_featured_listings();
        return $output;
    }

    public function side_json()
    {
        $leads = $this->idx_api->get_leads();
        //order newest first
        return array_reverse($leads);
    }

    public function hours_before_now($timeframe)
    {
        return Carbon::parse($timeframe)->diffInHours(Carbon::now());
    }
}

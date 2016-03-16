<?php
namespace IDX;

use \Carbon\Carbon;
use \Exception;

class Dashboard_Widget {
    public function __construct(Idx_Api $idx_api)
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        $this->idx_api = $idx_api;
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
        echo $this->dashboard_widget_html();
        $this->load_scripts();
    }

    public function dashboard_widget_html()
    {
        $output = '';
        $output .= $this->leads_overview(168, 'week') . $this->listings_overview() . $this->side_overview();
        $output .= '';
        return $output;
    }

    public function leads_overview($timeframe, $interval)
    {
        $output = '<div class="leads-overview">';
        wp_localize_script('idx-dashboard-widget', 'leadsData', $this->leads_json($timeframe, 'day'));
        $output .= '</div>';
        return $output;
    }

    public function listings_overview()
    {
        $output = '<div class="listings-overview">';
        try {
            $listings_json = $this->listings_json();
        } catch (Exception $error){
            return 'No Leads Returned';
        }

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

    public function leads_json($timeframe = null, $interval = 'month')
    {
        try {
            $interval_array = $this->get_interval_data($timeframe, $interval);
        } catch(Exception $error) {
            return;
        }
        $interval_data = $interval_array['interval_data'];
        $min_max = $interval_array['min_max'];
    
        if($interval === 'month'){
            $data = $this->leads_month_interval($interval_data);
        } elseif($interval === 'day'){
            $data = $this->leads_day_interval($interval_data, $min_max);
        }

        return $data;
    }

    public function leads_month_interval($interval_data)
    {
        $data = array();
        $data[] = array(
            'Month', 
            'Registrations'
        );
        for($i = 1; $i <= 12; ++$i){
            //hardcode day and year as they are irrelevant
            $month_name = jdmonthname(gregoriantojd($i,1,2017), 0);
            if(isset($interval_data[$i])){
                $data[] = array(
                    $month_name, 
                    $interval_data[$i]['value']
                ); 
            } else {
                $data[] = array(
                    $month_name, 
                    0
                );
            }
        }
        return $data;
    }

    public function leads_day_interval($interval_data, $min_max)
    {
        $data = array();
        $data[] = array(
            'Day', 
            'Registrations'
        );
        $min = $min_max['min'];
        $max = $min_max['max'];

        //create week from last 7 days to iterate over
        $week = $this->create_week($min, $max);
        //if lead capture day matches day of week, add to array
        foreach($interval_data as $data_day){
            foreach($week as $day){
                $date = $day['date'];
                $data_timestamp = $data_day['timestamp'];
                $timestamp = $day['timestamp'];
                if(date('m-d', $data_timestamp) === $date){
                    $data[] = array(
                        $date, 
                        $data_day['value']
                    ); 
                } else {
                    $data[] = array(
                        $date, 
                        0
                    );
                }
            }
        }
        return $data;
    }

    public function create_week($min, $max)
    {
        $week_array = array();
        $day = $min;
        for($i = 0; $i < 7; $i++){
            $date = date('m-d', $day);
            $week_array[] = array(
                'date' => $date,
                'value' => 0,
                'timestamp' => $day
            );
            //move to next day
            $day = $day + 60*60*24;
        }
        return $week_array; 
    }

    public function get_interval_data($timeframe, $interval)
    {
        $leads_array;
        $api_data = $this->idx_api->get_leads($timeframe);

        if(empty($api_data)){
            throw new Exception('No Leads Returned');
        }

        foreach($api_data as $api_data_lead){
            //convert date to Carbon instance for easy parsing
            $subscribe_date = Carbon::parse($api_data_lead->subscribeDate);
            $leads_array[] = array(
                'timestamp' => $subscribe_date->timestamp
            );
        }
        $min_max = $this->min_max_intervals($interval);
        $interval_data = $this->interval_data($min_max['min'], $min_max['max'], $leads_array, $interval);

        return compact('min_max', 'interval_data');

    }

    public function min_max_intervals($interval)
    {
        if($interval === 'month'){
            $min = Carbon::parse('7 months ago')->timestamp;
            $max = Carbon::now()->timestamp;
        } elseif($interval === 'day'){
            $min = Carbon::parse('7 days ago')->timestamp;
            $max = Carbon::now()->timestamp;
        }

        return compact('min', 'max');
    }

    //feed in week or month. Example: 3, 7, $data, 'month'
    public function interval_data($min, $max, $data, $interval)
    {
        $interval_data = array();
        foreach($data as $datum){
            $interval_number = Carbon::createFromTimestamp($datum['timestamp'])->$interval;
            if(! isset($interval_data[$interval_number])){
                $interval_data[$interval_number] = array(
                    'timestamp' => $datum['timestamp'],
                    'value' => 0
                    );
            }
            $interval_data[$interval_number]['value'] +=1; 
        }

        return $interval_data;
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

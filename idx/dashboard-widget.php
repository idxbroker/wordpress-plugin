<?php
namespace IDX;

class Dashboard_Widget {
    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }

    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'idx_dashboard_widget',
            'IMPress for IDX',
            array($this, 'load_dashboard_widget')
        );
    }

    public function compile_dashboard_widget()
    {
        $this->dashboard_widget_html(
            $this->lead_overview(), 
            $this->mls_overview(), 
            $this->side_overview()
        );
        $this->load_scripts();
    }

    public function dashboard_widget_html($lead_overview, $mls_overview, $side_overview)
    {
        $output = '';
        $output .= $lead_overview . $mls_overview . $side_overview;
        $output .= '';
        return $output;
    }

    public function lead_overview()
    {
        $output = '';
        return $output;
    }

    public function mls_overview()
    {
        $output = '';
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
        wp_enqueue_script('dashboard-widget', plugins_url('/assets/js/dashboard-widget.js', dirname(__FILE__)));
    }
}

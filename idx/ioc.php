<?php
namespace IDX;

//fork of illuminate/container compatible with PHP5.3
use NetRivet\Container\Container;

class Ioc
{
    public function __construct(Container $app)
    {
        //bind app to itself then bind all classes
        $this->app = $app;
        $app->bind('app', $app);
        $this->bind_classes($this->app, $this->idx_api());
    }

    public function idx_api(){
        return $this->app->make('\IDX\Idx_Api');
    }

    public function bind_classes($app, $idx_api)
    {
        $app->bind('\IDX\Wrappers', function ($app) use($idx_api)
        {
            return new Wrappers($idx_api);
        });
        $app->bind('\IDX\Idx_Pages', function ($app) use($idx_api) {
            return new Idx_Pages($idx_api, $app);
        });
        $app->bind('\IDX\Widgets\Create_Idx_Widgets', function ($app) use($idx_api)
        {
            return new Widgets\Create_Idx_Widgets($idx_api);
        });
        $app->singleton('\IDX\Idx_Api', function ($app) use($idx_api)
        {
            return new Idx_Api;
        });
        $app->bind('\IDX\Shortcodes\Register_Idx_Shortcodes', function ($app) use($idx_api)
        {
            return new Shortcodes\Register_Idx_Shortcodes($idx_api);
        });
        $app->bind('\IDX\Widgets\Create_Impress_Widgets', function ($app) use($idx_api)
        {
            return new Widgets\Create_Impress_Widgets($idx_api, $app);
        });
        $app->bind('\IDX\Widgets\Impress_Carousel_Widget', function ($app) use($idx_api)
        {
            return new Widgets\Impress_Carousel_Widget($idx_api);
        });
        $app->bind('\IDX\Widgets\Impress_City_Links_Widget', function ($app) use($idx_api)
        {
            return new Widgets\Impress_City_Links_Widget($idx_api);
        });
        $app->bind('\IDX\Widgets\Impress_Lead_Login_Widget', function ($app) use($idx_api)
        {
            return new Widgets\Impress_Lead_Login_Widget($idx_api);
        });
        $app->bind('\IDX\Widgets\Impress_Lead_Signup_Widget', function ($app) use($idx_api)
        {
            return new Widgets\Impress_Lead_Signup_Widget($idx_api);
        });
        $app->bind('\IDX\Widgets\Impress_Showcase_Widget', function ($app) use($idx_api)
        {
            return new Widgets\Impress_Showcase_Widget($idx_api);
        });
        $app->bind('\IDX\Widgets\IDX_Omnibar_Widget', function ($app) use($idx_api)
        {
            return new Widgets\IDX_Omnibar_Widget($app->make('\IDX\Widgets\Omnibar\Create_Omnibar'));
        });
        $app->bind('\IDX\Widgets\IDX_Omnibar_Widget_Extra', function ($app) use($idx_api)
        {
            return new Widgets\IDX_Omnibar_Widget_Extra($app->make('\IDX\Widgets\Omnibar\Create_Omnibar'));
        });
        $app->bind('\IDX\Shortcodes\Register_Impress_Shortcodes', function ($app) use($idx_api)
        {
            return new Shortcodes\Register_Impress_Shortcodes($idx_api, $app);
        });
        $app->bind('\IDX\Widgets\Omnibar\Create_Omnibar', function ($app) use($idx_api)
        {
            return new Widgets\Omnibar\Create_Omnibar($app);
        });
        $app->bind('\IDX\Widgets\Omnibar\Get_Locations', function ($app) use($idx_api)
        {
            return new Widgets\Omnibar\Get_Locations($idx_api);
        });
        $app->bind('\IDX\Shortcodes\Shortcode_Ui', function ($app) use($idx_api)
        {
            return new Shortcodes\Shortcode_Ui($app->make('\IDX\Shortcodes\Register_Shortcode_For_Ui'));
        });
        $app->bind('\IDX\Shortcodes\Register_Shortcode_For_Ui', function ($app) use($idx_api)
        {
            return new Shortcodes\Register_Shortcode_For_Ui($idx_api);
        });
        $app->bind('\IDX\Shortcodes\Impress_Lead_Signup_Shortcode', function($app) use($idx_api)
        {
            return new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode($idx_api);
        });
        $app->bind('\IDX\Help', function ($app)
        {
            return new Help;
        });
        $app->bind('\IDX\Views\Omnibar_Settings', function ($app) use($idx_api) {
            return new Views\Omnibar_Settings($app, $idx_api);
        });
        $app->bind('\IDX\Backward_Compatibility\Migrate_Old_Table', function ($app) use($idx_api) {
            return new \IDX\Backward_Compatibility\Migrate_Old_Table($idx_api);
        });
        $app->bind('\IDX\Review_Prompt', function ($app)
        {
            return new Review_Prompt();
        });
        $app->bind('\IDX\Blacklist', function ($app)
        {
            return new Blacklist();
        });
        $app->bind('\IDX\Dashboard_Widget', function ($app) use($idx_api) {
            return new Dashboard_Widget($idx_api);
        });
        $app->bind('\IDX\Backward_Compatibility\Add_Uid_To_Idx_Pages', function ($app) use($idx_api) {
            return new \IDX\Backward_Compatibility\Add_Uid_To_Idx_Pages($idx_api);
        });

    }
}

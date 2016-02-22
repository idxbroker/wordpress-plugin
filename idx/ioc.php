<?php
namespace IDX;

use Illuminate\Container\Container;

class Ioc
{
    public function __construct(Container $app)
    {
        //bind app to itself then bind all classes
        $this->app = $app;
        $app->bind('app', $app);
        $this->bind_classes($this->app);
    }

    public function idx_api(){
        return $this->app->make('\IDX\Idx_Api');
    }

    public function bind_classes($app)
    {
        $app->bind('\IDX\Wrappers', function ($app) {

            return new Wrappers($this->idx_api());
        });
        $app->bind('\IDX\Idx_Pages', function ($app) {

            return new Idx_Pages($this->idx_api());
        });
        $app->bind('\IDX\Widgets\Create_Idx_Widgets', function ($app) {

            return new Widgets\Create_Idx_Widgets($this->idx_api());
        });
        $app->singleton('\IDX\Idx_Api', function ($app) {

            return new Idx_Api;
        });
        $app->bind('\IDX\Shortcodes\Register_Idx_Shortcodes', function ($app) {

            return new Shortcodes\Register_Idx_Shortcodes($this->idx_api());
        });
        $app->bind('\IDX\Widgets\Create_Impress_Widgets', function ($app) {

            return new Widgets\Create_Impress_Widgets($this->idx_api(), $app);
        });
        $app->bind('\IDX\Widgets\Impress_Carousel_Widget', function ($app) {

            return new Widgets\Impress_Carousel_Widget($this->idx_api());
        });
        $app->bind('\IDX\Widgets\Impress_City_Links_Widget', function ($app) {

            return new Widgets\Impress_City_Links_Widget($this->idx_api());
        });
        $app->bind('\IDX\Widgets\Impress_Lead_Login_Widget', function ($app) {

            return new Widgets\Impress_Lead_Login_Widget($this->idx_api());
        });
        $app->bind('\IDX\Widgets\Impress_Lead_Signup_Widget', function ($app) {

            return new Widgets\Impress_Lead_Signup_Widget($this->idx_api());
        });
        $app->bind('\IDX\Widgets\Impress_Showcase_Widget', function ($app) {

            return new Widgets\Impress_Showcase_Widget($this->idx_api());
        });
        $app->bind('\IDX\Widgets\IDX_Omnibar_Widget', function ($app) {

            return new Widgets\IDX_Omnibar_Widget($app->make('\IDX\Widgets\Omnibar\Create_Omnibar'));
        });
        $app->bind('\IDX\Widgets\IDX_Omnibar_Widget_Extra', function ($app) {

            return new Widgets\IDX_Omnibar_Widget_Extra($app->make('\IDX\Widgets\Omnibar\Create_Omnibar'));
        });
        $app->bind('\IDX\Shortcodes\Register_Impress_Shortcodes', function ($app) {

            return new Shortcodes\Register_Impress_Shortcodes($this->idx_api());
        });
        $app->bind('\IDX\Widgets\Omnibar\Create_Omnibar', function ($app) {

            return new Widgets\Omnibar\Create_Omnibar($app);
        });
        $app->bind('\IDX\Widgets\Omnibar\Get_Locations', function ($app) {

            return new Widgets\Omnibar\Get_Locations($this->idx_api());
        });
        $app->bind('\IDX\Shortcodes\Shortcode_Ui', function ($app) {

            return new Shortcodes\Shortcode_Ui($app->make('\IDX\Shortcodes\Register_Shortcode_For_Ui'));
        });
        $app->bind('\IDX\Shortcodes\Register_Shortcode_For_Ui', function ($app) {
            return new Shortcodes\Register_Shortcode_For_Ui($this->idx_api());
        });
        $app->bind('\IDX\Help', function ($app) {

            return new Help;
        });
        $app->bind('\IDX\Views\Omnibar_Settings', function ($app) {

            return new Views\Omnibar_Settings($this->idx_api());
        });
        $app->bind('\IDX\Migrate_Old_Table', function ($app) {

            return new Migrate_Old_Table($this->idx_api());
        });
    }
}

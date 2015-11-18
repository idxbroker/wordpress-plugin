<?php
namespace IDX;

class Help
{
    public function __construct()
    {
        add_action('load-post.php', array($this, 'add_pages_help_tabs'), 20);
        add_action('load-post-new.php', array($this, 'add_pages_help_tabs'), 20);
        add_action('current_screen', array($this, 'settings_help'));
    }

    public function settings_help()
    {
        if (!empty($_GET['page']) && $_GET['page'] === 'idx-broker') {
            $this->add_settings_help_tabs();
        }
    }

    public $tabs = array(
        // The assoc key represents the ID
        // It is NOT allowed to contain spaces
        'idx_create_wrapper' => array(
            'title' => 'Create Wrapper'
            , 'content' => '
                <strong>Create Wrapper</strong> - Wrappers set the overall styling of your IDX Broker pages.
                <br>&bull; Create new page wrappers by entering a unique page name and selecting Update.
                <br>&bull; These pages are added to your Wrappers menu, not your WordPress pages.
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1919274-automatically-create-wordpress-dynamic-wrapper" target="_blank">this article</a>.
                ',
        ),
        'idx_apply_wrapper' => array(
            'title' => 'Apply Wrapper'
            , 'content' => '
                <strong>Apply Wrapper</strong> - You may create many wrappers and use different ones for each category or page.
                <br>&bull; To apply a new wrapper within WordPress, edit the Wrapper page from the IDX Broker/Wrappers menu.
                <br>&bull; In edit mode select where to apply the wrapper in the upper right of the screen.
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1919274-automatically-create-wordpress-dynamic-wrapper" target="_blank">this article</a>.
                ',
        ),
        'idx_shortcodes' => array(
            'title' => 'IDX Shortcodes'
            , 'content' => '
                <strong>Insert Shortcode</strong> - Insert IDX Broker content in any page or post.
                <br>&bull; Select the Insert IDX Shortcode button
                <br>&bull; System and Saved Links add an external link to IDX Broker pages
                <br>&bull; Widgets add widget content into your page.
                <br>&bull; Omnibar adds a property listing search bar to any of your pages
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1917460-wordpress-plugin" target="_blank">this article</a>.
                ',
        ),
    );

    public function add_pages_help_tabs()
    {
        $id = 'idx_shortcodes';
        $data = $this->tabs['idx_shortcodes'];
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => $id
            , 'title' => __($data['title'], 'idxbroker')
            // Use the content only if you want to add something
            // static on every help tab. Example: Another title inside the tab
            , 'callback' => array($this, "prepare"),
        ));
    }

    public function add_settings_help_tabs()
    {
        $tabs = $this->tabs;
        foreach ($tabs as $id => $data) {
            $screen = get_current_screen();
            $screen->add_help_tab(array(
                'id' => $id
                , 'title' => __($data['title'], 'idxbroker')
                // Use the content only if you want to add something
                // static on every help tab. Example: Another title inside the tab
                , 'callback' => array($this, 'prepare'),
            ));
            $screen->set_help_sidebar(
                '<p><a href="https://middleware.idxbroker.com/mgmt/login.php" target="_blank">IDX Control Panel</a></p>' .
                '<p><a href="http://support.idxbroker.com/customer/en/portal/topics/784215-wordpress/articles" target="_blank">IDX Plugin Knowledgebase</a></p>' .
                '<p><a href="http://support.idxbroker.com" target="_blank">IDX Support</a></p>'
            );
        }
    }

    public function prepare($screen, $tab)
    {
        printf(
            '<p>%s</p>',
            __(
                $tab['callback'][0]->tabs[$tab['id']]['content'],
                'idxbroker'
            )
        );
    }
}

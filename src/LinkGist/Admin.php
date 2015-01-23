<?php
/**
 * Handle the plugin backend.
 */
class LinkGist_Admin
{
    /** Admin Constants **/
    const PAGE_SLUG = 'linkgist';

    /**
     * Assign Admin Action Hooks.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_enqueue_scripts', array($this, 'scripts'));
    }

    /**
     * Register the Menu.
     *
     * @return void
     */
    public function menu()
    {
        $page = add_options_page(
            'LinkGist Affiliate Marketing Tool'.__('Options', LinkGist::TEXT_DOMAIN),
            'LinkGist Affiliate Marketing Tool',
            'administrator',
            self::PAGE_SLUG,
            array($this, 'render')
        );
    }

    /**
     * Render the admin page.
     *
     * @return void
     */
    public function render()
    {
        $data = array(
            'pageSlug'  => LinkGist_Admin::PAGE_SLUG,
            'optionKey' => LinkGist::OPTION_KEY,
        );

        echo LinkGist_View::render('admin', $data);
    }

    /**
     * Load external script files for the settings page.
     *
     * @param  string  $hook
     * @return void
     */
    public function scripts($hook)
    {
        if ($hook != 'settings_page_linkgist') {
            return;
        }

        $plugin = get_plugin_data(LinkGist::FILE, false, false);
        $version = $plugin['Version'];

        wp_register_style(
            'linkgist',
            plugins_url('assets/css/jquery-ui.min.css', LinkGist::FILE),
            array(),
            $version
        );
        wp_enqueue_style('linkgist');

        wp_enqueue_script(
            'paypal-donations',
            plugins_url('assets/js/admin.js', LinkGist::FILE),
            array('jquery', 'jquery-ui-tooltip'),
            $version,
            false
        );
    }

    /**
     * Register the Settings.
     *
     * @return void
     */
    public function init()
    {
        add_settings_section(
            'account_section',
            __('Account', LinkGist::TEXT_DOMAIN),
            array($this, 'accountSectionCallback'),
            self::PAGE_SLUG
        );
        add_settings_field(
            'customer_id',
            __('Customer ID', LinkGist::TEXT_DOMAIN),
            array($this, 'textInputCallback'),
            self::PAGE_SLUG,
            'account_section',
            array(
                'id' => 'customer_id',
                'label_for' => 'customer_id',
                'description' => ''
            )
        );
        add_settings_field(
            'media_id',
            __('Media ID', LinkGist::TEXT_DOMAIN),
            array($this, 'textInputCallback'),
            self::PAGE_SLUG,
            'account_section',
            array(
                'id' => 'media_id',
                'label_for' => 'media_id',
                'description' => ''
            )
        );

        // ---

        add_settings_section(
            'option_section',
            __('Options', LinkGist::TEXT_DOMAIN),
            null,
            self::PAGE_SLUG
        );
        add_settings_field(
            'redirect_title',
            __('Redirect Title', LinkGist::TEXT_DOMAIN),
            array($this, 'textInputCallback'),
            self::PAGE_SLUG,
            'option_section',
            array(
                'id' => 'redirect_title',
                'label_for' => 'redirect_title',
                'description' => __('Once words are replaced by LinkGist this will be the new mouseover title.', LinkGist::TEXT_DOMAIN)
            )
        );
        add_settings_field(
            'replace_multiwords',
            __('Replace Multiple Words', LinkGist::TEXT_DOMAIN),
            array($this, 'replaceMultiwordsCallback'),
            self::PAGE_SLUG,
            'option_section',
            array(
                'id' => 'replace_multiwords',
                'label_for' => 'replace_multiwords',
                'description' => 'Enable to replace words multiple times.'
            )
        );
        add_settings_field(
            'amount',
            __('Amount', LinkGist::TEXT_DOMAIN),
            array($this, 'numberInputCallback'),
            self::PAGE_SLUG,
            'option_section',
            array(
                'id' => 'amount',
                'label_for' => 'amount',
                'postfix' => 'words',
                'description' => __('How many words that may be replaced.', LinkGist::TEXT_DOMAIN)
            )
        );
        add_settings_field(
            'script_mode',
            __('Script Mode', LinkGist::TEXT_DOMAIN),
            array($this, 'scriptModeCallback'),
            self::PAGE_SLUG,
            'option_section',
            array(
                'id' => 'script_mode',
                'label_for' => null,
                'description' => ''
            )
        );

        // ---

        add_settings_section(
            'commission_section',
            __('Commission', LinkGist::TEXT_DOMAIN),
            array($this, 'commissionSectionCallback'),
            self::PAGE_SLUG
        );


        register_setting(
            LinkGist::OPTION_KEY,
            LinkGist::OPTION_KEY
        );
    }


    /* -------------------------------------------------------------------------
    :: Settings Field Callbacks
    ------------------------------------------------------------------------- */

    /**
     * Header for the account section.
     *
     * @return void
     */
    public function accountSectionCallback()
    {
        printf(
            '<p>%s %s</p>',
            __('Enter your LinkGist customer and/or media ID.', LinkGist::TEXT_DOMAIN),
            sprintf(
                __('Log in %s to look up.', LinkGist::TEXT_DOMAIN),
                sprintf(
                    '<a href="https://www.linkgist.com/customer/media">%s</a>',
                    __('here', LinkGist::TEXT_DOMAIN)
                )
            )
        );
    }

    /**
     * Header for the commission section.
     *
     * @return void
     */
    public function commissionSectionCallback()
    {
        $commission = __('Check out my earned commission.', LinkGist::TEXT_DOMAIN);
        $options = get_option(LinkGist::OPTION_KEY);
        $mediaId = isset($options['media_id']) ? $options['media_id'] : '';

        printf(
            '<p>%s</p>',
            $mediaId
            ? sprintf('<a href="https://www.linkgist.com/customer/mediastats/hits/id/%s">%s</a>', $mediaId, $commission)
            : $commission
        );
    }

    /**
     * Text Input Fields.
     *
     * @param  array  $args
     * @return void
     */
    public function textInputCallback($args)
    {
        $optionKey = LinkGist::OPTION_KEY;
        $options = get_option($optionKey);
        $id = $args['id'];

        printf('<input class="regular-text" type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" />',
            $id,
            $optionKey,
            $options[$id]
        );

        if ($args['description']) {
            echo "<p class='description'>{$args['description']}</p>";
        }
    }

    /**
     * Number Input Fields.
     *
     * @param  array  $args
     * @return void
     */
    public function numberInputCallback($args)
    {
        $optionKey = LinkGist::OPTION_KEY;
        $options = get_option($optionKey);
        $id = $args['id'];

        printf('<input class="small-text" type="number" step="1" min="5" id="%1$s" name="%2$s[%1$s]" value="%3$s" /> %4$s',
            $id,
            $optionKey,
            isset($options[$id]) ? $options[$id] : 5,
            $args['postfix']
        );

        if ($args['description']) {
            echo "<p class='description'>{$args['description']}</p>";
        }
    }

    /**
     * Replace Multiple Words checkbox.
     *
     * @param  array  $args
     * @return void
     */
    public function replaceMultiwordsCallback($args)
    {
        $this->checkbox($args['id'], $args['description']);
    }

    /**
     * Script Mode Checkboxes.
     *
     * @param  array  $args
     * @return void
     */
    public function scriptModeCallback($args)
    {
        echo '<fieldset>';
        $this->checkbox('mode_all', __('All services', LinkGist::TEXT_DOMAIN), __('This enables the full potential of the LinkGist service.', LinkGist::TEXT_DOMAIN));
        $this->checkbox('mode_existing_links', __('Monetize existing links', LinkGist::TEXT_DOMAIN), __('This enables the Link Replacement service. We\'ll scan for existing links and replace the ones which are monetizable. No alterations are made to existing affiliate links or non-monetizable links.', LinkGist::TEXT_DOMAIN));
        $this->checkbox('mode_merchant_names', __('Replace merchant names to links', LinkGist::TEXT_DOMAIN), __('Replaces names and unlinked domains names of merchants with their appropriate affiliate link.', LinkGist::TEXT_DOMAIN));
        $this->checkbox('mode_cpm_campaigns', __('CPM and CPC campaigns', LinkGist::TEXT_DOMAIN), __('LinkGist exclusive CPM and CPC campaigns. Enables keyword buying for advertisers. Generates significantly higher revenue than general campaigns.', LinkGist::TEXT_DOMAIN));
        $this->checkbox('mode_contextual', __('Contextual advertising (word replacement)', LinkGist::TEXT_DOMAIN), __('In content contextual advertising. This is the Word Replacement service, which replaces words and wordgroups for relevant advertising links. The content itself remains untouched.', LinkGist::TEXT_DOMAIN));
        echo '</fieldset>';
    }

    /**
     * Generate a checkbox.
     *
     * @param  string  $id
     * @param  string  $label
     * @return void
     */
    private function checkbox($id, $label, $tooltip = '')
    {
        $optionKey = LinkGist::OPTION_KEY;
        $options = get_option($optionKey);
        $checked = isset($options[$id]) ? 'checked="checked"' : '';

        $disabled = (isset($options['mode_all']) && $id != 'mode_all' && strpos($id, 'mode_') === 0)
            ? ' disabled'
            : '';

        printf('<label for="%s">', $id);
        printf('<input name="%2$s[%1$s]" type="checkbox" id="%1$s" value="1" %3$s%4$s/>',
            $id,
            $optionKey,
            $checked,
            $disabled
        );
        printf('%s</label>%s',
            $label,
            $tooltip
                ? sprintf(' <span class="dashicons dashicons-editor-help" title="%s"></span>', $tooltip)
                : ''
        );
        printf('<br/>');
    }
}

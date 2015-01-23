<?php
/**
 * Handle code output on the frontend.
 */
class LinkGist_Frontend
{
    /**
     * Assign Frontend Action Hooks.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('wp_head', array($this, 'head'));
    }

    /**
     * Output the LinkGist code.
     *
     * @return void
     */
    public function head()
    {
        // Only run the script on single pages
        if (!is_singular()) {
            return;
        }

        // Check if LinkGist have been disabled for the post
        $disabled = get_post_meta(get_the_ID(), '_linkgist_disable', true);
        if ($disabled) {
            return;
        }

        // Output the code
        $settings = array('settings' => $this->settings());
        echo LinkGist_View::render('linkgist', $settings);
    }

    /**
     * Get the settings string.
     *
     * @return string
     */
    private function settings()
    {
        $options = get_option(LinkGist::OPTION_KEY);

        $settings = array(
            'customerID' => $options['customer_id'],
            'mediaID' => $options['media_id'],
            'scriptMode' => $this->scriptMode(),
            'redirect_title' => sprintf('\'%s\'', $options['redirect_title']),
            'replaceMultiWords' => isset($options['replace_multiwords']) ? 'true' : 'false',
            'amount' => $options['amount'],
        );

        // Clean out unused properties
        $settings = array_filter($settings);

        // Convert the array to a JS string (json_encode is another option),
        // though this replicates the generator without using "" for properties.
        $settings = implode(
            ',',
            array_map(
                function ($v, $k) {
                    return $k . ':' . $v;
                },
                $settings,
                array_keys($settings)
            )
        );

        return $settings;
    }

    /**
     * Get the numerical script mode.
     *
     * @return int
     */
    private function scriptMode()
    {
        $modes = array(
            'mode_all' => 1,
            'mode_existing_links' => 2,
            'mode_merchant_names' => 4,
            'mode_cpm_campaigns' => 8,
            'mode_contextual' => 16
        );

        $mode = 0;
        $options = get_option(LinkGist::OPTION_KEY);

        foreach ($modes as $key => $value) {
            if (isset($options[$key])) {
                $mode += $value;
            }

            // Exit from further additions if all services is set already
            if ($mode == 1) {
                break;
            }
        }

        return $mode;
    }
}

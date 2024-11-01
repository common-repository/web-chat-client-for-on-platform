<?php

/**
 * Plugin Name: Web Chat Client for ON Platform
 * Description: A WordPress Plugin for embedding the ON Platform Web Chat Client
 * Author: ON Platform
 * Author URI: https://www.onplatform.com
 * Version: 1.0.2
 * License: GPL2+
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}



class ONPLATFORM_WebChatClient
{
    private $options;
    private $botScriptUrl;

    public function __construct()
    {
        $this->prefix = 'onplat_';
        $this->optionsName = $this->prefix . 'client_settings';
        $this->defaults = [
            $this->prefix . 'client_id' => '',
            $this->prefix . 'display_mode' => 'WIDGET',
            $this->prefix . 'auto_open' => false,
            $this->prefix . 'auto_open_mobile' => false,
            $this->prefix . 'should_hide_widget_button' => false,
            $this->prefix . 'initial_prompt' => '',
            $this->prefix . 'open_image_url' => '',
            $this->prefix . 'close_image_url' => '',
            $this->prefix . 'display_widget_urls' => 'all',
            $this->prefix . 'include_urls' => ''
        ];
        $this->options = wp_parse_args(get_option($this->optionsName), $this->defaults);
        $this->botScriptUrl = "https://web-chat.services.onplatform.com/v2/static/bot-client.js";


        //Actions
        add_action('admin_init', [$this, 'initPluginAdminPage']);
        add_action('admin_menu', [$this, 'addPluginAdminPage']);
        add_action('admin_enqueue_scripts', [$this, 'loadCustomSettingsScripts']);

        // Only load the Client if Client ID is set, not empty and a valid uuid
        if ($this->options[$this->prefix . 'client_id'] !== '' && $this->isValidUuid($this->options[$this->prefix . 'client_id'])) {
            add_action('wp_enqueue_scripts', [$this, 'loadClientScripts']);
        }
    }

    public function loadCustomSettingsScripts()
    {
        global $pagenow;
        // Get the current page
        if (isset($_GET['page'])) {
            $current_page = esc_html(sanitize_text_field($_GET['page']));
        } else {
            $current_page = '';
        }
        // Check both current page base and slug
        if (($pagenow == 'tools.php') && ($current_page == 'onplatform-settings-admin')) {
            // Now enqueue your script and styles here
            //enqueue media js library to use wordpress media library in our plugin / theme.
            wp_enqueue_media();
            $url = plugin_dir_url(__FILE__) . '/assets/js/onplatform-plugin.js';
            wp_register_script('onplatform-custom-settings-script', $url, ['jquery'], '1.0.0', true);
            wp_enqueue_script('onplatform-custom-settings-script');
        }
    }

    /**
     * Add options page
     */
    public function addPluginAdminPage()
    {
        // This page will be under "Tools"
        add_management_page(
            'OnPlatform Settings',
            'OnPlatform Settings',
            'manage_options',
            'onplatform-settings-admin',
            [$this, 'createPluginAdminPage']
        );
    }

    public function createPluginAdminPage()
    { ?>
        <div class="wrap onplatform-settings">
            <form method="post" action="options.php">
                <?php
                settings_fields($this->prefix . 'client_settings_group');
                do_settings_sections($this->prefix . 'client_settings-admin');
                submit_button();
                ?>
            </form>
        </div>
<?php }

    /**
     * Register and add settings
     */
    public function initPluginAdminPage()
    {

        register_setting(
            $this->prefix . 'client_settings_group', // Option group
            $this->optionsName, // Option name
            [$this, 'sanitize'] // Sanitize
        );

        add_settings_section(
            $this->prefix . 'client_settings_section', // ID
            'OnPlatform Web Chat Client Settings', // Title
            [$this, 'printSectionInfo'], // Callback
            $this->prefix . 'client_settings-admin' // Page
        );

        add_settings_field(
            $this->prefix . 'client_id', // ID
            'Client ID', // Title
            [$this, 'clientIdCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'auto_open', // ID
            'Auto Open Chat Window', // Title
            [$this, 'autoOpenCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'auto_open_mobile', // ID
            'Auto Open Chat Window on Mobile', // Title
            [$this, 'autoOpenMobileCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'should_hide_widget_button', // ID
            'Hide Widget Button', // Title
            [$this, 'hideWidgetButtonCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'initial_prompt', // ID
            'Initial Chat Prompt', // Title
            [$this, 'initialPromptCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'open_image_url', // ID
            'Open Image URL', // Title
            [$this, 'openImageCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'close_image_url', // ID
            'Close Image URL', // Title
            [$this, 'closeImageCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'display_widget_urls', // ID
            'Display Widget', // Title
            [$this, 'displayWidgetCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );

        add_settings_field(
            $this->prefix . 'include_urls', // ID
            'Embed Client on the listed URLs', // Title
            [$this, 'includeUrlsCallback'], // Callback
            $this->prefix . 'client_settings-admin', // Page
            $this->prefix . 'client_settings_section' // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = [];
        if (isset($input[$this->prefix . 'client_id'])) {
            $new_input[$this->prefix . 'client_id'] = sanitize_text_field($input[$this->prefix . 'client_id']);
        }

        if (isset($input[$this->prefix . 'display_mode'])) {
            $new_input[$this->prefix . 'display_mode'] = sanitize_text_field($input[$this->prefix . 'display_mode']);
        }

        if (isset($input[$this->prefix . 'auto_open'])) {
            $new_input[$this->prefix . 'auto_open'] = sanitize_text_field($input[$this->prefix . 'auto_open']);
        }

        if (isset($input[$this->prefix . 'auto_open_mobile'])) {
            $new_input[$this->prefix . 'auto_open_mobile'] = sanitize_text_field($input[$this->prefix . 'auto_open_mobile']);
        }

        if (isset($input[$this->prefix . 'should_hide_widget_button'])) {
            $new_input[$this->prefix . 'should_hide_widget_button'] = sanitize_text_field($input[$this->prefix . 'should_hide_widget_button']);
        }

        if (isset($input[$this->prefix . 'initial_prompt'])) {
            $new_input[$this->prefix . 'initial_prompt'] = sanitize_text_field($input[$this->prefix . 'initial_prompt']);
        }

        if (isset($input[$this->prefix . 'open_image_url'])) {
            $new_input[$this->prefix . 'open_image_url'] = sanitize_text_field($input[$this->prefix . 'open_image_url']);
        }

        if (isset($input[$this->prefix . 'close_image_url'])) {
            $new_input[$this->prefix . 'close_image_url'] = sanitize_text_field($input[$this->prefix . 'close_image_url']);
        }

        if (isset($input[$this->prefix . 'include_urls'])) {
            $new_input[$this->prefix . 'include_urls'] = sanitize_textarea_field($input[$this->prefix . 'include_urls']);
        }

        if (isset($input[$this->prefix . 'display_widget_urls'])) {
            $new_input[$this->prefix . 'display_widget_urls'] = sanitize_text_field($input[$this->prefix . 'display_widget_urls']);
        }

        return $new_input;
    }

    public function renderTextField($key)
    {
        printf(
            '<input type="text" id="%2$s" class="regular-text" name="%3$s[%2$s]" value="%1$s" />',
            !empty($this->options[$key]) ? esc_attr($this->options[$key]) : '',
            esc_attr($key),
            esc_attr($this->optionsName),
        );
    }

    public function renderCheckboxField($key)
    {
        printf(
            '<input type="checkbox" id="%2$s" name="%3$s[%2$s]" %1$s />',
            checked(!empty($this->options[$key]), true, false),
            esc_attr($key),
            esc_attr($this->optionsName),
        );
    }

    public function renderImageField($key, $buttonId)
    {
        printf(
            '<input type="text" id="%2$s" name="%3$s[%2$s]" class="regular-text" value="%1$s">' .
                '<input type="button" name="%4$s" id="%4$s" class="button-secondary" value="Upload Image">',
            !empty($this->options[$key]) ? esc_url($this->options[$key]) : '',
            esc_attr($key),
            esc_attr($this->optionsName),
            esc_attr($buttonId)
        );
    }

    public function renderRadioButtonField($key)
    {
        printf(
            '<div class="radio-group" data-selected="%1$s">' .
                '<input type="radio" name="%5$s[%4$s]" value="all" %2$s />All URLs<br />' .
                '<input type="radio" name="%5$s[%4$s]" value="selected" %3$s />Selected URLs' .
                '</div>',
            !empty($this->options[$key]) ? esc_attr($this->options[$key]) : '',
            checked('all', $this->options[$key], false),
            checked('selected', $this->options[$key], false),
            esc_attr($key),
            esc_attr($this->optionsName),
        );
    }

    public function renderTextAreaField($key)
    {
        printf(
            '<textarea class="regular-text" cols="25" rows="5" id="%2$s" name="%3$s[%2$s]">%1$s</textarea>',
            !empty($this->options[$key]) ? esc_attr($this->options[$key]) : '',
            esc_attr($key),
            esc_attr($this->optionsName),
        );
    }

    /**
     * Print the Section text
     */
    public function printSectionInfo()
    {
        echo '<p>' . esc_html('Enter your settings below:') . '</p>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function initialPromptCallback()
    {
        return $this->renderTextField($this->prefix . 'initial_prompt');
    }

    public function clientIdCallback()
    {
        return $this->renderTextField($this->prefix . 'client_id');
    }

    public function autoOpenCallback()
    {
        return $this->renderCheckboxField($this->prefix . 'auto_open');
    }

    public function autoOpenMobileCallback()
    {
        return $this->renderCheckboxField($this->prefix . 'auto_open_mobile');
    }

    public function hideWidgetButtonCallback()
    {
        return $this->renderCheckboxField($this->prefix . 'should_hide_widget_button');
    }

    public function openImageCallback()
    {
        return $this->renderImageField($this->prefix . 'open_image_url', 'open-upload-btn');
    }

    public function closeImageCallback()
    {
        return $this->renderImageField($this->prefix . 'close_image_url', 'close-upload-btn');
    }

    public function displayWidgetCallback()
    {
        return $this->renderRadioButtonField($this->prefix . 'display_widget_urls');
    }

    public function includeUrlsCallback()
    {
        return $this->renderTextAreaField($this->prefix . 'include_urls');
    }

    public function setAttribute($setting, $value)
    {
        if (!empty($value) && is_bool($value)) {
            return "el.setAttribute('{$setting}', 'true');";
        }

        if (!empty($value)) {
            return "el.setAttribute('{$setting}', '{$value}');";
        }
    }

    public function setDisplayMode()
    {
        if ($this->options[$this->prefix . 'should_hide_widget_button'] == true) {
            $this->options[$this->prefix . 'display_mode'] = "WIDGET_WITHOUT_BUTTON";
        } else {
            $this->options[$this->prefix . 'display_mode'] = "WIDGET";
        }

        return $this->options[$this->prefix . 'display_mode'];
    }

    public function checkIncludedUrls($current_url)
    {
        $includeurls = explode("\n", str_replace("\r", "", trim($this->options[$this->prefix .  'include_urls'])));

        $includeurls = array_map(function ($url) {
            if (strpos($url, "*") !== false) {
                return $url;
            }

            return trailingslashit($url);
        }, $includeurls);

        foreach ($includeurls as $wUrl) {
            $pattern = preg_quote($wUrl, '/');
            $pattern = str_replace('\*', '.+', $pattern);
            $matched = preg_match('/^' . $pattern . '$/i', $current_url);
            if ($matched > 0) {
                return true;
            }
        }

        return false;
    }

    public function getCurrentUrl()
    {
        global $wp;
        $current_url = trailingslashit(home_url(add_query_arg([], $wp->request)));

        return $current_url;
    }

    public function checkFilter($key)
    {
        return array_map(function ($url) {
            return trailingslashit($url);
        }, apply_filters($key, []));
    }

    public function shouldEmbedClient()
    {
        $current_url = $this->getCurrentUrl();
        $includedUrlsFromFilter = $this->checkFilter('onplatform_included_urls');
        $excludedUrlsFromFilter = $this->checkFilter('onplatform_excluded_urls');

        if ($this->options[$this->prefix . 'display_widget_urls'] == "all") {
            if (count($includedUrlsFromFilter) > 0 && in_array($current_url, $includedUrlsFromFilter)) {
                return true;
            }

            if (count($excludedUrlsFromFilter) > 0 && in_array($current_url, $excludedUrlsFromFilter)) {
                return false;
            }

            return true;
        }

        if ($this->options[$this->prefix . 'display_widget_urls'] == "selected") {
            if (count($includedUrlsFromFilter) > 0 && in_array($current_url, $includedUrlsFromFilter)) {
                return true;
            }

            if (count($excludedUrlsFromFilter) > 0 && in_array($current_url, $excludedUrlsFromFilter)) {
                return false;
            }

            if ($this->checkIncludedUrls($current_url)) {
                return true;
            }

            return false;
        }
    }

    public function loadClientScripts()
    {
        if ($this->shouldEmbedClient()) {
            wp_enqueue_script('onplatform-bot-client', $this->botScriptUrl, [], null, true);
            wp_add_inline_script(
                'onplatform-bot-client',
                "(function() {
                    var el = document.createElement('on-chat-bot-client');
                    " . $this->setAttribute('client-id', $this->options[$this->prefix . 'client_id']) . "
                    " . $this->setAttribute('display-mode', $this->setDisplayMode()) . "
                    " . $this->setAttribute('should-auto-open', $this->options[$this->prefix . 'auto_open']) . "
                    " . $this->setAttribute('should-auto-open-mobile', $this->options[$this->prefix . 'auto_open_mobile']) . "
                    " . $this->setAttribute('should-hide-widget-button', $this->options[$this->prefix . 'should_hide_widget_button']) . "
                    " . $this->setAttribute('initial-prompt', $this->options[$this->prefix . 'initial_prompt']) . "
                    " . $this->setAttribute('chat-open-image-url', $this->options[$this->prefix . 'open_image_url']) . "
                    " . $this->setAttribute('chat-close-image-url', $this->options[$this->prefix . 'close_image_url']) . "
                    document.body.appendChild(el);
                    })();
                ",
                'before'
            );
        }
    }

    /**
     * Check if a given string is a valid UUID
     * 
     * @param   string  $uuid   The string to check
     * @return  boolean
     */
    public function isValidUuid($uuid)
    {
        if (!is_string($uuid) || (!preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid))) {
            return false;
        }

        return true;
    }
} //End Class

/**
 * Instantiate this class to ensure the action and filter hooks are hooked.
 * This instantiation can only be done once (see it's __construct() to understand why.)
 */

new ONPLATFORM_WebChatClient();

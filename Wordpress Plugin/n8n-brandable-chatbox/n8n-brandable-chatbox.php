<?php
/*
Plugin Name: Brandable Custom Chatbox for N8N
Description: A brandable chat widget that connects to an n8n Webhook. Adds a shortcode [n8n_brandable_chatbox] and optional auto-injection.
Version: 1.0.0
Author: Muhammad Omer Fayyaz<omerfayyaz.com>
Required WordPress version: 6.0
Required PHP version: 8.0
License: MIT
*/

if (!defined('ABSPATH')) {
    exit;
}

class N8N_Brandable_Chatbox_Plugin {
    const OPTION_KEY = 'n8n_brandable_chatbox_options';

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_footer', [$this, 'maybe_auto_inject'], 999);
        add_shortcode('n8n_brandable_chatbox', [$this, 'shortcode_handler']);
        add_shortcode('n8n_brandable_chatbox_fullscreen', [$this, 'fullscreen_shortcode_handler']);
    }

    private function get_default_options() {
        return [
            'webhook_url' => '',
            'method' => 'POST',
            'headers_json' => '',
            'brand_color' => '#2563eb',
            'accent_color' => '#0ea5e9',
            'bot_name' => 'Chatbox',
            'bot_avatar_url' => '',
            'user_avatar_url' => '',
            'welcome_message' => 'Hi! How can I help you?',
            'launcher_text' => '',
            'launcher_text' => '',
            'launcher_variant' => 'icon',
            'position' => 'right',
            'z_index' => 999999,
            'open_by_default' => false,
            'placeholder' => 'Type your message...',
            'storage_key' => 'n8n-brandable-chatbox',
            'typing_indicator_text' => 'Typing...',
            'dark_mode' => false,
            'allow_html' => false,
            'extra_context_json' => '',
            'response_field' => 'reply',
            'max_messages' => 200,
            'session_ttl_minutes' => 0,
            'auto_inject' => false,
            'dispatch_events' => false,
            'display_mode' => 'widget',
        ];
    }

    private function get_options() {
        $stored = get_option(self::OPTION_KEY, []);
        $defaults = $this->get_default_options();
        return wp_parse_args(is_array($stored) ? $stored : [], $defaults);
    }

    public function register_admin_menu() {
        add_options_page(
            'Brandable Custom Chatbox for N8N',
            'Brandable Custom Chatbox for N8N',
            'manage_options',
            'n8n-brandable-chatbox',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting(self::OPTION_KEY, self::OPTION_KEY, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_options'],
            'default' => $this->get_default_options(),
        ]);

        add_settings_section('n8n_brandable_chatbox_main', 'Chatbox Settings', function () {
            echo '<p>Configure the chatbox that connects to your n8n Webhook.</p>';
        }, 'n8n-brandable-chatbox');

        $fields = [
            ['webhook_url', 'Webhook URL', 'text'],
            ['method', 'HTTP Method', 'text'],
            ['headers_json', 'Headers (JSON)', 'textarea'],
            ['brand_color', 'Brand Color', 'text'],
            ['accent_color', 'Accent Color', 'text'],
            ['bot_name', 'Bot Name', 'text'],
            ['bot_avatar_url', 'Bot Avatar URL', 'text'],
            ['user_avatar_url', 'User Avatar URL', 'text'],
            ['welcome_message', 'Welcome Message', 'textarea'],
            ['launcher_text', 'Launcher Button Text (aria-label)', 'text'],
            ['launcher_text', 'Launcher Button Text', 'text'],
            ['launcher_variant', 'Launcher Style (icon|text|icon-text)', 'text'],
            ['position', 'Position (left|right)', 'text'],
            ['z_index', 'z-index', 'number'],
            ['open_by_default', 'Open by Default', 'checkbox'],
            ['placeholder', 'Input Placeholder', 'text'],
            ['storage_key', 'Storage Key', 'text'],
            ['typing_indicator_text', 'Typing Indicator Text', 'text'],
            ['dark_mode', 'Dark Mode', 'checkbox'],
            ['allow_html', 'Allow HTML in Responses', 'checkbox'],
            ['extra_context_json', 'Extra Context (JSON)', 'textarea'],
            ['response_field', 'Response Field (JSON key)', 'text'],
            ['max_messages', 'Max Messages', 'number'],
            ['session_ttl_minutes', 'Session TTL (minutes, 0 disables)', 'number'],
            ['auto_inject', 'Auto-inject on all pages', 'checkbox'],
            ['dispatch_events', 'Dispatch window CustomEvents', 'checkbox'],
        ];

        foreach ($fields as [$key, $label, $type]) {
            add_settings_field($key, esc_html($label), function () use ($key, $type) {
                $opts = $this->get_options();
                $value = isset($opts[$key]) ? $opts[$key] : '';
                $name = self::OPTION_KEY . "[$key]";
                if ($type === 'checkbox') {
                    printf('<input type="checkbox" name="%s" value="1" %s />', esc_attr($name), checked((bool)$value, true, false));
                } elseif ($type === 'textarea') {
                    printf('<textarea name="%s" rows="5" cols="50">%s</textarea>', esc_attr($name), esc_textarea($value));
                } elseif ($type === 'number') {
                    printf('<input type="number" name="%s" value="%s" />', esc_attr($name), esc_attr($value));
                } else {
                    printf('<input type="text" class="regular-text" name="%s" value="%s" />', esc_attr($name), esc_attr($value));
                }
            }, 'n8n-brandable-chatbox', 'n8n_brandable_chatbox_main');
        }
    }

    public function sanitize_options($input) {
        $defaults = $this->get_default_options();
        $out = [];
        $out['webhook_url'] = isset($input['webhook_url']) ? esc_url_raw($input['webhook_url']) : $defaults['webhook_url'];
        $out['method'] = isset($input['method']) ? sanitize_text_field($input['method']) : $defaults['method'];
        $out['headers_json'] = isset($input['headers_json']) ? wp_kses_post(wp_unslash($input['headers_json'])) : '';
        $out['brand_color'] = isset($input['brand_color']) ? sanitize_text_field($input['brand_color']) : $defaults['brand_color'];
        $out['accent_color'] = isset($input['accent_color']) ? sanitize_text_field($input['accent_color']) : $defaults['accent_color'];
        $out['bot_name'] = isset($input['bot_name']) ? sanitize_text_field($input['bot_name']) : $defaults['bot_name'];
        $out['bot_avatar_url'] = isset($input['bot_avatar_url']) ? esc_url_raw($input['bot_avatar_url']) : '';
        $out['user_avatar_url'] = isset($input['user_avatar_url']) ? esc_url_raw($input['user_avatar_url']) : '';
        $out['welcome_message'] = isset($input['welcome_message']) ? wp_kses_post(wp_unslash($input['welcome_message'])) : $defaults['welcome_message'];
        $out['position'] = isset($input['position']) && in_array($input['position'], ['left', 'right'], true) ? $input['position'] : 'right';
        $out['launcher_text'] = isset($input['launcher_text']) ? sanitize_text_field($input['launcher_text']) : '';
        $out['launcher_variant'] = isset($input['launcher_variant']) && in_array($input['launcher_variant'], ['icon','text','icon-text'], true) ? $input['launcher_variant'] : 'icon';
        $out['z_index'] = isset($input['z_index']) ? intval($input['z_index']) : $defaults['z_index'];
        $out['open_by_default'] = !empty($input['open_by_default']) ? true : false;
        $out['launcher_text'] = isset($input['launcher_text']) ? sanitize_text_field($input['launcher_text']) : '';
        $out['placeholder'] = isset($input['placeholder']) ? sanitize_text_field($input['placeholder']) : $defaults['placeholder'];
        $out['storage_key'] = isset($input['storage_key']) ? sanitize_text_field($input['storage_key']) : $defaults['storage_key'];
        $out['typing_indicator_text'] = isset($input['typing_indicator_text']) ? sanitize_text_field($input['typing_indicator_text']) : $defaults['typing_indicator_text'];
        $out['dark_mode'] = !empty($input['dark_mode']) ? true : false;
        $out['allow_html'] = !empty($input['allow_html']) ? true : false;
        $out['extra_context_json'] = isset($input['extra_context_json']) ? wp_kses_post(wp_unslash($input['extra_context_json'])) : '';
        $out['response_field'] = isset($input['response_field']) ? sanitize_text_field($input['response_field']) : $defaults['response_field'];
        $out['max_messages'] = isset($input['max_messages']) ? max(1, intval($input['max_messages'])) : $defaults['max_messages'];
        $out['session_ttl_minutes'] = isset($input['session_ttl_minutes']) ? max(0, intval($input['session_ttl_minutes'])) : 0;
        $out['auto_inject'] = !empty($input['auto_inject']) ? true : false;
        $out['dispatch_events'] = !empty($input['dispatch_events']) ? true : false;
        $out['display_mode'] = isset($input['display_mode']) && in_array($input['display_mode'], ['widget', 'fullscreen'], true) ? $input['display_mode'] : $defaults['display_mode'];
        return $out;
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>Brandable Custom Chatbox for N8N</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields(self::OPTION_KEY);
        do_settings_sections('n8n-brandable-chatbox');
        submit_button();
        echo '</form>';
        echo '<p><strong>Shortcodes:</strong> [n8n_brandable_chatbox] or [n8n_brandable_chatbox_fullscreen]</p>';
        echo '<p><strong>Note:</strong> For headers/extra context, provide valid JSON objects.</p>';
        echo '</div>';
    }

    public function enqueue_frontend_assets() {
        $handle = 'n8n-brandable-chatbox-widget';
        $src = plugins_url('assets/js/n8n-brandable-chatbox.js', __FILE__);
        wp_register_script($handle, $src, [], '1.0.0', true);
        wp_enqueue_script($handle);
    }

    private function parse_json_or_default($json_str, $default = []) {
        if (!is_string($json_str) || trim($json_str) === '') {
            return $default;
        }
        $decoded = json_decode($json_str, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return $default;
    }

    private function output_init_script($overrides = []) {
        $opts = wp_parse_args($overrides, $this->get_options());
        $headers = $this->parse_json_or_default($opts['headers_json'], []);
        $extra = $this->parse_json_or_default($opts['extra_context_json'], []);
        $webhookUrl = esc_url_raw($opts['webhook_url']);
        if (empty($webhookUrl)) {
            return; // do not init when missing URL
        }

        $config = [
            'webhookUrl' => $webhookUrl,
            'method' => $opts['method'],
            'headers' => $headers,
            'brandColor' => $opts['brand_color'],
            'accentColor' => $opts['accent_color'],
            'botName' => $opts['bot_name'],
            'botAvatarUrl' => $opts['bot_avatar_url'],
            'userAvatarUrl' => $opts['user_avatar_url'],
            'welcomeMessage' => $opts['welcome_message'],
            'launcherText' => $opts['launcher_text'],
            'position' => $opts['position'],
            'launcherText' => $opts['launcher_text'],
            'launcherVariant' => $opts['launcher_variant'],
            'zIndex' => (int) $opts['z_index'],
            'openByDefault' => (bool) $opts['open_by_default'],
            'placeholder' => $opts['placeholder'],
            'storageKey' => $opts['storage_key'],
            'typingIndicatorText' => $opts['typing_indicator_text'],
            'darkMode' => (bool) $opts['dark_mode'],
            'allowHTMLInResponses' => (bool) $opts['allow_html'],
            'extraContext' => $extra,
            'maxMessages' => (int) $opts['max_messages'],
            'sessionTtlMinutes' => (int) $opts['session_ttl_minutes'],
            'displayMode' => $opts['display_mode'],
            'mountId' => isset($opts['mount_id']) ? $opts['mount_id'] : '',
        ];

        $config_json = wp_json_encode($config);
        $response_field = esc_js($opts['response_field']);
        $dispatch_events = !empty($opts['dispatch_events']);

        echo "\n<script>\n(function(){\n  try {\n    var cfg = $config_json;\n    cfg.transformRequest = function(text, ctx){ return { message: text, sessionId: ctx.sessionId, metadata: ctx.metadata }; };\n    cfg.transformResponse = function(data){\n      var allowHtml = !!cfg.allowHTMLInResponses;\n      function maybeHtml(value){\n        if (allowHtml && typeof value === 'string' && /<[^>]+>/.test(value)) { return { html: value }; }\n        return value;\n      }\n      if (data && typeof data === 'object') {\n        if (Object.prototype.hasOwnProperty.call(data, 'html') && typeof data.html === 'string') return { html: data.html };\n        if (Object.prototype.hasOwnProperty.call(data, '$response_field')) return maybeHtml(data['$response_field']);\n        if (Object.prototype.hasOwnProperty.call(data, 'reply')) return maybeHtml(data['reply']);\n        if (Object.prototype.hasOwnProperty.call(data, 'output')) return maybeHtml(data['output']);\n        if (Object.prototype.hasOwnProperty.call(data, 'message')) return maybeHtml(data['message']);\n        if (Object.prototype.hasOwnProperty.call(data, 'text')) return maybeHtml(data['text']);\n      }\n      if (typeof data === 'string') return maybeHtml(data);\n      return 'No response';\n    };\n    " . ($dispatch_events ? "cfg.onEvent = function(eventName, detail){ try { window.dispatchEvent(new CustomEvent('n8nbrandablechatbox:' + eventName, { detail: detail })); } catch(e){} };" : "") . "\n    if (window.N8NbrandableChatbox && typeof window.N8NbrandableChatbox.init === 'function') {\n      window.N8NbrandableChatbox.init(cfg);\n    }\n  } catch(e) { if (window.console && console.error) console.error(e); }\n})();\n</script>\n";
    }

    public function maybe_auto_inject() {
        $opts = $this->get_options();
        if (!empty($opts['auto_inject'])) {
            $this->output_init_script();
        }
    }

    public function shortcode_handler($atts = []) {
        $atts = shortcode_atts([
            'webhook_url' => '',
            'method' => '',
            'brand_color' => '',
            'accent_color' => '',
            'bot_name' => '',
            'bot_avatar_url' => '',
            'user_avatar_url' => '',
            'welcome_message' => '',
            'launcher_text' => '',
            'launcher_text' => '',
            'launcher_variant' => '',
            'position' => '',
            'z_index' => '',
            'open_by_default' => '',
            'placeholder' => '',
            'storage_key' => '',
            'typing_indicator_text' => '',
            'dark_mode' => '',
            'allow_html' => '',
            'headers_json' => '',
            'extra_context_json' => '',
            'response_field' => '',
            'max_messages' => '',
            'session_ttl_minutes' => '',
            'dispatch_events' => '',
            'display_mode' => '',
        ], $atts, 'n8n_brandable_chatbox');

        $overrides = [];
        foreach ($atts as $k => $v) {
            if ($v !== '') {
                if (in_array($k, ['open_by_default','dark_mode','allow_html','dispatch_events'], true)) {
                    $overrides[$k] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
                } elseif (in_array($k, ['z_index','max_messages','session_ttl_minutes'], true)) {
                    $overrides[$k] = intval($v);
                } elseif ($k === 'display_mode') {
                    $mode = strtolower($v);
                    if (in_array($mode, ['widget', 'fullscreen'], true)) {
                        $overrides[$k] = $mode;
                    }
                } else {
                    $overrides[$k] = $v;
                }
            }
        }

        $placeholder_id = '';
        if (isset($overrides['display_mode']) && $overrides['display_mode'] === 'fullscreen') {
            $placeholder_id = 'n8n-chatbox-' . wp_generate_uuid4();
            $overrides['mount_id'] = $placeholder_id;
        }

        ob_start();
        if ($placeholder_id) {
            printf(
                '<div id="%1$s" class="n8n-brandable-chatbox-fullscreen" style="position:relative;width:100%%;min-height:100vh;"></div>',
                esc_attr($placeholder_id)
            );
        }
        $this->output_init_script($overrides);
        return ob_get_clean();
    }

    public function fullscreen_shortcode_handler($atts = []) {
        $atts['display_mode'] = 'fullscreen';
        if (!isset($atts['open_by_default'])) {
            $atts['open_by_default'] = 'true';
        }
        return $this->shortcode_handler($atts);
    }
}

new N8N_Brandable_Chatbox_Plugin();



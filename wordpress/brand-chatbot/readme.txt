=== Brand Chatbot for n8n ===
Contributors: you
Tags: chatbot, n8n, webhook, support, assistant
Requires at least: 5.5
Tested up to: 6.5
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/license/mit

A brandable chat widget that connects to an n8n Webhook. Provides a shortcode and optional auto-injection site-wide.

== Description ==
This plugin embeds a lightweight chat widget on your site and sends user messages to your n8n Webhook. Customize colors, avatars, welcome text, and more.

== Installation ==
1. Upload the `brand-chatbot` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings → Brand Chatbot and set your Webhook URL and options.

== Usage ==
- Shortcode: `[brand_chatbot]`
  - Attributes (optional): `webhook_url, brand_color, accent_color, bot_name, bot_avatar_url, user_avatar_url, welcome_message, position, z_index, open_by_default, placeholder, storage_key, typing_indicator_text, dark_mode, allow_html, headers_json, extra_context_json, response_field, max_messages`.
- Auto-inject: Enable “Auto-inject on all pages” in settings.

== Changelog ==
= 1.0.0 =
* Initial release.



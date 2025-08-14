=== N8N Brandable Chatbot ===
Contributors: Omer Fayyaz, Vividsol.ai
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
1. Upload the `n8n-brandable-chatbot` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings → Brand Chatbot and set your Webhook URL and options.

== Usage ==
- Shortcode: `[n8n_brandable_chatbot]`
  - Attributes (optional): `webhook_url, brand_color, accent_color, bot_name, bot_avatar_url, user_avatar_url, welcome_message, launcher_text, launcher_variant, position, z_index, open_by_default, placeholder, storage_key, typing_indicator_text, dark_mode, allow_html, headers_json, extra_context_json, response_field, max_messages, session_ttl_minutes, dispatch_events`.
  - `launcher_variant` accepts `icon`, `text`, or `icon-text`.
- Auto-inject: Enable “Auto-inject on all pages” in settings.
  - Optional: “Dispatch window CustomEvents” to receive events on `window` like `n8nbrandablechatbot:ready`, `n8nbrandablechatbot:toggle`, `n8nbrandablechatbot:message`, `n8nbrandablechatbot:error`.

== Settings ==
- Webhook URL, colors, avatars, welcome text
- Launcher Button Text, Launcher Style (icon|text|icon-text)
- Position (left|right), dark mode, open by default, z-index
- Allow HTML in Responses (client-side sanitized; also sanitize server-side)
- Extra context / headers (JSON)
- Response Field (preferred JSON key), Max Messages, Session TTL (minutes)
- Auto-inject on all pages; optional window CustomEvents

== Changelog ==
= 1.0.0 =
* Initial release.



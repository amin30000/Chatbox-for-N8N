# N8N Brandable Chatbot (n8n Webhook)

A lightweight, brandable chat widget you can embed on any website that talks to your n8n Webhook.

## Files
- `n8n-brandable-chatbot.js`: The widget script.
- `examples/basic.html`: Minimal example (light theme).
- `examples/dark.html`: Dark theme example with HTML responses and left position.

## Quick start
1. Open `examples/basic.html` (or `examples/dark.html`).
2. Update the `webhookUrl` to your n8n Webhook URL.
3. Optionally set `launcherVariant` to `icon`, `text`, or `icon-text` and set `launcherText`.
4. Open the HTML file in a browser.

To embed into your site:
```html
<script src="/path/to/n8n-brandable-chatbot.js"></script>
<script>
window.N8NbrandableChatbot.init({
  webhookUrl: "https://your-n8n.example.com/webhook/abc123",
  botName: "Acme Assistant",
  brandColor: "#2563eb",
  accentColor: "#0ea5e9",
  welcomeMessage: "Hi! How can I help?",
  allowHTMLInResponses: true,
  launcherVariant: "icon-text",  // 'icon' | 'text' | 'icon-text'
  launcherText: "Chat with us",
});
</script>
```

## n8n webhook contract
- Request (default): `{ message: string, sessionId: string, metadata: object }`
- Response: either `{ "reply": "..." }` JSON or a plain text body
- CORS headers recommended:
  - `Access-Control-Allow-Origin: *` (or your site origin)
  - `Access-Control-Allow-Headers: Content-Type, x-api-key`
  - `Access-Control-Allow-Methods: POST, OPTIONS`

### Example n8n Function
```javascript
return [{ reply: `You said: ${$json.message}` }];
```

## Options
- `webhookUrl` (required)
- `method` (default `POST`)
- `headers` (object)
- `brandColor`, `accentColor`
- `botName`, `botAvatarUrl`, `userAvatarUrl`
- `welcomeMessage`, `placeholder`
- `position` (left|right), `zIndex`, `openByDefault`, `darkMode`
- `launcherVariant` ('icon' | 'text' | 'icon-text'), `launcherText`
- `allowHTMLInResponses` (bool). If true, the widget auto-renders replies that contain HTML tags. You can also return `{ html: "..." }`.
- `extraContext` (object)
- `transformRequest(text, ctx)` -> payload
- `transformResponse(data)` -> `string` or `{ text, html }`
- `onEvent(eventName, data)` -> hooks (ready, toggle, message, error)
- `storageKey`, `maxMessages`, `typingIndicatorText`
- `sessionTtlMinutes` (number, minutes). If > 0, a conversation expires after this inactivity period since the last user or bot message. Expiry also applies if the site is closed and later revisited.

Notes
- Safe-area aware placement for mobile; high z-index; min-height for the panel.
- Response field fallback order: `reply` → `output` → `message` → `text` (or `html`).
- Footer shows “Powered by Vividsol.ai”.

## License
MIT

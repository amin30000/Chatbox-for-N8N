# Brand Chatbot (n8n Webhook)

A lightweight, brandable chat widget you can embed on any website that talks to your n8n Webhook.

## Files
- `brand-chatbot.js`: The widget script.
- `examples/basic.html`: Minimal example (light theme).
- `examples/dark.html`: Dark theme example with HTML responses and left position.

## Quick start
1. Open `examples/basic.html` (or `examples/dark.html`).
2. Update the `webhookUrl` to your n8n Webhook URL.
3. Open the HTML file in a browser.

To embed into your site:
```html
<script src="/path/to/brand-chatbot.js"></script>
<script>
window.BrandChatbot.init({
  webhookUrl: "https://your-n8n.example.com/webhook/abc123",
  botName: "Acme Assistant",
  brandColor: "#2563eb",
  accentColor: "#0ea5e9",
  welcomeMessage: "Hi! How can I help?",
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
- `allowHTMLInResponses` (bool)
- `extraContext` (object)
- `transformRequest(text, ctx)` -> payload
- `transformResponse(data)` -> `string` or `{ text, html }`
- `onEvent(eventName, data)` -> hooks (ready, toggle, message, error)
- `storageKey`, `maxMessages`, `typingIndicatorText`

## License
MIT

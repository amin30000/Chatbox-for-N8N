## N8N Brandable Chatbot (n8n Webhook)

A lightweight, brandable chat widget you can embed on any website that talks to your n8n Webhook. It renders inside a Shadow DOM to avoid CSS collisions, supports theming, and persists conversation locally with session expiration.

### Features
- Brandable colors, avatars, and launcher styles
- Light/dark mode with mobile-safe placement
- Local history persistence with optional inactivity TTL
- Pluggable request/response transforms
- Optional HTML rendering with sanitization
- Small surface API and lifecycle events
- WordPress plugin with settings page, shortcode, and auto-inject
- Modern UI: glassy panel (backdrop blur), gradient accents, smooth animations, hover/focus states, custom scrollbar

### Files
- `n8n-brandable-chatbot.js`: The widget script (vanilla JS, no deps)
- `basic.html`: Minimal example (light theme)
- `dark.html`: Dark theme example with left-side launcher and HTML responses
- `Wordpress Plugin/n8n-brandable-chatbot/`: WordPress plugin (zipped in the same folder)

## Quick start (local examples)
1. Open `basic.html` (or `dark.html`).
2. Change `webhookUrl` to your n8n Webhook URL.
3. Open the HTML file in a browser.

## Embed on any website
Add the script and initialize the widget. Capture the returned API if you want to control the widget programmatically.

```html
<script src="/path/to/n8n-brandable-chatbot.js"></script>
<script>
  const chatbot = window.N8NbrandableChatbot.init({
    webhookUrl: "https://your-n8n.example.com/webhook/abc123",
    botName: "Acme Assistant",
    brandColor: "#2563eb",
    accentColor: "#0ea5e9",
    welcomeMessage: "Hi! How can I help?",
    allowHTMLInResponses: true,
    launcherVariant: "icon-text", // 'icon' | 'text' | 'icon-text'
    launcherText: "Chat with us",
  });

  // Optional control
  // chatbot.open(); chatbot.close(); chatbot.toggle();
  // chatbot.send("Hello"); chatbot.clear();
  // console.log(chatbot.getSessionId());
</script>
```

### Optional: Global API on window
After initialization, the same API is also exposed at `window.N8NbrandableChatbot.api` for easy access in inline handlers:

```html
<button onclick="window.N8NbrandableChatbot.api.open()">Open Chatbot</button>
<button onclick="window.N8NbrandableChatbot.api.close()">Close Chatbot</button>
<button onclick="window.N8NbrandableChatbot.api.toggle()">Toggle Chatbot</button>
<button onclick="window.N8NbrandableChatbot.api.clear()">Clear Chatbot</button>
<button onclick="window.N8NbrandableChatbot.api.send('Hello')">Send Message</button>
```

## Configuration (options)
- webhookUrl (string, required): n8n webhook endpoint
- method (string, default "POST"): HTTP method used by fetch
- headers (object, default {}): extra headers to send
- brandColor (string, default "#2563eb"): primary color (bubble and UI accents)
- accentColor (string, default "#0ea5e9"): secondary color (send button, links)
- botName (string, default "Chatbot"): header title and default avatar letter
- botAvatarUrl (string): custom bot avatar image URL
- userAvatarUrl (string): custom user avatar image URL
- welcomeMessage (string): first message from the bot on a new session
- launcherText (string): label for `text`/`icon-text` launcher and aria-label
- launcherVariant ("icon" | "text" | "icon-text", default "icon"): launcher style
- position ("right" | "left", default "right"): screen side for the launcher/panel
- zIndex (number, default 999999): stacking context
- openByDefault (boolean, default false): panel initially open
- placeholder (string, default "Type your message..."): input placeholder
- storageKey (string, default "n8n-brandable-chatbot"): localStorage key used for persistence
- typingIndicatorText (string, default "Typing...")
- darkMode (boolean, default false): render dark UI
- allowHTMLInResponses (boolean, default false): if true, will render HTML safely
- extraContext (object, default {}): merged into each request as `metadata`
- maxMessages (number, default 200): history cap in memory and storage
- sessionTtlMinutes (number, default 0): if > 0, session resets after inactivity
- transformRequest (function: (text, ctx) => payload | null): customize request body
  - Default: `{ message: text, sessionId: ctx.sessionId, metadata: ctx.metadata }`
- transformResponse (function: (data) => string | { text?, html? }): normalize webhook response
  - Default behavior: picks `reply` → `message` → `text` → `output`; if HTML tags are detected and `allowHTMLInResponses` is true, renders as HTML
- onEvent (function: (eventName, data) => void): lifecycle events: `ready`, `toggle`, `message`, `error`

### API returned by init
- open(): open the panel
- close(): close the panel
- toggle(): toggle open/close
- send(text: string): append user message and send to webhook
- clear(): clear message history in the UI and storage
- getSessionId(): current session identifier

The same methods are available as `window.N8NbrandableChatbot.api` after `init()` completes.

## Webhook contract (n8n)
Default request body when `transformRequest` is not provided:

```json
{ "message": "<user text>", "sessionId": "<id>", "metadata": { /* from extraContext */ } }
```

Response handling (without `transformResponse`):
- JSON: the first present of `reply` → `message` → `text` → `output`
- Plain text: taken as the message string
- If `allowHTMLInResponses` is true and a string contains HTML tags, it is rendered as HTML (after sanitization). You can also return `{ html: "..." }`.

Recommended CORS headers on your webhook:
- `Access-Control-Allow-Origin: *` (or your site origin)
- `Access-Control-Allow-Headers: Content-Type, x-api-key`
- `Access-Control-Allow-Methods: POST, OPTIONS`

### Example n8n Function node
```javascript
return [{ reply: `You said: ${$json.message}` }];
```

## Security and HTML rendering
- HTML replies are sanitized: `script`, `style`, `iframe`, `object`, `embed` elements are removed and inline event handlers are stripped
- Keep `allowHTMLInResponses` disabled unless you trust the source
- Prefer JSON responses from your webhook to simplify parsing and control

## Accessibility and UX
- Shadow DOM avoids style collisions
- Mobile safe-area insets for placement
- Aria labels on launcher and input
- Smooth open/close (fade + translate), visible focus states, subtle shadows

## WordPress plugin
The folder `Wordpress Plugin/n8n-brandable-chatbot` contains a ready-to-install plugin and a `n8n-brandable-chatbot.zip` archive.

### Install
1. In WordPress admin, go to Plugins → Add New → Upload Plugin
2. Upload `n8n-brandable-chatbot.zip` and activate
3. Go to Settings → N8N Brandable Chatbot to configure

### Configure
Key fields mirror the widget options:
- Webhook URL, Method, Headers (JSON)
- Brand/Accent colors, Bot/User avatars, Bot name, Welcome message
- Launcher text/variant, Position, z-index, Open by default
- Placeholder, Storage key, Typing indicator, Dark mode, Allow HTML
- Extra context (JSON), Response field preference, Max messages, Session TTL
- Auto-inject (append on all pages), Dispatch events (emit window CustomEvents)

### Auto-inject
Enable “Auto-inject on all pages” to include the widget site-wide using your saved settings.

### Shortcode
Use `[n8n_brandable_chatbot]` to render on specific pages. All settings are overridable via attributes. Examples:

```text
[n8n_brandable_chatbot webhook_url="https://your-n8n/webhook/abc123" bot_name="Acme Assistant" dark_mode="true" position="left" launcher_variant="icon-text" launcher_text="Chat"]

; Boolean/number attributes are parsed: open_by_default, dark_mode, allow_html, dispatch_events, z_index, max_messages, session_ttl_minutes
```

When “Dispatch window CustomEvents” is enabled, the plugin sets `onEvent` to dispatch `n8nbrandablechatbot:<event>` events on `window` for `ready`, `toggle`, `message`, `error`.

## Troubleshooting
- Ensure the webhook URL is reachable from the browser and CORS is configured
- If you see “Sorry, I couldn’t reach the server”, check network errors in DevTools
- If responses appear empty, verify your webhook returns one of the expected fields or add a `transformResponse`
- If sessions do not reset, set `sessionTtlMinutes` > 0

## Examples
- See `basic.html` and `dark.html` for end-to-end usage, including `transformRequest` and `transformResponse` samples and control buttons using the global API

## License
MIT

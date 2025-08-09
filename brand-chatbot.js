(() => {
  const BrandChatbot = (() => {
    const defaultOptions = {
      webhookUrl: "",
      method: "POST",
      headers: {},
      brandColor: "#2563eb",
      accentColor: "#0ea5e9",
      botName: "Chatbot",
      botAvatarUrl: "",
      userAvatarUrl: "",
      welcomeMessage: "Hi! How can I help you?",
      launcherText: "",
      position: "right", // 'right' | 'left'
      zIndex: 999999,
      openByDefault: false,
      placeholder: "Type your message...",
      storageKey: "brand-chatbot",
      typingIndicatorText: "Typing...",
      darkMode: false,
      allowHTMLInResponses: false,
      extraContext: {},
      onEvent: null, // (eventName, data) => void
      transformRequest: null, // (text, ctx) => payload
      transformResponse: null, // (data) => string | { text, html }
      maxMessages: 200
    };

    function generateSessionId() {
      return "bc_" + Math.random().toString(36).slice(2) + Date.now().toString(36);
    }

    function sanitizeHtml(unsafeHtml) {
      const temp = document.createElement("div");
      temp.innerHTML = unsafeHtml;
      const scripts = temp.querySelectorAll("script, style, iframe, object, embed");
      scripts.forEach((el) => el.remove());
      const treeWalker = document.createTreeWalker(temp, NodeFilter.SHOW_ELEMENT, null);
      while (treeWalker.nextNode()) {
        const el = treeWalker.currentNode;
        [...el.attributes].forEach((attr) => {
          if (/^on/i.test(attr.name)) el.removeAttribute(attr.name);
        });
      }
      return temp.innerHTML;
    }

    function createShadowRoot(zIndex) {
      const host = document.createElement("div");
      host.setAttribute("data-bc-root", "true");
      host.style.all = "initial";
      host.style.position = "fixed";
      host.style.zIndex = String(zIndex);
      document.body.appendChild(host);
      return host.attachShadow({ mode: "open" });
    }

    function createStyles(options) {
      const css = `
      :host { all: initial; }
      *, *::before, *::after { box-sizing: border-box; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; }
      .bc-container { position: fixed; ${options.position === "left" ? "left" : "right"}: 20px; bottom: 20px; }
      .bc-launcher {
        display: inline-flex; align-items: center; justify-content: center;
        width: 56px; height: 56px; border-radius: 9999px; border: none; cursor: pointer;
        background: ${options.brandColor}; color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      }
      .bc-launcher svg { width: 26px; height: 26px; }
      .bc-panel {
        position: absolute; ${options.position === "left" ? "left" : "right"}: 0; bottom: 72px;
        width: min(360px, calc(100vw - 40px));
        max-height: min(70vh, 640px);
        border-radius: 16px; overflow: hidden; box-shadow: 0 16px 48px rgba(0,0,0,0.25);
        background: ${options.darkMode ? "#0b1220" : "#ffffff"};
        color: ${options.darkMode ? "#f5f7fb" : "#0b1220"};
        display: flex; flex-direction: column; border: 1px solid ${options.darkMode ? "rgba(255,255,255,0.08)" : "rgba(0,0,0,0.06)"};
      }
      .bc-panel.hidden { display: none; }

      .bc-header {
        display: flex; align-items: center; gap: 10px; padding: 12px 14px;
        background: ${options.darkMode ? "rgba(255,255,255,0.04)" : "rgba(0,0,0,0.02)"};
        border-bottom: 1px solid ${options.darkMode ? "rgba(255,255,255,0.08)" : "rgba(0,0,0,0.06)"};
      }
      .bc-title { font-weight: 600; font-size: 14px; line-height: 1.2; }
      .bc-sub { font-size: 12px; opacity: 0.7; }
      .bc-header .bc-avatar {
        width: 28px; height: 28px; border-radius: 9999px; overflow: hidden; background: ${options.brandColor}; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;
      }
      .bc-close {
        margin-left: auto; background: transparent; border: none; color: inherit; cursor: pointer; opacity: 0.7;
      }

      .bc-messages {
        padding: 14px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; flex: 1 1 auto;
      }
      .bc-msg {
        display: flex; align-items: flex-end; gap: 8px; max-width: 90%;
      }
      .bc-msg-user { align-self: flex-end; flex-direction: row-reverse; }
      .bc-bubble {
        padding: 10px 12px; border-radius: 14px; line-height: 1.35; font-size: 14px; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere;
      }
      .bc-bubble-user {
        background: ${options.brandColor}; color: #fff; border-bottom-right-radius: 4px;
      }
      .bc-bubble-bot {
        background: ${options.darkMode ? "rgba(255,255,255,0.06)" : "#f4f6fb"}; color: inherit; border-bottom-left-radius: 4px;
      }
      .bc-msg .bc-mini-avatar {
        width: 22px; height: 22px; border-radius: 9999px; overflow: hidden; background: ${options.darkMode ? "#1f2937" : "#e5e7eb"};
        display: inline-flex; align-items: center; justify-content: center; font-size: 11px; color: ${options.darkMode ? "#e5e7eb" : "#111827"};
      }
      .bc-typing { font-size: 12px; opacity: 0.7; }

      .bc-input {
        border-top: 1px solid ${options.darkMode ? "rgba(255,255,255,0.08)" : "rgba(0,0,0,0.06)"};
        padding: 10px; display: flex; gap: 8px; align-items: center;
        background: ${options.darkMode ? "rgba(255,255,255,0.02)" : "#ffffff"};
      }
      .bc-input input[type="text"] {
        flex: 1 1 auto; border-radius: 12px; border: 1px solid ${options.darkMode ? "rgba(255,255,255,0.12)" : "rgba(0,0,0,0.12)"};
        padding: 10px 12px; background: ${options.darkMode ? "rgba(255,255,255,0.03)" : "#ffffff"}; color: inherit;
        outline: none;
      }
      .bc-input button {
        border: none; background: ${options.accentColor}; color: #fff; padding: 10px 12px; border-radius: 12px; cursor: pointer;
      }
      .bc-badge {
        display: inline-flex; align-items: center; gap: 6px; font-size: 11px; opacity: 0.7;
      }
      `;
      const style = document.createElement("style");
      style.textContent = css;
      return style;
    }

    function createUI(options, shadowRoot) {
      const container = document.createElement("div");
      container.className = "bc-container";

      const launcher = document.createElement("button");
      launcher.className = "bc-launcher";
      launcher.setAttribute("aria-label", options.launcherText || `Open ${options.botName}`);
      launcher.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none">
          <path d="M12 3C7.03 3 3 6.58 3 11c0 2.43 1.23 4.61 3.19 6.11-.09.76-.39 2.02-1.31 3.16 0 0 2.06-.21 3.76-1.45.73.2 1.5.31 2.36.31 4.97 0 9-3.58 9-8s-4.03-8-9-8z" fill="currentColor"/>
        </svg>
      `;

      const panel = document.createElement("div");
      panel.className = "bc-panel hidden";

      const header = document.createElement("div");
      header.className = "bc-header";
      header.innerHTML = `
        <div class="bc-avatar">${options.botAvatarUrl ? `<img src="${options.botAvatarUrl}" alt="${options.botName}" style="width:100%;height:100%;object-fit:cover" />` : options.botName.slice(0,1).toUpperCase()}</div>
        <div>
          <div class="bc-title">${options.botName}</div>
          <div class="bc-sub">Online</div>
        </div>
        <button class="bc-close" aria-label="Close">&times;</button>
      `;

      const messages = document.createElement("div");
      messages.className = "bc-messages";

      const inputBar = document.createElement("form");
      inputBar.className = "bc-input";
      inputBar.innerHTML = `
        <input type="text" aria-label="Message input" placeholder="${options.placeholder}">
        <button type="submit">Send</button>
      `;

      panel.appendChild(header);
      panel.appendChild(messages);
      panel.appendChild(inputBar);
      container.appendChild(panel);
      container.appendChild(launcher);

      shadowRoot.appendChild(container);

      return { container, launcher, panel, header, messages, inputBar };
    }

    function scrollToBottom(el) {
      el.scrollTop = el.scrollHeight;
    }

    function createMessageElement({ role, text, html, botAvatarUrl, userAvatarUrl, allowHTMLInResponses }) {
      const row = document.createElement("div");
      row.className = `bc-msg ${role === "user" ? "bc-msg-user" : "bc-msg-bot"}`;

      const avatar = document.createElement("div");
      avatar.className = "bc-mini-avatar";
      if (role === "bot") {
        avatar.innerHTML = botAvatarUrl ? `<img src="${botAvatarUrl}" alt="bot" style="width:100%;height:100%;object-fit:cover" />` : "🤖";
      } else {
        avatar.innerHTML = userAvatarUrl ? `<img src="${userAvatarUrl}" alt="you" style="width:100%;height:100%;object-fit:cover" />` : "🧑";
      }

      const bubble = document.createElement("div");
      bubble.className = `bc-bubble ${role === "user" ? "bc-bubble-user" : "bc-bubble-bot"}`;

      if (html && allowHTMLInResponses) {
        bubble.innerHTML = sanitizeHtml(html);
      } else {
        bubble.textContent = text || "";
      }

      row.appendChild(avatar);
      row.appendChild(bubble);
      return row;
    }

    function loadState(storageKey) {
      try {
        const raw = localStorage.getItem(storageKey);
        if (!raw) return null;
        return JSON.parse(raw);
      } catch {
        return null;
      }
    }

    function saveState(storageKey, state) {
      try {
        localStorage.setItem(storageKey, JSON.stringify(state));
      } catch {
        // ignore
      }
    }

    function init(userOptions) {
      const options = { ...defaultOptions, ...userOptions };
      if (!options.webhookUrl) {
        console.error("[BrandChatbot] Missing required option: webhookUrl");
        return;
      }

      const sessionState = loadState(options.storageKey) || {};
      const sessionId = sessionState.sessionId || generateSessionId();
      const history = Array.isArray(sessionState.history) ? sessionState.history.slice(-options.maxMessages) : [];

      const shadowRoot = createShadowRoot(options.zIndex);
      const styleEl = createStyles(options);
      shadowRoot.appendChild(styleEl);
      const ui = createUI(options, shadowRoot);

      function persist() {
        saveState(options.storageKey, { sessionId, history });
      }

      function addMessage(role, content) {
        const entry = typeof content === "string" ? { role, text: content } : { role, ...content };
        history.push(entry);
        while (history.length > options.maxMessages) history.shift();

        const node = createMessageElement({
          role,
          text: entry.text,
          html: entry.html,
          botAvatarUrl: options.botAvatarUrl,
          userAvatarUrl: options.userAvatarUrl,
          allowHTMLInResponses: options.allowHTMLInResponses
        });
        ui.messages.appendChild(node);
        scrollToBottom(ui.messages);
        persist();
        if (typeof options.onEvent === "function") options.onEvent("message", { role, entry });
      }

      function setTyping(isTyping) {
        const existing = ui.messages.querySelector('[data-typing="true"]');
        if (existing) existing.remove();
        if (isTyping) {
          const typing = document.createElement("div");
          typing.className = "bc-msg";
          typing.setAttribute("data-typing", "true");
          typing.innerHTML = `<div class="bc-mini-avatar">🤖</div><div class="bc-bubble bc-bubble-bot"><span class="bc-typing">${options.typingIndicatorText}</span></div>`;
          ui.messages.appendChild(typing);
          scrollToBottom(ui.messages);
        }
      }

      function reopenFromOption() {
        if (options.openByDefault) ui.panel.classList.remove("hidden");
      }

      // Restore history
      history.forEach((msg) => {
        const node = createMessageElement({
          role: msg.role,
          text: msg.text,
          html: msg.html,
          botAvatarUrl: options.botAvatarUrl,
          userAvatarUrl: options.userAvatarUrl,
          allowHTMLInResponses: options.allowHTMLInResponses
        });
        ui.messages.appendChild(node);
      });

      // Welcome
      if (!history.length && options.welcomeMessage) {
        addMessage("bot", { text: options.welcomeMessage });
      }

      function buildPayload(userText) {
        const context = { sessionId, history: history.slice(-20), metadata: options.extraContext };
        if (typeof options.transformRequest === "function") {
          return options.transformRequest(userText, context);
        }
        return { message: userText, sessionId, metadata: options.extraContext };
      }

      async function sendToWebhook(userText) {
        setTyping(true);
        try {
          const payload = buildPayload(userText);
          const resp = await fetch(options.webhookUrl, {
            method: options.method,
            headers: { "Content-Type": "application/json", ...options.headers },
            body: options.method.toUpperCase() === "GET" ? undefined : JSON.stringify(payload),
            credentials: "omit",
            mode: "cors"
          });
          let data;
          const contentType = resp.headers.get("content-type") || "";
          if (contentType.includes("application/json")) {
            data = await resp.json();
          } else {
            data = { reply: await resp.text() };
          }

          let out = null;
          if (typeof options.transformResponse === "function") {
            out = options.transformResponse(data);
          } else {
            const text = data?.reply ?? data?.message ?? data?.text ?? data?.output ?? "";
            out = { text };
          }

          setTyping(false);

          if (typeof out === "string") {
            addMessage("bot", { text: out });
          } else if (out && typeof out === "object") {
            const { text, html } = out;
            if (options.allowHTMLInResponses && html) {
              addMessage("bot", { html });
            } else {
              addMessage("bot", { text: text ?? "" });
            }
          } else {
            addMessage("bot", { text: "Received empty response." });
          }
        } catch (err) {
          setTyping(false);
          addMessage("bot", { text: "Sorry, I couldn’t reach the server. Please try again." });
          if (typeof options.onEvent === "function") options.onEvent("error", { error: err });
        }
      }

      // Events
      ui.launcher.addEventListener("click", () => {
        ui.panel.classList.toggle("hidden");
        if (typeof options.onEvent === "function") options.onEvent("toggle", { open: !ui.panel.classList.contains("hidden") });
      });
      ui.header.querySelector(".bc-close").addEventListener("click", () => {
        ui.panel.classList.add("hidden");
        if (typeof options.onEvent === "function") options.onEvent("toggle", { open: false });
      });

      ui.inputBar.addEventListener("submit", (e) => {
        e.preventDefault();
        const input = ui.inputBar.querySelector('input[type="text"]');
        const value = (input.value || "").trim();
        if (!value) return;
        addMessage("user", { text: value });
        input.value = "";
        sendToWebhook(value);
      });

      reopenFromOption();

      const api = {
        open: () => ui.panel.classList.remove("hidden"),
        close: () => ui.panel.classList.add("hidden"),
        toggle: () => ui.panel.classList.toggle("hidden"),
        send: (text) => {
          addMessage("user", { text });
          sendToWebhook(text);
        },
        clear: () => {
          history.splice(0, history.length);
          ui.messages.innerHTML = "";
          persist();
        },
        getSessionId: () => sessionId
      };

      if (typeof options.onEvent === "function") options.onEvent("ready", { sessionId });

      return api;
    }

    return { init };
  })();

  window.BrandChatbot = BrandChatbot;
})();



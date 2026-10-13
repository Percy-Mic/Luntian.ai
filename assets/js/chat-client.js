/**
 * Luntian AI chat client.
 * Uses authenticated server APIs for generation and persisted chat history.
 */
(function () {
  const feed = document.getElementById('chat-feed');
  const input = document.getElementById('chat-input');
  const sendBtn = document.getElementById('send-btn');
  if (!feed || !input || !sendBtn || !window.LuntianAPI) return;

  let sessionId = null;
  let sending = false;
  let historyHost = null;

  function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = String(text ?? '');
    return d.innerHTML;
  }

  function contentContainer() {
    return feed.querySelector('.max-w-max-content-width') || feed;
  }

  function clearFeed() {
    const container = contentContainer();
    container.replaceChildren();
  }

  function appendMessage(role, text) {
    const container = contentContainer();
    const wrap = document.createElement('div');
    wrap.className = role === 'user' ? 'flex justify-end' : 'flex justify-start gap-3';
    const bubble = document.createElement('div');
    bubble.className = role === 'user'
      ? 'max-w-[85%] bg-primary text-on-primary px-4 py-3 rounded-2xl rounded-tr-sm shadow-lg'
      : 'max-w-[85%] bg-white dark:bg-[#1d232b] border border-outline-variant/10 dark:border-white/10 px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm';
    const paragraph = document.createElement('p');
    paragraph.className = 'text-body-md whitespace-pre-wrap break-words';
    paragraph.textContent = String(text ?? '');
    bubble.appendChild(paragraph);
    if (role === 'assistant') {
      const icon = document.createElement('div');
      icon.className = 'w-8 h-8 rounded-lg bg-brand-gradient flex items-center justify-center shrink-0';
      icon.innerHTML = '<span class="material-symbols-outlined text-white text-sm">auto_awesome</span>';
      wrap.appendChild(icon);
    }
    wrap.appendChild(bubble);
    container.appendChild(wrap);
    feed.scrollTop = feed.scrollHeight;
  }

  function showWelcome() {
    const container = contentContainer();
    container.innerHTML = '<div class="text-center py-12"><h2 class="text-2xl font-bold mb-2">Start a conversation</h2><p class="text-sm text-on-surface-variant dark:text-outline">Ask Luntian AI anything you are working on.</p></div>';
  }

  function setupHistoryHost() {
    if (historyHost) return;
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const newChatButton = Array.from(sidebar.querySelectorAll('button'))
      .find((button) => button.textContent.toLowerCase().includes('new chat'));
    historyHost = document.createElement('section');
    historyHost.className = 'px-xs py-sm';
    historyHost.innerHTML = '<div class="flex items-center justify-between px-md mb-2"><h2 class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant dark:text-outline">Your conversations</h2><button type="button" data-refresh-chats class="text-xs text-primary hover:underline" aria-label="Refresh chat history">Refresh</button></div><div data-chat-list class="space-y-1" aria-live="polite"><p class="px-md py-2 text-xs text-on-surface-variant dark:text-outline">Loading history…</p></div>';
    if (newChatButton) newChatButton.insertAdjacentElement('afterend', historyHost);
    else sidebar.prepend(historyHost);
    historyHost.querySelector('[data-refresh-chats]').addEventListener('click', loadHistory);
  }

  function renderHistory(chats) {
    setupHistoryHost();
    if (!historyHost) return;
    const list = historyHost.querySelector('[data-chat-list]');
    list.replaceChildren();
    if (!chats.length) {
      const empty = document.createElement('p');
      empty.className = 'px-md py-2 text-xs text-on-surface-variant dark:text-outline';
      empty.textContent = 'Your saved chats will appear here.';
      list.appendChild(empty);
      return;
    }
    chats.forEach((chat) => {
      const row = document.createElement('div');
      row.className = 'flex items-center gap-1 rounded-lg hover:bg-surface-container-high dark:hover:bg-white/5 group';
      const open = document.createElement('button');
      open.type = 'button';
      open.className = 'min-w-0 flex-1 flex items-center gap-2 px-md py-2 text-left text-sm text-on-surface-variant dark:text-outline';
      open.title = chat.title || 'New chat';
      open.innerHTML = '<span class="material-symbols-outlined text-base shrink-0">chat_bubble_outline</span><span class="truncate"></span>';
      open.querySelector('span.truncate').textContent = chat.title || 'New chat';
      open.addEventListener('click', () => openChat(Number(chat.id)));
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'p-2 mr-1 rounded-md opacity-70 hover:opacity-100 hover:bg-red-500/10 text-on-surface-variant dark:text-outline';
      remove.title = 'Delete conversation';
      remove.setAttribute('aria-label', 'Delete ' + (chat.title || 'conversation'));
      remove.innerHTML = '<span class="material-symbols-outlined text-base">delete</span>';
      remove.addEventListener('click', async (event) => {
        event.stopPropagation();
        if (!window.confirm('Delete this conversation and its messages? This cannot be undone.')) return;
        remove.disabled = true;
        try {
          await LuntianAPI.api('/api/chats.php', {
            method: 'DELETE',
            body: JSON.stringify({ sessionId: Number(chat.id) }),
          });
          if (sessionId === Number(chat.id)) {
            sessionId = null;
            showWelcome();
          }
          await loadHistory();
        } catch (error) {
          window.alert(error.message || 'Could not delete this conversation.');
          remove.disabled = false;
        }
      });
      row.append(open, remove);
      list.appendChild(row);
    });
  }

  async function loadHistory() {
    setupHistoryHost();
    if (!historyHost) return;
    try {
      const data = await LuntianAPI.api('/api/chats.php');
      renderHistory(Array.isArray(data.chats) ? data.chats : []);
    } catch (error) {
      const list = historyHost.querySelector('[data-chat-list]');
      list.textContent = error.status === 401
        ? 'Sign in to view saved conversations.'
        : 'Chat history could not be loaded. Use Refresh to try again.';
    }
  }

  async function openChat(id) {
    if (sending) return;
    try {
      const data = await LuntianAPI.api('/api/chats.php?id=' + encodeURIComponent(id));
      sessionId = Number(data.chat.id);
      clearFeed();
      (data.messages || []).forEach((message) => appendMessage(message.role, message.content));
      if (!(data.messages || []).length) showWelcome();
    } catch (error) {
      window.alert(error.message || 'Could not open this conversation.');
    }
  }

  async function send() {
    const message = input.value.trim();
    if (!message || sending) return;
    sending = true;
    sendBtn.disabled = true;
    sendBtn.setAttribute('aria-busy', 'true');
    appendMessage('user', message);
    input.value = '';
    input.style.height = 'auto';

    const thinking = document.createElement('p');
    thinking.className = 'text-center text-sm text-on-surface-variant dark:text-outline animate-pulse py-4';
    thinking.textContent = 'Luntian is thinking…';
    contentContainer().appendChild(thinking);

    try {
      const data = await LuntianAPI.api('/api/chat.php', {
        method: 'POST',
        body: JSON.stringify({ message, sessionId }),
      });
      thinking.remove();
      sessionId = Number(data.sessionId);
      appendMessage('assistant', data.reply);
      await loadHistory();
    } catch (error) {
      thinking.remove();
      if (!input.value.trim()) {
        input.value = message;
        input.focus();
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 240) + 'px';
      }
      appendMessage('assistant', 'I couldn’t send that message. ' + (error.message || 'Please check your connection and try again.'));
    } finally {
      sending = false;
      sendBtn.disabled = false;
      sendBtn.removeAttribute('aria-busy');
    }
  }

  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      send();
    }
  });

  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    const newChatButton = Array.from(sidebar.querySelectorAll('button'))
      .find((button) => button.textContent.toLowerCase().includes('new chat'));
    if (newChatButton) {
      newChatButton.addEventListener('click', () => {
        if (sending) return;
        sessionId = null;
        clearFeed();
        showWelcome();
        input.focus();
      });
    }
  }

  LuntianAPI.requireAuth('signinPage.php').then((user) => {
    if (!user) return;
    const welcome = feed.querySelector('h2');
    if (welcome && user.displayName) {
      welcome.textContent = 'Good day, ' + (user.displayName.split(' ')[0] || 'there') + '.';
    }
    const img = document.querySelector('#profile-btn img');
    if (img && user.photoUrl) img.src = user.photoUrl;
    loadHistory();
  });
})();

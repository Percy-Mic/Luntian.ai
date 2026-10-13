/**
 * Wire chat UI on index.php to Gemini via /api/chat.php
 */
(function () {
  const feed = document.getElementById('chat-feed');
  const input = document.getElementById('chat-input');
  const sendBtn = document.getElementById('send-btn');
  if (!feed || !input || !sendBtn || !window.LuntianAPI) return;

  let sessionId = null;
  let sending = false;

  function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
  }

  function appendMessage(role, text) {
    const container = feed.querySelector('.max-w-max-content-width') || feed;
    let welcome = container.querySelector('.text-center');
    if (welcome) welcome.remove();

    const wrap = document.createElement('div');
    wrap.className = role === 'user'
      ? 'flex justify-end'
      : 'flex justify-start gap-3';
    const bubble = document.createElement('div');
    bubble.className = role === 'user'
      ? 'max-w-[85%] bg-primary text-on-primary px-4 py-3 rounded-2xl rounded-tr-sm shadow-lg'
      : 'max-w-[85%] bg-white dark:bg-[#1d232b] border border-outline-variant/10 dark:border-white/10 px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm';
    bubble.innerHTML = '<p class="text-body-md whitespace-pre-wrap">' + escapeHtml(text) + '</p>';
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
    (feed.querySelector('.max-w-max-content-width') || feed).appendChild(thinking);

    try {
      const data = await LuntianAPI.api('/api/chat.php', {
        method: 'POST',
        body: JSON.stringify({ message, sessionId }),
      });
      thinking.remove();
      sessionId = data.sessionId;
      appendMessage('assistant', data.reply);
    } catch (err) {
      thinking.remove();
      // Keep the user's message available to retry after a temporary failure.
      if (!input.value.trim()) {
        input.value = message;
        input.focus();
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 240) + 'px';
      }
      appendMessage('assistant', 'I couldn’t send that message. ' + (err.message || 'Please check your connection and try again.'));
    } finally {
      sending = false;
      sendBtn.disabled = false;
      sendBtn.removeAttribute('aria-busy');
    }
  }

  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      send();
    }
  });

  LuntianAPI.requireAuth('signinPage.php').then((user) => {
    if (!user) return;
    const welcome = feed.querySelector('h2');
    if (welcome && user.displayName) {
      welcome.textContent = 'Good day, ' + (user.displayName.split(' ')[0] || 'there') + '.';
    }
    const img = document.querySelector('#profile-btn img');
    if (img && user.photoUrl) img.src = user.photoUrl;
    const nameEl = document.querySelector('#profile-dropdown p.font-bold');
  });
})();

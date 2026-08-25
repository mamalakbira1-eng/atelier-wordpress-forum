/* Atelier — interactions éditoriales : recherche progressive, suivi, partage et notifications accessibles. */
(() => {
  const searchConfig = window.atelierSearch;
  const community = window.atelierCommunity;
  const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character]));
  const escapeAttribute = escapeHtml;

  if (searchConfig) {
    document.querySelectorAll('[data-atelier-search]').forEach((form) => {
      const input = form.querySelector('input[type="search"]');
      const panel = form.parentElement.querySelector('.atelier-search-suggestions');
      if (!input || !panel) return;
      let controller;
      let debounce;
      let activeIndex = -1;
      const close = () => { panel.hidden = true; panel.innerHTML = ''; input.setAttribute('aria-expanded', 'false'); activeIndex = -1; };
      const render = (items, query) => {
        panel.innerHTML = items.length
          ? `<p class="atelier-search-suggestions__heading">Sources trouvées</p><div role="listbox">${items.map((item, index) => `<a class="atelier-search-suggestions__item" role="option" aria-selected="false" data-index="${index}" href="${escapeAttribute(item.url)}"><span><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.kind)} · ${escapeHtml(item.meta)}</small></span><b aria-hidden="true">↗</b></a>`).join('')}</div><button class="atelier-search-suggestions__all" type="submit" form="${form.id}">Voir tous les résultats pour « ${escapeHtml(query)} »</button>`
          : `<p class="atelier-search-suggestions__empty">Aucune source directe pour « ${escapeHtml(query)} ».</p><button class="atelier-search-suggestions__all" type="submit" form="${form.id}">Voir tous les résultats</button>`;
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
      };
      const updateActive = (index) => { const options = [...panel.querySelectorAll('[role="option"]')]; if (!options.length) return; activeIndex = (index + options.length) % options.length; options.forEach((option, optionIndex) => option.setAttribute('aria-selected', String(optionIndex === activeIndex))); options[activeIndex].focus({ preventScroll: true }); };
      const search = async () => {
        const query = input.value.trim();
        if (query.length < Number(searchConfig.minChars || 2)) { close(); return; }
        controller?.abort(); controller = new AbortController(); panel.hidden = false; panel.innerHTML = '<p class="atelier-search-suggestions__loading">Recherche dans les archives…</p>';
        try { const url = new URL(searchConfig.ajaxUrl, window.location.origin); url.searchParams.set('action', 'atelier_suggest'); url.searchParams.set('q', query); const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin' }); const payload = await response.json(); if (!payload.success) throw new Error('invalid'); render(payload.data.items || [], query); } catch (error) { if (error.name !== 'AbortError') panel.innerHTML = '<p class="atelier-search-suggestions__empty">La suggestion est indisponible. Lancez la recherche complète.</p>'; }
      };
      input.addEventListener('input', () => { window.clearTimeout(debounce); debounce = window.setTimeout(search, 130); });
      input.addEventListener('keydown', (event) => { if (event.key === 'ArrowDown') { event.preventDefault(); updateActive(activeIndex + 1); } if (event.key === 'ArrowUp') { event.preventDefault(); updateActive(activeIndex - 1); } if (event.key === 'Escape') { close(); input.focus(); } });
      input.addEventListener('focus', () => { if (input.value.trim().length >= Number(searchConfig.minChars || 2)) search(); });
      document.addEventListener('click', (event) => { if (!form.parentElement.contains(event.target)) close(); });
    });
  }

  document.querySelectorAll('[data-atelier-share]').forEach((button) => button.addEventListener('click', async () => {
    const url = button.dataset.url || window.location.href;
    try { if (navigator.share) await navigator.share({ title: document.title, url }); else await navigator.clipboard.writeText(url); button.textContent = 'Lien copié'; } catch (_) { button.textContent = 'Lien prêt à partager'; }
    window.setTimeout(() => { button.textContent = button.dataset.label || 'Partager'; }, 1800);
  }));

  if (community) {
    let communityNonce = community.nonce || '';
    const refreshNonce = async () => {
      if (!community.nonceUrl) return communityNonce;
      const response = await fetch(community.nonceUrl, { credentials: 'same-origin', cache: 'no-store' });
      const payload = await response.json();
      if (!payload.success || !payload.data?.nonce) throw new Error('nonce');
      communityNonce = payload.data.nonce;
      return communityNonce;
    };
    const communityRequest = async (params) => {
      const send = async () => { const body = new URLSearchParams(params); body.set('nonce', communityNonce); const response = await fetch(community.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body }); return response.json(); };
      let payload = await send();
      if (!payload.success && community.loggedIn) { await refreshNonce(); payload = await send(); }
      return payload;
    };
    document.querySelectorAll('[data-pfc-follow]').forEach((button) => button.addEventListener('click', async () => {
      if (!community.loggedIn) { window.location.href = button.dataset.login || '/wp-login.php'; return; }
      button.disabled = true;
      try { await refreshNonce(); const payload = await communityRequest({ action: 'pfc_toggle_follow', topic_id: button.dataset.topicId }); if (!payload.success) throw new Error('follow'); button.textContent = payload.data.label; button.setAttribute('aria-pressed', String(payload.data.following)); } catch (_) { button.textContent = 'Réessayer'; } finally { button.disabled = false; }
    }));
    document.querySelectorAll('[data-pfc-mark-read]').forEach((button) => button.addEventListener('click', async () => {
      button.disabled = true;
      try { await refreshNonce(); const payload = await communityRequest({ action: 'pfc_mark_notifications_read' }); if (payload.success) { document.querySelectorAll('.atelier-notifications .is-unread').forEach((item) => item.classList.remove('is-unread')); button.remove(); document.querySelectorAll('.atelier-notifications__count').forEach((count) => count.remove()); } } finally { button.disabled = false; }
    }));
    document.querySelectorAll('[data-pfc-vote]').forEach((button) => button.addEventListener('click', async () => {
      if (!community.loggedIn) { window.location.href = button.dataset.login || '/wp-login.php'; return; }
      button.disabled = true;
      try {
        await refreshNonce();
        const payload = await communityRequest({ action: 'pfc_toggle_vote', object_id: button.dataset.objectId });
        if (!payload.success) throw new Error(payload.data?.message || 'vote');
        button.classList.toggle('is-active', Boolean(payload.data.voted));
        button.setAttribute('aria-pressed', String(Boolean(payload.data.voted)));
        const label = button.querySelector('[data-pfc-vote-label]');
        const count = button.querySelector('[data-pfc-vote-count]');
        if (label) label.textContent = payload.data.voted ? 'Vote ajouté' : 'Voter utile';
        if (count) count.textContent = String(payload.data.count);
      } catch (error) {
        const label = button.querySelector('[data-pfc-vote-label]');
        if (label && error?.message) label.textContent = error.message;
      } finally { button.disabled = false; }
    }));
  }
})();

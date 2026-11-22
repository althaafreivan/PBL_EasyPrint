(() => {
  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  /* ---------- Menu akun ---------- */
  const btnUser = $('btnUser');
  const menu = $('userMenu');
  const setMenu = (open) => { menu.hidden = !open; btnUser.setAttribute('aria-expanded', String(open)); };
  btnUser.addEventListener('click', (e) => { e.stopPropagation(); setMenu(menu.hidden); });
  document.addEventListener('click', (e) => { if (!menu.hidden && !menu.contains(e.target)) setMenu(false); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !menu.hidden) { setMenu(false); btnUser.focus(); }
  });

  /* ---------- Panel notifikasi ---------- */
  const data = (window.NOTIF || []).map((n) => ({ ...n }));
  const panel = $('notifPanel'), overlay = $('notifOverlay'), list = $('notifList');
  const sub = $('notifSub'), dot = $('notifDot'), clear = $('notifClear'), closeBtn = $('notifClose');

  const GRUP = ['Hari ini', 'Kemarin', 'Sebelumnya'];
  const SVG = {
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    printer: '<path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="7" rx="1"/><path d="M6 17H4a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-2"/>',
    check: '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
    alert: '<path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    cash: '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
    info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
  };
  const WARNA = { clock: 'amber', printer: 'blue', check: 'green', alert: 'red', cash: 'green', info: 'blue' };

  const kartu = (n) => `
    <article class="nitem${n.baru ? ' is-unread' : ''}${n.url ? ' is-link' : ''}" data-id="${n.id}">
      <span class="nitem__icon nitem__icon--${WARNA[n.ikon] || 'blue'}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${SVG[n.ikon] || SVG.info}</svg>
      </span>
      <div>
        <div class="nitem__top"><h3>${esc(n.judul)}</h3><time>${esc(n.waktu)}</time></div>
        <p>${esc(n.isi)}</p>
        <div class="nitem__foot">
          <span class="nitem__meta">${esc(n.meta)}</span>
          <span class="pill pill--${esc(n.warna)}">${esc(n.tag)}</span>
        </div>
      </div>
    </article>`;

  function updateBadge() {
    const belum = data.filter((n) => n.baru).length;
    dot.hidden = belum === 0;
  }

  function render() {
    const belum = data.filter((n) => n.baru).length;
    sub.textContent = belum ? `${belum} notifikasi belum dibaca` : 'Semua notifikasi sudah dibaca';
    dot.hidden = belum === 0;
    clear.disabled = belum === 0;
    list.innerHTML = GRUP.map((g) => {
      const items = data.filter((n) => n.grup === g);
      if (!items.length) return '';
      return `<section class="ngroup" aria-label="${g}">
        <div class="ngroup__head"><h2>${g}</h2><span class="ngroup__count">${items.length}</span></div>
        ${items.map(kartu).join('')}
      </section>`;
    }).join('') || '<p class="notif-empty">Tidak ada notifikasi.</p>';
  }

  let fokusAwal = null;
  let tutupTimer = null;
  function buka() {
    clearTimeout(tutupTimer);
    setMenu(false);
    render();
    fokusAwal = document.activeElement;
    overlay.hidden = false;
    panel.hidden = false;
    void panel.offsetWidth;
    panel.classList.add('is-open');
    overlay.classList.add('is-open');
    panel.setAttribute('aria-hidden', 'false');
    document.body.classList.add('notif-lock');
    closeBtn.focus();
  }
  function tutup() {
    panel.classList.remove('is-open');
    overlay.classList.remove('is-open');
    panel.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('notif-lock');
    clearTimeout(tutupTimer);
    tutupTimer = setTimeout(() => {
      if (!panel.classList.contains('is-open')) {
        panel.hidden = true;
        overlay.hidden = true;
      }
    }, 260);
    (fokusAwal || $('btnNotif')).focus();
  }

  $('btnNotif').addEventListener('click', buka);
  document.querySelectorAll('[data-notif-open]').forEach((b) => b.addEventListener('click', buka));
  closeBtn.addEventListener('click', tutup);
  overlay.addEventListener('click', tutup);
  // Tandai semua dibaca -> PATCH /api/notifications/read {semua:true}
  clear.addEventListener('click', async () => {
    data.forEach((n) => (n.baru = false));
    render();
    if (window.EPAPI) await EPAPI.call('/api/notifications/read', { method: 'PATCH', body: { semua: true } });
  });
  // Klik kartu: tandai dibaca lalu buka halaman tracking pesanan terkait.
  list.addEventListener('click', async (e) => {
    const art = e.target.closest('.nitem');
    const n = art && data.find((x) => x.id === Number(art.dataset.id));
    if (!n) return;
    if (n.baru) {
      n.baru = false;
      render();
      if (window.EPAPI) await EPAPI.call('/api/notifications/read', { method: 'PATCH', body: { id_notifikasi: n.id } });
    }
    if (n.url) location.href = n.url;
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && panel.classList.contains('is-open')) tutup();
  });

  updateBadge();
})();
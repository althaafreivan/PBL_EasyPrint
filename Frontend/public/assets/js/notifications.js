(() => {
  const data = (window.NOTIF || []).map((n) => ({ ...n }));
  const $ = (id) => document.getElementById(id);

  const panel = $('notifPanel');
  const overlay = $('notifOverlay');
  const list = $('notifList');
  const sub = $('notifSub');
  const bell = $('btnBell');
  const dot = $('bellDot');
  const closeBtn = $('notifClose');
  const clearBtn = $('notifClear');
  if (!panel || !bell) return;

  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const GRUP = [['hari-ini', 'Hari ini'], ['kemarin', 'Kemarin']];
  const TUJUAN = {
    1: 'admin-orders.php', 2: 'admin-stock.php',   3: 'admin-cashier.php', 4: 'admin-orders.php',
    5: 'admin-orders.php', 6: 'admin-cashier.php', 7: 'admin-cashier.php', 8: 'admin-stock.php',
  };
  const SVG = {
    box: '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/>',
    warning: '<path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    check: '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
    truck: '<path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
  };
  const WARNA = { box: 'blue', truck: 'blue', warning: 'amber', check: 'green' };
  const ikon = (k) =>
    `<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${SVG[k] || SVG.box}</svg>`;

  function kartu(n) {
    const judul = `<a class="nitem__read" href="${TUJUAN[n.id] || '#'}">${esc(n.judul)}</a>`;
    return `
      <article class="nitem${n.baru ? ' is-unread' : ''}" data-id="${n.id}">
        <span class="nitem__icon nitem__icon--${WARNA[n.ikon] || 'blue'}">${ikon(n.ikon)}</span>
        <div>
          <div class="nitem__top"><h3>${judul}</h3><time>${esc(n.waktu)}</time></div>
          <p>${esc(n.isi)}</p>
          <div class="nitem__foot">
            <span class="nitem__meta">${esc(n.meta)}</span>
            <div class="nitem__side">
              <span class="pill pill--${esc(n.warna)}">${esc(n.tag)}</span>
              <button type="button" class="nitem__del" data-del aria-label="Hapus notifikasi ${esc(n.judul)}">Hapus</button>
            </div>
          </div>
        </div>
      </article>`;
  }

  function render() {
    const belum = data.filter((n) => n.baru).length;
    sub.textContent = belum ? `${belum} notifikasi belum dibaca` : 'Semua notifikasi sudah dibaca';
    dot.hidden = belum === 0;
    bell.setAttribute('aria-label', belum ? `Notifikasi, ${belum} belum dibaca` : 'Notifikasi');
    clearBtn.disabled = data.length === 0;

    const html = GRUP.map(([key, label]) => {
      const items = data.filter((n) => n.grup === key);
      if (!items.length) return '';
      return `
        <section class="ngroup" aria-label="${label}">
          <div class="ngroup__head"><h2>${label}</h2><span class="ngroup__count">${items.length}</span></div>
          ${items.map(kartu).join('')}
        </section>`;
    }).join('');
    list.innerHTML = html || '<p class="notif-empty">Tidak ada notifikasi.</p>';
  }

  let fokusAwal = null;
  function buka() {
    fokusAwal = document.activeElement;
    panel.classList.add('is-open');
    overlay.classList.add('is-open');
    panel.setAttribute('aria-hidden', 'false');
    bell.setAttribute('aria-expanded', 'true');
    document.body.classList.add('notif-lock');
    closeBtn.focus();
  }
  function tutup() {
    panel.classList.remove('is-open');
    overlay.classList.remove('is-open');
    panel.setAttribute('aria-hidden', 'true');
    bell.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('notif-lock');
    (fokusAwal || bell).focus();
  }

  bell.addEventListener('click', buka);
  closeBtn.addEventListener('click', tutup);
  overlay.addEventListener('click', tutup);

  // Klik kartu = tandai dibaca; klik Hapus = hapus satu notifikasi
  list.addEventListener('click', (e) => {
    const art = e.target.closest('.nitem');
    if (!art) return;
    const n = data.find((x) => x.id === Number(art.dataset.id));
    if (!n) return;
    if (e.target.closest('[data-del]')) {
      data.splice(data.indexOf(n), 1);
    } else if (n.baru) {
      n.baru = false;
    } else {
      return;
    }
    render();
    list.focus({ preventScroll: true });
  });

  clearBtn.addEventListener('click', () => {
    if (!data.length || !confirm('Hapus semua notifikasi?')) return;
    data.length = 0;
    render();
    closeBtn.focus();
  });

  // Esc menutup; Tab tetap berputar di dalam panel
  document.addEventListener('keydown', (e) => {
    if (!panel.classList.contains('is-open')) return;
    if (e.key === 'Escape') return tutup();
    if (e.key !== 'Tab') return;
    const f = [...panel.querySelectorAll('button:not([disabled])')];
    const first = f[0];
    const last = f[f.length - 1];
    const aktif = document.activeElement;
    if (e.shiftKey && (aktif === first || aktif === list)) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && aktif === last) { e.preventDefault(); first.focus(); }
  });

  render();
})();
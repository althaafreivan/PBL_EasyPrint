(() => {
  const orders = (window.ORDERS || []).map((o) => ({ ...o }));
  const { esc, rupiah, angka, call, toast, prompt, konfirmasi } = window.EPAPI;
  const $ = (id) => document.getElementById(id);

  const body = $('orderBody');
  const empty = $('emptyMsg');
  const cari = $('cari');
  const detail = $('detail');
  const chips = document.querySelectorAll('[data-filter]');

  const AKTIF = ['Pending', 'Diverifikasi', 'Diproses', 'Tertunda', 'Tertunda - Stok Tidak Tersedia'];
  const tertunda = (s) => s.startsWith('Tertunda');
  const label = (s) => (tertunda(s) ? 'Tertunda' : s);
  const pill = (s) => tertunda(s) ? 'pill--tertunda' : ({ Pending: 'pill--yellow', Diverifikasi: 'pill--soft', Diproses: 'pill--blue',
    Selesai: 'pill--green', Diambil: 'pill--green', Ditolak: 'pill--red' }[s] || 'pill--blue');
  const cocokFilter = (o, f) => f === 'semua' || (f === 'aktif' ? AKTIF.includes(o.status) : (f === 'Tertunda' ? tertunda(o.status) : o.status === f));

  let filter = (document.querySelector('[data-filter].is-active') || { dataset: { filter: 'aktif' } }).dataset.filter;
  let terpilih = window.PILIH || null;

  function aksi(o) {
    const b = (act, teks, kelas) => `<button type="button" class="btn ${kelas}" data-act="${act}">${teks}</button>`;
    switch (true) {
      case o.status === 'Pending':
        return b('verifikasi', 'Verifikasi', 'btn--green') + b('tolak', 'Tolak', 'btn--danger');
      case o.status === 'Diverifikasi':
        return b('proses', 'Proses', 'btn--primary') + b('tunda', 'Tunda', 'btn--outline') + b('tolak', 'Tolak', 'btn--danger');
      case tertunda(o.status):
        return b('proses', 'Coba proses', 'btn--primary') + b('tolak', 'Tolak', 'btn--danger');
      case o.status === 'Diproses':
        return b('selesai', 'Selesai', 'btn--green');
      case o.status === 'Selesai':
        return `<a class="btn btn--primary" href="admin-cashier.php?q=${encodeURIComponent(o.kode.replace('#', ''))}">Kasir</a>`;
      case o.status === 'Diambil':
        return `<a class="btn btn--outline" href="invoice.php?id=${o.id}">Nota</a>`;
      default:
        return b('detail', 'Detail', 'btn--outline');
    }
  }

  function hitungChip() {
    document.querySelectorAll('[data-count]').forEach((el) => {
      const f = el.dataset.count;
      el.textContent = f === 'semua' ? orders.length : orders.filter((o) => cocokFilter(o, f)).length;
    });
  }

  function render() {
    const q = cari.value.trim().toLowerCase().replace(/^#/, '');
    const qDigit = q.replace(/\D/g, '');
    const daftar = orders.filter((o) => cocokFilter(o, filter) &&
      (!q || o.kode.toLowerCase().includes(q) || o.pelanggan.toLowerCase().includes(q) || (qDigit.length >= 4 && o.telepon.replace(/\D/g, '').includes(qDigit))));

    // Antrean aktif: terlama dulu (FIFO). Selain itu: terbaru dulu.
    if (filter === 'aktif') daftar.sort((a, b) => a.id - b.id);

    body.innerHTML = daftar.map((o) => `
      <tr data-id="${o.id}" class="${o.id === terpilih ? 'is-selected' : ''}" tabindex="0">
        <td class="col-kode">${esc(o.kode)}<small class="aq-sub">${esc(o.pelanggan)} · ${esc(o.masuk)}</small></td>
        <td>${esc(o.layanan)}${o.berkas ? ' <span class="aq-file" title="Ada berkas">📎</span>' : ''}</td>
        <td class="col-total">${rupiah(o.total)}</td>
        <td><span class="pill ${pill(o.status)}">${esc(label(o.status))}</span></td>
        <td><div class="actions">${aksi(o)}</div></td>
      </tr>`).join('');

    empty.hidden = daftar.length > 0;
    $('infoMsg').textContent = `Menampilkan ${daftar.length} dari ${orders.length} pesanan`;
    hitungChip();
  }

  async function muatDetail(id) {
    terpilih = id;
    render();
    detail.innerHTML = '<p class="aq-detail__empty">Memuat detail…</p>';
    const res = await call('/api/orders/' + id);
    if (!res.ok) { detail.innerHTML = `<p class="aq-detail__empty">${esc(res.data.message)}</p>`; return; }
    const { pesanan: p, items, riwayat, kebutuhan_bahan: keb } = res.data;
    const o = orders.find((x) => x.id === id);
    if (o && o.status !== p.status) { o.status = p.status; render(); }
    const adaFile = items.some((i) => i.file_dokumen);
    const kurang = keb.filter((k) => !k.cukup);

    detail.innerHTML = `
      <div class="aq-detail__head">
        <div><h2>${esc('#' + p.kode_pesanan)}</h2><p>${esc(p.nama)} · ${esc(p.no_telepon || '-')}</p></div>
        <span class="pill ${pill(p.status)}">${esc(label(p.status))}</span>
      </div>
      <h3>Item pesanan</h3>
      <ul class="aq-list">${items.map((i) => `<li><span><strong>${esc(i.nama_layanan)}</strong><small>${esc(i.spesifikasi || i.jumlah + ' ' + i.satuan)}</small></span><b>${rupiah(i.subtotal)}</b></li>`).join('')}
        <li class="aq-list__total"><span>Total</span><b>${rupiah(p.total_harga)}</b></li></ul>
      <h3>Berkas</h3>
      ${adaFile
        ? `<div class="file-box__row"><strong>${esc(items.find((i) => i.file_dokumen).file_dokumen)}</strong><a class="btn-white" href="file.php?id=${p.id}" target="_blank" rel="noopener">Preview</a></div>`
        : '<p class="aq-muted">Tidak ada berkas (dokumen dibawa ke outlet).</p>'}
      <h3>Catatan pelanggan</h3><p class="aq-muted">${esc(p.catatan || '-')}</p>
      ${p.alasan_tolak ? `<h3>Alasan penolakan</h3><p class="aq-muted">${esc(p.alasan_tolak)}</p>` : ''}
      <h3>Kebutuhan bahan ${p.stok_dikurangi ? '<span class="pill pill--soft">Stok sudah dipotong</span>' : ''}</h3>
      ${keb.length ? `<table class="aq-keb"><thead><tr><th>Bahan</th><th>Butuh</th><th>Stok</th></tr></thead><tbody>
        ${keb.map((k) => `<tr class="${k.cukup ? '' : 'is-kurang'}"><td>${esc(k.nama_bahan)}</td><td>${angka(k.dibutuhkan, 4)} ${esc(k.satuan)}</td><td>${angka(k.stok_tersedia, 2)} ${k.cukup ? '✓' : '✗'}</td></tr>`).join('')}
        </tbody></table>` : '<p class="aq-muted">Layanan ini tidak memakai bahan yang dilacak.</p>'}
      ${kurang.length && !p.stok_dikurangi ? `<p class="aq-warn">Stok ${esc(kurang.map((k) => k.nama_bahan).join(', '))} tidak mencukupi. Jika diproses, pesanan otomatis menjadi "Tertunda - Stok Tidak Tersedia".</p>` : ''}
      <h3>Riwayat status</h3>
      <ul class="aq-log">${riwayat.slice().reverse().map((r) => `<li><time>${esc(r.created_at.slice(0, 16))}</time><span>${esc(r.status_baru)}${r.keterangan && r.status_lama ? ' — ' + esc(r.keterangan) : ''}</span></li>`).join('')}</ul>
      <div class="actions aq-actions" data-id="${p.id}">${aksi({ ...p, id: p.id, kode: '#' + p.kode_pesanan })}</div>`;
  }

  async function ubahStatus(o, statusBaru, extra = {}) {
    const res = await call('/api/orders/status', { method: 'PATCH', body: { id_pesanan: o.id, status_pesanan: statusBaru, ...extra } });
    if (!res.ok) { toast(res.data.message || 'Gagal memperbarui status.', 'err'); return; }
    o.status = res.data.status_pesanan;
    toast(res.data.stok_cukup === false ? `${o.kode}: ${res.data.message}` : `${o.kode} → ${label(o.status)}`, res.data.stok_cukup === false ? 'warn' : 'ok');
    await muatDetail(o.id);
  }

  async function jalankan(act, o) {
    if (act === 'detail') return muatDetail(o.id);
    if (act === 'verifikasi') return ubahStatus(o, 'Diverifikasi');
    if (act === 'proses') return ubahStatus(o, 'Diproses');
    if (act === 'selesai') {
      if (!(await konfirmasi({ judul: `Tandai ${o.kode} selesai?`, pesan: 'Pelanggan akan diberi tahu bahwa pesanan siap diambil.', tombol: 'Ya, selesai' }))) return;
      return ubahStatus(o, 'Selesai');
    }
    if (act === 'tunda') {
      const alasan = await prompt({ judul: `Tunda pesanan ${o.kode}`, label: 'Alasan penundaan', placeholder: 'Contoh: mesin cetak warna sedang diperbaiki', tombol: 'Tunda' });
      if (alasan) return ubahStatus(o, 'Tertunda', { alasan });
    }
    if (act === 'tolak') {
      const alasan = await prompt({ judul: `Tolak pesanan ${o.kode}?`, pesan: 'Alasan akan ditampilkan kepada pelanggan.', label: 'Alasan penolakan', placeholder: 'Contoh: berkas buram / tidak terbaca', tombol: 'Tolak pesanan', bahaya: true });
      if (alasan) return ubahStatus(o, 'Ditolak', { alasan });
    }
  }

  // Filter status
  chips.forEach((c) => c.addEventListener('click', () => {
    filter = c.dataset.filter;
    chips.forEach((x) => { const on = x === c; x.classList.toggle('is-active', on); x.setAttribute('aria-pressed', on); });
    render();
  }));
  cari.addEventListener('input', render);

  // Klik baris / tombol aksi (tabel maupun panel detail)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-act]');
    const tr = e.target.closest('#orderBody tr[data-id]');
    const panel = e.target.closest('.aq-actions');
    const id = Number((tr && tr.dataset.id) || (panel && panel.dataset.id) || 0);
    if (!id) return;
    const o = orders.find((x) => x.id === id);
    if (!o) return;
    if (btn) {
      btn.disabled = true;
      try { await jalankan(btn.dataset.act, o); } finally { btn.disabled = false; render(); }
    } else if (tr && !e.target.closest('a')) {
      muatDetail(id);
    }
  });
  body.addEventListener('keydown', (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (tr && e.key === 'Enter' && e.target === tr) muatDetail(Number(tr.dataset.id));
  });

  render();
  if (terpilih && orders.some((o) => o.id === terpilih)) muatDetail(terpilih);
})();

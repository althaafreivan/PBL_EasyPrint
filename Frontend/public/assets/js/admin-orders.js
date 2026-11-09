(() => {
  const orders = (window.ORDERS || []).map((o) => ({ ...o }));
  const hitung = { ...(window.HITUNG || {}) };
  const NAMA = { pending: 'Pending', tertunda: 'Tertunda', diproses: 'Diproses', selesai: 'Selesai' };
  const CATATAN = {
    buram: 'Hasil scan terlalu kecil sehingga teks tidak jelas. Mohon unggah ulang minimal 300 dpi.',
    format: 'Format berkas tidak didukung. Mohon unggah ulang dalam format PDF atau DOCX.',
    spek: 'Spesifikasi pesanan tidak sesuai dengan isi berkas. Mohon periksa kembali lalu buat pesanan baru.',
    permanen: 'Mohon maaf, pesanan ini tidak dapat kami penuhi.',
    lain: '',
  };

  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const tglPendek = (iso) => `${Number(iso.slice(8, 10))} ${BULAN[Number(iso.slice(5, 7)) - 1]}`;

  const body = $('qBody');
  let filter = 'semua';
  let aktif = null; // pesanan yang sedang di popup

  const IKON_MATA = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  const IKON_UNDUH = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/></svg>';

  function aksi(o) {
    if (o.status === 'pending') {
      return `<button type="button" class="qb ${o.blokir ? 'qb--soft' : 'qb--primary'}" data-act="proses" ${o.blokir ? 'disabled title="Stok bahan tidak mencukupi"' : ''}>Proses</button>
              <button type="button" class="qb ${o.blokir ? 'qb--amber' : 'qb--tunda'}" data-act="tunda">Tunda</button>
              <button type="button" class="qb qb--danger" data-act="tolak">Tolak</button>`;
    }
    if (o.status === 'tertunda') return '<span class="qhint">Lanjut otomatis saat stok masuk</span>';
    if (o.status === 'diproses') return '<button type="button" class="qb qb--primary" data-act="selesai">Tandai Selesai</button>';
    return `<a class="qb qb--dark" href="admin-cashier.php?q=${encodeURIComponent(o.kode.replace('#', ''))}">Ke kasir</a>`;
  }

  function render() {
    const q = $('cari').value.trim().toLowerCase();
    const daftar = orders.filter((o) =>
      (filter === 'semua' || o.status === filter) &&
      (!q || o.kode.toLowerCase().includes(q) || o.pelanggan.toLowerCase().includes(q)));

    body.innerHTML = daftar.map((o) => `
      <tr data-kode="${esc(o.kode)}">
        <td><span class="qcode">${esc(o.kode)}</span><span class="qsub">${esc(o.pelanggan)} · ${esc(o.jam)}</span></td>
        <td>${esc(o.layanan)}${o.catatan ? `<span class="qnote qnote--${o.nada}">${esc(o.catatan)}</span>` : ''}</td>
        <td><span class="qfile">${esc(o.berkas)}</span>
          <span class="qfile-act">
            <button type="button" class="qmini" data-act="preview">${IKON_MATA} Preview</button>
            <button type="button" class="qmini qmini--ico" data-act="unduh" aria-label="Unduh ${esc(o.berkas)}">${IKON_UNDUH}</button>
          </span></td>
        <td class="r qtotal">${rupiah(o.total)}</td>
        <td><span class="badge badge--${o.status}">${NAMA[o.status]}</span></td>
        <td><div class="qacts">${aksi(o)}</div></td>
      </tr>`).join('');

    $('qEmpty').hidden = daftar.length > 0;
    Object.keys(hitung).forEach((k) => { const el = $('cnt-' + k); if (el) el.textContent = hitung[k]; });
    $('qInfo').textContent = `Menampilkan ${daftar.length} dari ${hitung.semua} pesanan aktif`;
  }

  function pindah(o, baru) {
    hitung[o.status] = Math.max(0, hitung[o.status] - 1);
    hitung[baru] += 1;
    o.status = baru;
  }

  function toast(pesan) {
    const t = $('toast');
    t.textContent = pesan;
    t.hidden = false;
    clearTimeout(toast.id);
    toast.id = setTimeout(() => { t.hidden = true; }, 2600);
  }

  // Filter, cari
  document.querySelectorAll('[data-filter]').forEach((b) => b.addEventListener('click', () => {
    filter = b.dataset.filter;
    document.querySelectorAll('[data-filter]').forEach((x) => {
      x.classList.toggle('is-active', x === b);
      x.setAttribute('aria-pressed', String(x === b));
    });
    render();
  }));
  $('cari').addEventListener('input', render);

  // Aksi pada baris
  body.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn || btn.disabled) return;
    const o = orders.find((x) => x.kode === btn.closest('tr').dataset.kode);
    if (!o) return;
    const act = btn.dataset.act;
    if (act === 'proses') { pindah(o, 'diproses'); render(); toast(`${o.kode} diproses. Stok bahan dipotong otomatis.`); }
    if (act === 'selesai') { pindah(o, 'selesai'); render(); toast(`${o.kode} ditandai selesai.`); }
    if (act === 'preview') toast(`Pratinjau berkas ${o.berkas} (demo).`);
    if (act === 'unduh') toast(`Mengunduh ${o.berkas} (demo).`);
    if (act === 'tunda') bukaTunda(o);
    if (act === 'tolak') bukaTolak(o);
  });

  // Popup Tunda
  const dlgTunda = $('dlgTunda');
  function bukaTunda(o) {
    aktif = o;
    $('tdTitle').textContent = `Tunda pesanan ${o.kode}?`;
    $('tdAlert').innerHTML = `<strong>${esc(o.habis || 'Bahan belum dipilih')}</strong> · ${esc(o.butuh || 'periksa stok bahan')}`;
    $('tdBahan').innerHTML = (window.BAHAN_OPSI || []).map((n) => `<option>${esc(n)}</option>`).join('');
    if (o.habis) $('tdBahan').value = o.habis;
    $('tdPesan').value = 'Mohon maaf, Kertas F4 sedang habis. Pesanan dilanjutkan setelah pasokan tiba (perkiraan 10 Okt 2026).';
    dlgTunda.showModal();
  }
  $('formTunda').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!aktif) return;
    pindah(aktif, 'tertunda');
    aktif.catatan = `Menunggu ${$('tdBahan').value.replace(/ HVS.*$/, '')} (est. ${tglPendek($('tdTgl').value)})`;
    aktif.nada = 'bad';
    dlgTunda.close();
    render();
    toast(`${aktif.kode} ditunda. Pesan tampil di tracking pelanggan.`);
  });

  // Popup Tolak
  const dlgTolak = $('dlgTolak');
  function bukaTolak(o) {
    aktif = o;
    $('tlTitle').textContent = `Tolak pesanan ${o.kode}?`;
    dlgTolak.querySelector('input[value="buram"]').checked = true;
    $('tlCatatan').value = CATATAN.buram;
    dlgTolak.showModal();
  }
  dlgTolak.querySelectorAll('input[name="alasan"]').forEach((r) => r.addEventListener('change', () => {
    $('tlCatatan').value = CATATAN[r.value];
  }));
  $('formTolak').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!aktif) return;
    hitung[aktif.status] = Math.max(0, hitung[aktif.status] - 1);
    hitung.semua = Math.max(0, hitung.semua - 1);
    orders.splice(orders.indexOf(aktif), 1);
    dlgTolak.close();
    render();
    toast('Pesanan ditolak. Alasan tampil di tracking pelanggan.');
  });

  // Tutup popup
  document.querySelectorAll('dialog [data-tutup]').forEach((b) => b.addEventListener('click', () => b.closest('dialog').close()));

  render();
})();
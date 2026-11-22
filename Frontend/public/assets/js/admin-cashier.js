(() => {
  const { call, rupiah, esc, toast, konfirmasi } = window.EPAPI;
  const $ = (id) => document.getElementById(id);

  const STATUS = {
    Pending: 'pill--yellow', Diverifikasi: 'pill--soft', Diproses: 'pill--blue',
    Selesai: 'pill--green', Diambil: 'pill--soft', Ditolak: 'pill--red',
  };
  let aktif = null;

  function baris(nama, harga) {
    const row = document.createElement('div');
    row.className = 'sum-row';
    const a = document.createElement('span');
    a.textContent = nama;
    const b = document.createElement('strong');
    b.textContent = rupiah(harga);
    row.append(a, b);
    return row;
  }

  function sembunyi() {
    aktif = null;
    $('orderCard').hidden = true;
    $('cashierActions').hidden = true;
  }

  // Tampilkan detail dari GET /api/orders/{id}
  async function tampil(id) {
    $('notFound').hidden = true;
    $('multi').hidden = true;
    const res = await call('/api/orders/' + id);
    if (!res.ok) { sembunyi(); $('notFound').textContent = res.data.message; $('notFound').hidden = false; return; }
    const { pesanan: p, items } = res.data;
    aktif = p;
    $('orderCard').hidden = false;
    $('cashierActions').hidden = false;

    $('ordKode').textContent = '#' + p.kode_pesanan;
    $('ordNama').textContent = p.nama;
    $('ordTelp').textContent = p.no_telepon || '-';
    $('ordTotal').textContent = rupiah(p.total_harga);
    $('ordRincian').replaceChildren(...items.map((i) => baris(`${i.nama_layanan} · ${i.spesifikasi || i.jumlah + ' ' + i.satuan}`, i.subtotal)));

    const pill = $('ordStatus');
    pill.textContent = p.status.startsWith('Tertunda') ? 'Tertunda' : p.status;
    pill.className = 'pill ' + (p.status.startsWith('Tertunda') ? 'pill--tertunda' : (STATUS[p.status] || 'pill--blue'));

    const metode = $('ordMetode');
    metode.disabled = p.status !== 'Selesai';
    if (p.metode_bayar) metode.value = p.metode_bayar;

    const btn = $('btnAmbil');
    const info = $('handoverText');
    $('btnStruk').href = p.status === 'Diambil' ? 'admin-receipt.php?id=' + p.id : 'invoice.php?id=' + p.id;
    $('btnStruk').textContent = p.status === 'Diambil' ? 'Cetak Struk' : 'Lihat Nota';
    if (p.status === 'Selesai') {
      btn.disabled = false;
      btn.textContent = 'Konfirmasi & Ambil';
      info.textContent = 'Terima pembayaran, lalu konfirmasi. Status berubah menjadi Diambil.';
    } else if (p.status === 'Diambil') {
      btn.disabled = true;
      btn.textContent = 'Sudah diambil';
      info.textContent = `Diserahterimakan · dibayar ${p.metode_bayar || '-'}.`;
    } else {
      btn.disabled = true;
      btn.textContent = 'Konfirmasi & Ambil';
      info.textContent = 'Pesanan baru bisa diambil setelah berstatus Selesai.';
    }
  }

  // Cari via GET /api/orders?q= (kode pesanan / nama / nomor telepon)
  async function cari(q) {
    q = q.trim().replace(/^#/, '');
    if (!q) return;
    const res = await call('/api/orders', { query: { q, limit: 10 } });
    if (!res.ok) return toast(res.data.message, 'err');
    const hasil = res.data.data;
    if (!hasil.length) { sembunyi(); $('multi').hidden = true; $('notFound').textContent = 'Pesanan tidak ditemukan.'; $('notFound').hidden = false; return; }
    if (hasil.length === 1) return tampil(hasil[0].id);
    // Banyak hasil (mis. cari nomor telepon): prioritaskan yang siap diambil.
    hasil.sort((a, b) => (b.status === 'Selesai') - (a.status === 'Selesai'));
    sembunyi();
    $('notFound').hidden = true;
    $('multi').hidden = false;
    $('multi').innerHTML = `<span class="label">${hasil.length} pesanan cocok — pilih salah satu</span><div class="ks-ready__list">` +
      hasil.map((o) => `<button type="button" class="ks-chip" data-id="${o.id}"><strong>#${esc(o.kode_pesanan)}</strong><span>${esc(o.nama_pelanggan)} · ${esc(o.status)} · ${rupiah(o.total_harga)}</span></button>`).join('') + '</div>';
  }

  $('searchForm').addEventListener('submit', (e) => { e.preventDefault(); cari($('q').value); });
  document.addEventListener('click', (e) => {
    const chip = e.target.closest('.ks-chip');
    if (!chip) return;
    if (chip.dataset.id) return tampil(Number(chip.dataset.id));
    $('q').value = chip.dataset.q;
    cari(chip.dataset.q);
  });

  // Serah-terima: PATCH /api/orders/status -> Diambil (+ metode bayar), lalu ke struk
  $('btnAmbil').addEventListener('click', async () => {
    if (!aktif || aktif.status !== 'Selesai') return;
    const metode = $('ordMetode').value;
    const ya = await konfirmasi({ judul: `Konfirmasi pengambilan #${aktif.kode_pesanan}?`, pesan: `Pembayaran ${rupiah(aktif.total_harga)} via ${metode} sudah diterima.`, tombol: 'Ya, konfirmasi' });
    if (!ya) return;
    const btn = $('btnAmbil');
    btn.disabled = true;
    const res = await call('/api/orders/status', { method: 'PATCH', body: { id_pesanan: aktif.id, status_pesanan: 'Diambil', metode_bayar: metode } });
    if (!res.ok) { btn.disabled = false; return toast(res.data.message, 'err'); }
    location.href = 'admin-receipt.php?id=' + aktif.id + '&baru=1';
  });

  const awal = new URLSearchParams(location.search).get('q');
  if (awal) { $('q').value = awal; cari(awal); }
})();

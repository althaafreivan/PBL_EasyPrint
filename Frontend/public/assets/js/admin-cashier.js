(() => {
  const orders = (window.KASIR || []).map((o) => ({ ...o }));
  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
  const STATUS = {
    pending: ['Pending', 'pill--yellow'], tertunda: ['Tertunda', 'pill--tertunda'],
    diproses: ['Diproses', 'pill--blue'], selesai: ['Selesai', 'pill--green'], diambil: ['Diambil', 'pill--soft'],
  };
  let aktif = null;

  const sel = (teks, kelas) => { const td = document.createElement('td'); td.textContent = teks; if (kelas) td.className = kelas; return td; };

  function tampil(o) {
    aktif = o || null;
    $('kGrid').hidden = !o;
    $('notFound').hidden = !!o;
    if (!o) return;

    $('oKode').textContent = o.kode;
    $('oSelesai').textContent = o.selesai ? `Selesai dicetak ${o.selesai}` : '';
    $('oNama').textContent = o.pelanggan;
    $('oTelp').textContent = o.telepon;
    $('oMetode').textContent = o.metode;

    const [teks, kelas] = STATUS[o.status];
    $('oStatus').textContent = teks;
    $('oStatus').className = 'pill ' + kelas;

    let total = 0;
    $('oItems').replaceChildren(...o.items.map(([nama, qty, harga, sat]) => {
      const sub = qty * harga; total += sub;
      const tr = document.createElement('tr');
      tr.append(sel(nama), sel(sat ? `${qty} ${sat}` : String(qty), 'r'), sel(rupiah(harga), 'r'), sel(rupiah(sub), 'r b'));
      return tr;
    }));
    $('oTotal').textContent = rupiah(total);

    const btn = $('btnAmbil');
    const info = $('oInfo');
    btn.disabled = o.status !== 'selesai';
    if (o.status === 'selesai') info.innerHTML = 'Setelah dikonfirmasi, status pesanan berubah menjadi <strong>Diambil</strong> dan transaksi tercatat.';
    else if (o.status === 'diambil') info.textContent = 'Pesanan ini sudah diserahterimakan.';
    else info.textContent = 'Pesanan baru bisa diserahterimakan setelah berstatus Selesai.';
    $('btnStruk').href = 'admin-receipt.php?q=' + encodeURIComponent(o.kode.replace('#', ''));
  }

  function cari() {
    const q = $('q').value.trim().toLowerCase().replace(/^#/, '').replace(/[\s-]/g, '');
    if (!q) return tampil(orders[0]);
    tampil(orders.find((o) =>
      o.kode.toLowerCase().replace(/[#-]/g, '').includes(q) || o.telepon.replace(/[\s-]/g, '').includes(q)));
  }

  $('searchForm').addEventListener('submit', (e) => { e.preventDefault(); cari(); });

  // Konfirmasi serah terima -> status Diambil -> halaman struk
  $('btnAmbil').addEventListener('click', () => {
    if (!aktif || aktif.status !== 'selesai') return;
    aktif.status = 'diambil';
    location.href = 'admin-receipt.php?q=' + encodeURIComponent(aktif.kode.replace('#', ''));
  });

  cari();
})();
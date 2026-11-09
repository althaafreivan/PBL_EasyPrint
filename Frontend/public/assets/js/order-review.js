(() => {
  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

  let d = null;
  try { d = JSON.parse(sessionStorage.getItem('ep_draft') || 'null'); } catch (e) { d = null; }
  if (!d || !d.lembar) { location.replace('order-service.php'); return; }

  const baris = [
    ['Layanan', d.nama],
    ['Ukuran kertas', `${d.kertas} · HVS 70 gsm`],
    ['Mode cetak', `${d.mode === 'color' ? 'Warna' : 'Hitam-putih'} · ${d.duplex === 'ya' ? 'dua sisi' : 'satu sisi'}`],
    ['Jumlah', `${d.lembar} lembar × ${d.eks} eksemplar`],
    ['Jilid / finishing', d.namaJilid || 'Tanpa jilid'],
    ['Catatan untuk operator', d.catatan || '-'],
  ];
  $('rvList').replaceChildren(...baris.map(([k, v]) => {
    const row = document.createElement('div');
    const dt = document.createElement('dt'); dt.textContent = k;
    const dd = document.createElement('dd'); dd.textContent = v;
    row.append(dt, dd);
    return row;
  }));

  $('rvFile').textContent = d.berkas.nama;
  $('rvFileMeta').textContent = `${d.berkas.ukuran} · ${d.berkas.tipe}`;

  $('rvCetakLabel').textContent = `${d.lembar} lembar × ${rupiah(d.satuan)}` + (d.eks > 1 ? ` × ${d.eks} eks.` : '');
  $('rvCetak').textContent = rupiah(d.biayaCetak);
  $('rvJilidWrap').hidden = !d.biayaJilid;
  $('rvJilidLabel').textContent = `${d.namaJilid} × ${d.eks}`;
  $('rvJilid').textContent = rupiah(d.biayaJilid);
  $('rvTotal').textContent = rupiah(d.subtotal);

  $('rvCek').addEventListener('change', (e) => { $('rvKirim').disabled = !e.target.checked; });

  $('reviewForm').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!$('rvCek').checked) return;
    const t = new Date();
    const p2 = (n) => String(n).padStart(2, '0');
    d.kode = `#RI-${t.getFullYear()}${p2(t.getMonth() + 1)}${p2(t.getDate())}-0${Math.floor(10 + Math.random() * 89)}`;
    sessionStorage.setItem('ep_last', JSON.stringify(d));
    sessionStorage.removeItem('ep_draft');
    location.href = 'order-success.php';
  });
})();
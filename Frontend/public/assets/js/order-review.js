(() => {
  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));

  let d = null;
  try { d = JSON.parse(sessionStorage.getItem('ep_draft') || 'null'); } catch (e) { d = null; }
  if (!d || !d.items) { location.replace('order-service.php'); return; }

  const isJilid = d.kategori === 'finishing';
  const baris = [
    ['Layanan', d.nama],
    isJilid ? null : ['Sisi cetak', d.duplex === 'ya' ? 'Dua sisi (bolak-balik)' : 'Satu sisi'],
    ['Jumlah', isJilid ? `${d.eks} buku` : `${d.lembar} lembar × ${d.eks} eksemplar`],
    isJilid ? null : ['Jilid / finishing', d.namaJilid || 'Tanpa jilid'],
    ['Catatan untuk operator', d.catatan || '-'],
  ].filter(Boolean);
  $('rvList').replaceChildren(...baris.map(([k, v]) => {
    const row = document.createElement('div');
    const dt = document.createElement('dt'); dt.textContent = k;
    const dd = document.createElement('dd'); dd.textContent = v;
    row.append(dt, dd);
    return row;
  }));

  $('rvFile').textContent = d.berkas ? d.berkas.nama : 'Tanpa berkas';
  $('rvFileMeta').textContent = d.berkas ? `${d.berkas.ukuran} · ${d.berkas.tipe}` : 'Dokumen dibawa langsung ke outlet';
  if (!d.berkas) $('rvFilePill').hidden = true;

  $('rvCetakLabel').textContent = d.satuan === 'lembar'
    ? `${d.lembar} lembar × ${rupiah(d.harga)}` + (d.eks > 1 ? ` × ${d.eks} eks.` : '')
    : `${d.eks} ${d.satuan} × ${rupiah(d.harga)}`;
  $('rvCetak').textContent = rupiah(d.biayaCetak);
  $('rvJilidWrap').hidden = !d.biayaJilid;
  $('rvJilidLabel').textContent = `${d.namaJilid} × ${d.eks}`;
  $('rvJilid').textContent = rupiah(d.biayaJilid);
  $('rvTotal').textContent = rupiah(d.subtotal);

  const err = (m) => { $('rvError').textContent = m || ''; $('rvError').hidden = !m; };
  $('rvCek').addEventListener('change', (e) => { $('rvKirim').disabled = !e.target.checked; });

  // Kirim: POST /api/orders (multipart: items JSON + catatan + file_dokumen)
  $('reviewForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!$('rvCek').checked) return;
    err('');

    const form = new FormData();
    form.append('items', JSON.stringify(d.items));
    form.append('catatan', d.catatan || '');
    if (d.berkas) {
      const file = await EPDraftFile.ambil();
      if (!file) return err('Berkas tidak ditemukan lagi di browser. Kembali ke langkah sebelumnya dan pilih ulang berkas.');
      form.append('file_dokumen', file, file.name || d.berkas.nama);
    }

    const btn = $('rvKirim');
    btn.disabled = true;
    btn.textContent = 'Mengirim…';
    const res = await EPAPI.call('/api/orders', { method: 'POST', form });
    if (!res.ok) {
      btn.disabled = false;
      btn.textContent = 'Kirim Pesanan';
      return err(res.data.message || 'Pesanan gagal dikirim.');
    }
    sessionStorage.setItem('ep_last', JSON.stringify({ id: res.data.id_pesanan, kode: res.data.kode_pesanan }));
    sessionStorage.removeItem('ep_draft');
    await EPDraftFile.hapus();
    location.href = 'order-success.php?id=' + encodeURIComponent(res.data.id_pesanan);
  });
})();

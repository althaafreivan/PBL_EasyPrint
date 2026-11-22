(() => {
  // Katalog dari GET /api/layanan (diinjeksi PHP). Estimasi di sini; harga final dihitung backend.
  const SEMUA = window.LAYANAN || [];
  const KATEGORI = {
    cetak:     { nama: 'Print Dokumen', desc: 'Cetak PDF, Word, dan dokumen lainnya', berkasWajib: true },
    fotokopi:  { nama: 'Fotokopi',      desc: 'Bawa dokumen asli ke outlet, atau unggah berkasnya', berkasWajib: false },
    finishing: { nama: 'Jilid',         desc: 'Finishing dokumen yang sudah Anda cetak', berkasWajib: false },
  };
  const MAKS = 10 * 1024 * 1024; // sama dengan batas backend

  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
  const angka = (el, max) => Math.min(max, Math.max(1, parseInt(el.value, 10) || 1));
  const pilih = (name) => document.querySelector(`input[name="${name}"]:checked`).value;
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  let draft = {};
  try { draft = JSON.parse(sessionStorage.getItem('ep_draft') || '{}') || {}; } catch (e) { draft = {}; }
  const kat = KATEGORI[draft.kategori] ? draft.kategori : 'cetak';
  const info = KATEGORI[kat];
  const varian = SEMUA.filter((l) => l.kategori === kat);
  const finishing = SEMUA.filter((l) => l.kategori === 'finishing');
  const isJilid = kat === 'finishing';

  if (!varian.length) {
    $('specError').textContent = 'Layanan untuk kategori ini belum tersedia. Silakan pilih layanan lain.';
    $('specError').hidden = false;
  }

  // Isi pilihan
  $('varian').innerHTML = varian.map((l) =>
    `<option value="${l.id}">${esc(l.nama)} — ${rupiah(l.harga)}/${esc(l.satuan)}</option>`).join('');
  $('jilid').innerHTML = '<option value="">Tanpa jilid</option>' +
    finishing.map((l) => `<option value="${l.id}">${esc(l.nama)} (+${rupiah(l.harga)}/${esc(l.satuan)})</option>`).join('');

  // Jilid saja: tidak ada lembar, sisi cetak, atau finishing tambahan.
  $('wrapLembar').hidden = isJilid;
  $('wrapDuplex').hidden = isJilid;
  $('wrapJilid').hidden = isJilid;
  $('lblEks').textContent = isJilid ? 'Jumlah buku yang dijilid' : 'Jumlah eksemplar';

  $('svcNama').textContent = info.nama;
  $('svcDesc').textContent = info.desc;
  $('wizLayanan').textContent = info.nama;
  $('dzHint').textContent = 'atau pilih dari perangkat · PDF, DOCX · Maks. 10 MB' + (info.berkasWajib ? '' : ' · opsional');

  // Isi ulang formulir saat kembali dari langkah 3
  if (draft.varianId) {
    if (varian.some((l) => l.id === draft.varianId)) $('varian').value = draft.varianId;
    $('lembar').value = draft.lembar || 10;
    $('eksemplar').value = draft.eks || 1;
    $('jilid').value = draft.jilidId || '';
    $('catatan').value = draft.catatan || '';
    const d = document.querySelector(`input[name="duplex"][value="${draft.duplex}"]`);
    if (d) d.checked = true;
  }

  let berkas = null;      // metadata {nama, ukuran, tipe}
  let fileObj = null;     // File asli (disimpan ke IndexedDB)
  let hasil = {};

  function hitung() {
    const v = varian.find((l) => l.id === Number($('varian').value));
    if (!v) return;
    const lembar = isJilid ? 1 : angka($('lembar'), 2000);
    const eks = angka($('eksemplar'), 500);
    const duplex = isJilid ? 'tidak' : pilih('duplex');
    const j = isJilid ? null : finishing.find((l) => l.id === Number($('jilid').value)) || null;

    // satuan "lembar" = harga x lembar x eksemplar; satuan lain (buku, dll) = harga x eksemplar
    const qtyUtama = v.satuan === 'lembar' ? lembar * eks : eks;
    const biayaCetak = v.harga * qtyUtama;
    const biayaJilid = j ? j.harga * eks : 0;
    const subtotal = biayaCetak + biayaJilid;

    const spekUtama = isJilid ? `${eks} buku` : `${lembar} lembar x ${eks} eks` + (duplex === 'ya' ? ' | dua sisi' : '');
    const items = [{ layanan_id: v.id, jumlah: qtyUtama, spesifikasi: spekUtama }];
    if (j) items.push({ layanan_id: j.id, jumlah: eks, spesifikasi: `${eks} buku` });

    hasil = {
      kategori: kat, namaKategori: info.nama, varianId: v.id, nama: v.nama, satuan: v.satuan, harga: v.harga,
      lembar, eks, duplex, jilidId: j ? j.id : '', namaJilid: j ? j.nama : '', hargaJilid: j ? j.harga : 0,
      qtyUtama, biayaCetak, biayaJilid, subtotal, items,
    };

    $('svcPrice').textContent = rupiah(v.harga);
    $('svcSatuan').textContent = 'per ' + v.satuan;
    $('sumLayanan').textContent = v.nama;
    $('sumSpek').textContent = [isJilid ? `${eks} buku` : `${lembar} lembar × ${eks} eks.`,
      j ? j.nama : '', duplex === 'ya' ? 'Dua sisi' : ''].filter(Boolean).join(' · ');

    $('rowCetakLabel').textContent = v.satuan === 'lembar'
      ? `${lembar} lembar × ${rupiah(v.harga)}` + (eks > 1 ? ` × ${eks} eks.` : '')
      : `${eks} ${v.satuan} × ${rupiah(v.harga)}`;
    $('rowCetak').textContent = rupiah(biayaCetak);

    $('rowJilidWrap').hidden = !j;
    $('rowJilidLabel').textContent = j ? `${j.nama} × ${eks}` : '';
    $('rowJilid').textContent = rupiah(biayaJilid);

    $('sumSubtotal').textContent = rupiah(subtotal);
    $('sumTotal').textContent = rupiah(subtotal);
  }

  function tampilBerkas() {
    $('fileName').textContent = berkas ? berkas.nama : 'Belum ada berkas';
    $('fileMeta').textContent = berkas ? `${berkas.ukuran} · ${berkas.tipe}` : (info.berkasWajib ? 'Unggah dokumen Anda' : 'Opsional untuk layanan ini');
    $('dzCheck').hidden = !berkas;
  }

  function error(pesan) {
    const el = $('berkasError');
    el.textContent = pesan || '';
    el.hidden = !pesan;
  }

  function setFile(file) {
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['pdf', 'docx'].includes(ext)) return error('Format berkas harus PDF atau DOCX.');
    if (file.size > MAKS) return error('Ukuran berkas maksimal 10 MB.');
    error('');
    fileObj = file;
    berkas = { nama: file.name, ukuran: (file.size / 1048576).toFixed(1).replace('.', ',') + ' MB', tipe: ext.toUpperCase() };
    tampilBerkas();
  }

  ['varian', 'lembar', 'eksemplar', 'jilid'].forEach((id) => $(id).addEventListener('input', hitung));
  document.querySelectorAll('input[name="duplex"]').forEach((r) => r.addEventListener('change', hitung));

  $('berkas').addEventListener('change', (e) => setFile(e.target.files[0]));
  const dz = $('dropzone');
  dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('is-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('is-over'));
  dz.addEventListener('drop', (e) => { e.preventDefault(); dz.classList.remove('is-over'); setFile(e.dataTransfer.files[0]); });

  // Selanjutnya: simpan draf (teks -> sessionStorage, berkas -> IndexedDB) lalu ke langkah 3
  $('orderForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!varian.length) return;
    if (info.berkasWajib && !berkas) return error('Unggah berkas dokumen terlebih dahulu.');
    hitung();
    if (fileObj) await EPDraftFile.simpan(fileObj);
    else if (!berkas) await EPDraftFile.hapus();
    sessionStorage.setItem('ep_draft', JSON.stringify({ ...hasil, catatan: $('catatan').value.trim(), berkas }));
    location.href = 'order-review.php';
  });

  // Pulihkan berkas dari IndexedDB bila pengguna kembali dari langkah 3
  (async () => {
    if (draft.berkas) {
      const f = await EPDraftFile.ambil();
      if (f) { fileObj = f; berkas = draft.berkas; }
    }
    tampilBerkas();
  })();

  hitung();
  tampilBerkas();
})();

(() => {
  // Tarif contoh (nanti diganti data dari backend)
  const LAYANAN = {
    print:    { nama: 'Print Dokumen', desc: 'Cetak PDF, Word, dan dokumen lainnya',    harga: { bw: 500, color: 1000 } },
    fotokopi: { nama: 'Fotokopi',      desc: 'Fotokopi hitam-putih maupun warna',        harga: { bw: 300, color: 800 } },
    jilid:    { nama: 'Jilid',         desc: 'Finishing dokumen: jilid spiral & soft cover', harga: { bw: 0, color: 0 } },
  };
  const FAKTOR = { A4: 1, F4: 1, A3: 2 };
  const JILID  = { none: ['', 0], spiral: ['Jilid spiral', 5000], hard: ['Jilid hard cover', 15000] };
  const MODE   = { bw: 'Hitam-putih', color: 'Warna' };
  const MAKS   = 20 * 1024 * 1024;

  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
  const angka = (el) => Math.max(1, parseInt(el.value, 10) || 1);
  const pilih = (name) => document.querySelector(`input[name="${name}"]:checked`).value;

  let draft = {};
  try { draft = JSON.parse(sessionStorage.getItem('ep_draft') || '{}') || {}; } catch (e) { draft = {}; }
  const key = LAYANAN[draft.layanan] ? draft.layanan : 'print';
  const svc = LAYANAN[key];

  // Berkas contoh sesuai desain; diganti saat pengguna memilih berkas
  let berkas = draft.berkas || { nama: 'materi-kuliah.pdf', ukuran: '2,4 MB', tipe: 'PDF' };
  let hasil = {};

  // Isi ulang formulir (mis. saat kembali dari langkah 3)
  if (draft.lembar) {
    $('lembar').value = draft.lembar;
    $('eksemplar').value = draft.eks;
    $('kertas').value = draft.kertas;
    $('jilid').value = draft.jilid;
    $('catatan').value = draft.catatan || '';
    const m = document.querySelector(`input[name="mode"][value="${draft.mode}"]`);
    const d = document.querySelector(`input[name="duplex"][value="${draft.duplex}"]`);
    if (m) m.checked = true;
    if (d) d.checked = true;
  }
  $('svcNama').textContent = svc.nama;
  $('svcDesc').textContent = svc.desc;
  $('wizLayanan').textContent = svc.nama;

  function hitung() {
    const lembar = angka($('lembar'));
    const eks = angka($('eksemplar'));
    const mode = pilih('mode');
    const kertas = $('kertas').value;
    const [namaJilid, hargaJilid] = JILID[$('jilid').value];

    const satuan = svc.harga[mode] * FAKTOR[kertas];
    const biayaCetak = satuan * lembar * eks;
    const biayaJilid = hargaJilid * eks;
    const subtotal = biayaCetak + biayaJilid;

    hasil = { lembar, eks, kertas, mode, duplex: pilih('duplex'), jilid: $('jilid').value, namaJilid, satuan, biayaCetak, biayaJilid, subtotal };

    $('svcPrice').textContent = rupiah(satuan);
    $('sumLayanan').textContent = `${svc.nama} ${kertas}`;
    $('sumSpek').textContent = [MODE[mode], namaJilid, hasil.duplex === 'ya' ? 'Dua sisi' : ''].filter(Boolean).join(' · ');

    $('rowCetakLabel').textContent = `${lembar} lembar × ${rupiah(satuan)}` + (eks > 1 ? ` × ${eks} eks.` : '');
    $('rowCetak').textContent = rupiah(biayaCetak);

    $('rowJilidWrap').hidden = hargaJilid === 0;
    $('rowJilidLabel').textContent = `${namaJilid} × ${eks}`;
    $('rowJilid').textContent = rupiah(biayaJilid);

    $('sumSubtotal').textContent = rupiah(subtotal);
    $('sumTotal').textContent = rupiah(subtotal);
  }

  function tampilBerkas() {
    $('fileName').textContent = berkas ? berkas.nama : 'Belum ada berkas';
    $('fileMeta').textContent = berkas ? `${berkas.ukuran} · ${berkas.tipe}` : 'Unggah dokumen Anda';
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
    if (!['pdf', 'doc', 'docx'].includes(ext)) return error('Format berkas harus PDF atau DOCX.');
    if (file.size > MAKS) return error('Ukuran berkas maksimal 20 MB.');
    error('');
    berkas = { nama: file.name, ukuran: (file.size / 1048576).toFixed(1).replace('.', ',') + ' MB', tipe: ext.toUpperCase() };
    tampilBerkas();
  }

  ['lembar', 'eksemplar', 'kertas', 'jilid'].forEach((id) => $(id).addEventListener('input', hitung));
  document.querySelectorAll('input[name="mode"], input[name="duplex"]').forEach((r) => r.addEventListener('change', hitung));

  $('berkas').addEventListener('change', (e) => setFile(e.target.files[0]));
  const dz = $('dropzone');
  dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('is-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('is-over'));
  dz.addEventListener('drop', (e) => { e.preventDefault(); dz.classList.remove('is-over'); setFile(e.dataTransfer.files[0]); });

  // Selanjutnya: simpan draf lalu ke langkah 3
  $('orderForm').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!berkas) return error('Unggah berkas dokumen terlebih dahulu.');
    sessionStorage.setItem('ep_draft', JSON.stringify({
      layanan: key, nama: svc.nama, ...hasil, catatan: $('catatan').value.trim(), berkas,
    }));
    location.href = 'order-review.php';
  });

  hitung();
  tampilBerkas();
})();
(() => {
  // Tabel harga (nanti bisa diganti data dari backend)
  const HARGA_LEMBAR = {
    print:    { bw: 300, color: 500 },
    fotokopi: { bw: 300, color: 1000 },
    jilid:    { bw: 0,   color: 0 },
  };
  const FAKTOR_KERTAS = { A4: 1, F4: 1, A3: 2 };
  const HARGA_JILID   = { none: 0, spiral: 5000, hard: 15000 };
  const NAMA_JILID    = { spiral: 'Spiral', hard: 'Hard cover' };
  const NAMA_LAYANAN  = { print: 'Print', fotokopi: 'Fotokopi', jilid: 'Jilid' };
  const BIAYA_LAYANAN = 3500;

  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

  const el = {
    kertas: $('kertas'), warna: $('warna'), jilid: $('jilid'),
    lembar: $('lembar'), eksemplar: $('eksemplar'),
    berkas: $('berkas'), berkasNama: $('berkasNama'),
    printLabel: $('sumPrintLabel'), print: $('sumPrint'),
    jilidRow: $('sumJilidRow'), jilidLabel: $('sumJilidLabel'), jilidVal: $('sumJilid'),
    fee: $('sumFee'), total: $('sumTotal'),
  };

  let layanan = 'print';

  const angka = (input) => Math.max(1, parseInt(input.value, 10) || 1);

  function hitung() {
    const lembar = angka(el.lembar);
    const eks = angka(el.eksemplar);
    const satuan = HARGA_LEMBAR[layanan][el.warna.value] * FAKTOR_KERTAS[el.kertas.value];

    const biayaCetak = satuan * lembar * eks;
    const biayaJilid = HARGA_JILID[el.jilid.value] * eks;
    const total = biayaCetak + biayaJilid + BIAYA_LAYANAN;

    const namaWarna = el.warna.value === 'color' ? 'warna' : 'hitam-putih';
    const suffixEks = eks > 1 ? ` × ${eks} eks` : '';
    el.printLabel.textContent = `${NAMA_LAYANAN[layanan]} ${el.kertas.value} ${namaWarna} × ${lembar}${suffixEks}`;
    el.print.textContent = rupiah(biayaCetak);

    el.jilidRow.hidden = el.jilid.value === 'none';
    el.jilidLabel.textContent = `Jilid ${NAMA_JILID[el.jilid.value] || ''}${suffixEks}`.trim();
    el.jilidVal.textContent = rupiah(biayaJilid);

    el.fee.textContent = rupiah(BIAYA_LAYANAN);
    el.total.textContent = rupiah(total);
  }

  // Pilih layanan
  const kartu = document.querySelectorAll('.service[data-service]');
  function pilihLayanan(key) {
    layanan = key;
    kartu.forEach((k) => k.classList.toggle('is-selected', k.dataset.service === key));
    hitung();
  }
  document.querySelectorAll('[data-pick]').forEach((btn) => {
    btn.addEventListener('click', () => {
      pilihLayanan(btn.dataset.pick);
      $('konfigurasi').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  // Hitung ulang saat input berubah
  [el.kertas, el.warna, el.jilid, el.lembar, el.eksemplar].forEach((i) => {
    i.addEventListener('input', hitung);
  });

  // Nama file yang diunggah
  el.berkas.addEventListener('change', () => {
    el.berkasNama.textContent = el.berkas.files[0]?.name || 'Upload file';
  });

  // Konfirmasi (demo). Hapus preventDefault saat backend sudah siap.
  $('orderForm').addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Pesanan dikonfirmasi (demo). Total: ' + el.total.textContent);
  });

  pilihLayanan('print');
})();
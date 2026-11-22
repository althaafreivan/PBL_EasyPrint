(() => {
  // Data transaksi periode terpilih berasal dari GET /api/laporan/transaksi (dirender server).
  const tampil = window.TRANSAKSI || [];
  const periode = window.PERIODE || {};

  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const tgl = (iso) => iso.slice(8, 10) + '/' + iso.slice(5, 7) + '/' + iso.slice(2, 4); // 2026-10-08 -> 08/10/26

  function render() {
    $('trxBody').innerHTML = tampil.map((t) => `
      <tr>
        <td class="col-kode">${tgl(t.tanggal)}</td>
        <td class="col-kode"><a class="inv-link" href="invoice.php?id=${t.id}">${esc(t.invoice)}</a></td>
        <td class="col-kode">${esc(t.pelanggan)}</td>
        <td class="col-kode">${esc(t.layanan)}</td>
        <td class="col-kode">${rupiah(t.total)}</td>
      </tr>`).join('');
    $('emptyTrx').hidden = tampil.length > 0;
  }

  // Validasi periode sebelum form GET dikirim ke server
  $('filterForm').addEventListener('submit', (e) => {
    const dari = $('dari').value;
    const sampai = $('sampai').value;
    const salah = dari && sampai && dari > sampai;
    $('filterError').hidden = !salah;
    if (salah) e.preventDefault();
  });

  // Ekspor
  const dlg = $('dlgEkspor');
  $('btnEkspor').addEventListener('click', () => dlg.showModal());
  $('btnBatalEkspor').addEventListener('click', () => dlg.close());
  $('btnPdf').addEventListener('click', () => { dlg.close(); window.print(); });
  $('btnCsv').addEventListener('click', () => {
    const baris = [['Tanggal', 'Invoice', 'Pelanggan', 'Layanan', 'Total']]
      .concat(tampil.map((t) => [t.tanggal, t.invoice, t.pelanggan, t.layanan, t.total]));
    const csv = '﻿' + baris.map((r) => r.map((v) => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
    a.download = `laporan-transaksi_${periode.dari || ''}_${periode.sampai || ''}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
    dlg.close();
  });

  render();
})();

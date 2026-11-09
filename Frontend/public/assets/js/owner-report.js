(() => {
  const semua = window.TRANSAKSI || [];
  let tampil = semua.slice();

  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const tgl = (iso) => iso.slice(8, 10) + '/' + iso.slice(5, 7); // 2026-10-08 -> 08/10

  function render() {
    $('trxBody').innerHTML = tampil.map((t) => `
      <tr>
        <td class="col-kode">${tgl(t.tanggal)}</td>
        <td class="col-kode"><a class="inv-link" href="invoice.php">${esc(t.invoice)}</a></td>
        <td class="col-kode">${esc(t.pelanggan)}</td>
        <td class="col-kode">${esc(t.layanan)}</td>
        <td class="col-kode">${rupiah(t.total)}</td>
      </tr>`).join('');
    $('emptyTrx').hidden = tampil.length > 0;
  }

  // Filter periode (tanggal ISO bisa dibandingkan sebagai teks)
  $('filterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const dari = $('dari').value;
    const sampai = $('sampai').value;
    const salah = dari && sampai && dari > sampai;
    $('filterError').hidden = !salah;
    if (salah) return;
    tampil = semua.filter((t) => (!dari || t.tanggal >= dari) && (!sampai || t.tanggal <= sampai));
    render();
  });

  // Ekspor PDF / Excel
  const dlg = $('dlgEkspor');
  $('btnEkspor').addEventListener('click', () => dlg.showModal());
  $('btnBatalEkspor').addEventListener('click', () => dlg.close());
  $('btnPdf').addEventListener('click', () => { dlg.close(); window.print(); });
  $('btnCsv').addEventListener('click', () => {
    const baris = [['Tanggal', 'Invoice', 'Pelanggan', 'Layanan', 'Total']]
      .concat(tampil.map((t) => [t.tanggal, t.invoice, t.pelanggan, t.layanan, t.total]));
    const csv = '\ufeff' + baris.map((r) => r.map((v) => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
    a.download = 'laporan-transaksi.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(a.href);
    dlg.close();
  });

  render();
})();
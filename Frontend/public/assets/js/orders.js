(() => {
  const rows = [...document.querySelectorAll('#riwayatBody tr')];
  const tabs = [...document.querySelectorAll('[data-tab]')];
  const cari = document.getElementById('cariPesanan');
  const kosong = document.getElementById('riwayatKosong');
  const info = document.getElementById('riwayatInfo');
  let filter = (tabs.find((t) => t.classList.contains('is-active')) || { dataset: { tab: 'semua' } }).dataset.tab;

  function render() {
    const q = cari.value.trim().toLowerCase().replace(/^#/, '');
    let tampil = 0;
    rows.forEach((tr) => {
      const cocok = (filter === 'semua' || tr.dataset.status === filter) && (!q || tr.dataset.kode.includes(q));
      tr.hidden = !cocok;
      if (cocok) tampil++;
    });
    kosong.hidden = tampil > 0;
    info.textContent = `Menampilkan ${tampil} dari ${rows.length} pesanan`;
  }

  tabs.forEach((t) => t.addEventListener('click', () => {
    filter = t.dataset.tab;
    tabs.forEach((x) => {
      const on = x === t;
      x.classList.toggle('is-active', on);
      x.setAttribute('aria-pressed', on);
    });
    render();
  }));
  cari.addEventListener('input', render);

  // Batalkan pesanan Pending -> DELETE /api/orders/{id}
  document.getElementById('riwayatBody').addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-batal]');
    if (!btn) return;
    const ya = await EPAPI.konfirmasi({ judul: `Batalkan pesanan ${btn.dataset.kode}?`, pesan: 'Pesanan dan berkas yang diunggah akan dihapus. Tindakan ini tidak dapat dibatalkan.', tombol: 'Ya, batalkan' });
    if (!ya) return;
    const res = await EPAPI.call('/api/orders/' + btn.dataset.batal, { method: 'DELETE' });
    if (!res.ok) return EPAPI.toast(res.data.message || 'Gagal membatalkan pesanan.', 'err');
    const tr = btn.closest('tr');
    rows.splice(rows.indexOf(tr), 1);
    tr.remove();
    EPAPI.toast(res.data.message);
    render();
  });

  render();
})();

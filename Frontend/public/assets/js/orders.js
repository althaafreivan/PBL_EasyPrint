(() => {
  const rows = [...document.querySelectorAll('#riwayatBody tr')];
  const tabs = [...document.querySelectorAll('[data-tab]')];
  const cari = document.getElementById('cariPesanan');
  const kosong = document.getElementById('riwayatKosong');
  const info = document.getElementById('riwayatInfo');
  let filter = 'semua';

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
  render();
})();

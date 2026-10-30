(() => {
  const orders = (window.ORDERS || []).map((o) => ({ ...o }));
  const NAMA_STATUS = { pending: 'Pending', diproses: 'Diproses', selesai: 'Selesai' };

  const body = document.getElementById('orderBody');
  const empty = document.getElementById('emptyMsg');
  const cari = document.getElementById('cari');
  const fileName = document.getElementById('fileName');
  const chips = document.querySelectorAll('[data-filter]');

  let filter = 'semua';
  let terpilih = orders[0]?.kode || null;

  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  function aksi(o) {
    if (o.status === 'pending') {
      return `<button type="button" class="btn btn--green" data-act="terima">Terima</button>
              <button type="button" class="btn btn--danger" data-act="tolak">Tolak</button>`;
    }
    if (o.status === 'diproses') {
      return `<button type="button" class="btn btn--primary" data-act="tandai">Tandai</button>`;
    }
    return `<button type="button" class="btn btn--outline" data-act="detail">Detail</button>`;
  }

  function render() {
    const q = cari.value.trim().toLowerCase();
    const daftar = orders.filter((o) =>
      (filter === 'semua' || o.status === filter) &&
      (!q || o.kode.toLowerCase().includes(q) || o.pelanggan.toLowerCase().includes(q))
    );

    body.innerHTML = daftar.map((o) => `
      <tr data-kode="${esc(o.kode)}" class="${o.kode === terpilih ? 'is-selected' : ''}">
        <td class="col-kode">${esc(o.kode)}</td>
        <td>${esc(o.pelanggan)}</td>
        <td>${esc(o.layanan)}</td>
        <td class="col-total">${rupiah(o.total)}</td>
        <td><span class="badge badge--${o.status}">${NAMA_STATUS[o.status]}</span></td>
        <td><div class="actions">${aksi(o)}</div></td>
      </tr>`).join('');

    empty.hidden = daftar.length > 0;
    const aktif = orders.find((o) => o.kode === terpilih);
    fileName.textContent = aktif ? aktif.berkas : '-';
  }

  // Filter status
  chips.forEach((c) => c.addEventListener('click', () => {
    filter = c.dataset.filter;
    chips.forEach((x) => {
      const on = x === c;
      x.classList.toggle('is-active', on);
      x.setAttribute('aria-pressed', on);
    });
    render();
  }));

  // Pencarian
  cari.addEventListener('input', render);

  // Klik baris / tombol aksi
  body.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-kode]');
    if (!tr) return;
    const o = orders.find((x) => x.kode === tr.dataset.kode);
    terpilih = o.kode;

    const act = e.target.closest('[data-act]')?.dataset.act;
    if (act === 'terima') o.status = 'diproses';
    if (act === 'tandai') o.status = 'selesai';
    if (act === 'tolak' && confirm(`Tolak pesanan ${o.kode}?`)) {
      orders.splice(orders.indexOf(o), 1);
      terpilih = orders[0]?.kode || null;
    }
    if (act === 'detail') alert(`Detail ${o.kode}\n${o.pelanggan} - ${o.layanan}\n${rupiah(o.total)}`);
    render();
  });

  // Preview berkas (demo)
  document.getElementById('btnPreview').addEventListener('click', () => {
    const o = orders.find((x) => x.kode === terpilih);
    if (o) alert('Preview berkas: ' + o.berkas + ' (demo)');
  });

  render();
})();
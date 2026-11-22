(() => {
  const bahan = (window.BAHAN || []).map((b) => ({ ...b }));
  const { esc, angka, call, toast } = window.EPAPI;
  let mode = 'restock';

  const $ = (id) => document.getElementById(id);
  const body = $('stokBody');
  const empty = $('emptyStok');
  const cari = $('cari');
  const dlg = $('dlgStok');
  const selBahan = $('stokBahan');
  const inJumlah = $('stokJumlah');
  const err = (m) => { $('stokErr').textContent = m || ''; $('stokErr').hidden = !m; };

  // Stok <= reorder point berarti kritis (sama dengan trigger fn_update_status_bahan)
  const status = (b) => (b.stok <= 0 ? ['Habis', 'pill--red'] : b.stok <= b.reorder ? ['Kritis', 'pill--red'] : ['Aman', 'pill--green']);

  function render() {
    const q = cari.value.trim().toLowerCase();
    const daftar = bahan.filter((b) => !q || b.nama.toLowerCase().includes(q))
      .sort((a, b) => (b.stok <= b.reorder) - (a.stok <= a.reorder) || a.nama.localeCompare(b.nama));
    body.innerHTML = daftar.map((b) => {
      const [teks, kelas] = status(b);
      return `
        <tr data-id="${b.id}">
          <td class="col-kode">${esc(b.nama)}</td>
          <td>${angka(b.stok, 2)}</td>
          <td>${esc(b.satuan)}</td>
          <td>${angka(b.reorder, 2)}</td>
          <td><span class="pill ${kelas}">${teks}</span></td>
          <td><div class="actions">
            <button type="button" class="btn btn--primary" data-act="restock">Restock</button>
            <button type="button" class="btn btn--outline" data-act="edit">Koreksi</button>
          </div></td>
        </tr>`;
    }).join('');
    empty.hidden = daftar.length > 0;
  }

  function bukaDialog(m, b) {
    if (!bahan.length) return;
    mode = m;
    err('');
    selBahan.innerHTML = bahan.map((x) => `<option value="${x.id}">${esc(x.nama)} (${esc(x.satuan)})</option>`).join('');
    if (b) selBahan.value = b.id;
    selBahan.disabled = m === 'edit';
    const cur = bahan.find((x) => x.id === Number(selBahan.value));

    $('dlgStokTitle').textContent = m === 'edit' ? 'Koreksi stok (stock opname)' : 'Restock bahan';
    $('stokJumlahLabel').textContent = m === 'edit' ? `Stok fisik saat ini (${cur.satuan})` : 'Jumlah tambahan';
    inJumlah.min = 0;
    inJumlah.value = m === 'edit' ? cur.stok : 10;
    $('stokKet').value = '';
    $('stokKet').placeholder = m === 'edit' ? 'Wajib: alasan selisih, mis. hasil hitung fisik' : 'Contoh: pembelian dari supplier';
    dlg.showModal();
    inJumlah.focus();
  }

  cari.addEventListener('input', render);
  $('btnRestock').addEventListener('click', () => bukaDialog('restock'));
  $('btnBatalStok').addEventListener('click', () => dlg.close());

  body.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const b = bahan.find((x) => x.id === Number(btn.closest('tr').dataset.id));
    if (b) bukaDialog(btn.dataset.act, b);
  });

  // Simpan: POST /api/stok/{id}/restock atau PATCH /api/stok/{id} (koreksi)
  $('formStok').addEventListener('submit', async (e) => {
    e.preventDefault();
    const b = bahan.find((x) => x.id === Number(selBahan.value));
    const n = Number(inJumlah.value);
    const ket = $('stokKet').value.trim();
    if (!b || Number.isNaN(n) || n < 0) return err('Masukkan angka 0 atau lebih.');
    if (mode === 'restock' && n <= 0) return err('Jumlah tambahan harus lebih dari 0.');
    if (mode === 'edit' && !ket) return err('Isi keterangan koreksi stok.');

    const submit = e.submitter || $('formStok').querySelector('[type="submit"]');
    submit.disabled = true;
    const res = mode === 'edit'
      ? await call('/api/stok/' + b.id, { method: 'PATCH', body: { stok: n, keterangan: ket } })
      : await call(`/api/stok/${b.id}/restock`, { method: 'POST', body: { jumlah: n, keterangan: ket || undefined } });
    submit.disabled = false;
    if (!res.ok) return err(res.data.message);

    b.stok = Number(res.data.data.stok);
    dlg.close();
    render();
    toast(res.data.message);
    setTimeout(() => location.reload(), 900); // segarkan KPI & log
  });

  render();
})();

(() => {
  const bahan = (window.BAHAN || []).map((b) => ({ ...b }));
  const { esc, angka, rupiah, call, toast, konfirmasi } = window.EPAPI;
  let editId = null;

  const $ = (id) => document.getElementById(id);
  const body = $('bahanBody');
  const empty = $('emptyBahan');
  const supBahan = $('supBahan');
  const dlg = $('dlgBahan');
  const err = (m) => { $('fErr').textContent = m || ''; $('fErr').hidden = !m; };
  const statusPill = (b) => b.stok <= 0 ? '<span class="pill pill--red">Habis</span>'
    : b.stok <= b.reorder ? '<span class="pill pill--red">Kritis</span>' : '<span class="pill pill--green">Aman</span>';

  function render() {
    body.innerHTML = bahan.map((b) => `
      <tr data-id="${b.id}">
        <td class="col-kode">${esc(b.nama)}</td>
        <td>${esc(b.satuan)}</td>
        <td>${angka(b.stok)}</td>
        <td>${angka(b.reorder)}</td>
        <td>${rupiah(b.harga)}</td>
        <td>${statusPill(b)}</td>
        <td><div class="actions">
          <button type="button" class="btn btn--outline" data-act="edit">Edit</button>
          <button type="button" class="btn btn--danger" data-act="hapus">Hapus</button>
        </div></td>
      </tr>`).join('');
    empty.hidden = bahan.length > 0;

    const dipilih = supBahan.value;
    supBahan.innerHTML = bahan.map((b) => `<option value="${b.id}">${esc(b.nama)} (${esc(b.satuan)})</option>`).join('');
    if (dipilih) supBahan.value = dipilih;
  }

  function bukaDialog(b) {
    editId = b ? b.id : null;
    err('');
    $('dlgBahanTitle').textContent = b ? 'Edit bahan' : 'Tambah bahan';
    $('fNama').value = b ? b.nama : '';
    $('fSatuan').value = b ? b.satuan : '';
    $('fStok').value = b ? b.stok : 0;
    $('fReorder').value = b ? b.reorder : 5;
    $('fHarga').value = b ? b.harga : 0;
    dlg.showModal();
    $('fNama').focus();
  }

  $('btnTambah').addEventListener('click', () => bukaDialog(null));
  $('btnBatal').addEventListener('click', () => dlg.close());

  body.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const id = Number(btn.closest('tr').dataset.id);
    const idx = bahan.findIndex((b) => b.id === id);
    if (idx < 0) return;
    if (btn.dataset.act === 'edit') return bukaDialog(bahan[idx]);
    if (btn.dataset.act === 'hapus') {
      const b = bahan[idx];
      if (b.dipakai) {
        return konfirmasi({ judul: `${b.nama} masih dipakai`, pesan: `Bahan ini terhubung ke ${b.dipakai} konfigurasi layanan. Hapus konfigurasinya di Master Layanan terlebih dahulu.`, info: true });
      }
      if (!(await konfirmasi({ judul: `Hapus ${b.nama}?`, pesan: 'Riwayat sirkulasi bahan ini ikut terhapus.' }))) return;
      const res = await call('/api/stok/' + id, { method: 'DELETE' });
      if (!res.ok) return toast(res.data.message, 'err');
      bahan.splice(idx, 1);
      render();
      toast(res.data.message);
    }
  });

  // Tambah: POST /api/stok · Edit: PATCH /api/stok/{id}
  $('formBahan').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
      nama_bahan: $('fNama').value.trim(),
      satuan: $('fSatuan').value.trim(),
      stok: Number($('fStok').value),
      reorder_point: Number($('fReorder').value),
      harga_satuan: Number($('fHarga').value || 0),
    };
    if (!data.nama_bahan || !data.satuan) return err('Nama bahan dan satuan wajib diisi.');
    if ([data.stok, data.reorder_point, data.harga_satuan].some((n) => Number.isNaN(n) || n < 0)) return err('Angka tidak boleh negatif.');

    const lama = editId ? bahan.find((b) => b.id === editId) : null;
    if (lama && lama.stok === data.stok) delete data.stok; // jangan buat log koreksi bila stok tidak berubah
    if (lama && data.stok !== undefined) data.keterangan = 'Koreksi stok oleh owner';

    const res = editId
      ? await call('/api/stok/' + editId, { method: 'PATCH', body: data })
      : await call('/api/stok', { method: 'POST', body: data });
    if (!res.ok) return err(res.data.message);

    const r = res.data.data;
    const baru = { id: Number(r.id), nama: r.nama_bahan, satuan: r.satuan, stok: Number(r.stok), reorder: Number(r.reorder_point), harga: Number(r.harga_satuan), status: r.status, dipakai: lama ? lama.dipakai : 0 };
    if (lama) Object.assign(lama, baru);
    else bahan.push(baru);
    dlg.close();
    render();
    toast(res.data.message);
  });

  // Pasokan masuk: POST /api/stok/{id}/restock {jumlah, tanggal}
  $('supplyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const b = bahan.find((x) => x.id === Number(supBahan.value));
    const jumlah = Number($('supJumlah').value);
    const tanggal = $('supTanggal').value;
    if (!b || !(jumlah > 0)) return;
    const res = await call(`/api/stok/${b.id}/restock`, { method: 'POST', body: { jumlah, tanggal, keterangan: 'Pasokan masuk (input owner)' } });
    if (!res.ok) { $('supplyMsg').textContent = res.data.message; return; }
    b.stok = Number(res.data.data.stok);
    const [y, m, d] = tanggal.split('-');
    $('supplyMsg').textContent = `Tercatat ${d}/${m}/${y}: +${angka(jumlah)} ${b.satuan} ${b.nama}. Stok sekarang ${angka(b.stok)} ${b.satuan}.`;
    render();
  });

  render();
})();

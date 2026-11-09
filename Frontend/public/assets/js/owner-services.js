(() => {
  const layanan = (window.LAYANAN || []).map((s) => ({ ...s, bahan: s.bahan.map((b) => [...b]) }));
  const opsi = window.BAHAN_OPSI || [];
  let nextId = layanan.reduce((m, s) => Math.max(m, s.id), 0) + 1;
  let editId = null;

  const $ = (id) => document.getElementById(id);
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const singkat = (n) => n.replace(/ HVS.*$/, '');
  const dlg = $('dlgSvc');

  const IKON_HAPUS = '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/></svg>';

  function toast(pesan) {
    const t = $('toast');
    t.textContent = pesan;
    t.hidden = false;
    clearTimeout(toast.id);
    toast.id = setTimeout(() => { t.hidden = true; }, 2600);
  }

  function render() {
    $('svcBody').innerHTML = layanan.map((s) => `
      <tr data-id="${s.id}">
        <td class="nm">${esc(s.nama)}</td>
        <td class="r nm">${rupiah(s.harga)}</td>
        <td>${esc(s.satuan)}</td>
        <td>${s.bahan.length ? s.bahan.map(([n, q]) => `${esc(singkat(n))} × ${q}`).join(' · ') : '-'}</td>
        <td><div class="qacts">
          <button type="button" class="qb qb--ghost" data-act="edit">Edit</button>
          <button type="button" class="qb qb--danger" data-act="hapus">Hapus</button>
        </div></td>
      </tr>`).join('');
  }

  // Baris bahan di popup
  function barisBahan(nama = opsi[0] || '', qty = 1) {
    const row = document.createElement('div');
    row.className = 'svrow';
    row.innerHTML = `
      <select aria-label="Bahan">${opsi.map((n) => `<option${n === nama ? ' selected' : ''}>${esc(n)}</option>`).join('')}</select>
      <input type="number" min="1" step="1" value="${qty}" aria-label="Jumlah terpotong">
      <button type="button" class="svdel" aria-label="Hapus bahan">${IKON_HAPUS}</button>`;
    return row;
  }
  $('btnBahan').addEventListener('click', () => $('svBahan').append(barisBahan()));
  $('svBahan').addEventListener('click', (e) => {
    const del = e.target.closest('.svdel');
    if (del) del.closest('.svrow').remove();
  });

  function bukaDialog(s) {
    editId = s ? s.id : null;
    $('svTitle').textContent = s ? 'Edit layanan' : 'Tambah layanan';
    $('svSimpan').textContent = s ? 'Simpan perubahan' : 'Simpan layanan';
    $('svNama').value = s ? s.nama : '';
    $('svSatuan').value = s ? s.satuan : 'lembar';
    $('svHarga').value = s ? s.harga : '';
    const baris = s ? s.bahan : [[opsi[0] || '', 1]];
    $('svBahan').replaceChildren(...baris.map(([n, q]) => barisBahan(n, q)));
    dlg.showModal();
    $('svNama').focus();
  }
  $('btnTambah').addEventListener('click', () => bukaDialog(null));

  $('svcBody').addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const idx = layanan.findIndex((s) => s.id === Number(btn.closest('tr').dataset.id));
    if (idx < 0) return;
    if (btn.dataset.act === 'edit') return bukaDialog(layanan[idx]);
    const ya = await window.EP.konfirmasiHapus({
      judul: 'Hapus data ini?',
      pesan: `Hapus layanan ${layanan[idx].nama}? Pesanan lama tetap tersimpan.`,
    });
    if (ya) { layanan.splice(idx, 1); render(); toast('Layanan dihapus.'); }
  });

  $('formSvc').addEventListener('submit', (e) => {
    e.preventDefault();
    const bahan = [...$('svBahan').querySelectorAll('.svrow')].map((r) => [
      r.querySelector('select').value,
      Math.max(1, parseInt(r.querySelector('input').value, 10) || 1),
    ]);
    const data = {
      nama: $('svNama').value.trim(),
      harga: Math.max(0, parseInt($('svHarga').value, 10) || 0),
      satuan: $('svSatuan').value,
      bahan,
    };
    if (!data.nama) return;
    if (editId) Object.assign(layanan.find((s) => s.id === editId), data);
    else layanan.push({ id: nextId++, ...data });
    dlg.close();
    render();
    toast(editId ? 'Layanan diperbarui.' : 'Layanan ditambahkan.');
  });

  document.querySelectorAll('dialog [data-tutup]').forEach((x) => x.addEventListener('click', () => x.closest('dialog').close()));

  render();
})();
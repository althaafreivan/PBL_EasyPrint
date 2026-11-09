(() => {
  const bahan = (window.BAHAN || []).map((b) => ({ ...b }));
  const dipakai = new Set(window.DIPAKAI || []);
  let nextId = bahan.reduce((m, b) => Math.max(m, b.id), 0) + 1;
  let editId = null;

  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const kritis = (b) => b.stok <= b.reorder;
  const persen = (b) => (b.reorder > 0 ? Math.max(0, Math.min(100, Math.round((b.stok / (b.reorder * 2)) * 100))) : 100);

  function toast(pesan) {
    const t = $('toast');
    t.textContent = pesan;
    t.hidden = false;
    clearTimeout(toast.id);
    toast.id = setTimeout(() => { t.hidden = true; }, 2600);
  }

  function render() {
    $('bahanBody').innerHTML = bahan.map((b) => `
      <tr data-id="${b.id}">
        <td class="nm">${esc(b.nama)}</td>
        <td>${esc(b.satuan)}</td>
        <td class="r nm">${b.stok}</td>
        <td class="r">${b.reorder}</td>
        <td><span class="lvl lvl--${kritis(b) ? 'bad' : 'ok'}" role="img" aria-label="Level stok ${persen(b)} persen"><i style="width:${persen(b)}%"></i></span></td>
        <td><span class="pill ${kritis(b) ? 'pill--red' : 'pill--green'}">${kritis(b) ? 'Kritis' : 'Aman'}</span></td>
        <td><div class="qacts">
          <button type="button" class="qb qb--ghost" data-act="edit">Edit</button>
          <button type="button" class="qb qb--danger" data-act="hapus">Hapus</button>
        </div></td>
      </tr>`).join('');

    const k = bahan.filter(kritis);
    $('alertBar').hidden = k.length === 0;
    $('alertJudul').textContent = `Peringatan stok kritis (${k.length} bahan)`;
    $('alertIsi').textContent = k.map((b) => `${b.nama} ${b.stok} / ${b.reorder} ${b.satuan}`).join(' - ') + ' (stok <= reorder point). Segera lakukan restock.';

    const opsi = bahan.map((b) => `<option value="${b.id}">${esc(b.nama)}</option>`).join('');
    [$('supBahan'), $('adBahan')].forEach((s) => { const v = s.value; s.innerHTML = opsi; if (v) s.value = v; });
    hitungAdj();
  }

  // ---------- Tambah / Edit ----------
  const dlg = $('dlgBahan');
  function bukaBahan(b) {
    editId = b ? b.id : null;
    $('bhTitle').textContent = b ? 'Edit bahan baku' : 'Tambah bahan baku';
    $('bhSimpan').textContent = b ? 'Simpan perubahan' : 'Simpan bahan';
    $('bhNama').value = b ? b.nama : '';
    $('bhSatuan').value = b ? b.satuan : 'rim';
    $('bhStok').value = b ? b.stok : '';
    $('bhReorder').value = b ? b.reorder : '';
    dlg.showModal();
    $('bhNama').focus();
  }
  $('btnTambah').addEventListener('click', () => bukaBahan(null));

  $('formBahan').addEventListener('submit', (e) => {
    e.preventDefault();
    const data = {
      nama: $('bhNama').value.trim(),
      satuan: $('bhSatuan').value,
      stok: Math.max(0, parseInt($('bhStok').value, 10) || 0),
      reorder: Math.max(0, parseInt($('bhReorder').value, 10) || 0),
    };
    if (!data.nama) return;
    if (editId) Object.assign(bahan.find((b) => b.id === editId), data);
    else bahan.push({ id: nextId++, singkat: data.nama, ...data });
    dlg.close();
    render();
    toast(editId ? 'Bahan diperbarui.' : 'Bahan ditambahkan.');
  });

  // ---------- Edit / Hapus ----------
  $('bahanBody').addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const idx = bahan.findIndex((b) => b.id === Number(btn.closest('tr').dataset.id));
    if (idx < 0) return;
    const b = bahan[idx];
    if (btn.dataset.act === 'edit') return bukaBahan(b);

    if (dipakai.has(b.nama)) {
      await window.EP.konfirmasiHapus({ judul: 'Bahan tidak bisa dihapus', pesan: `${b.nama} masih dipakai di komposisi layanan.`, info: true });
      return;
    }
    const ya = await window.EP.konfirmasiHapus({
      judul: 'Hapus data ini?',
      pesan: `Hapus ${b.nama}? Bahan yang masih dipakai di komposisi layanan tidak bisa dihapus.`,
    });
    if (ya) { bahan.splice(idx, 1); render(); toast('Bahan dihapus.'); }
  });

  // ---------- Pasokan masuk ----------
  $('supplyForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const b = bahan.find((x) => x.id === Number($('supBahan').value));
    const n = parseInt($('supJumlah').value, 10);
    if (!b || !(n > 0)) return;
    b.stok += n;
    const [y, m, d] = $('supTanggal').value.split('-');
    $('supplyMsg').textContent = `Tercatat ${d}/${m}/${y}: +${n} ${b.satuan} ${b.nama}. Stok sekarang ${b.stok} ${b.satuan}.`;
    render();
  });

  // ---------- Penyesuaian stok ----------
  const dlgAdj = $('dlgAdj');
  function hitungAdj() {
    const b = bahan.find((x) => x.id === Number($('adBahan').value));
    if (!b) { $('adHasil').value = ''; return; }
    const n = parseInt($('adJumlah').value, 10) || 0;
    $('adHasil').value = `${b.stok} → ${Math.max(0, b.stok - n)} ${b.satuan}`;
  }
  $('btnAdj').addEventListener('click', () => {
    $('adJumlah').value = '';
    $('adAlasan').value = '';
    hitungAdj();
    dlgAdj.showModal();
  });
  $('adBahan').addEventListener('change', hitungAdj);
  $('adJumlah').addEventListener('input', hitungAdj);
  $('formAdj').addEventListener('submit', (e) => {
    e.preventDefault();
    const b = bahan.find((x) => x.id === Number($('adBahan').value));
    const n = parseInt($('adJumlah').value, 10);
    if (!b || !(n > 0) || !$('adAlasan').value.trim()) return;
    b.stok = Math.max(0, b.stok - n);
    dlgAdj.close();
    render();
    toast(`Penyesuaian tercatat: −${n} ${b.satuan} ${b.nama}.`);
  });

  document.querySelectorAll('dialog [data-tutup]').forEach((x) => x.addEventListener('click', () => x.closest('dialog').close()));

  render();
})();
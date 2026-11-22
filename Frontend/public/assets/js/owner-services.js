(() => {
  const layanan = (window.LAYANAN || []).map((s) => ({ ...s, bahan: [...s.bahan] }));
  const opsiBahan = window.BAHAN_OPSI || [];
  const { esc, rupiah, angka, call, toast, konfirmasi } = window.EPAPI;
  let editId = null;
  let editCfg = null;

  const $ = (id) => document.getElementById(id);
  const KAT = { cetak: 'Cetak', fotokopi: 'Fotokopi', finishing: 'Finishing' };
  const dlg = $('dlgSvc');
  const dlgCfg = $('dlgCfg');
  const errSvc = (m) => { $('sErr').textContent = m || ''; $('sErr').hidden = !m; };
  const errCfg = (m) => { $('cErr').textContent = m || ''; $('cErr').hidden = !m; };

  function render() {
    $('svcBody').innerHTML = layanan.map((s) => `
      <tr data-id="${s.id}" class="${s.aktif ? '' : 'is-off'}">
        <td class="col-kode">${esc(s.nama)}${s.aktif ? '' : ' <span class="pill pill--soft">Nonaktif</span>'}${s.deskripsi ? `<small class="aq-sub">${esc(s.deskripsi)}</small>` : ''}</td>
        <td>${KAT[s.kategori] || esc(s.kategori)}</td>
        <td>${rupiah(s.harga)}</td>
        <td>${esc(s.satuan)}</td>
        <td>${s.bahan.length ? s.bahan.map((b) => `${esc(b.nama)} × ${angka(b.jumlah, 4)}`).join('<br>') : '-'}</td>
        <td><div class="actions">
          <button type="button" class="btn btn--outline" data-act="edit">Edit</button>
          <button type="button" class="btn btn--danger" data-act="hapus">${s.dipakai ? 'Nonaktifkan' : 'Hapus'}</button>
        </div></td>
      </tr>`).join('');
    $('emptySvc').hidden = layanan.length > 0;

    const cfg = layanan.flatMap((s) => s.bahan.map((b) => ({ ...b, layanan: s })));
    $('cfgBody').innerHTML = cfg.map((c) => `
      <tr data-cfg="${c.id}">
        <td class="col-kode">${esc(c.layanan.nama)}</td>
        <td>${esc(c.nama)}</td>
        <td>${angka(c.jumlah, 4)} ${esc(c.satuan)} / ${esc(c.layanan.satuan)}</td>
        <td><div class="actions">
          <button type="button" class="btn btn--outline" data-cfg-act="edit">Edit</button>
          <button type="button" class="btn btn--danger" data-cfg-act="hapus">Hapus</button>
        </div></td>
      </tr>`).join('') || '<tr><td colspan="4" class="table__empty">Belum ada konfigurasi bahan.</td></tr>';
  }

  // ---------------- Layanan: POST /api/layanan · PATCH /api/layanan/{id} · DELETE /api/layanan/{id}
  function bukaDialog(s) {
    editId = s ? s.id : null;
    errSvc('');
    $('dlgSvcTitle').textContent = s ? 'Edit layanan' : 'Tambah layanan';
    $('sNama').value = s ? s.nama : '';
    $('sHarga').value = s ? s.harga : 0;
    $('sSatuan').value = s ? s.satuan : 'lembar';
    $('sKategori').value = s ? s.kategori : 'cetak';
    $('sAktif').value = s && !s.aktif ? '0' : '1';
    $('sDesk').value = s ? s.deskripsi : '';
    dlg.showModal();
    $('sNama').focus();
  }

  $('btnTambah').addEventListener('click', () => bukaDialog(null));
  $('btnBatalSvc').addEventListener('click', () => dlg.close());

  $('svcBody').addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-act]');
    const tr = btn && btn.closest('tr[data-id]');
    if (!tr) return;
    const s = layanan.find((x) => x.id === Number(tr.dataset.id));
    if (!s) return;
    if (btn.dataset.act === 'edit') return bukaDialog(s);
    const ya = await konfirmasi(s.dipakai
      ? { judul: `Nonaktifkan ${s.nama}?`, pesan: `Layanan sudah dipakai ${s.dipakai} item pesanan, jadi tidak dihapus agar laporan tetap utuh. Layanan disembunyikan dari katalog.`, tombol: 'Ya, nonaktifkan' }
      : { judul: `Hapus layanan ${s.nama}?`, pesan: 'Konfigurasi bahan untuk layanan ini ikut terhapus.' });
    if (!ya) return;
    const res = await call('/api/layanan/' + s.id, { method: 'DELETE' });
    if (!res.ok) return toast(res.data.message, 'err');
    if (res.data.nonaktif) s.aktif = false;
    else layanan.splice(layanan.indexOf(s), 1);
    render();
    toast(res.data.message);
  });

  $('formSvc').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
      nama_layanan: $('sNama').value.trim(),
      harga: Number($('sHarga').value),
      satuan: $('sSatuan').value,
      kategori: $('sKategori').value,
      deskripsi: $('sDesk').value.trim(),
      aktif: $('sAktif').value === '1',
    };
    if (!data.nama_layanan) return errSvc('Nama jasa wajib diisi.');
    if (Number.isNaN(data.harga) || data.harga < 0) return errSvc('Harga tidak boleh negatif.');
    const res = editId
      ? await call('/api/layanan/' + editId, { method: 'PATCH', body: data })
      : await call('/api/layanan', { method: 'POST', body: data });
    if (!res.ok) return errSvc(res.data.message);
    const r = res.data.data;
    const baru = { id: Number(r.id), nama: r.nama_layanan, harga: Number(r.harga), satuan: r.satuan, kategori: r.kategori, deskripsi: r.deskripsi || '', aktif: !!r.aktif };
    if (editId) Object.assign(layanan.find((s) => s.id === editId), baru);
    else layanan.push({ ...baru, dipakai: 0, bahan: [] });
    dlg.close();
    render();
    toast(res.data.message);
  });

  // ---------------- Bill of Materials: /api/bahan-layanan
  function bukaCfg(c) {
    editCfg = c || null;
    errCfg('');
    $('dlgCfgTitle').textContent = c ? 'Edit konfigurasi bahan' : 'Tambah konfigurasi bahan';
    $('cLayanan').innerHTML = layanan.map((s) => `<option value="${s.id}">${esc(s.nama)} (per ${esc(s.satuan)})</option>`).join('');
    $('cBahan').innerHTML = opsiBahan.map((b) => `<option value="${b.id}">${esc(b.nama)} (${esc(b.satuan)})</option>`).join('');
    if (c) { $('cLayanan').value = c.layanan.id; $('cBahan').value = c.bahan_id; }
    $('cLayanan').disabled = $('cBahan').disabled = !!c;
    $('cJumlah').value = c ? c.jumlah : 1;
    dlgCfg.showModal();
    $('cJumlah').focus();
  }
  const cariCfg = (id) => {
    for (const s of layanan) {
      const b = s.bahan.find((x) => x.id === id);
      if (b) return { ...b, layanan: s };
    }
    return null;
  };

  $('btnTambahCfg').addEventListener('click', () => bukaCfg(null));
  $('btnBatalCfg').addEventListener('click', () => dlgCfg.close());

  $('cfgBody').addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-cfg-act]');
    const tr = btn && btn.closest('tr[data-cfg]');
    if (!tr) return;
    const c = cariCfg(Number(tr.dataset.cfg));
    if (!c) return;
    if (btn.dataset.cfgAct === 'edit') return bukaCfg(c);
    if (!(await konfirmasi({ judul: 'Hapus konfigurasi?', pesan: `${c.layanan.nama} tidak lagi memotong stok ${c.nama}.` }))) return;
    const res = await call('/api/bahan-layanan/' + c.id, { method: 'DELETE' });
    if (!res.ok) return toast(res.data.message, 'err');
    c.layanan.bahan = c.layanan.bahan.filter((b) => b.id !== c.id);
    render();
    toast(res.data.message);
  });

  $('formCfg').addEventListener('submit', async (e) => {
    e.preventDefault();
    const jumlah = Number($('cJumlah').value);
    if (!(jumlah > 0)) return errCfg('Jumlah pemakaian harus lebih dari 0.');
    const res = editCfg
      ? await call('/api/bahan-layanan/' + editCfg.id, { method: 'PATCH', body: { jumlah_kebutuhan: jumlah } })
      : await call('/api/bahan-layanan', { method: 'POST', body: { layanan_id: Number($('cLayanan').value), bahan_baku_id: Number($('cBahan').value), jumlah_kebutuhan: jumlah } });
    if (!res.ok) return errCfg(res.data.message);
    const s = layanan.find((x) => x.id === Number(res.data.data.layanan_id));
    if (editCfg) {
      s.bahan.find((b) => b.id === editCfg.id).jumlah = Number(res.data.data.jumlah_kebutuhan);
    } else {
      const b = opsiBahan.find((x) => x.id === Number(res.data.data.bahan_baku_id));
      s.bahan.push({ id: Number(res.data.data.id), bahan_id: b.id, nama: b.nama, satuan: b.satuan, jumlah: Number(res.data.data.jumlah_kebutuhan) });
    }
    dlgCfg.close();
    render();
    toast(res.data.message);
  });

  render();
})();

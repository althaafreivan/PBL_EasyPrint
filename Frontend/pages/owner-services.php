<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['owner']);
$res = api_get('/api/layanan?semua=1');
$resBahan = api_get('/api/stok');
$layanan = array_map(fn($l) => [
  'id' => (int) $l['id'], 'nama' => $l['nama_layanan'], 'harga' => (float) $l['harga'], 'satuan' => $l['satuan'],
  'kategori' => $l['kategori'], 'deskripsi' => (string) $l['deskripsi'], 'aktif' => (bool) $l['aktif'], 'dipakai' => (int) $l['dipakai'],
  'bahan' => array_map(fn($b) => ['id' => (int) $b['id'], 'bahan_id' => (int) $b['bahan_baku_id'], 'nama' => $b['nama_bahan'], 'satuan' => $b['satuan'], 'jumlah' => (float) $b['jumlah_kebutuhan']], $l['bahan']),
], $res['data']['data'] ?? []);
$bahan = array_map(fn($b) => ['id' => (int) $b['id'], 'nama' => $b['nama_bahan'], 'satuan' => $b['satuan']], $resBahan['data']['data'] ?? []);
$active = 'layanan'; $role = 'owner'; $pageTitle = 'Master Layanan & Harga';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Master Layanan & Harga'; $subtitle = 'Kelola katalog jasa, harga dasar, dan pemakaian bahan.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>
    <div class="sec-head">
      <h2 class="sec-title">Layanan</h2>
      <button type="button" class="btn-main" id="btnTambah">+ Tambah Layanan</button>
    </div>

    <section class="card table-card table-card--svc" aria-label="Daftar layanan">
      <table class="table table--plain table--tall">
        <colgroup><col style="width:27%"><col style="width:13%"><col style="width:13%"><col style="width:9%"><col style="width:22%"><col></colgroup>
        <thead>
          <tr>
            <th scope="col">Nama jasa</th>
            <th scope="col">Kategori</th>
            <th scope="col">Harga dasar</th>
            <th scope="col">Satuan</th>
            <th scope="col">Pemakaian bahan</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody id="svcBody"></tbody>
      </table>
      <p class="table__empty" id="emptySvc" hidden>Belum ada layanan.</p>
    </section>

    <div class="sec-head">
      <h2 class="sec-title">Konfigurasi bahan per layanan <small class="sec-hint">(Bill of Materials — dasar potong stok otomatis)</small></h2>
      <button type="button" class="btn-main" id="btnTambahCfg">+ Tambah Konfigurasi</button>
    </div>
    <section class="card table-card table-card--cfg" aria-label="Konfigurasi bahan per layanan">
      <table class="table table--plain table--short">
        <colgroup><col style="width:27.7%"><col style="width:25.3%"><col style="width:27%"><col></colgroup>
        <thead>
          <tr>
            <th scope="col">Layanan</th>
            <th scope="col">Bahan baku</th>
            <th scope="col">Pemakaian per satuan layanan</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody id="cfgBody"></tbody>
      </table>
    </section>

    <dialog class="dlg" id="dlgSvc" aria-labelledby="dlgSvcTitle">
      <form id="formSvc">
        <h2 id="dlgSvcTitle">Tambah layanan</h2>
        <div class="fld"><label for="sNama">Nama jasa</label><input type="text" id="sNama" maxlength="100" required></div>
        <div class="fld-row">
          <div class="fld"><label for="sHarga">Harga dasar (Rp)</label><input type="number" id="sHarga" min="0" step="50" value="0" required></div>
          <div class="fld"><label for="sSatuan">Satuan</label>
            <select id="sSatuan">
              <option value="lembar">lembar</option>
              <option value="buku">buku</option>
              <option value="eksemplar">eksemplar</option>
              <option value="paket">paket</option>
              <option value="pcs">pcs</option>
            </select>
          </div>
        </div>
        <div class="fld-row">
          <div class="fld"><label for="sKategori">Kategori</label>
            <select id="sKategori">
              <option value="cetak">Cetak / print</option>
              <option value="fotokopi">Fotokopi</option>
              <option value="finishing">Finishing / jilid</option>
            </select>
          </div>
          <div class="fld"><label for="sAktif">Status</label>
            <select id="sAktif"><option value="1">Aktif (tampil di katalog)</option><option value="0">Nonaktif</option></select>
          </div>
        </div>
        <div class="fld"><label for="sDesk">Deskripsi</label><input type="text" id="sDesk" maxlength="500" placeholder="Contoh: Cetak hitam putih kertas HVS A4 70gr"></div>
        <p class="field-error" id="sErr" role="alert" hidden></p>
        <div class="dlg__actions">
          <button type="button" class="btn-ghost" id="btnBatalSvc">Batal</button>
          <button type="submit" class="btn-main">Simpan</button>
        </div>
      </form>
    </dialog>

    <dialog class="dlg" id="dlgCfg" aria-labelledby="dlgCfgTitle">
      <form id="formCfg">
        <h2 id="dlgCfgTitle">Tambah konfigurasi bahan</h2>
        <div class="fld"><label for="cLayanan">Layanan</label><select id="cLayanan"></select></div>
        <div class="fld"><label for="cBahan">Bahan baku</label><select id="cBahan"></select></div>
        <div class="fld"><label for="cJumlah" id="cJumlahLabel">Jumlah pemakaian per satuan layanan</label><input type="number" id="cJumlah" min="0.0001" step="any" value="1" required></div>
        <p class="sec-hint">Contoh: 1 lembar print memakai 0,002 rim kertas (1 rim = 500 lembar).</p>
        <p class="field-error" id="cErr" role="alert" hidden></p>
        <div class="dlg__actions">
          <button type="button" class="btn-ghost" id="btnBatalCfg">Batal</button>
          <button type="submit" class="btn-main">Simpan</button>
        </div>
      </form>
    </dialog>
  </div>

  <script>
    window.LAYANAN = <?= json_encode($layanan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    window.BAHAN_OPSI = <?= json_encode($bahan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  </script>
  <script src="assets/js/modal.js"></script>
  <script src="assets/js/owner-services.js"></script>
</body>
</html>
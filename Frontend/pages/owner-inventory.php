<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['owner']);
$res = api_get('/api/stok');
$bahan = array_map(fn($b) => [
  'id' => (int) $b['id'], 'nama' => $b['nama_bahan'], 'satuan' => $b['satuan'], 'stok' => (float) $b['stok'],
  'reorder' => (float) $b['reorder_point'], 'harga' => (float) $b['harga_satuan'], 'status' => $b['status'],
  'dipakai' => (int) $b['dipakai_layanan'],
], $res['data']['data'] ?? []);
$active = 'bahan'; $role = 'owner'; $pageTitle = 'Manajemen Inventaris';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Manajemen Inventaris'; $subtitle = 'Atur master bahan dan batas reorder point.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>
    <div class="sec-head">
      <h2 class="sec-title">Master bahan baku</h2>
      <button type="button" class="btn-main" id="btnTambah">+ Tambah Bahan</button>
    </div>

    <section class="card table-card table-card--inv" aria-label="Master bahan baku">
      <table class="table table--plain">
        <colgroup><col style="width:26%"><col style="width:10%"><col style="width:11%"><col style="width:14%"><col style="width:13%"><col style="width:10%"><col></colgroup>
        <thead>
          <tr>
            <th scope="col">Nama bahan</th>
            <th scope="col">Satuan</th>
            <th scope="col">Stok</th>
            <th scope="col">Reorder point</th>
            <th scope="col">Harga satuan</th>
            <th scope="col">Status</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody id="bahanBody"></tbody>
      </table>
      <p class="table__empty" id="emptyBahan" hidden>Belum ada bahan baku.</p>
    </section>

    <h2 class="sec-title">Pasokan masuk</h2>
    <form class="card supply" id="supplyForm">
      <div class="fld">
        <label for="supBahan">Bahan</label>
        <select id="supBahan" name="bahan" required></select>
      </div>
      <div class="fld">
        <label for="supJumlah">Jumlah</label>
        <input type="number" id="supJumlah" name="jumlah" min="0.01" step="any" value="10" required>
      </div>
      <div class="fld">
        <label for="supTanggal">Tanggal</label>
        <input type="date" id="supTanggal" name="tanggal" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
      </div>
      <button type="submit" class="btn-main btn-main--lg">Simpan Pasokan</button>

      <div class="supply__note">
        <span class="label">Catatan</span>
        <p>Pasokan baru menambah stok bahan terkait dan tercatat di laporan sirkulasi bahan.</p>
        <p class="supply__status" id="supplyMsg" role="status"></p>
      </div>
    </form>

    <dialog class="dlg" id="dlgBahan" aria-labelledby="dlgBahanTitle">
      <form id="formBahan">
        <h2 id="dlgBahanTitle">Tambah bahan</h2>
        <div class="fld"><label for="fNama">Nama bahan</label><input type="text" id="fNama" required></div>
        <div class="fld"><label for="fSatuan">Satuan</label><input type="text" id="fSatuan" placeholder="rim, botol, pak" required></div>
        <div class="fld-row">
          <div class="fld"><label for="fStok">Stok</label><input type="number" id="fStok" min="0" step="any" value="0" required></div>
          <div class="fld"><label for="fReorder">Reorder point</label><input type="number" id="fReorder" min="0" step="any" value="5" required></div>
        </div>
        <div class="fld"><label for="fHarga">Harga satuan (Rp)</label><input type="number" id="fHarga" min="0" step="100" value="0"></div>
        <p class="field-error" id="fErr" role="alert" hidden></p>
        <div class="dlg__actions">
          <button type="button" class="btn-ghost" id="btnBatal">Batal</button>
          <button type="submit" class="btn-main">Simpan</button>
        </div>
      </form>
    </dialog>
  </div>

  <script>window.BAHAN = <?= json_encode($bahan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
  <script src="assets/js/modal.js"></script>
  <script src="assets/js/owner-inventory.js"></script>
</body>
</html>
<?php
$data  = require __DIR__ . '/../includes/mock/orders.php';
$bahan = array_column(require __DIR__ . '/../includes/mock/inventory.php', 'nama');
$active = 'antrean'; $role = 'admin'; $pageTitle = 'Antrean Pesanan';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';

$tab = ['semua' => 'Semua', 'pending' => 'Pending', 'tertunda' => 'Tertunda', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
$x = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>';
?>
  <div class="admin__main">
    <?php $title = 'Antrean Pesanan'; $subtitle = 'Periksa berkas & ketersediaan bahan, lalu ubah status pesanan.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <section class="card qcard" aria-label="Antrean pesanan">
      <div class="qtools">
        <div class="qtabs" role="group" aria-label="Filter status">
          <?php foreach ($tab as $key => $label): ?>
            <button type="button" class="qtab<?= $key === 'semua' ? ' is-active' : '' ?>" data-filter="<?= $key ?>" aria-pressed="<?= $key === 'semua' ? 'true' : 'false' ?>"><?= $label ?> <span id="cnt-<?= $key ?>"><?= $data['hitung'][$key] ?></span></button>
          <?php endforeach; ?>
        </div>
        <label class="qsearch">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-3.5-3.5"/></svg>
          <input type="search" id="cari" placeholder="Cari ID / pelanggan" autocomplete="off" aria-label="Cari ID atau pelanggan">
        </label>
      </div>

      <div class="qscroll">
        <table class="qt">
          <thead>
            <tr>
              <th scope="col">Pesanan</th><th scope="col">Layanan</th><th scope="col">Berkas</th>
              <th scope="col" class="r">Total</th><th scope="col">Status</th><th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody id="qBody"></tbody>
        </table>
      </div>
      <p class="qempty" id="qEmpty" role="status" hidden>Tidak ada pesanan yang cocok.</p>

      <div class="qfoot">
        <span id="qInfo"></span>
        <span>Stok bahan dipotong otomatis saat status menjadi <strong>Diproses</strong>.</span>
      </div>
    </section>

    <!-- Popup: Tunda -->
    <dialog class="mdl" id="dlgTunda" aria-labelledby="tdTitle">
      <form class="mdl__form" id="formTunda">
        <div class="mdl__head">
          <div>
            <h2 id="tdTitle">Tunda pesanan</h2>
            <p class="mdl__sub">Gunakan bila stok bahan tidak mencukupi pesanan. Status menjadi “Tertunda – Stok Tidak Tersedia”.</p>
          </div>
          <button type="button" class="mdl__x" data-tutup aria-label="Tutup"><?= $x ?></button>
        </div>
        <p class="mdl__alert" id="tdAlert"></p>
        <div class="mdl__row">
          <div><label class="lbl" for="tdBahan">Bahan yang tidak tersedia</label><select id="tdBahan"></select></div>
          <div><label class="lbl" for="tdTgl">Perkiraan stok tersedia</label><input type="date" id="tdTgl" value="2026-10-10" required></div>
        </div>
        <div><label class="lbl" for="tdPesan">Pesan untuk pelanggan</label><textarea id="tdPesan" required></textarea></div>
        <p class="mdl__hint">Pesan tampil di halaman tracking pelanggan. Stok belum dipotong selama pesanan tertunda.</p>
        <div class="mdl__actions">
          <button type="button" class="mdl__btn" data-tutup>Batal</button>
          <button type="submit" class="mdl__btn mdl__btn--amber">Tunda pesanan</button>
        </div>
      </form>
    </dialog>

    <!-- Popup: Tolak -->
    <dialog class="mdl" id="dlgTolak" aria-labelledby="tlTitle">
      <form class="mdl__form" id="formTolak">
        <div class="mdl__head">
          <div>
            <h2 id="tlTitle">Tolak pesanan</h2>
            <p class="mdl__sub">Status menjadi Ditolak dan alasan penolakan tampil di halaman tracking pelanggan.</p>
          </div>
          <button type="button" class="mdl__x" data-tutup aria-label="Tutup"><?= $x ?></button>
        </div>
        <fieldset class="mdl__opts">
          <legend class="sr-only">Alasan penolakan</legend>
          <label class="mdl__opt"><input type="radio" name="alasan" value="buram" checked> Berkas tidak terbaca / buram</label>
          <label class="mdl__opt"><input type="radio" name="alasan" value="format"> Format berkas tidak didukung</label>
          <label class="mdl__opt"><input type="radio" name="alasan" value="spek"> Spesifikasi tidak sesuai berkas</label>
          <label class="mdl__opt"><input type="radio" name="alasan" value="permanen"> Tidak dapat dipenuhi (permanen)</label>
          <label class="mdl__opt"><input type="radio" name="alasan" value="lain"> Lainnya</label>
        </fieldset>
        <div><label class="lbl" for="tlCatatan">Catatan untuk pelanggan</label><textarea id="tlCatatan" required></textarea></div>
        <p class="mdl__alert mdl__alert--warn">Stok bahan habis sementara? Gunakan <strong>Tunda</strong>, bukan Tolak.</p>
        <div class="mdl__actions">
          <button type="button" class="mdl__btn" data-tutup>Batal</button>
          <button type="submit" class="mdl__btn mdl__btn--danger">Tolak pesanan</button>
        </div>
      </form>
    </dialog>

    <div class="toast" id="toast" role="status" hidden></div>
  </div>

  <script>
    window.ORDERS = <?= json_encode($data['orders'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    window.HITUNG = <?= json_encode($data['hitung'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    window.BAHAN_OPSI = <?= json_encode($bahan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  </script>
  <script src="assets/js/admin-orders.js"></script>
</body>
</html>
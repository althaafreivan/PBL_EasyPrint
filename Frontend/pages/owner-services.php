<?php
$layanan = require __DIR__ . '/../includes/mock/services.php';
$bahan   = array_column(require __DIR__ . '/../includes/mock/inventory.php', 'nama');
$active = 'layanan'; $role = 'owner'; $pageTitle = 'Master Layanan & Harga';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
$x = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>';
$plus = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';
?>
  <div class="admin__main">
    <?php $title = 'Master Layanan & Harga'; $subtitle = 'Atur tarif dasar layanan dan komposisi bahan yang terpotong per 1 unit layanan.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <section class="card stcard invcard" aria-label="Layanan dan komposisi bahan">
      <div class="invcard__head">
        <h2>Layanan &amp; komposisi bahan</h2>
        <button type="button" class="btn-round btn-round--sm" id="btnTambah"><?= $plus ?> Tambah Layanan</button>
      </div>
      <div class="qscroll">
        <table class="dtable dtable--svc">
          <thead>
            <tr>
              <th scope="col">Nama layanan</th><th scope="col" class="r">Harga dasar</th><th scope="col">Satuan</th>
              <th scope="col">Bahan terpotong per 1 unit</th><th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody id="svcBody"></tbody>
        </table>
      </div>
      <p class="svcnote">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg>
        Tinta dicatat saat botol baru dipasang, karena pemakaian per lembar terlalu kecil untuk dipotong per pesanan.
      </p>
    </section>

    <!-- Popup: Tambah / Edit layanan -->
    <dialog class="mdl mdl--wide" id="dlgSvc" aria-labelledby="svTitle">
      <form class="mdl__form" id="formSvc">
        <div class="mdl__head">
          <div>
            <h2 id="svTitle">Tambah layanan</h2>
            <p class="mdl__sub">Atur nama jasa, harga dasar, dan bahan yang terpotong per 1 unit layanan.</p>
          </div>
          <button type="button" class="mdl__x" data-tutup aria-label="Tutup"><?= $x ?></button>
        </div>
        <div class="mdl__row">
          <div><label class="lbl" for="svNama">Nama layanan</label><input type="text" id="svNama" placeholder="Contoh: Print Dokumen A3 Warna" required></div>
          <div><label class="lbl" for="svSatuan">Satuan</label>
            <select id="svSatuan"><option>lembar</option><option>buku</option><option>eksemplar</option><option>paket</option></select>
          </div>
        </div>
        <div class="mdl__row">
          <div><label class="lbl" for="svHarga">Harga dasar</label><input type="number" id="svHarga" min="0" step="100" placeholder="Rp 0" required></div>
        </div>
        <div>
          <span class="lbl">Bahan terpotong per 1 unit (bahan_layanan)</span>
          <div id="svBahan" class="svrows"></div>
          <button type="button" class="btn-ghost2 btn-ghost2--sm" id="btnBahan"><?= $plus ?> Tambah bahan</button>
        </div>
        <div class="mdl__actions">
          <button type="button" class="mdl__btn" data-tutup>Batal</button>
          <button type="submit" class="mdl__btn mdl__btn--primary" id="svSimpan">Simpan layanan</button>
        </div>
      </form>
    </dialog>

    <div class="toast" id="toast" role="status" hidden></div>
  </div>

  <script>
    window.LAYANAN = <?= json_encode($layanan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    window.BAHAN_OPSI = <?= json_encode($bahan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  </script>
  <script src="assets/js/modal.js"></script>
  <script src="assets/js/owner-services.js"></script>
</body>
</html>
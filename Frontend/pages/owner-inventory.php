<?php
$bahan = require __DIR__ . '/../includes/mock/inventory.php';
$layanan = require __DIR__ . '/../includes/mock/services.php';
$dipakai = [];
foreach ($layanan as $l) { foreach ($l['bahan'] as [$nama]) { $dipakai[$nama] = true; } }
$active = 'bahan'; $role = 'owner'; $pageTitle = 'Manajemen Bahan Baku';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
$x = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>';
$plus = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';
?>
  <div class="admin__main">
    <?php $title = 'Manajemen Bahan Baku'; $subtitle = 'Kelola master bahan, batas minimum (reorder point), pasokan masuk, dan penyesuaian stok.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <div class="alertbar" id="alertBar" role="alert" hidden>
      <span class="alertbar__ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
      <div><strong id="alertJudul"></strong><p id="alertIsi"></p></div>
    </div>

    <section class="card stcard invcard" aria-label="Master bahan baku">
      <div class="invcard__head">
        <h2>Master bahan baku</h2>
        <button type="button" class="btn-round btn-round--sm" id="btnTambah"><?= $plus ?> Tambah Bahan</button>
      </div>
      <div class="qscroll">
        <table class="dtable">
          <thead>
            <tr>
              <th scope="col">Nama bahan</th><th scope="col">Satuan</th><th scope="col" class="r">Stok</th>
              <th scope="col" class="r">Reorder point</th><th scope="col">Level</th><th scope="col">Status</th><th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody id="bahanBody"></tbody>
        </table>
      </div>
    </section>

    <div class="invgrid">
      <form class="card stcard" id="supplyForm">
        <h2>Pasokan masuk dari supplier</h2>
        <div class="restock restock--row">
          <div class="fld"><label for="supBahan">Bahan</label><select id="supBahan"></select></div>
          <div class="fld"><label for="supJumlah">Jumlah masuk</label><input type="number" id="supJumlah" min="1" value="10" required></div>
          <div class="fld"><label for="supTanggal">Tanggal terima</label><input type="date" id="supTanggal" value="2026-10-10" required></div>
        </div>
        <button type="submit" class="btn-round btn-round--sm">Simpan pasokan</button>
        <p class="restock__msg" id="supplyMsg" role="status"></p>
      </form>

      <section class="card stcard" aria-label="Penyesuaian stok">
        <h2>Penyesuaian Stok (Stock Adjustment)</h2>
        <p class="stcard__sub stcard__sub--lg">Catat selisih stok fisik akibat bahan rusak, terbuang, atau human error operator (DEC-04).</p>
        <button type="button" class="btn-ghost2" id="btnAdj"><?= $plus ?> Buat Penyesuaian</button>
      </section>
    </div>

    <!-- Popup: Tambah / Edit bahan -->
    <dialog class="mdl" id="dlgBahan" aria-labelledby="bhTitle">
      <form class="mdl__form" id="formBahan">
        <div class="mdl__head">
          <div>
            <h2 id="bhTitle">Tambah bahan baku</h2>
            <p class="mdl__sub">Bahan baru tampil di pemantauan stok admin dan bisa dipakai di komposisi layanan.</p>
          </div>
          <button type="button" class="mdl__x" data-tutup aria-label="Tutup"><?= $x ?></button>
        </div>
        <div class="mdl__row">
          <div><label class="lbl" for="bhNama">Nama bahan</label><input type="text" id="bhNama" placeholder="Contoh: Kertas A3" required></div>
          <div><label class="lbl" for="bhSatuan">Satuan</label>
            <select id="bhSatuan"><option>rim</option><option>botol</option><option>pcs</option><option>set</option><option>pak</option></select>
          </div>
        </div>
        <div class="mdl__row">
          <div><label class="lbl" for="bhStok">Stok awal</label><input type="number" id="bhStok" min="0" placeholder="0" required></div>
          <div><label class="lbl" for="bhReorder">Reorder point (batas minimum)</label><input type="number" id="bhReorder" min="0" placeholder="Contoh: 10" required></div>
        </div>
        <div class="mdl__actions">
          <button type="button" class="mdl__btn" data-tutup>Batal</button>
          <button type="submit" class="mdl__btn mdl__btn--primary" id="bhSimpan">Simpan bahan</button>
        </div>
      </form>
    </dialog>

    <!-- Popup: Penyesuaian stok -->
    <dialog class="mdl" id="dlgAdj" aria-labelledby="adTitle">
      <form class="mdl__form" id="formAdj">
        <div class="mdl__head">
          <div>
            <h2 id="adTitle">Penyesuaian stok</h2>
            <p class="mdl__sub">Catat bahan rusak/terbuang di luar pesanan agar stok sesuai kondisi fisik.</p>
          </div>
          <button type="button" class="mdl__x" data-tutup aria-label="Tutup"><?= $x ?></button>
        </div>
        <div class="mdl__row">
          <div><label class="lbl" for="adBahan">Bahan</label><select id="adBahan"></select></div>
          <div><label class="lbl" for="adJenis">Jenis penyesuaian</label>
            <select id="adJenis"><option>Rusak</option><option>Terbuang</option><option>Human error</option><option>Selisih stok fisik</option></select>
          </div>
        </div>
        <div class="mdl__row">
          <div><label class="lbl" for="adJumlah">Jumlah dikurangi</label><input type="number" id="adJumlah" min="1" placeholder="Contoh: 1" required></div>
          <div><label class="lbl" for="adHasil">Stok sebelum → sesudah</label><input type="text" id="adHasil" readonly></div>
        </div>
        <div><label class="lbl" for="adAlasan">Alasan (wajib)</label><textarea id="adAlasan" placeholder="Contoh: 1 rim basah terkena tetesan air hujan." required></textarea></div>
        <div class="mdl__actions">
          <button type="button" class="mdl__btn" data-tutup>Batal</button>
          <button type="submit" class="mdl__btn mdl__btn--primary">Simpan penyesuaian</button>
        </div>
      </form>
    </dialog>

    <div class="toast" id="toast" role="status" hidden></div>
  </div>

  <script>
    window.BAHAN = <?= json_encode($bahan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    window.DIPAKAI = <?= json_encode(array_keys($dipakai), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  </script>
  <script src="assets/js/modal.js"></script>
  <script src="assets/js/owner-inventory.js"></script>
</body>
</html>
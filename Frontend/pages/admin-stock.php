<?php
$bahan = require __DIR__ . '/../includes/mock/inventory.php';
$active = 'stok'; $role = 'admin'; $pageTitle = 'Pemantauan Stok';

// Log pemotongan stok otomatis (data contoh)
$log = [
  ['Kertas A4 HVS 70 gsm',      '08 Okt, 09.35 · #RI-20261008-014', '−0,08 rim'],
  ['Plastic Binding (ring spiral)', '08 Okt, 09.35 · #RI-20261008-014', '−1 pcs'],
  ['Kertas A4 HVS 70 gsm',      '08 Okt, 09.05 · #RI-20261008-013', '−0,06 rim'],
  ['Cover Jilid (mika + karton)', '07 Okt, 15.10 · #RI-20261007-008', '−2 set'],
];

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Pemantauan Stok'; $subtitle = 'Pastikan ketersediaan kertas, tinta, dan bahan jilid untuk operasional.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <section class="kpis kpis--stock" aria-label="Ringkasan stok">
      <article class="card kpi">
        <span class="kpi__label">Item kritis</span>
        <span class="kpi__icon kpi__icon--red" aria-hidden="true"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
        <strong class="kpi__value" id="kKritis">0 item</strong>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Total SKU</span>
        <span class="kpi__icon kpi__icon--blue" aria-hidden="true"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/></svg></span>
        <strong class="kpi__value"><?= count($bahan) ?> SKU</strong>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Stok masuk (Okt)</span>
        <span class="kpi__icon kpi__icon--green" aria-hidden="true"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/></svg></span>
        <strong class="kpi__value" id="kMasuk">2 kali</strong>
      </article>
    </section>

    <div class="alertbar" id="alertBar" role="alert" hidden>
      <span class="alertbar__ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
      <div><strong id="alertJudul"></strong><p id="alertIsi"></p></div>
    </div>

    <div class="stgrid">
      <section class="card stcard" aria-label="Inventaris bahan baku">
        <h2>Inventaris bahan baku</h2>
        <div class="qscroll">
          <table class="dtable">
            <thead>
              <tr>
                <th scope="col">Nama bahan</th><th scope="col">Satuan</th><th scope="col" class="r">Stok</th>
                <th scope="col" class="r">Reorder point</th><th scope="col">Level</th><th scope="col">Status</th>
              </tr>
            </thead>
            <tbody id="stokBody"></tbody>
          </table>
        </div>
      </section>

      <div class="stside">
        <form class="card stcard" id="restockForm">
          <h2>Input cepat restock (bahan masuk dari supplier)</h2>
          <div class="restock">
            <div class="fld"><label for="rsBahan">Bahan</label><select id="rsBahan"></select></div>
            <div class="fld"><label for="rsJumlah">Jumlah masuk</label><input type="number" id="rsJumlah" min="1" value="10" required></div>
            <div class="fld"><label for="rsTgl">Tanggal terima</label><input type="date" id="rsTgl" value="2026-10-10" required></div>
          </div>
          <button type="submit" class="btn-round btn-round--sm">Simpan pasokan</button>
          <p class="restock__msg" id="rsMsg" role="status"></p>
        </form>

        <section class="card stcard" aria-label="Log pemotongan stok">
          <h2>Log otomatis pemotongan stok</h2>
          <p class="stcard__sub">Tercatat saat pesanan diubah ke Diproses.</p>
          <ul class="stlog">
            <?php foreach ($log as [$nama, $ket, $jml]): ?>
              <li><div><strong><?= htmlspecialchars($nama) ?></strong><small><?= htmlspecialchars($ket) ?></small></div><b><?= $jml ?></b></li>
            <?php endforeach; ?>
          </ul>
        </section>
      </div>
    </div>
  </div>

  <script>window.BAHAN = <?= json_encode($bahan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
  <script src="assets/js/admin-stock.js"></script>
</body>
</html>
<?php
$data = require __DIR__ . '/../includes/mock/reports.php';
$active = 'laporan'; $role = 'owner'; $pageTitle = 'Laporan & Analitik';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';

$ikon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><rect x="4" y="11" width="4" height="9" rx="1"/><rect x="10" y="4" width="4" height="16" rx="1"/><rect x="16" y="8" width="4" height="12" rx="1"/></svg>';
?>
  <div class="admin__main">
    <?php $title = 'Laporan & Analitik'; $subtitle = 'Rekap omzet, transaksi, dan sirkulasi bahan berdasarkan periode.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <h2 class="sec-title sec-title--sm">Filter laporan</h2>
    <form class="filter-bar" id="filterForm">
      <div class="fld">
        <label for="dari">Dari</label>
        <input type="date" id="dari" value="<?= $data['periode']['dari'] ?>">
      </div>
      <div class="fld">
        <label for="sampai">Sampai</label>
        <input type="date" id="sampai" value="<?= $data['periode']['sampai'] ?>">
      </div>
      <button type="submit" class="btn-main btn-main--lg">Terapkan</button>
      <button type="button" class="btn-ghost btn-ghost--export" id="btnEkspor">Ekspor PDF / Excel</button>
    </form>
    <p class="filter-error" id="filterError" role="alert" hidden>Tanggal "Dari" tidak boleh setelah tanggal "Sampai".</p>

    <section class="kpis kpis--rep" aria-label="Ringkasan laporan">
      <article class="card kpi">
        <span class="kpi__label">Penjualan</span>
        <span class="kpi__icon kpi__icon--green" aria-hidden="true"><?= $ikon ?></span>
        <strong class="kpi__value"><?= $data['kpi']['penjualan'] ?></strong>
        <span class="pill pill--soft">Periode</span>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Pesanan</span>
        <span class="kpi__icon kpi__icon--blue" aria-hidden="true"><?= $ikon ?></span>
        <strong class="kpi__value"><?= $data['kpi']['pesanan'] ?></strong>
        <span class="pill pill--soft">Periode</span>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Nilai bahan keluar</span>
        <span class="kpi__icon kpi__icon--amber" aria-hidden="true"><?= $ikon ?></span>
        <strong class="kpi__value"><?= $data['kpi']['bahan'] ?></strong>
        <span class="pill pill--soft">Periode</span>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Layanan teratas</span>
        <strong class="kpi__value"><?= $data['kpi']['teratas'] ?></strong>
      </article>
    </section>

    <section class="card panel rep-table" aria-label="Rincian transaksi">
      <h2>Rincian transaksi</h2>
      <table class="table table--plain table--tiny">
        <colgroup><col style="width:15.7%"><col style="width:17.6%"><col style="width:18.7%"><col style="width:33%"><col></colgroup>
        <thead>
          <tr>
            <th scope="col">Tanggal</th>
            <th scope="col">Invoice</th>
            <th scope="col">Pelanggan</th>
            <th scope="col">Layanan</th>
            <th scope="col">Total</th>
          </tr>
        </thead>
        <tbody id="trxBody"></tbody>
      </table>
      <p class="table__empty" id="emptyTrx" hidden>Tidak ada transaksi pada periode ini.</p>
    </section>

    <div class="rep-grid">
      <section class="card panel" aria-label="Sirkulasi bahan">
        <h2>Sirkulasi bahan</h2>
        <ul class="circ">
          <?php foreach ($data['sirkulasi'] as [$nama, $jumlah, $satuan]): ?>
            <li><span><?= htmlspecialchars($nama) ?></span><strong><?= $jumlah ?> <?= htmlspecialchars($satuan) ?></strong></li>
          <?php endforeach; ?>
        </ul>
      </section>
      <section class="card panel" aria-label="Catatan">
        <h2>Catatan</h2>
        <p class="rep-note">Laporan dapat diekspor untuk kebutuhan pembukuan dan evaluasi berkala pemilik.</p>
      </section>
    </div>

    <dialog class="dlg" id="dlgEkspor" aria-labelledby="dlgEksporTitle">
      <div class="dlg__body">
        <h2 id="dlgEksporTitle">Ekspor laporan</h2>
        <p class="rep-note">Pilih format untuk rincian transaksi pada periode yang ditampilkan.</p>
        <div class="export-opts">
          <button type="button" class="btn-main" id="btnPdf">PDF (cetak halaman)</button>
          <button type="button" class="btn-main" id="btnCsv">Excel (.csv)</button>
        </div>
        <div class="dlg__actions"><button type="button" class="btn-ghost" id="btnBatalEkspor">Batal</button></div>
      </div>
    </dialog>
  </div>

  <script>window.TRANSAKSI = <?= json_encode($data['transaksi'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
  <script src="assets/js/owner-report.js"></script>
</body>
</html>
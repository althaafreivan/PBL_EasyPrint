<?php
$orders = require __DIR__ . '/../includes/mock/orders.php';
$active = 'pesanan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Antrean Pesanan - EasyPrint Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="assets/css/pill.css">
</head>
<body class="admin">
  <?php include __DIR__ . '/../components/sidebar-admin.php'; ?>

  <div class="admin__main">
    <header class="topbar">
      <div>
        <h1>Antrean Pesanan</h1>
        <p>Verifikasi berkas dan perbarui status produksi.</p>
      </div>
      <div class="topbar__right">
        <button type="button" class="bell" aria-label="Notifikasi">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
        </button>
        <span class="avatar avatar--sm">RI</span>
      </div>
    </header>

    <!-- FILTER -->
    <section class="card filter" aria-label="Filter pesanan">
      <div>
        <span class="label">Filter status</span>
        <div class="filter__pills" role="group" aria-label="Filter status">
          <button type="button" class="chip chip--semua is-active" data-filter="semua" aria-pressed="true">Semua</button>
          <button type="button" class="chip chip--pending"  data-filter="pending"  aria-pressed="false">Pending</button>
          <button type="button" class="chip chip--diproses" data-filter="diproses" aria-pressed="false">Diproses</button>
          <button type="button" class="chip chip--selesai"  data-filter="selesai"  aria-pressed="false">Selesai</button>
        </div>
      </div>

      <label class="search">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-3.5-3.5"/></svg>
        <input type="search" id="cari" placeholder="Cari ID / pelanggan" autocomplete="off">
      </label>
    </section>

    <!-- TABEL -->
    <section class="card table-card" aria-label="Daftar pesanan">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">Pesanan</th>
            <th scope="col">Pelanggan</th>
            <th scope="col">Layanan</th>
            <th scope="col">Total</th>
            <th scope="col">Status</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody id="orderBody"></tbody>
      </table>
      <p class="table__empty" id="emptyMsg" hidden>Tidak ada pesanan yang cocok.</p>

      <div class="file-box" aria-live="polite">
        <span class="label">Berkas terpilih</span>
        <div class="file-box__row">
          <strong id="fileName">-</strong>
          <button type="button" class="btn-white" id="btnPreview">Preview</button>
        </div>
      </div>
    </section>
  </div>

  <script>window.ORDERS = <?= json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
  <script src="assets/js/admin-orders.js"></script>
</body>
</html>
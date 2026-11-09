<?php
$inv = require __DIR__ . '/../includes/mock/invoice.php';
$nav = 'pesanan';

if (!function_exists('rupiah')) {
  function rupiah(int $n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
}
$subtotal = array_sum(array_column($inv['items'], 'jumlah'));
$total    = $subtotal - $inv['diskon'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice <?= htmlspecialchars($inv['nomor']) ?> - Restu Ibu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/pill.css">
  <link rel="stylesheet" href="assets/css/order-invoice.css">
</head>
<body class="app">
  <?php include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="dashboard.php">Beranda</a> / <a href="tracking.php">Pesanan Saya</a> / <span aria-current="page">Invoice</span>
    </nav>

    <div class="pagehead">
      <div>
        <h1 class="greet">Invoice Pesanan</h1>
        <p class="greet__sub">Rincian tagihan untuk pesanan yang telah selesai dikerjakan.</p>
      </div>
      <div class="pagehead__actions">
        <a href="tracking.php" class="btn-pill">Kembali ke Pesanan</a>
        <button type="button" class="btn-pill btn-pill--primary" id="btnCetak">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="7" rx="1"/><path d="M6 17H4a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-2"/></svg>
          Cetak Invoice
        </button>
      </div>
    </div>

    <article class="card invoice" aria-label="Invoice">
      <header class="invoice__top">
        <div class="invoice__brand">
          <div class="invoice__brand-row">
            <img src="assets/images/Icon.png" alt="" class="invoice__logo">
            <strong>Restu Ibu Print &amp; Fotocopy</strong>
          </div>
          <p>Jasa print, fotokopi, jilid, dan kebutuhan dokumen.</p>
          <p>Lokasi outlet · Malang, Jawa Timur</p>
          <p class="invoice__small">Jam operasional mengikuti jadwal outlet.</p>
        </div>
        <div class="invoice__meta">
          <h2>INVOICE</h2>
          <strong><?= htmlspecialchars($inv['nomor']) ?></strong>
          <p>Tanggal terbit <?= htmlspecialchars($inv['tanggal']) ?></p>
          <p>Pesanan <?= htmlspecialchars($inv['pesanan']) ?></p>
        </div>
      </header>

      <section class="invoice__info">
        <div>
          <span class="cap">Ditagihkan kepada</span>
          <p class="invoice__name"><?= htmlspecialchars($inv['pelanggan']['nama']) ?></p>
          <p class="muted">Pelanggan terdaftar</p>
          <p class="muted">No. telepon: <?= htmlspecialchars($inv['pelanggan']['telepon']) ?></p>
        </div>
        <div>
          <span class="cap">Ringkasan pesanan</span>
          <div class="status-grid">
            <span>Status pengerjaan</span>
            <span class="pill pill--green pill--caps"><?= htmlspecialchars($inv['status_kerja']) ?></span>
            <span></span>
            <span>Status pembayaran</span>
            <span class="pill pill--yellow pill--caps"><?= htmlspecialchars($inv['status_bayar']) ?></span>
            <small><?= htmlspecialchars($inv['info_bayar']) ?></small>
          </div>
        </div>
      </section>

      <div class="inv-scroll">
        <table class="inv-table">
          <thead>
            <tr>
              <th scope="col">Deskripsi</th>
              <th scope="col">Spesifikasi</th>
              <th scope="col" class="r">Qty</th>
              <th scope="col" class="r">Harga satuan</th>
              <th scope="col" class="r">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inv['items'] as $it): ?>
              <tr>
                <td><strong><?= htmlspecialchars($it['nama']) ?></strong><small><?= htmlspecialchars($it['desc']) ?></small></td>
                <td><?= htmlspecialchars($it['spek']) ?></td>
                <td class="r"><?= htmlspecialchars($it['qty']) ?></td>
                <td class="r"><?= rupiah($it['harga']) ?></td>
                <td class="r"><strong><?= rupiah($it['jumlah']) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <section class="invoice__bottom">
        <div>
          <span class="cap">Catatan</span>
          <p class="invoice__note">Pesanan telah selesai dan siap diambil di outlet.</p>
          <p class="invoice__note invoice__note--blue">Silakan tunjukkan nomor pesanan kepada petugas kasir.</p>

          <div class="info-box">
            <span class="info-box__icon" aria-hidden="true">!</span>
            <div>
              <strong>Informasi pembayaran</strong>
              <p>Pembayaran dilakukan secara tunai/manual saat pengambilan.</p>
            </div>
          </div>
        </div>

        <div class="totals">
          <div class="totals__row"><span>Subtotal</span><strong><?= rupiah($subtotal) ?></strong></div>
          <div class="totals__row"><span>Diskon</span><strong><?= rupiah($inv['diskon']) ?></strong></div>
          <hr>
          <div class="totals__grand">
            <span class="cap">Total tagihan</span>
            <strong><?= rupiah($total) ?></strong>
            <span class="pill pill--yellow pill--caps">Menunggu pembayaran</span>
          </div>
        </div>
      </section>

      <footer class="invoice__foot">
        <span>Terima kasih telah menggunakan layanan Restu Ibu Print &amp; Fotocopy.</span>
        <span>Invoice digital · <?= htmlspecialchars($inv['pesanan']) ?></span>
      </footer>
    </article>
  </main>

  <script>document.getElementById('btnCetak').addEventListener('click', () => window.print());</script>
</body>
</html>
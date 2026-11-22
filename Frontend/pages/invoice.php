<?php
// Nota/Invoice digital - GET /api/orders/{id}/invoice. Bisa dibuka pelanggan (miliknya) & admin/owner.
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_role(['pelanggan', 'admin', 'owner']);
$staff = $user['role'] !== 'pelanggan';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$res = $id ? api_get("/api/orders/$id/invoice") : ['ok' => false, 'data' => ['message' => 'Pilih pesanan terlebih dahulu.']];
if (!$res['ok']) {
  flash(api_message($res, 'Invoice tidak ditemukan.'), 'err');
  header('Location: ' . ($staff ? 'admin-orders.php' : 'orders.php'));
  exit;
}
$inv = $res['data']['invoice'];
$nav = 'pesanan';
$subtotal = array_sum(array_map(fn($it) => (float) $it['subtotal'], $inv['items']));
$diskon = 0;
$total = (float) $inv['total'];
$lunas = (bool) $inv['lunas'];
$selesai = punya_nota($inv['status']);
$kembali = $staff ? 'admin-orders.php' : link_lacak((int) $id);
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
  <link rel="stylesheet" href="assets/css/integrasi.css">
</head>
<body class="app<?= $staff ? ' app--staff' : '' ?>">
  <?php if (!$staff) include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= home_for_role($user['role']) ?>">Beranda</a> / <a href="<?= $staff ? 'admin-orders.php' : 'orders.php' ?>"><?= $staff ? 'Antrean Pesanan' : 'Pesanan Saya' ?></a> / <span aria-current="page">Invoice</span>
    </nav>

    <div class="pagehead">
      <div>
        <h1 class="greet">Invoice Pesanan</h1>
        <p class="greet__sub"><?= $selesai ? 'Rincian tagihan untuk pesanan yang telah selesai dikerjakan.' : 'Pratinjau tagihan. Nota final terbit setelah pesanan selesai dikerjakan.' ?></p>
      </div>
      <div class="pagehead__actions">
        <a href="<?= $kembali ?>" class="btn-pill">Kembali ke Pesanan</a>
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
          <p><?= e(ALAMAT_TOKO) ?> · Telp. <?= e(TELP_TOKO) ?></p>
          <p class="invoice__small"><?= e(JAM_BUKA) ?> · <?= e(HARI_BUKA) ?></p>
        </div>
        <div class="invoice__meta">
          <h2>INVOICE</h2>
          <strong><?= htmlspecialchars($inv['nomor']) ?></strong>
          <p>Tanggal pesanan <?= e(tgl($inv['tanggal'])) ?></p>
          <p>Pesanan <?= e(kode($inv['kode_pesanan'])) ?></p>
        </div>
      </header>

      <section class="invoice__info">
        <div>
          <span class="cap">Ditagihkan kepada</span>
          <p class="invoice__name"><?= e($inv['pelanggan']) ?></p>
          <p class="muted"><?= e($inv['email']) ?></p>
          <p class="muted">No. telepon: <?= e($inv['no_telepon'] ?: '-') ?></p>
        </div>
        <div>
          <span class="cap">Ringkasan pesanan</span>
          <div class="status-grid">
            <span>Status pengerjaan</span>
            <span class="pill <?= kelas_status($inv['status']) ?> pill--caps"><?= e(label_status($inv['status'])) ?></span>
            <span></span>
            <span>Status pembayaran</span>
            <span class="pill <?= $lunas ? 'pill--green' : 'pill--yellow' ?> pill--caps"><?= $lunas ? 'Lunas' : 'Belum dibayar' ?></span>
            <small><?= $lunas ? e($inv['metode_bayar'] . ' · ' . tgl_jam($inv['dibayar_at'])) : 'Tunai / QRIS di outlet saat pengambilan' ?></small>
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
                <td><strong><?= e($it['nama_layanan']) ?></strong><small><?= e(ucfirst($it['kategori'])) ?></small></td>
                <td><?= e($it['spesifikasi'] ?: '-') ?></td>
                <td class="r"><?= e(angka($it['jumlah'], 0) . ' ' . $it['satuan']) ?></td>
                <td class="r"><?= rupiah($it['harga_satuan']) ?></td>
                <td class="r"><strong><?= rupiah($it['subtotal']) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <section class="invoice__bottom">
        <div>
          <span class="cap">Catatan</span>
          <p class="invoice__note"><?= e($inv['catatan'] ?: ($inv['status'] === 'Diambil' ? 'Pesanan telah diambil pelanggan.' : 'Pesanan siap diambil di outlet setelah selesai.')) ?></p>
          <p class="invoice__note invoice__note--blue">Silakan tunjukkan nomor pesanan <?= e(kode($inv['kode_pesanan'])) ?> kepada petugas kasir.</p>

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
          <div class="totals__row"><span>Diskon</span><strong><?= rupiah($diskon) ?></strong></div>
          <hr>
          <div class="totals__grand">
            <span class="cap">Total tagihan</span>
            <strong><?= rupiah($total) ?></strong>
            <span class="pill <?= $lunas ? 'pill--green' : 'pill--yellow' ?> pill--caps"><?= $lunas ? 'Lunas' : 'Menunggu pembayaran' ?></span>
          </div>
        </div>
      </section>

      <footer class="invoice__foot">
        <span>Terima kasih telah menggunakan layanan Restu Ibu Print &amp; Fotocopy.</span>
        <span>Invoice digital · <?= e(kode($inv['kode_pesanan'])) ?></span>
      </footer>
    </article>
  </main>

  <script>document.getElementById('btnCetak').addEventListener('click', () => window.print());</script>
</body>
</html>
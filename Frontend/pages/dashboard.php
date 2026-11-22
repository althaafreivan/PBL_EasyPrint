<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_role(['pelanggan']);
$nav  = 'dashboard';

$resOrders  = api_get('/api/orders');
$resLayanan = api_get('/api/layanan');
$orders = $resOrders['data']['data'] ?? [];

$hitung = fn(array $st) => count(array_filter($orders, fn($o) => in_array($o['status'], $st, true)));
$stats = [
  ['label' => 'Pesanan aktif',       'nilai' => $hitung(['Diverifikasi', 'Diproses', 'Tertunda', 'Tertunda - Stok Tidak Tersedia', 'Selesai']), 'tipe' => 'blue'],
  ['label' => 'Menunggu verifikasi', 'nilai' => $hitung(['Pending']), 'tipe' => 'amber'],
  ['label' => 'Pesanan diambil',     'nilai' => $hitung(['Diambil']), 'tipe' => 'green'],
];

// Kartu katalog per kategori: harga "mulai" = harga termurah pada kategori tsb.
$kategori = [
  'cetak'     => ['Print Dokumen', 'PDF, Word, dan dokumen lainnya'],
  'fotokopi'  => ['Fotokopi',      'Hitam-putih maupun warna'],
  'finishing' => ['Jilid',         'Finishing dokumen'],
];
$services = [];
foreach ($kategori as $key => [$nama, $desc]) {
  $harga = array_map(fn($l) => (float) $l['harga'], array_filter($resLayanan['data']['data'] ?? [], fn($l) => $l['kategori'] === $key));
  if (!$harga) continue;
  $satuan = $key === 'finishing' ? '' : ' / lembar';
  $services[] = ['key' => $key, 'nama' => $nama, 'desc' => $desc, 'harga' => 'Mulai ' . rupiah(min($harga)) . $satuan];
}
$ikon = [
  'cetak'     => '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
  'fotokopi'  => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 12 12 4M4 20 20 4M12 20l8-8"/>',
  'finishing' => '<rect x="5" y="5" width="14" height="14" rx="2"/><rect x="10" y="10" width="4" height="4"/>',
];
$panggilan = explode(' ', trim($user['nama']))[0];
$flash = flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Restu Ibu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/pill.css">
  <link rel="stylesheet" href="assets/css/customer-extra.css">
  <link rel="stylesheet" href="assets/css/integrasi.css">
</head>
<body class="app">
  <?php include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <div class="dash-head">
      <div>
        <h1 class="greet">Halo, <?= e($panggilan) ?>!</h1>
        <p class="greet__sub">Selamat datang kembali di Restu Ibu Print &amp; Fotocopy.</p>
      </div>
      <a href="order-service.php" class="btn-cta">+ Buat Pesanan</a>
    </div>

    <?php if ($flash): ?><p class="ep-alert ep-alert--<?= e($flash['tipe']) ?>" role="status"><?= e($flash['pesan']) ?></p><?php endif; ?>
    <?php if (!$resOrders['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($resOrders)) ?></p><?php endif; ?>

    <section class="stats" aria-label="Ringkasan pesanan">
      <?php foreach ($stats as $s): ?>
        <article class="card stat">
          <div>
            <span class="stat__label"><?= htmlspecialchars($s['label']) ?></span>
            <strong class="stat__value"><?= str_pad((string) $s['nilai'], 2, '0', STR_PAD_LEFT) ?></strong>
          </div>
          <span class="stat__icon stat__icon--<?= $s['tipe'] ?>" aria-hidden="true">
            <?php if ($s['tipe'] === 'green'): ?>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
            <?php else: ?><i></i><?php endif; ?>
          </span>
        </article>
      <?php endforeach; ?>
    </section>

    <h2 class="section-title">Katalog Layanan</h2>
    <section class="catalog" aria-label="Katalog layanan">
      <?php foreach ($services as $s): ?>
        <article class="card svc">
          <div class="svc__head">
            <span class="svc__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $ikon[$s['key']] ?></svg>
            </span>
            <div>
              <h3><?= htmlspecialchars($s['nama']) ?></h3>
              <p><?= htmlspecialchars($s['desc']) ?></p>
            </div>
          </div>
          <div class="svc__foot">
            <span class="svc__price"><?= htmlspecialchars($s['harga']) ?></span>
            <a href="order-service.php?layanan=<?= $s['key'] ?>" class="btn-pick" aria-label="Pilih <?= htmlspecialchars($s['nama']) ?>">Pilih</a>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <div class="ep-sechead"><h2 class="section-title">Pesanan Terbaru</h2><?php if (count($orders) > 5): ?><a href="orders.php" class="link-detail">Semua pesanan →</a><?php endif; ?></div>
    <section class="card orders" aria-label="Pesanan terbaru">
      <table class="orders__table">
        <thead>
          <tr>
            <th scope="col">Nomor Pesanan</th>
            <th scope="col">Layanan</th>
            <th scope="col">Tanggal</th>
            <th scope="col">Status</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($orders, 0, 5) as $o): ?>
            <tr>
              <td class="orders__code"><?= e(kode($o['kode_pesanan'])) ?></td>
              <td><?= e($o['layanan']) ?></td>
              <td class="orders__date"><?= e(tgl($o['created_at'])) ?></td>
              <td><span class="pill <?= kelas_status($o['status']) ?>"><?= e(label_status($o['status'])) ?></span></td>
              <td><a href="<?= link_lacak((int) $o['id']) ?>" class="link-detail">Lihat detail <span aria-hidden="true">→</span></a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$orders): ?>
            <tr><td colspan="5" class="orders__empty">Belum ada pesanan. <a href="order-service.php">Buat pesanan pertama Anda →</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
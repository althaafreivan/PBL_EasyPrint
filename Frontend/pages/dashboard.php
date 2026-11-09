<?php
$data   = require __DIR__ . '/../includes/mock/data.php';
$user   = $data['user'];
$nav    = 'dashboard';
$kelas  = ['Tertunda' => 'pill--tertunda', 'Diproses' => 'pill--blue', 'Selesai' => 'pill--green', 'Diambil' => 'pill--green'];
$ikon   = [
  'print'    => '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
  'fotokopi' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 12 12 4M4 20 20 4M12 20l8-8"/>',
  'jilid'    => '<rect x="5" y="5" width="14" height="14" rx="2"/><rect x="10" y="10" width="4" height="4"/>',
];
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
</head>
<body class="app">
  <?php include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <div class="dash-head">
      <div>
        <h1 class="greet">Halo, <?= htmlspecialchars($user['panggilan']) ?>!</h1>
        <p class="greet__sub">Selamat datang kembali di Restu Ibu Print &amp; Fotocopy.</p>
      </div>
      <a href="order-service.php" class="btn-cta">+ Buat Pesanan</a>
    </div>

    <section class="stats" aria-label="Ringkasan pesanan">
      <?php foreach ($data['stats'] as $s): ?>
        <article class="card stat">
          <div>
            <span class="stat__label"><?= htmlspecialchars($s['label']) ?></span>
            <strong class="stat__value"><?= htmlspecialchars($s['nilai']) ?></strong>
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
      <?php foreach ($data['services'] as $s): ?>
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

    <h2 class="section-title">Pesanan Terbaru</h2>
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
          <?php foreach ($data['orders'] as $o): ?>
            <tr>
              <td class="orders__code"><?= htmlspecialchars($o['kode']) ?></td>
              <td><?= htmlspecialchars($o['layanan']) ?></td>
              <td class="orders__date"><?= htmlspecialchars($o['tanggal']) ?></td>
              <td><span class="pill <?= $kelas[$o['status']] ?? 'pill--blue' ?>"><?= htmlspecialchars($o['status']) ?></span></td>
              <td><a href="tracking.php" class="link-detail">Lihat detail <span aria-hidden="true">→</span></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
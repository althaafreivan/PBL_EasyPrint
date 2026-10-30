<?php
$data   = require __DIR__ . '/../includes/mock/tracking.php';
$order  = $data['order'];
$others = $data['lainnya'];
$aktif  = $order['langkah_aktif'];
$nav    = 'cek'; // menandai menu aktif di navbar

if (!function_exists('rupiah')) {
  function rupiah(int $n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
}
$kelasStatus = ['Pending' => 'pill--yellow', 'Diproses' => 'pill--blue', 'Selesai' => 'pill--green'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tracking Pesanan - Restu Ibu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/pill.css">
  <link rel="stylesheet" href="assets/css/tracking.css">
</head>
<body class="app">
  <?php include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <h1 class="greet">Tracking Pesanan</h1>
    <p class="greet__sub">Pantau progres pengerjaan pesanan Anda.</p>

    <!-- RINGKASAN -->
    <section class="card track-head" aria-label="Ringkasan pesanan">
      <div class="track-head__main">
        <strong class="track-head__code"><?= htmlspecialchars($order['kode']) ?></strong>
        <span class="pill <?= $kelasStatus[$order['status']] ?? 'pill--blue' ?>"><?= htmlspecialchars($order['status']) ?></span>
        <p class="track-head__total">
          <span>Total tagihan</span>
          <strong><?= rupiah($order['total']) ?></strong>
        </p>
      </div>
      <a href="#" class="btn-outline btn-outline--icon">
        Unduh Nota
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/></svg>
      </a>
    </section>

    <!-- STATUS -->
    <h2 class="section-title">Status pesanan</h2>
    <ol class="stepper" aria-label="Progres pesanan">
      <?php foreach ($order['langkah'] as $i => $nama):
        $kelas = $i < $aktif ? 'is-done' : ($i === $aktif ? 'is-current' : ''); ?>
        <li class="stepper__item <?= $kelas ?>" <?= $i === $aktif ? 'aria-current="step"' : '' ?>>
          <span class="stepper__dot" aria-hidden="true"></span>
          <span class="stepper__label"><?= htmlspecialchars($nama) ?></span>
        </li>
      <?php endforeach; ?>
    </ol>

    <!-- RINCIAN -->
    <h2 class="section-title">Rincian pesanan</h2>
    <section class="card detail" aria-label="Rincian pesanan">
      <dl class="detail__grid">
        <?php foreach ($order['rincian'] as [$label, $nilai]): ?>
          <div>
            <dt><?= htmlspecialchars($label) ?></dt>
            <dd><?= htmlspecialchars($nilai) ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </section>

    <!-- PESANAN LAINNYA -->
    <h2 class="section-title">Pesanan lainnya</h2>
    <div class="other">
      <?php foreach ($others as $o): ?>
        <article class="card other__row">
          <strong><?= htmlspecialchars($o['kode']) ?></strong>
          <span><span class="pill <?= $kelasStatus[$o['status']] ?? 'pill--blue' ?>"><?= htmlspecialchars($o['status']) ?></span></span>
          <strong><?= rupiah($o['total']) ?></strong>
          <a href="#" class="other__link">
            Lihat nota
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </main>
</body>
</html>
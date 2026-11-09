<?php
$data  = require __DIR__ . '/../includes/mock/executive.php';
$bahan = require __DIR__ . '/../includes/mock/inventory.php';
$kritis = array_values(array_filter($bahan, fn($b) => $b['stok'] <= $b['reorder']));
$data['kpi'][2]['value'] = count($kritis) . ' item';

$active = 'dashboard'; $role = 'owner'; $pageTitle = 'Dashboard Eksekutif';

// Geometri grafik garis
$x0 = 20; $step = 100; $yBase = 190; $skala = 5;
$titik = [];
foreach ($data['tren'] as $i => [$hari, $n]) {
  $titik[] = ['x' => $x0 + $i * $step, 'y' => $yBase - $n * $skala, 'hari' => $hari];
}
$garis = implode(' ', array_map(fn($p) => $p['x'] . ',' . $p['y'], $titik));
$bars = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><rect x="4" y="11" width="4" height="9" rx="1"/><rect x="10" y="4" width="4" height="16" rx="1"/><rect x="16" y="8" width="4" height="12" rx="1"/></svg>';
$seru = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 6v8"/><path d="M12 18h.01"/></svg>';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Dashboard Eksekutif'; $subtitle = 'Ringkasan omzet, transaksi, layanan populer, dan peringatan stok.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <!-- KPI -->
    <section class="kpis" aria-label="Ringkasan">
      <?php foreach ($data['kpi'] as $k): ?>
        <article class="card kpi">
          <span class="kpi__label"><a href="<?= $k['url'] ?>" class="stretch"><?= htmlspecialchars($k['label']) ?></a></span>
          <span class="kpi__icon kpi__icon--<?= $k['warna'] ?>" aria-hidden="true"><?= $k['warna'] === 'red' ? $seru : $bars ?></span>
          <strong class="kpi__value"><?= htmlspecialchars($k['value']) ?></strong>
          <span class="pill <?= $k['warna'] === 'red' ? 'pill--red' : 'pill--soft' ?>"><?= htmlspecialchars($k['pill']) ?></span>
        </article>
      <?php endforeach; ?>
    </section>

    <!-- TREN + PERINGATAN -->
    <div class="panels">
      <section class="card panel" aria-label="Tren pemesanan">
        <h2>Tren pemesanan</h2>
        <p class="panel__sub">7 hari terakhir</p>
        <svg class="chart" viewBox="0 0 720 230" role="img" aria-label="Grafik garis jumlah pesanan harian selama 7 hari terakhir">
          <line class="chart__axis" x1="20" y1="10" x2="20" y2="190"/>
          <line class="chart__axis" x1="20" y1="190" x2="700" y2="190"/>
          <polyline class="chart__line" points="<?= $garis ?>"/>
          <?php foreach ($titik as $p): ?>
            <circle class="chart__dot" cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="5"/>
            <text class="chart__label" x="<?= $p['x'] ?>" y="216" text-anchor="middle"><?= $p['hari'] ?></text>
          <?php endforeach; ?>
        </svg>
      </section>

      <section class="card panel" aria-label="Peringatan stok">
        <h2>Peringatan stok</h2>
        <ul class="alerts">
          <?php foreach ($kritis as $b): ?>
            <li>
              <span class="dot dot--red" aria-hidden="true"></span>
              <div>
                <strong><a href="owner-inventory.php" class="stretch"><?= htmlspecialchars($b['singkat']) ?></a></strong>
                <small><?= $b['stok'] ?> / <?= $b['reorder'] ?> <?= htmlspecialchars($b['satuan']) ?> · kritis</small>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>

    <!-- LAYANAN TERPOPULER -->
    <section class="card panel popular" aria-label="Layanan terpopuler">
      <h2>Layanan terpopuler</h2>
      <p class="panel__sub">Berdasarkan jumlah pesanan</p>
      <div class="bars">
        <?php foreach ($data['populer'] as [$nama, $persen]): ?>
          <div class="bar-row">
            <strong><?= htmlspecialchars($nama) ?></strong>
            <div class="bar-row__line">
              <div class="bar" role="progressbar" aria-label="<?= htmlspecialchars($nama) ?>" aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100"><span style="width: <?= $persen ?>%"></span></div>
              <span class="bar-row__pct"><?= $persen ?>%</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</body>
</html>
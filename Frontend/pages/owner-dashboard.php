<?php
// Dashboard eksekutif owner - GET /api/dashboard (CTE, generate_series, GROUP BY/HAVING, window function)
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['owner']);
$res = api_get('/api/dashboard');
$d = $res['data'];
$k = $d['kpi'] ?? [];

$singkat = function ($n): string {
  $n = (float) $n;
  if ($n >= 1e6) return 'Rp ' . str_replace('.', ',', rtrim(rtrim(number_format($n / 1e6, 2, '.', ''), '0'), '.')) . ' jt';
  if ($n >= 1e3) return 'Rp ' . str_replace('.', ',', rtrim(rtrim(number_format($n / 1e3, 1, '.', ''), '0'), '.')) . ' rb';
  return rupiah($n);
};
$growth = $k['pertumbuhan_persen'] ?? null;
$populer = $d['layanan_populer'] ?? [];
$data = [
  'kpi' => [
    ['label' => 'Pendapatan bulan ini', 'value' => $singkat($k['pendapatan_bulan_ini'] ?? 0),
     'pill' => $growth === null ? 'Bulan pertama' : (($growth >= 0 ? '+' : '') . str_replace('.', ',', (string) $growth) . '% vs periode sama bln lalu'),
     'warna' => ($growth ?? 0) >= 0 ? 'green' : 'red', 'url' => 'owner-report.php'],
    ['label' => 'Pesanan diproses', 'value' => (string) ($k['pesanan_diproses'] ?? 0), 'pill' => ($k['pesanan_selesai_bulan_ini'] ?? 0) . ' selesai bln ini', 'warna' => 'blue', 'url' => 'owner-report.php'],
    ['label' => 'Bahan kritis', 'value' => ($k['bahan_kritis'] ?? 0) . ' item', 'pill' => ($k['bahan_kritis'] ?? 0) ? 'Perlu aksi' : 'Aman', 'warna' => ($k['bahan_kritis'] ?? 0) ? 'red' : 'green', 'url' => 'owner-inventory.php'],
    ['label' => 'Layanan terpopuler', 'value' => $populer[0]['nama_layanan'] ?? '-', 'pill' => 'Terlaris 30 hari', 'warna' => 'blue', 'url' => 'owner-services.php'],
  ],
  'tren' => array_map(fn($t) => [HARI_PENDEK[$t['hari_ke']], (int) $t['jumlah_pesanan'], $t['tanggal']], $d['tren_7_hari'] ?? []),
  'peringatan' => $d['stok_kritis'] ?? [],
  'populer' => array_map(fn($p) => [$p['nama_layanan'], (float) $p['persen'], (int) $p['jumlah_pesanan']], $populer),
];

$active = 'dashboard'; $role = 'owner'; $pageTitle = 'Dashboard Eksekutif';

// Geometri grafik garis (skala otomatis mengikuti nilai tertinggi)
$x0 = 40; $step = 105; $yBase = 190; $tinggi = 160;
$maks = max(1, ...array_map(fn($t) => $t[1], $data['tren'] ?: [[0, 0]]));
$titik = [];
foreach ($data['tren'] as $i => [$hari, $n, $tgl]) {
  $titik[] = ['x' => $x0 + $i * $step, 'y' => $yBase - (int) round($n / $maks * $tinggi), 'hari' => $hari, 'n' => $n, 'tgl' => $tgl];
}
$garis = implode(' ', array_map(fn($p) => $p['x'] . ',' . $p['y'], $titik));

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Dashboard Eksekutif'; $subtitle = 'Ringkasan performa dan peringatan inventaris.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>
    <!-- KPI -->
    <section class="kpis" aria-label="Ringkasan">
      <?php foreach ($data['kpi'] as $k): ?>
        <article class="card kpi">
          <span class="kpi__label"><a href="<?= $k['url'] ?>" class="stretch"><?= htmlspecialchars($k['label']) ?></a></span>
          <span class="kpi__icon kpi__icon--<?= $k['warna'] ?>" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><rect x="4" y="11" width="4" height="9" rx="1"/><rect x="10" y="4" width="4" height="16" rx="1"/><rect x="16" y="8" width="4" height="12" rx="1"/></svg>
          </span>
          <strong class="kpi__value"><?= htmlspecialchars($k['value']) ?></strong>
          <span class="pill pill--soft"><?= htmlspecialchars($k['pill']) ?></span>
        </article>
      <?php endforeach; ?>
    </section>

    <!-- TREN + PERINGATAN -->
    <div class="panels">
      <section class="card panel" aria-label="Tren pemesanan">
        <h2>Tren pemesanan</h2>
        <p class="panel__sub">7 hari terakhir</p>
        <svg class="chart" viewBox="0 0 720 230" role="img" aria-label="Grafik garis jumlah pesanan harian selama 7 hari terakhir">
          <line class="chart__axis" x1="40" y1="10" x2="40" y2="190"/>
          <line class="chart__axis" x1="40" y1="190" x2="700" y2="190"/>
          <text class="chart__label" x="32" y="34" text-anchor="end"><?= $maks ?></text>
          <text class="chart__label" x="32" y="194" text-anchor="end">0</text>
          <polyline class="chart__line" points="<?= $garis ?>"/>
          <?php foreach ($titik as $p): ?>
            <circle class="chart__dot" cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="5"><title><?= e(tgl($p['tgl'])) ?>: <?= $p['n'] ?> pesanan</title></circle>
            <text class="chart__label chart__label--val" x="<?= $p['x'] ?>" y="<?= $p['y'] - 12 ?>" text-anchor="middle"><?= $p['n'] ?></text>
            <text class="chart__label" x="<?= $p['x'] ?>" y="216" text-anchor="middle"><?= e($p['hari']) ?></text>
          <?php endforeach; ?>
        </svg>
      </section>

      <section class="card panel" aria-label="Peringatan stok">
        <h2>Peringatan stok</h2>
        <ul class="alerts">
          <?php if (!$data['peringatan']): ?><li><span class="dot dot--green" aria-hidden="true"></span><div><strong>Semua stok aman</strong><small>Tidak ada bahan di bawah reorder point.</small></div></li><?php endif; ?>
          <?php foreach ($data['peringatan'] as $b): ?>
            <li>
              <span class="dot dot--red" aria-hidden="true"></span>
              <div>
                <strong><a href="owner-inventory.php" class="stretch"><?= e($b['nama_bahan']) ?></a></strong>
                <small><?= e(angka($b['stok'])) ?> / <?= e(angka($b['reorder_point'])) ?> <?= e($b['satuan']) ?> · <?= $b['status'] === 'habis' ? 'Habis' : 'Kritis' ?></small>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>

    <!-- LAYANAN TERPOPULER -->
    <section class="card panel popular" aria-label="Layanan terpopuler">
      <h2>Layanan terpopuler</h2>
      <p class="panel__sub">Persentase dari jumlah pesanan 30 hari terakhir</p>
      <div class="bars">
        <?php foreach ($data['populer'] as [$nama, $persen, $jml]): ?>
          <div class="bar-row">
            <strong><?= htmlspecialchars($nama) ?></strong>
            <div class="bar-row__line">
              <div class="bar" role="progressbar" aria-label="<?= htmlspecialchars($nama) ?>" aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100"><span style="width: <?= $persen ?>%"></span></div>
              <span class="bar-row__pct"><?= str_replace('.', ',', (string) $persen) ?>%</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</body>
</html>
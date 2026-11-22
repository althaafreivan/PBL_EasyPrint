<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['admin']);
$active = 'dashboard'; $role = 'admin'; $pageTitle = 'Dashboard Operasional';

$res = api_get('/api/dashboard');
$resStok = api_get('/api/stok');
$d = $res['data'];
$bahanList = $resStok['data']['data'] ?? [];
usort($bahanList, fn($a, $b) => [$b['stok_kritis'], $a['nama_bahan']] <=> [$a['stok_kritis'], $b['nama_bahan']]);
$ps = $d['per_status'] ?? [];
$n = fn(...$st) => str_pad((string) array_sum(array_map(fn($s) => (int) ($ps[$s] ?? 0), $st)), 2, '0', STR_PAD_LEFT);
$data = [
  'ringkasan' => [
    ['label' => 'Pesanan Pending', 'nilai' => $n('Pending'), 'tipe' => 'amber', 'url' => 'admin-orders.php?status=Pending'],
    ['label' => 'Tertunda',        'nilai' => $n('Tertunda', 'Tertunda - Stok Tidak Tersedia'), 'tipe' => 'blue', 'url' => 'admin-orders.php?status=Tertunda'],
    ['label' => 'Sedang Diproses', 'nilai' => $n('Diverifikasi', 'Diproses'), 'tipe' => 'blue', 'url' => 'admin-orders.php?status=Diproses'],
    ['label' => 'Siap Diambil',    'nilai' => $n('Selesai'), 'tipe' => 'green', 'url' => 'admin-cashier.php'],
  ],
  'prioritas' => $d['prioritas'] ?? [],
  // Bahan: kritis dulu, lalu sisanya (maks. 5)
  'bahan' => array_slice($bahanList, 0, 5),
  'pengingat' => [
    'Periksa berkas dan kolom "Kebutuhan bahan" sebelum mengubah pesanan menjadi Diproses.',
    'Stok dipotong otomatis saat status berubah menjadi Diproses. Bila bahan kurang, pesanan otomatis menjadi "Tertunda - Stok Tidak Tersedia" dan pelanggan diberi tahu.',
  ],
];

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Dashboard Operasional'; $subtitle = 'Ringkasan pekerjaan harian Admin / Operator.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>
    <section class="opk" aria-label="Ringkasan pesanan">
      <?php foreach ($data['ringkasan'] as $r): ?>
        <article class="card opk__item">
          <div><span class="opk__label"><a href="<?= $r['url'] ?>" class="stretch"><?= e($r['label']) ?></a></span><strong class="opk__value"><?= e($r['nilai']) ?></strong></div>
          <span class="opk__dot opk__dot--<?= $r['tipe'] ?>" aria-hidden="true"></span>
        </article>
      <?php endforeach; ?>
    </section>

    <h2 class="ops-title">Prioritas Pekerjaan</h2>
    <div class="ops">
      <section class="card ops__table" aria-label="Pesanan prioritas">
        <table class="opt">
          <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Masuk</th><th scope="col" colspan="2">Status</th></tr></thead>
          <tbody>
            <?php foreach ($data['prioritas'] as $p): ?>
              <tr>
                <td class="opt__code"><?= e(kode($p['kode_pesanan'])) ?></td>
                <td><?= e($p['nama_pelanggan']) ?></td>
                <td><?= e(grup_waktu($p['created_at']) === 'Hari ini' ? jam($p['created_at']) : tgl_jam($p['created_at'])) ?></td>
                <td><span class="pill <?= kelas_status($p['status']) ?>"><?= e(label_status($p['status'])) ?></span></td>
                <td><a href="admin-orders.php?pilih=<?= (int) $p['id'] ?>" class="opt__link">Detail →</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$data['prioritas']): ?><tr><td colspan="5" class="opt__empty">Tidak ada pesanan yang perlu ditindaklanjuti. 🎉</td></tr><?php endif; ?>
          </tbody>
        </table>
      </section>

      <aside class="card ops__stock" aria-label="Ketersediaan bahan">
        <h3>Ketersediaan Bahan</h3>
        <p>Informasi untuk verifikasi pesanan</p>
        <ul>
          <?php foreach ($data['bahan'] as $b): ?>
            <li><span class="dotx dotx--<?= $b['stok_kritis'] ? 'bad' : 'ok' ?>" aria-hidden="true"></span><div><strong><?= e($b['nama_bahan']) ?></strong><span><?= e(angka($b['stok']) . ' ' . $b['satuan'] . ' · ' . ($b['status'] === 'habis' ? 'Habis' : ($b['stok_kritis'] ? 'Kritis' : 'Aman'))) ?></span></div></li>
          <?php endforeach; ?>
        </ul>
        <a href="admin-stock.php" class="opt__link">Buka pemantauan stok →</a>
      </aside>
    </div>

    <section class="card remind" aria-label="Pengingat operasional">
      <h3>Pengingat Operasional</h3>
      <?php foreach ($data['pengingat'] as $t): ?><p><?= e($t) ?></p><?php endforeach; ?>
    </section>
  </div>
</body>
</html>

<?php
// Cek status pesanan publik: GET /api/track?kode=&telepon= (nomor telepon harus cocok).
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../components/stepper2.php';

$kode    = strtoupper(ltrim(trim($_GET['kode'] ?? ''), '#'));
$telepon = preg_replace('/\D+/', '', $_GET['telepon'] ?? '');
if ($kode === '' || $telepon === '') { header('Location: check-order.php?err=kosong'); exit; }

$res = callBackendApi('/api/track?' . http_build_query(['kode' => $kode, 'telepon' => $telepon]));
if (!$res['ok']) {
  header('Location: check-order.php?' . http_build_query(['err' => $res['code'] === 404 ? 'tidak-ada' : 'server', 'kode' => '#' . $kode]));
  exit;
}
$order = $res['data']['pesanan'];
$riwayat = $res['data']['riwayat'];
$tel = $order['telepon_samaran'];
$st = $order['status'];

$info = [
  'Pending'      => ['Menunggu pemeriksaan admin', 'Status akan berubah setelah admin memeriksa berkas Anda.'],
  'Diverifikasi' => ['Berkas sudah diverifikasi', 'Pesanan segera masuk antrean cetak.'],
  'Tertunda'     => ['Pesanan ditunda sementara', 'Admin akan melanjutkan pesanan secepatnya.'],
  'Tertunda - Stok Tidak Tersedia' => ['Pesanan ditunda – stok bahan habis', 'Pesanan dilanjutkan otomatis setelah stok tersedia, tanpa biaya tambahan.'],
  'Diproses'     => ['Pesanan sedang dikerjakan', 'Kami beri tahu di akun Anda saat pesanan siap diambil.'],
  'Selesai'      => ['Pesanan siap diambil', 'Tunjukkan nomor pesanan di kasir saat mengambil.'],
  'Diambil'      => ['Pesanan sudah diambil', 'Terima kasih telah menggunakan layanan kami.'],
  'Ditolak'      => ['Pesanan ditolak oleh admin', $order['alasan_tolak'] ? 'Alasan: ' . $order['alasan_tolak'] : 'Masuk untuk melihat detail dan membuat pesanan baru.'],
][$st] ?? ['Status: ' . $st, ''];
$ico = fn($p, $s = 16) => ico($p, $s);

$pageTitle = 'Status Pesanan'; $navPublic = 'cek'; $extraCss = ['customer-extra.css'];
include __DIR__ . '/../components/head-pub.php';
include __DIR__ . '/../components/navbar-public.php';
?>
  <main class="pmain">
    <p class="crumb"><a href="index.php">Beranda</a> / <a href="check-order.php">Cek Pesanan</a> / Hasil</p>
    <div class="result-head">
      <div>
        <h1>Status Pesanan</h1>
        <p>Ditemukan 1 pesanan untuk nomor telepon <?= e($tel) ?>.</p>
      </div>
      <a href="check-order.php" class="pbtn pbtn--outline"><?= $ico('<circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>', 14) ?>Cek nomor lain</a>
    </div>

    <div class="result-grid">
      <section class="card res-card" aria-label="Status pesanan">
        <div class="res-card__top">
          <div>
            <h2><?= e(kode($order['kode_pesanan'])) ?></h2>
            <p><?= e($order['layanan']) ?> · <?= e(tgl($order['created_at'])) ?></p>
          </div>
          <span class="pill <?= kelas_status($st) ?>"><?= e(label_status($st)) ?></span>
        </div>
        <div style="margin-top:26px"><?php ep_stepper2(ep_steps_riwayat($st, $riwayat)); ?></div>
        <div class="eta">
          <?= $ico('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>') ?>
          <div><strong><?= e($info[0]) ?></strong><?= e($info[1]) ?></div>
        </div>
      </section>

      <aside class="card pickup" aria-label="Info pengambilan">
        <h2>Info pengambilan</h2>
        <ul>
          <li><?= $ico('<path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>', 15) ?><span><strong><?= e(NAMA_TOKO) ?></strong><?= e(ALAMAT_TOKO) ?></span></li>
          <li><?= $ico('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>', 15) ?><span><strong><?= e(JAM_BUKA) ?></strong><?= e(HARI_BUKA) ?></span></li>
          <li><?= $ico('<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>', 15) ?><span><strong><?= rupiah($order['total_harga']) ?></strong><?= $st === 'Ditolak' ? 'Tidak ada biaya untuk pesanan ditolak' : ($st === 'Diambil' ? 'Sudah dibayar' : 'Bayar tunai / QRIS saat pengambilan') ?></span></li>
        </ul>
        <a href="<?= current_user() ? home_for_role(current_user()['role']) : 'login.php' ?>" class="pbtn pbtn--primary"><?= current_user() ? 'Buka dashboard' : 'Masuk untuk detail lengkap' ?></a>
      </aside>
    </div>
  </main>
  <?php include __DIR__ . '/../components/footer-public.php'; ?>
</body>
</html>

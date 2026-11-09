<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../components/stepper2.php';

$kode    = strtoupper(ltrim(trim($_GET['kode'] ?? ''), '#'));
$telepon = preg_replace('/\D+/', '', $_GET['telepon'] ?? '');
if ($kode === '' || $telepon === '') { header('Location: check-order.php?err=kosong'); exit; }

// Data contoh. Validasi nomor telepon dilakukan backend nanti; di sini pesanan dicari dari nomor pesanan saja.
$order = null;
foreach (require __DIR__ . '/../includes/mock/history.php' as $o) {
  if (strtoupper(ltrim($o['kode'], '#')) === $kode) { $order = $o; break; }
}
if (!$order) { header('Location: check-order.php?err=tidak-ada'); exit; }

$tel  = strlen($telepon) >= 8 ? substr($telepon, 0, 4) . '-****-' . substr($telepon, -4) : '****';
$info = [
  'Pending'  => ['Menunggu pemeriksaan admin', 'Kami kirim WhatsApp saat status pesanan berubah.'],
  'Tertunda' => ['Pesanan ditunda sementara', 'Stok bahan sedang habis. Pesanan dilanjutkan setelah stok tersedia.'],
  'Diproses' => ['Estimasi selesai hari ini, ± 15.00 WIB', 'Kami kirim WhatsApp saat pesanan siap diambil.'],
  'Selesai'  => ['Pesanan siap diambil', 'Tunjukkan nomor pesanan di kasir saat mengambil.'],
  'Diambil'  => ['Pesanan sudah diambil', 'Terima kasih telah menggunakan layanan kami.'],
  'Ditolak'  => ['Pesanan ditolak oleh admin', 'Masuk untuk melihat alasan dan membuat pesanan baru.'],
][$order['status']];
$ico = fn($p, $s = 16) => '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';

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
            <h2><?= e($order['kode']) ?></h2>
            <p><?= e($order['layanan']) ?> · <?= e($order['tanggal']) ?></p>
          </div>
          <span class="pill <?= kelas_status($order['status']) ?>"><?= e($order['status']) ?></span>
        </div>
        <div style="margin-top:26px"><?php ep_stepper2(ep_steps($order['status'], $order['waktu'])); ?></div>
        <div class="eta">
          <?= $ico('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>') ?>
          <div><strong><?= e($info[0]) ?></strong><?= e($info[1]) ?></div>
        </div>
      </section>

      <aside class="card pickup" aria-label="Info pengambilan">
        <h2>Info pengambilan</h2>
        <ul>
          <li><?= $ico('<path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>', 15) ?><span><strong>Restu Ibu Print &amp; Fotocopy</strong>Jl. Soekarno Hatta No. 9, Lowokwaru, Malang</span></li>
          <li><?= $ico('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>', 15) ?><span><strong>08.00 – 20.00 WIB</strong>Senin – Sabtu</span></li>
          <li><?= $ico('<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>', 15) ?><span><strong><?= rupiah($order['total']) ?></strong>Bayar tunai saat pengambilan</span></li>
        </ul>
        <a href="login.php" class="pbtn pbtn--primary">Masuk untuk detail lengkap</a>
      </aside>
    </div>
  </main>
  <?php include __DIR__ . '/../components/footer-public.php'; ?>
</body>
</html>

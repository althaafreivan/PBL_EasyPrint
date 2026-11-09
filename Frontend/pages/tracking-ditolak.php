<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../components/stepper2.php';
$orders = require __DIR__ . '/../includes/mock/history.php';
$o = $orders[2]; // #RI-20261007-011 (Ditolak)
$nav = 'pesanan'; $pageTitle = 'Tracking Pesanan';
$log = [
  ['07 Okt, 14.05', '<strong>Pesanan ditolak</strong> · oleh Admin (Rina)'],
  ['07 Okt, 13.48', 'Berkas sedang diperiksa admin'],
  ['07 Okt, 13.20', 'Pesanan dibuat · surat-keterangan-scan.pdf diunggah'],
];
$ico = fn($p, $s = 16) => '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
include __DIR__ . '/../components/head-app.php';
include __DIR__ . '/../components/navbar-app.php';
?>
  <main class="page">
    <p class="label" style="font-weight:500;font-size:11.5px"><a href="dashboard.php">Beranda</a> / <a href="orders.php">Pesanan Saya</a> / <?= e($o['kode']) ?></p>
    <h1 class="greet">Tracking Pesanan</h1>
    <p class="greet__sub">Pantau perkembangan pesanan dan riwayat transaksi Anda.</p>

    <section class="card trk-head" aria-label="Ringkasan pesanan">
      <div>
        <div class="trk-head__code"><?= e($o['kode']) ?> <span class="pill pill--red">Ditolak</span></div>
        <p><?= e($o['layanan']) ?> · Dibuat 07 Oktober 2026, 13.20</p>
      </div>
      <p class="trk-head__sum">Estimasi biaya<strong><?= rupiah($o['total']) ?></strong></p>
    </section>

    <div class="notice notice--reject" role="alert" style="margin-top:18px">
      <span class="notice__ico"><?= $ico('<path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>', 18) ?></span>
      <div class="notice__body">
        <strong>Pesanan ditolak oleh admin</strong><br>
        <b>Alasan:</b> Berkas tidak terbaca / buram. Hasil scan di berkas terlalu kecil sehingga teks tidak terbaca saat dicetak.
        <small>Saran: scan ulang dokumen dengan lebih jelas, lalu buat pesanan baru. Tidak ada biaya untuk pesanan yang ditolak.</small>
      </div>
      <a href="order-service.php" class="btn-fill"><span aria-hidden="true">+</span> Buat pesanan baru</a>
    </div>

    <div class="trk-two">
      <section class="card trk-card" aria-label="Status pesanan">
        <h2>Status pesanan</h2>
        <?php ep_stepper2(ep_steps($o['status'], $o['waktu'])); ?>
        <hr>
        <h3>Riwayat aktivitas</h3>
        <ul class="log">
          <?php foreach ($log as [$waktu, $teks]): ?><li><time><?= e($waktu) ?></time><span><?= $teks ?></span></li><?php endforeach; ?>
        </ul>
      </section>

      <aside class="card trk-card" aria-label="Ringkasan pesanan">
        <h2>Ringkasan pesanan</h2>
        <dl class="sumlist">
          <div><dt>Layanan</dt><dd>Print Dokumen · A4 Warna</dd></div>
          <div><dt>Jumlah</dt><dd>4 lembar</dd></div>
          <div><dt>Berkas</dt><dd>surat-keterangan-scan.pdf</dd></div>
          <div class="sumlist__total"><dt>Estimasi total</dt><dd><?= rupiah($o['total']) ?></dd></div>
        </dl>
        <p class="fine">Tidak ada biaya yang ditagihkan untuk pesanan yang ditolak.</p>
      </aside>
    </div>
  </main>
</body>
</html>

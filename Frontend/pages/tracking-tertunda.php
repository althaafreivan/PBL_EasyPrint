<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../components/stepper2.php';
$orders = require __DIR__ . '/../includes/mock/history.php';
$o = $orders[0]; // #RI-20261008-016 (Tertunda)
$riwayat = array_values(array_filter($orders, fn($x) => in_array($x['status'], ['Selesai', 'Diambil'], true)));
$nav = 'pesanan'; $pageTitle = 'Tracking Pesanan';
$ico = fn($p, $s = 16) => '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
include __DIR__ . '/../components/head-app.php';
include __DIR__ . '/../components/navbar-app.php';
?>
  <main class="page">
    <h1 class="greet">Tracking Pesanan</h1>
    <p class="greet__sub">Pantau perkembangan pesanan dan riwayat transaksi Anda.</p>

    <section class="card trk-head" aria-label="Ringkasan pesanan">
      <div>
        <div class="trk-head__code"><?= e($o['kode']) ?> <span class="pill pill--tertunda">Tertunda</span></div>
        <p><?= e($o['layanan']) ?> · Dibuat 08 Oktober 2026</p>
      </div>
      <p class="trk-head__sum">Estimasi biaya<strong><?= rupiah($o['total']) ?></strong></p>
    </section>

    <h2 class="section-title">Status Pesanan</h2>
    <div class="trk-steps"><?php ep_stepper2(ep_steps($o['status'], $o['waktu'])); ?></div>

    <div class="notice notice--hold" role="status">
      <span class="notice__ico"><?= $ico('<circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 1.5"/>', 16) ?></span>
      <p class="notice__body"><strong>Tertunda – Stok Tidak Tersedia.</strong> Kertas F4 di toko sedang habis; pasokan diperkirakan tiba <strong>10 Okt 2026</strong>. Pesanan dilanjutkan setelah stok tersedia, tanpa biaya tambahan.</p>
    </div>

    <h2 class="section-title" style="margin-top:44px">Riwayat Pesanan</h2>
    <section class="card trk-card" aria-label="Riwayat pesanan" style="padding-top:8px">
      <table class="hist-table">
        <thead><tr><th scope="col">Nomor Pesanan</th><th scope="col">Layanan</th><th scope="col">Tanggal</th><th scope="col">Status</th><th scope="col">Nota</th></tr></thead>
        <tbody>
          <?php foreach ($riwayat as $r): ?>
            <tr>
              <td class="code"><?= e($r['kode']) ?></td><td><?= e($r['layanan']) ?></td><td><?= e($r['tanggal']) ?></td>
              <td><span class="pill <?= kelas_status($r['status']) ?>"><?= e($r['status']) ?></span></td>
              <td><a href="invoice.php" class="dl">Unduh nota ↓</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
    <p class="trk-note">Status Ditolak merupakan status akhir alternatif dan tidak termasuk alur pengerjaan normal.</p>
  </main>
</body>
</html>

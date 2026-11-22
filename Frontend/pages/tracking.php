<?php
// Tracking pesanan pelanggan (6 status DEC-02 + Ditolak) - GET /api/orders/{id}
// Menggabungkan tampilan tracking normal, tertunda, dan ditolak dalam satu halaman.
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../components/stepper2.php';
require_role(['pelanggan']);

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$resList = api_get('/api/orders');
$semua = $resList['data']['data'] ?? [];
if (!$id) {
  // Tanpa id: tampilkan pesanan aktif terbaru, atau arahkan ke riwayat.
  $aktif = array_values(array_filter($semua, fn($o) => !in_array($o['status'], ['Diambil', 'Ditolak'], true)));
  if (!$aktif && !$semua) { header('Location: orders.php'); exit; }
  $id = (int) ($aktif[0]['id'] ?? $semua[0]['id']);
}

$res = api_get("/api/orders/$id");
if (!$res['ok']) { flash(api_message($res), 'err'); header('Location: orders.php'); exit; }

$o = $res['data']['pesanan'];
$items = $res['data']['items'];
$riwayat = $res['data']['riwayat'];
$st = $o['status'];
$berkas = array_values(array_filter(array_column($items, 'file_dokumen')));
$lainnya = array_slice(array_values(array_filter($semua, fn($x) => (int) $x['id'] !== $id)), 0, 4);

$nav = 'pesanan'; $pageTitle = 'Tracking Pesanan';
include __DIR__ . '/../components/head-app.php';
include __DIR__ . '/../components/navbar-app.php';
?>
  <main class="page">
    <p class="label" style="font-weight:500;font-size:11.5px"><a href="dashboard.php">Beranda</a> / <a href="orders.php">Pesanan Saya</a> / <?= e(kode($o['kode_pesanan'])) ?></p>
    <h1 class="greet">Tracking Pesanan</h1>
    <p class="greet__sub">Pantau perkembangan pesanan dan riwayat transaksi Anda.</p>

    <section class="card trk-head" aria-label="Ringkasan pesanan">
      <div>
        <div class="trk-head__code"><?= e(kode($o['kode_pesanan'])) ?> <span class="pill <?= kelas_status($st) ?>"><?= e(label_status($st)) ?></span></div>
        <p><?= e(implode(' + ', array_column($items, 'nama_layanan'))) ?> · Dibuat <?= e(tgl($o['created_at'], true)) ?>, <?= e(jam($o['created_at'])) ?></p>
      </div>
      <div class="trk-head__right">
        <p class="trk-head__sum"><?= $st === 'Diambil' ? 'Total dibayar' : 'Total tagihan' ?><strong><?= rupiah($o['total_harga']) ?></strong></p>
        <?php if (punya_nota($st)): ?>
          <a href="invoice.php?id=<?= (int) $o['id'] ?>" class="btn-outline btn-outline--icon">Lihat Nota <?= ico('<path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>', 16) ?></a>
        <?php endif; ?>
      </div>
    </section>

    <?php if ($st === 'Ditolak'): ?>
      <div class="notice notice--reject" role="alert" style="margin-top:18px">
        <span class="notice__ico"><?= ico('<path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>', 18) ?></span>
        <div class="notice__body">
          <strong>Pesanan ditolak oleh admin</strong><br>
          <b>Alasan:</b> <?= e($o['alasan_tolak'] ?: '-') ?>
          <small>Silakan perbaiki berkas lalu buat pesanan baru. Tidak ada biaya untuk pesanan yang ditolak.</small>
        </div>
        <a href="order-service.php" class="btn-fill"><span aria-hidden="true">+</span> Buat pesanan baru</a>
      </div>
    <?php endif; ?>

    <h2 class="section-title">Status Pesanan</h2>
    <div class="trk-steps"><?php ep_stepper2(ep_steps_riwayat($st, $riwayat)); ?></div>

    <?php if (is_tertunda($st)):
      $alasanTunda = '';
      foreach (array_reverse($riwayat) as $r) { if (is_tertunda($r['status_baru'])) { $alasanTunda = (string) $r['keterangan']; break; } } ?>
      <div class="notice notice--hold" role="status">
        <span class="notice__ico"><?= ico('<circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 1.5"/>', 16) ?></span>
        <p class="notice__body"><strong><?= e($st) ?>.</strong>
          <?= e($alasanTunda ?: 'Bahan yang dibutuhkan sedang tidak tersedia.') ?>. Pesanan dilanjutkan setelah stok tersedia, tanpa biaya tambahan. Anda akan menerima notifikasi saat pesanan kembali diproses.</p>
      </div>
    <?php elseif ($st === 'Selesai'): ?>
      <div class="notice notice--ok" role="status">
        <span class="notice__ico"><?= ico('<path d="m5 12 5 5L20 7"/>', 16) ?></span>
        <p class="notice__body"><strong>Pesanan siap diambil.</strong> Tunjukkan nomor pesanan <?= e(kode($o['kode_pesanan'])) ?> di kasir <?= e(NAMA_TOKO) ?> (<?= e(JAM_BUKA) ?>). Pembayaran tunai/QRIS saat pengambilan.</p>
      </div>
    <?php endif; ?>

    <div class="trk-two">
      <section class="card trk-card" aria-label="Riwayat aktivitas">
        <h2>Riwayat aktivitas</h2>
        <ul class="log">
          <?php foreach (array_reverse($riwayat) as $r): ?>
            <li><time><?= e(tgl_jam($r['created_at'])) ?></time><span>
              <strong><?= e($r['status_lama'] ? 'Status: ' . $r['status_baru'] : 'Pesanan dibuat') ?></strong>
              <?php if ($r['oleh'] && $r['peran'] !== 'pelanggan'): ?> · oleh <?= e($r['peran'] === 'admin' ? 'Admin' : 'Owner') ?> (<?= e(explode(' ', $r['oleh'])[0]) ?>)<?php endif; ?>
              <?php if ($r['keterangan'] && $r['status_lama']): ?><br><small><?= e($r['keterangan']) ?></small><?php endif; ?>
            </span></li>
          <?php endforeach; ?>
        </ul>
      </section>

      <aside class="card trk-card" aria-label="Rincian pesanan">
        <h2>Rincian pesanan</h2>
        <dl class="sumlist">
          <?php foreach ($items as $it): ?>
            <div><dt><?= e($it['nama_layanan']) ?></dt><dd><?= e($it['spesifikasi'] ?: $it['jumlah'] . ' ' . $it['satuan']) ?> · <?= rupiah($it['subtotal']) ?></dd></div>
          <?php endforeach; ?>
          <div><dt>Berkas</dt><dd><?php if ($berkas): ?><a href="file.php?id=<?= (int) $o['id'] ?>" target="_blank" rel="noopener">Lihat berkas</a><?php else: ?>Tidak ada (dibawa ke outlet)<?php endif; ?></dd></div>
          <div><dt>Catatan</dt><dd><?= e($o['catatan'] ?: '-') ?></dd></div>
          <?php if ($o['metode_bayar']): ?><div><dt>Pembayaran</dt><dd><?= e($o['metode_bayar']) ?> · <?= e(tgl_jam($o['dibayar_at'])) ?></dd></div><?php endif; ?>
          <div class="sumlist__total"><dt>Total</dt><dd><?= rupiah($o['total_harga']) ?></dd></div>
        </dl>
        <?php if ($st === 'Ditolak'): ?><p class="fine">Tidak ada biaya yang ditagihkan untuk pesanan yang ditolak.</p><?php endif; ?>
      </aside>
    </div>

    <?php if ($lainnya): ?>
      <h2 class="section-title" style="margin-top:44px">Pesanan Lainnya</h2>
      <section class="card trk-card" aria-label="Pesanan lainnya" style="padding-top:8px">
        <div class="hist-scroll">
          <table class="hist-table">
            <thead><tr><th scope="col">Nomor Pesanan</th><th scope="col">Layanan</th><th scope="col">Tanggal</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($lainnya as $r): ?>
                <tr>
                  <td class="code"><?= e(kode($r['kode_pesanan'])) ?></td><td><?= e($r['layanan']) ?></td><td><?= e(tgl($r['created_at'])) ?></td>
                  <td><span class="pill <?= kelas_status($r['status']) ?>"><?= e(label_status($r['status'])) ?></span></td>
                  <td><a href="<?= punya_nota($r['status']) ? 'invoice.php?id=' . (int) $r['id'] : link_lacak((int) $r['id']) ?>" class="dl"><?= punya_nota($r['status']) ? 'Nota ↓' : 'Lacak →' ?></a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>
    <p class="trk-note">Status Ditolak merupakan status akhir alternatif dan tidak termasuk alur pengerjaan normal.</p>
  </main>
</body>
</html>

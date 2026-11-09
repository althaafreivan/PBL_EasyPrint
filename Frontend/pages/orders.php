<?php
require_once __DIR__ . '/../includes/helpers.php';
$orders = require __DIR__ . '/../includes/mock/history.php';
$nav = 'pesanan'; $pageTitle = 'Pesanan Saya';
$ico = fn($p, $s = 16) => '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
$tabs = ['semua' => 'Semua', 'pending' => 'Pending', 'tertunda' => 'Tertunda', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'diambil' => 'Diambil', 'ditolak' => 'Ditolak'];
$hitung = array_count_values(array_map(fn($o) => strtolower($o['status']), $orders));
$aksiKedua = ['Ditolak' => ['Pesan ulang', 'order-service.php'], 'Selesai' => ['Nota', 'invoice.php'], 'Diambil' => ['Nota', 'invoice.php']];
include __DIR__ . '/../components/head-app.php';
include __DIR__ . '/../components/navbar-app.php';
?>
  <main class="page">
    <p class="label" style="font-weight:500;font-size:11.5px"><a href="dashboard.php">Beranda</a> / Pesanan Saya</p>
    <div class="hist-head">
      <div>
        <h1 class="greet">Pesanan Saya</h1>
        <p class="greet__sub">Semua pesanan Anda beserta statusnya. Klik pesanan untuk melihat pelacakan.</p>
      </div>
      <a href="order-service.php" class="btn-cta" style="padding:0 40px"><span aria-hidden="true" style="margin-right:8px">+</span>Buat Pesanan</a>
    </div>

    <section class="card hist-card" aria-label="Daftar pesanan">
      <div class="hist-tools">
        <div class="tabs" role="group" aria-label="Filter status">
          <?php foreach ($tabs as $k => $nama): $n = $k === 'semua' ? count($orders) : ($hitung[$k] ?? 0); ?>
            <button type="button" class="tab<?= $k === 'semua' ? ' is-active' : '' ?>" data-tab="<?= $k ?>" aria-pressed="<?= $k === 'semua' ? 'true' : 'false' ?>"><?= $nama ?> <span><?= $n ?></span></button>
          <?php endforeach; ?>
        </div>
        <label class="hsearch"><?= $ico('<circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>', 16) ?>
          <input type="search" id="cariPesanan" placeholder="Cari nomor pesanan..." autocomplete="off" aria-label="Cari nomor pesanan"></label>
      </div>

      <div class="hist-scroll">
        <table class="hist-table">
          <thead><tr><th scope="col">Nomor Pesanan</th><th scope="col">Layanan</th><th scope="col">Tanggal</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
          <tbody id="riwayatBody">
            <?php foreach ($orders as $o): $st = $o['status']; $kedua = $aksiKedua[$st] ?? ['Lacak', link_lacak($st)]; ?>
              <tr data-status="<?= strtolower($st) ?>" data-kode="<?= strtolower(e($o['kode'])) ?>">
                <td class="code"><?= e($o['kode']) ?></td>
                <td><?= e($o['layanan']) ?></td>
                <td><?= e($o['tanggal']) ?></td>
                <td class="code"><?= rupiah($o['total']) ?></td>
                <td><span class="pill <?= kelas_status($st) ?>"><?= e($st) ?></span></td>
                <td><div class="hist-actions">
                  <a href="<?= link_lacak($st) ?>" class="dl">Lihat detail <span aria-hidden="true">→</span></a>
                  <a href="<?= $kedua[1] ?>" class="mini"><?= $kedua[0] ?></a>
                </div></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="hist-empty" id="riwayatKosong" hidden>Tidak ada pesanan yang cocok. Ubah filter atau kata kunci pencarian.</p>

      <div class="hist-foot"><span id="riwayatInfo">Menampilkan <?= count($orders) ?> pesanan</span></div>
    </section>
  </main>
  <script src="assets/js/orders.js"></script>
</body>
</html>

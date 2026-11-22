<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['pelanggan']);
$res = api_get('/api/orders');
$orders = $res['data']['data'] ?? [];
$nav = 'pesanan'; $pageTitle = 'Pesanan Saya';
$ico = fn($p, $s = 16) => ico($p, $s);
$tabs = ['semua' => 'Semua', 'pending' => 'Pending', 'diverifikasi' => 'Diverifikasi', 'tertunda' => 'Tertunda', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'diambil' => 'Diambil', 'ditolak' => 'Ditolak'];
$hitung = array_count_values(array_map(fn($o) => slug_status($o['status']), $orders));
$awal = isset($tabs[$_GET['status'] ?? '']) ? $_GET['status'] : 'semua';
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

    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>

    <section class="card hist-card" aria-label="Daftar pesanan">
      <div class="hist-tools">
        <div class="tabs" role="group" aria-label="Filter status">
          <?php foreach ($tabs as $k => $nama): $n = $k === 'semua' ? count($orders) : ($hitung[$k] ?? 0); ?>
            <button type="button" class="tab<?= $k === $awal ? ' is-active' : '' ?>" data-tab="<?= $k ?>" aria-pressed="<?= $k === $awal ? 'true' : 'false' ?>"><?= $nama ?> <span><?= $n ?></span></button>
          <?php endforeach; ?>
        </div>
        <label class="hsearch"><?= $ico('<circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>', 16) ?>
          <input type="search" id="cariPesanan" placeholder="Cari nomor pesanan..." autocomplete="off" aria-label="Cari nomor pesanan"></label>
      </div>

      <div class="hist-scroll">
        <table class="hist-table">
          <thead><tr><th scope="col">Nomor Pesanan</th><th scope="col">Layanan</th><th scope="col">Tanggal</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
          <tbody id="riwayatBody">
            <?php foreach ($orders as $o): $st = $o['status']; $id = (int) $o['id']; ?>
              <tr data-status="<?= slug_status($st) ?>" data-kode="<?= e(strtolower($o['kode_pesanan'])) ?>" data-id="<?= $id ?>">
                <td class="code"><?= e(kode($o['kode_pesanan'])) ?></td>
                <td><?= e($o['layanan']) ?></td>
                <td><?= e(tgl($o['created_at'])) ?></td>
                <td class="code"><?= rupiah($o['total_harga']) ?></td>
                <td><span class="pill <?= kelas_status($st) ?>"><?= e(label_status($st)) ?></span></td>
                <td><div class="hist-actions">
                  <a href="<?= link_lacak($id) ?>" class="dl">Lihat detail <span aria-hidden="true">→</span></a>
                  <?php if (punya_nota($st)): ?>
                    <a href="invoice.php?id=<?= $id ?>" class="mini">Nota</a>
                  <?php elseif ($st === 'Ditolak'): ?>
                    <a href="order-service.php" class="mini">Pesan ulang</a>
                  <?php elseif ($st === 'Pending'): ?>
                    <button type="button" class="mini mini--danger" data-batal="<?= $id ?>" data-kode="<?= e(kode($o['kode_pesanan'])) ?>">Batalkan</button>
                  <?php endif; ?>
                </div></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="hist-empty" id="riwayatKosong" hidden><?= $orders ? 'Tidak ada pesanan yang cocok. Ubah filter atau kata kunci pencarian.' : 'Belum ada pesanan. Klik "Buat Pesanan" untuk memulai.' ?></p>

      <div class="hist-foot"><span id="riwayatInfo">Menampilkan <?= count($orders) ?> pesanan</span></div>
    </section>
  </main>
  <script src="assets/js/modal.js"></script>
  <script src="assets/js/orders.js"></script>
</body>
</html>

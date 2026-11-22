<?php
// Antrean pesanan admin: GET /api/orders, detail GET /api/orders/{id}, aksi PATCH /api/orders/status
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['admin']);
$res = api_get('/api/orders');
$orders = array_map(fn($o) => [
  'id' => (int) $o['id'],
  'kode' => kode($o['kode_pesanan']),
  'pelanggan' => $o['nama_pelanggan'],
  'telepon' => (string) $o['no_telepon'],
  'layanan' => (string) $o['layanan'],
  'total' => (float) $o['total_harga'],
  'status' => $o['status'],
  'berkas' => $o['berkas'],
  'masuk' => tgl_jam($o['created_at']),
], $res['data']['data'] ?? []);

$filters = ['semua' => 'Semua', 'Pending' => 'Pending', 'Diverifikasi' => 'Diverifikasi', 'Tertunda' => 'Tertunda',
            'Diproses' => 'Diproses', 'Selesai' => 'Selesai', 'Diambil' => 'Diambil', 'Ditolak' => 'Ditolak'];
$awal = isset($filters[$_GET['status'] ?? '']) ? $_GET['status'] : 'aktif';
$active = 'antrean'; $role = 'admin'; $pageTitle = 'Antrean Pesanan';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Antrean Pesanan'; $subtitle = 'Verifikasi berkas dan perbarui status produksi.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>

    <!-- FILTER -->
    <section class="card filter" aria-label="Filter pesanan">
      <div>
        <span class="label">Filter status</span>
        <div class="filter__pills" role="group" aria-label="Filter status">
          <button type="button" class="chip chip--semua<?= $awal === 'aktif' ? ' is-active' : '' ?>" data-filter="aktif" aria-pressed="<?= $awal === 'aktif' ? 'true' : 'false' ?>">Perlu tindakan</button>
          <?php foreach ($filters as $k => $label): ?>
            <button type="button" class="chip chip--f<?= $awal === $k ? ' is-active' : '' ?>" data-filter="<?= e($k) ?>" aria-pressed="<?= $awal === $k ? 'true' : 'false' ?>"><?= e($label) ?> <span class="chip__n" data-count="<?= e($k) ?>"></span></button>
          <?php endforeach; ?>
        </div>
      </div>

      <label class="search">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-3.5-3.5"/></svg>
        <input type="search" id="cari" placeholder="Cari ID / pelanggan / telepon" autocomplete="off">
      </label>
    </section>

    <div class="aq-grid">
      <!-- TABEL -->
      <section class="card table-card" aria-label="Daftar pesanan">
        <div class="ep-scroll">
          <table class="table">
            <thead>
              <tr>
                <th scope="col">Pesanan / pelanggan</th>
                <th scope="col">Layanan</th>
                <th scope="col">Total</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
              </tr>
            </thead>
            <tbody id="orderBody"></tbody>
          </table>
        </div>
        <p class="table__empty" id="emptyMsg" hidden>Tidak ada pesanan yang cocok.</p>
        <p class="aq-info" id="infoMsg"></p>
      </section>

      <!-- DETAIL PESANAN TERPILIH -->
      <aside class="card aq-detail" id="detail" aria-live="polite" aria-label="Detail pesanan">
        <p class="aq-detail__empty">Pilih pesanan untuk melihat detail, berkas, dan kebutuhan bahan.</p>
      </aside>
    </div>
  </div>

  <script>
    window.ORDERS = <?= json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    window.PILIH = <?= (int) ($_GET['pilih'] ?? 0) ?>;
  </script>
  <script src="assets/js/modal.js"></script>
  <script src="assets/js/admin-orders.js"></script>
</body>
</html>

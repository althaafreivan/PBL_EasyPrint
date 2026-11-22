<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['admin']);
// Daftar pesanan siap diambil (status Selesai) untuk akses cepat kasir.
$resSiap = api_get('/api/orders?status=Selesai&limit=12');
$siap = $resSiap['data']['data'] ?? [];
$active = 'kasir'; $role = 'admin'; $pageTitle = 'Kasir & Pengambilan';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Kasir & Pengambilan'; $subtitle = 'Selesaikan pembayaran dan serah-terima pesanan.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <h2 class="sec-title">Cari pesanan</h2>
    <form class="search-row" id="searchForm" role="search">
      <label class="search search--lg">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-3.5-3.5"/></svg>
        <input type="search" id="q" placeholder="ID pesanan / nomor telepon" autocomplete="off" aria-label="ID pesanan atau nomor telepon">
      </label>
      <button type="submit" class="btn-main btn-main--lg">Cari</button>
    </form>

    <?php if ($siap): ?>
      <div class="ks-ready" aria-label="Pesanan siap diambil">
        <span class="label">Siap diambil (<?= count($siap) ?>)</span>
        <div class="ks-ready__list">
          <?php foreach ($siap as $o): ?>
            <button type="button" class="ks-chip" data-q="<?= e($o['kode_pesanan']) ?>"><strong><?= e(kode($o['kode_pesanan'])) ?></strong><span><?= e($o['nama_pelanggan']) ?> · <?= rupiah($o['total_harga']) ?></span></button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <p class="empty-msg" id="notFound" role="status" hidden>Pesanan tidak ditemukan.</p>
    <div class="ks-multi" id="multi" hidden></div>

    <section class="card order-card" id="orderCard" aria-label="Detail pesanan" hidden>
      <div class="order-card__head">
        <h2 id="ordKode"></h2>
        <span class="pill" id="ordStatus"></span>
      </div>

      <dl class="order-info">
        <div><dt>Pelanggan</dt><dd id="ordNama"></dd></div>
        <div><dt>No. Telepon</dt><dd id="ordTelp"></dd></div>
        <div><dt>Total tagihan</dt><dd class="order-info__total" id="ordTotal"></dd></div>
      </dl>

      <div class="order-card__sum">
        <span class="label">Ringkasan</span>
        <div id="ordRincian"></div>
      </div>

      <div class="order-card__pay">
        <label class="label" for="ordMetode">Metode pembayaran</label>
        <select id="ordMetode" class="ks-select">
          <option value="Tunai">Tunai</option>
          <option value="QRIS">QRIS</option>
          <option value="Transfer">Transfer</option>
        </select>
      </div>
    </section>

    <div class="cashier-actions" id="cashierActions" hidden>
      <a class="btn-ghost" id="btnStruk" href="#">Lihat Nota</a>
      <aside class="card handover" aria-label="Konfirmasi serah-terima">
        <h3>Konfirmasi serah-terima</h3>
        <p id="handoverText">Pesanan akan berubah menjadi Diambil setelah konfirmasi.</p>
        <button type="button" class="btn-main btn-main--block" id="btnAmbil">Konfirmasi &amp; Ambil</button>
      </aside>
    </div>
  </div>

  <script src="assets/js/modal.js"></script>
  <script src="assets/js/admin-cashier.js"></script>
</body>
</html>
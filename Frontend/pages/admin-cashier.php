<?php
$orders = require __DIR__ . '/../includes/mock/cashier.php';
$active = 'kasir'; $role = 'admin'; $pageTitle = 'Kasir & Pengambilan';
$awal = trim($_GET['q'] ?? '') !== '' ? '#' . ltrim(trim($_GET['q']), '#') : $orders[0]['kode'];

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Kasir & Pengambilan'; $subtitle = 'Cari pesanan, terima pembayaran tunai di tempat, dan serahkan hasil cetak.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <form class="card kform" id="searchForm" role="search">
      <label class="qsearch qsearch--wide">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-3.5-3.5"/></svg>
        <input type="search" id="q" value="<?= htmlspecialchars($awal) ?>" placeholder="ID pesanan / nomor telepon" autocomplete="off" aria-label="ID pesanan atau nomor telepon">
      </label>
      <button type="submit" class="btn-round">Cari</button>
    </form>

    <p class="kempty" id="notFound" role="status" hidden>Pesanan tidak ditemukan.</p>

    <div class="kgrid" id="kGrid">
      <section class="card korder" aria-label="Detail pesanan">
        <div class="korder__head">
          <h2 id="oKode"></h2>
          <span class="pill" id="oStatus"></span>
          <span class="korder__time" id="oSelesai"></span>
        </div>
        <dl class="kinfo">
          <div><dt>Pelanggan</dt><dd id="oNama"></dd></div>
          <div><dt>No. Telepon</dt><dd id="oTelp"></dd></div>
        </dl>
        <table class="ktable">
          <thead><tr><th scope="col">Item</th><th scope="col" class="r">Qty</th><th scope="col" class="r">Harga</th><th scope="col" class="r">Subtotal</th></tr></thead>
          <tbody id="oItems"></tbody>
        </table>
      </section>

      <aside class="card kside" aria-label="Ringkasan tagihan">
        <h3>Ringkasan tagihan</h3>
        <div class="kside__row kside__row--total"><span>Total tagihan</span><strong id="oTotal"></strong></div>
        <div class="kside__row"><span>Metode pembayaran</span><strong id="oMetode"></strong></div>
        <p class="kside__info" id="oInfo">Setelah dikonfirmasi, status pesanan berubah menjadi <strong>Diambil</strong> dan transaksi tercatat.</p>
        <button type="button" class="btn-block btn-block--primary" id="btnAmbil">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L20 7"/></svg>
          Konfirmasi serah terima
        </button>
        <a href="admin-receipt.php" class="btn-block" id="btnStruk">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="7" rx="1"/><path d="M6 17H4a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-2"/></svg>
          Cetak struk
        </a>
      </aside>
    </div>
  </div>

  <script>window.KASIR = <?= json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
  <script src="assets/js/admin-cashier.js"></script>
</body>
</html>
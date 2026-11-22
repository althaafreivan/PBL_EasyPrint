<?php
// Struk pembayaran kasir - GET /api/orders/{id}
require_once __DIR__ . '/../includes/bootstrap.php';
$kasir = require_role(['admin']);
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$res = $id ? api_get("/api/orders/$id") : ['ok' => false, 'data' => ['message' => 'Pilih pesanan dari halaman kasir.']];
if (!$res['ok']) { flash(api_message($res), 'err'); header('Location: admin-cashier.php'); exit; }
$o = $res['data']['pesanan'];
$items = $res['data']['items'];
if ($o['status'] !== 'Diambil') { header('Location: admin-cashier.php?q=' . urlencode($o['kode_pesanan'])); exit; }
$total = (float) $o['total_harga'];
$nomor = 'INV-' . $o['kode_pesanan'];
$baru = isset($_GET['baru']);
$active = 'kasir'; $role = 'admin'; $pageTitle = 'Struk Pembayaran';
$bulat = fn($n) => number_format((float) $n, 0, ',', '.');

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Struk Pembayaran'; $subtitle = 'Transaksi selesai · siap dicetak atau dikirim ke pelanggan.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <div class="rc-wrap">
      <section class="rc-bg" aria-label="Pratinjau struk">
        <article class="receipt">
          <header class="receipt__head">
            <strong>RESTU IBU PRINT &amp; FOTOCOPY</strong>
            <?= e(ALAMAT_TOKO) ?><br>Telp. <?= e(TELP_TOKO) ?>
          </header>
          <dl class="receipt__meta">
            <div><dt>No</dt><dd><?= e($nomor) ?></dd></div>
            <div><dt>Tanggal</dt><dd><?= e(waktu($o['dibayar_at'])?->format('d/m/Y H:i') ?? '-') ?></dd></div>
            <div><dt>Pesanan</dt><dd><?= e(kode($o['kode_pesanan'])) ?></dd></div>
            <div><dt>Kasir</dt><dd><?= e($kasir['nama']) ?></dd></div>
            <div><dt>Pelanggan</dt><dd><?= e($o['nama']) ?></dd></div>
          </dl>
          <div class="receipt__items">
            <?php foreach ($items as $it): ?>
              <div><span><?= e($it['nama_layanan']) ?> × <?= e(angka($it['jumlah'], 0)) ?></span><span><?= $bulat($it['subtotal']) ?></span></div>
            <?php endforeach; ?>
          </div>
          <div class="receipt__sum">
            <div class="receipt__total"><span>TOTAL</span><span><?= $bulat($total) ?></span></div>
            <div><span>Dibayar (<?= e($o['metode_bayar']) ?>)</span><span><?= $bulat($total) ?></span></div>
          </div>
          <p class="receipt__status">Status: LUNAS · DIAMBIL</p>
          <p class="receipt__thanks">Terima kasih telah menggunakan layanan EasyPrint.<br>Simpan struk ini sebagai bukti.</p>
        </article>
      </section>

      <aside class="rc-side">
        <section class="card rc-ok" aria-label="Ringkasan pembayaran">
          <div class="rc-ok__top">
            <span class="rc-ok__tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
            <div><strong><?= $baru ? 'Pembayaran berhasil' : 'Transaksi selesai' ?></strong><span>Pesanan berstatus <b>Diambil</b></span></div>
          </div>
          <dl>
            <div><dt>Total</dt><dd><?= rupiah($total) ?></dd></div>
            <div><dt>Metode</dt><dd><?= e($o['metode_bayar']) ?></dd></div>
            <div><dt>Waktu</dt><dd><?= e(tgl_jam($o['dibayar_at'])) ?></dd></div>
          </dl>
        </section>
        <button type="button" class="mdl__btn mdl__btn--primary rc-btn" id="btnCetak">Cetak struk</button>
        <a href="admin-cashier.php" class="mdl__btn rc-btn">‹ Kembali ke kasir</a>
      </aside>
    </div>
  </div>
  <script>document.getElementById('btnCetak').addEventListener('click', function () { window.print(); });</script>
</body>
</html>

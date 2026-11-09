<?php
require_once __DIR__ . '/../includes/helpers.php';
$daftar = require __DIR__ . '/../includes/mock/cashier.php';
$q = strtolower(ltrim(trim($_GET['q'] ?? ''), '#'));
$o = $daftar[0];
foreach ($daftar as $x) {
  if ($q !== '' && strpos(strtolower($x['kode']), $q) !== false) { $o = $x; break; }
}
$total = 0;
foreach ($o['items'] as [, $qty, $harga]) { $total += $qty * $harga; }
$bayar   = max((int) $o['bayar'], $total);
$kembali = $bayar - $total;
$angka   = fn(int $n) => number_format($n, 0, ',', '.');
$active = 'kasir'; $role = 'admin'; $pageTitle = 'Struk Pembayaran';

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
            Jl. Soekarno Hatta No. 9, Lowokwaru, Malang<br>Telp. 0812-3456-0009
          </header>
          <dl class="receipt__meta">
            <div><dt>No</dt><dd><?= e($o['nomor']) ?></dd></div>
            <div><dt>Tanggal</dt><dd><?= e($o['dibayar']) ?></dd></div>
            <div><dt>Pesanan</dt><dd><?= e($o['kode']) ?></dd></div>
            <div><dt>Kasir</dt><dd>Admin (Rina)</dd></div>
            <div><dt>Pelanggan</dt><dd><?= e($o['pelanggan']) ?></dd></div>
          </dl>
          <div class="receipt__items">
            <?php foreach ($o['items'] as [$nama, $qty, $harga]): ?>
              <div><span><?= e($nama) ?></span></div>
              <div><span>&nbsp;&nbsp;<?= (int) $qty ?> x <?= $angka((int) $harga) ?></span><span><?= $angka((int) ($qty * $harga)) ?></span></div>
            <?php endforeach; ?>
          </div>
          <div class="receipt__sum">
            <div class="receipt__total"><span>TOTAL</span><span><?= $angka($total) ?></span></div>
            <div><span>Tunai</span><span><?= $angka($bayar) ?></span></div>
            <div><span>Kembali</span><span><?= $angka($kembali) ?></span></div>
          </div>
          <p class="receipt__status">Status: LUNAS · DIAMBIL</p>
          <p class="receipt__thanks">Terima kasih telah menggunakan<br>layanan EasyPrint.<br>Simpan struk ini sebagai bukti.</p>
        </article>
      </section>

      <aside class="rc-side">
        <section class="card rc-ok" aria-label="Ringkasan pembayaran">
          <div class="rc-ok__top">
            <span class="rc-ok__tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
            <div><strong>Pembayaran berhasil</strong><span>Pesanan berubah menjadi <b>Diambil</b></span></div>
          </div>
          <dl>
            <div><dt>Total</dt><dd><?= rupiah($total) ?></dd></div>
            <div><dt>Metode</dt><dd>Tunai</dd></div>
            <div><dt>Kembalian</dt><dd><?= rupiah($kembali) ?></dd></div>
          </dl>
        </section>
        <button type="button" class="mdl__btn mdl__btn--primary rc-btn" id="btnCetak">Cetak struk</button>
        <a href="admin-cashier.php?q=<?= urlencode(ltrim($o['kode'], '#')) ?>" class="mdl__btn rc-btn">‹ Kembali ke kasir</a>
      </aside>
    </div>
  </div>
  <script>document.getElementById('btnCetak').addEventListener('click', function () { window.print(); });</script>
</body>
</html>
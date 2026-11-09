<?php
require_once __DIR__ . '/../includes/helpers.php';
$nav = 'pesanan'; $pageTitle = 'Pesanan Berhasil';
$ico = fn($p, $s = 16) => '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
include __DIR__ . '/../components/head-app.php';
include __DIR__ . '/../components/navbar-app.php';
?>
  <main class="page">
    <section class="card success" aria-labelledby="okTitle">
      <span class="success__tick"><?= $ico('<path d="m5 12 5 5L20 7"/>', 24) ?></span>
      <h1 id="okTitle">Pesanan berhasil dikirim!</h1>
      <p class="success__lead">Admin akan memeriksa berkas Anda. Kami kirim notifikasi saat status berubah.</p>

      <div class="success__sum">
        <div>Nomor pesanan<strong class="success__code" id="okKode">#RI-20261008-017</strong></div>
        <div>Layanan<strong id="okLayanan">Print Dokumen</strong></div>
        <div>Estimasi biaya<strong id="okTotal">Rp 25.000</strong></div>
        <span class="pill pill--yellow">Pending</span>
      </div>

      <ol class="next3" aria-label="Langkah berikutnya">
        <li><span class="ico"><?= $ico('<circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>', 15) ?></span><strong>1. Verifikasi</strong>Admin memeriksa berkas &amp; stok bahan</li>
        <li><span class="ico"><?= $ico('<path d="M7 9V4h10v5"/><rect x="4" y="9" width="16" height="8" rx="2"/><path d="M7 14h10v6H7z"/>', 15) ?></span><strong>2. Diproses</strong>Pesanan dicetak &amp; difinishing</li>
        <li><span class="ico"><?= $ico('<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>', 15) ?></span><strong>3. Ambil &amp; bayar</strong>Tunjukkan nomor pesanan di kasir</li>
      </ol>

      <div class="success__actions">
        <a href="tracking.php" class="btn-fill">Lihat status pesanan</a>
        <a href="dashboard.php" class="btn-outline">Kembali ke dashboard</a>
      </div>
    </section>
  </main>
  <script>
    // Isi ringkasan dari pesanan yang baru dikirim (disimpan order-review.js). Jika kosong, tampil data contoh.
    (function () {
      var d = null;
      try { d = JSON.parse(sessionStorage.getItem('ep_last') || 'null'); } catch (e) {}
      if (!d) return;
      if (d.kode) document.getElementById('okKode').textContent = d.kode;
      if (d.nama) document.getElementById('okLayanan').textContent = d.nama;
      if (d.subtotal) document.getElementById('okTotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(d.subtotal);
    })();
  </script>
</body>
</html>

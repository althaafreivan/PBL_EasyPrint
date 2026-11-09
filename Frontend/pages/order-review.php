<?php $nav = 'pesanan'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tinjau &amp; Kirim - Restu Ibu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/pill.css">
  <link rel="stylesheet" href="assets/css/order-invoice.css">
</head>
<body class="app">
  <?php include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="dashboard.php">Beranda</a> / <a href="tracking.php">Pesanan Saya</a> / <span aria-current="page">Buat Pesanan</span>
    </nav>
    <h1 class="greet">Buat Pesanan</h1>
    <p class="greet__sub">Periksa kembali pesanan sebelum dikirim ke admin.</p>

    <ol class="wizard wizard--navy card" aria-label="Langkah pesanan">
      <li class="is-done">
        <span class="wizard__dot" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
        <span><strong>Layanan dipilih</strong><small>Pilih jenis layanan</small></span>
      </li>
      <li class="is-done">
        <span class="wizard__dot" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
        <span><strong>Detail pesanan</strong><small>Spesifikasi &amp; berkas</small></span>
      </li>
      <li class="is-current" aria-current="step">
        <span class="wizard__dot" aria-hidden="true">3</span>
        <span><strong>Review &amp; kirim</strong><small>Konfirmasi pesanan</small></span>
      </li>
    </ol>

    <form class="order-grid order-grid--review" id="reviewForm">
      <div class="rv-col">
        <section class="card rv-card" aria-label="Detail pesanan">
          <div class="rv-head">
            <h2>Detail pesanan</h2>
            <a href="order-form.php" class="rv-edit">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
              Ubah
            </a>
          </div>
          <dl class="rv-list" id="rvList"></dl>
        </section>

        <section class="card rv-card" aria-label="Berkas">
          <h2>Berkas</h2>
          <div class="rv-file">
            <span class="rs-file__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
            </span>
            <div><strong id="rvFile"></strong><small id="rvFileMeta"></small></div>
            <span class="pill pill--green">Siap diperiksa</span>
          </div>
        </section>

        <div class="rv-two">
          <section class="card rv-card" aria-label="Pengambilan">
            <h2>Pengambilan</h2>
            <div class="rv-info"><span aria-hidden="true">📍</span><div><strong>Ambil di outlet</strong><small>Restu Ibu Print &amp; Fotocopy · Malang</small></div></div>
            <div class="rv-info"><span aria-hidden="true">🕒</span><div><strong>Estimasi selesai</strong><small>Hari ini, ± 15.00 WIB</small></div></div>
          </section>
          <section class="card rv-card" aria-label="Pembayaran">
            <h2>Pembayaran</h2>
            <div class="rv-info"><span aria-hidden="true">💵</span><div><strong>Tunai saat pengambilan</strong><small>Bayar di kasir setelah pesanan selesai</small></div></div>
          </section>
        </div>
      </div>

      <aside class="card rs" aria-label="Rincian harga">
        <h2>Rincian harga</h2>
        <div class="rs-row" style="margin-top:12px"><span id="rvCetakLabel"></span><strong id="rvCetak"></strong></div>
        <div class="rs-row" id="rvJilidWrap"><span id="rvJilidLabel"></span><strong id="rvJilid"></strong></div>
        <div class="rs-row"><span>Biaya tambahan</span><strong>Rp 0</strong></div>
        <div class="rs-total rs-total--row"><small>Estimasi total</small><strong id="rvTotal"></strong></div>

        <label class="check-line">
          <input type="checkbox" id="rvCek">
          <span>Saya sudah memeriksa detail &amp; berkas pesanan.</span>
        </label>
        <button type="submit" class="btn-send" id="rvKirim" disabled>Kirim Pesanan</button>
        <a href="order-form.php" class="btn-pill btn-pill--block">‹ Kembali</a>
      </aside>
    </form>
  </main>

  <script src="assets/js/order-review.js"></script>
</body>
</html>
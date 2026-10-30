<?php
$data     = require __DIR__ . '/../includes/mock/data.php';
$user     = $data['user'];
$order    = $data['active_order'];
$services = $data['services'];

function rupiah(int $n): string {
  return 'Rp ' . number_format($n, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Pelanggan - Restu Ibu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/pill.css">

</head>
<body class="app">
  <?php include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <h1 class="greet">Halo, <?= htmlspecialchars($user['nama']) ?>.</h1>
    <p class="greet__sub">Pilih layanan dan konfigurasi pesanan baru.</p>

    <!-- PESANAN AKTIF -->
    <section class="card active" aria-label="Pesanan aktif">
      <div class="active__item">
        <span class="label">Pesanan aktif</span>
        <div class="active__code">
          <strong><?= htmlspecialchars($order['kode']) ?></strong>
          <span class="pill pill--blue"><?= htmlspecialchars($order['status']) ?></span>
        </div>
      </div>
      <div class="active__item">
        <span class="label">Estimasi selesai</span>
        <strong class="active__value"><?= htmlspecialchars($order['estimasi']) ?></strong>
      </div>
      <div class="active__item">
        <span class="label">Total</span>
        <strong class="active__value"><?= rupiah($order['total']) ?></strong>
      </div>
      <a href="tracking.php" class="btn-outline">Lihat Tracking</a>
    </section>

    <!-- LAYANAN -->
    <h2 class="section-title" id="layanan">Layanan</h2>
    <div class="services">
      <?php foreach ($services as $s): ?>
        <article class="card service" data-service="<?= $s['key'] ?>">
          <div class="service__head">
            <span class="service__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
            </span>
            <div>
              <h3><?= htmlspecialchars($s['nama']) ?></h3>
              <p><?= htmlspecialchars($s['desc']) ?></p>
            </div>
          </div>
          <div class="service__foot">
            <span class="service__price"><?= htmlspecialchars($s['harga']) ?></span>
            <button type="button" class="btn-pick" data-pick="<?= $s['key'] ?>">Pilih</button>
          </div>
        </article>
      <?php endforeach; ?>

      <article class="card service service--new">
        <h3>Order baru</h3>
        <p>Konfigurasi cepat pesanan</p>
        <div class="service__pills">
          <span class="pill pill--soft">1.000+ order</span>
          <span class="pill pill--green">Aman</span>
        </div>
        <p class="service__flow">Upload berkas → hitung harga → kirim</p>
      </article>
    </div>

    <!-- KONFIGURASI + RINGKASAN -->
    <form class="config" id="orderForm" action="#" method="post">
      <section class="config__left" id="konfigurasi">
        <h2 class="section-title">Konfigurasi Pesanan</h2>
        <div class="card config__card">
          <div class="field">
            <label for="kertas">Jenis kertas</label>
            <select id="kertas" name="kertas">
              <option value="A4">A4</option>
              <option value="F4">F4</option>
              <option value="A3">A3</option>
            </select>
          </div>
          <div class="field">
            <label for="warna">Warna</label>
            <select id="warna" name="warna">
              <option value="color">Warna</option>
              <option value="bw">Hitam-putih</option>
            </select>
          </div>
          <div class="field">
            <label for="jilid">Jilid</label>
            <select id="jilid" name="jilid">
              <option value="none">Tanpa jilid</option>
              <option value="spiral" selected>Spiral</option>
              <option value="hard">Hard cover</option>
            </select>
          </div>

          <div class="field">
            <label for="lembar">Jumlah lembar</label>
            <input type="number" id="lembar" name="lembar" min="1" value="20">
          </div>
          <div class="field">
            <label for="eksemplar">Eksemplar</label>
            <input type="number" id="eksemplar" name="eksemplar" min="1" value="1">
          </div>
          <div class="field">
            <span class="field__spacer" aria-hidden="true"></span>
            <label class="upload" for="berkas">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
              <span id="berkasNama">Upload file</span>
              <input type="file" id="berkas" name="berkas" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
            </label>
          </div>
        </div>
      </section>

      <aside class="config__right" aria-label="Ringkasan harga">
        <h2 class="section-title">Ringkasan Harga</h2>
        <div class="card summary">
          <div class="summary__row"><span id="sumPrintLabel">Print A4 warna × 20</span><strong id="sumPrint">Rp 10.000</strong></div>
          <div class="summary__row" id="sumJilidRow"><span id="sumJilidLabel">Jilid</span><strong id="sumJilid">Rp 5.000</strong></div>
          <div class="summary__row"><span>Biaya layanan</span><strong id="sumFee">Rp 3.500</strong></div>
          <div class="summary__total"><span>Total</span><strong id="sumTotal">Rp 18.500</strong></div>
        </div>
        <button type="submit" class="btn-confirm">Konfirmasi Pesanan</button>
      </aside>
    </form>
  </main>

  <script src="assets/js/dashboard.js"></script>
</body>
</html>
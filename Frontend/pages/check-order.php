<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Cek Pesanan'; $navPublic = 'cek';
$err = $_GET['err'] ?? '';
$pesanErr = ['kosong' => 'Isi nomor pesanan dan nomor telepon terlebih dahulu.', 'tidak-ada' => 'Pesanan tidak ditemukan. Periksa kembali nomor pesanan dan nomor telepon Anda.'][$err] ?? '';
include __DIR__ . '/../components/head-pub.php';
include __DIR__ . '/../components/navbar-public.php';
$ico = fn($p, $s = 16) => '<svg viewBox="0 0 24 24" width="' . $s . '" height="' . $s . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
?>
  <main class="pmain">
    <div class="check">
      <section aria-labelledby="cekTitle">
        <span class="tagline">Cek pesanan</span>
        <h1 id="cekTitle">Lacak pesanan tanpa harus masuk akun.</h1>
        <p class="check__lead">Masukkan nomor pesanan dan nomor telepon yang dipakai saat memesan. Nomor pesanan ada di nota atau notifikasi WhatsApp.</p>
        <ul class="check__trust">
          <li><?= $ico('<path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6z"/>') ?>Data dilindungi</li>
          <li><?= $ico('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>') ?>Status real-time</li>
          <li><?= $ico('<path d="M21 12a9 9 0 0 1-13.4 7.8L3 21l1.3-4.4A9 9 0 1 1 21 12z"/>') ?>Notifikasi WhatsApp</li>
        </ul>
      </section>

      <form class="card check-form" action="check-result.php" method="get" novalidate>
        <h2>Cek status pesanan</h2>
        <?php if ($pesanErr): ?><p class="check-err" role="alert"><?= e($pesanErr) ?></p><?php endif; ?>
        <div class="pfld">
          <label for="kode">Nomor pesanan</label>
          <div class="pfld__box"><?= $ico('<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/>') ?>
            <input type="text" id="kode" name="kode" placeholder="#RI-20261008-014" autocomplete="off" required></div>
        </div>
        <div class="pfld">
          <label for="telepon">Nomor telepon</label>
          <div class="pfld__box"><?= $ico('<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>') ?>
            <input type="tel" id="telepon" name="telepon" placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="tel" required></div>
        </div>
        <button type="submit" class="pbtn pbtn--primary">Cek pesanan</button>
        <p class="check-form__foot">Punya akun? <a href="login.php">Masuk</a> untuk melihat semua pesanan.</p>
      </form>
    </div>
  </main>
  <?php include __DIR__ . '/../components/footer-public.php'; ?>
</body>
</html>

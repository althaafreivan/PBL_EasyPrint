<?php $nav = $nav ?? 'beranda'; ?>
<header class="appbar">
  <a href="dashboard.php" class="appbar__brand">
    <img src="assets/images/Icon.png" alt="" class="appbar__logo">
    <span>Restu Ibu Print &amp; Fotocopy</span>
  </a>
  <nav class="appbar__nav" aria-label="Menu utama">
    <a href="dashboard.php" <?= $nav === 'beranda' ? 'aria-current="page"' : '' ?>>Beranda</a>
    <a href="dashboard.php#layanan">Layanan</a>
    <a href="#">Tentang</a>
    <a href="tracking.php" <?= $nav === 'cek' ? 'aria-current="page"' : '' ?>>Cek Pesanan</a>
  </nav>
  <div class="appbar__actions">
    <a href="login.php" class="btn-sm btn-sm--outline">Masuk</a>
    <a href="register.php" class="btn-sm btn-sm--primary">Daftar</a>
  </div>
</header>
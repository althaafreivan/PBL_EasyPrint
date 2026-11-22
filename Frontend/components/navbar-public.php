<?php $navPublic = $navPublic ?? ''; $cur = fn($k) => $navPublic === $k ? ' aria-current="page"' : ''; $u = current_user(); ?>
<header class="pubbar">
  <div class="pubbar__in">
    <a href="index.php" class="pubbar__brand">
      <img src="assets/images/Icon.png" alt="" class="pubbar__logo">
      <span>Restu Ibu Print &amp; Fotocopy</span>
    </a>
    <nav class="pubbar__nav" aria-label="Menu utama">
      <a href="index.php"<?= $cur('beranda') ?>>Beranda</a>
      <a href="index.php#layanan"<?= $cur('layanan') ?>>Layanan</a>
      <a href="about.php"<?= $cur('tentang') ?>>Tentang</a>
      <a href="check-order.php"<?= $cur('cek') ?>>Cek Pesanan</a>
    </nav>
    <div class="pubbar__actions">
      <?php if ($u): ?>
        <a href="<?= home_for_role($u['role']) ?>" class="pbtn pbtn--primary">Dashboard</a>
      <?php else: ?>
        <a href="login.php" class="pbtn pbtn--outline">Masuk</a>
        <a href="register.php" class="pbtn pbtn--primary">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</header>

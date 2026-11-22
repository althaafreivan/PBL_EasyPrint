<?php $u = current_user(); ?>
<header class="navbar">
  <a href="index.php" class="navbar__brand">
    <img src="assets/images/Icon.png" alt="" class="navbar__logo">
    <span>EasyPrint</span>
  </a>
  <nav class="navbar__actions">
    <a href="#layanan" class="btn btn--outline">Layanan</a>
    <a href="check-order.php" class="btn btn--outline">Cek Pesanan</a>
    <?php if ($u): ?>
      <a href="<?= home_for_role($u['role']) ?>" class="btn btn--primary">Dashboard</a>
    <?php else: ?>
      <a href="login.php" class="btn btn--outline">Masuk</a>
      <a href="register.php" class="btn btn--primary">Daftar</a>
    <?php endif; ?>
  </nav>
</header>

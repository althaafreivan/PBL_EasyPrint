<?php $u = current_user(); ?>
<header class="navbar">
  <a href="index.php" class="navbar__brand">
    <img src="assets/images/Icon.png" alt="" class="navbar__logo">
    <span>Restu Ibu Print &amp; Fotocopy</span>
  </a>
  <button type="button" class="navbar__toggle" aria-expanded="false" aria-controls="navbarMenu" aria-label="Buka menu">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
  </button>
  <nav class="navbar__actions" id="navbarMenu" aria-label="Menu utama">
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
<script>
  (() => {
    const header = document.querySelector('.navbar');
    const toggle = header.querySelector('.navbar__toggle');
    const closeMenu = () => {
      header.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Buka menu');
    };
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') !== 'true';
      header.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    });
    header.querySelectorAll('.navbar__actions a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeMenu();
    });
  })();
</script>

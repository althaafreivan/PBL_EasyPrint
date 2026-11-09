<?php
$nav   = $nav ?? 'dashboard';
$user  = $user ?? ['nama' => 'Andra Maheswara', 'email' => 'andra@gmail.com', 'inisial' => 'A'];
$notif = require __DIR__ . '/../includes/mock/notifications-costumer.php';
?>
<header class="appbar">
  <a href="dashboard.php" class="appbar__brand">
    <img src="assets/images/Icon.png" alt="" class="appbar__logo">
    <span>Restu Ibu Print &amp; Fotocopy</span>
  </a>

  <nav class="appbar__nav" aria-label="Menu utama">
    <a href="dashboard.php" <?= $nav === 'dashboard' ? 'aria-current="page"' : '' ?>>Dashboard</a>
    <a href="tracking.php" <?= $nav === 'pesanan' ? 'aria-current="page"' : '' ?>>Pesanan Saya</a>
  </nav>

  <div class="appbar__actions">
    <button type="button" class="appbar__bell" id="btnNotif" aria-label="Notifikasi" aria-haspopup="dialog" aria-controls="notifPanel">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
      <span class="appbar__dot" id="notifDot" hidden></span>
    </button>

    <div class="usermenu">
      <button type="button" class="usermenu__btn" id="btnUser" aria-haspopup="menu" aria-expanded="false" aria-controls="userMenu" aria-label="Menu akun"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></button>
      <div class="usermenu__panel" id="userMenu" role="menu" hidden>
        <div class="usermenu__who">
          <strong><?= htmlspecialchars($user['nama']) ?></strong>
          <span><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <a href="tracking.php" role="menuitem">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
          Pesanan saya
        </a>
        <button type="button" role="menuitem" data-notif-open>
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
          Notifikasi
        </button>
        <a href="login.php" role="menuitem" class="is-danger">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l-5-5 5-5"/><path d="M5 12h12"/><path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4"/></svg>
          Keluar
        </a>
      </div>
    </div>
  </div>
</header>

<div class="notif-overlay" id="notifOverlay"></div>
<aside class="notif-panel" id="notifPanel" role="dialog" aria-modal="true" aria-labelledby="notifTitle" aria-hidden="true">
  <header class="notif-head">
    <div>
      <h2 id="notifTitle">Notifikasi</h2>
      <p id="notifSub" aria-live="polite"></p>
    </div>
    <div class="notif-head__actions">
      <button type="button" class="notif-btn" id="notifClear">Tandai dibaca</button>
      <button type="button" class="notif-x" id="notifClose" aria-label="Tutup notifikasi">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
      </button>
    </div>
  </header>
  <div class="notif-list" id="notifList"></div>
</aside>

<script>window.NOTIF = <?= json_encode($notif, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<script src="assets/js/app-nav.js"></script>
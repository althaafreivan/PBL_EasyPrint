<?php
$resNotif = api_get('/api/notifications?limit=40');
$notif = $resNotif['ok'] ? notif_staff($resNotif['data']['data'] ?? [], current_user()['role'] ?? 'admin') : [];
?>
<header class="topbar">
  <div>
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= htmlspecialchars($subtitle) ?></p>
  </div>
  <div class="topbar__right">
    <button type="button" class="bell" id="btnBell" aria-label="Notifikasi" aria-haspopup="dialog" aria-expanded="false" aria-controls="notifPanel">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
      <span class="bell__dot" id="bellDot" hidden></span>
    </button>
    <span class="avatar avatar--sm" title="<?= e(current_user()['nama'] ?? '') ?>"><?= e(inisial(current_user()['nama'] ?? 'RI')) ?></span>
  </div>
</header>

<div class="notif-overlay" id="notifOverlay" hidden></div>
<aside class="notif-panel" id="notifPanel" role="dialog" aria-modal="true" aria-labelledby="notifTitle" aria-hidden="true" hidden>
  <header class="notif-head">
    <div>
      <h2 id="notifTitle">Notifikasi</h2>
      <p id="notifSub" aria-live="polite"></p>
    </div>
    <div class="notif-head__actions">
      <button type="button" class="notif-btn" id="notifClear">Bersihkan</button>
      <button type="button" class="notif-x" id="notifClose" aria-label="Tutup notifikasi">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12"/><path d="M18 6 6 18"/></svg>
      </button>
    </div>
  </header>
  <div class="notif-list" id="notifList" tabindex="-1"></div>
</aside>

<script>window.NOTIF = <?= json_encode($notif, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="assets/js/api.js"></script>
<script src="assets/js/notifications.js"></script>
<?php if ($f = flash()): ?><script>document.addEventListener('DOMContentLoaded', () => EPAPI.toast(<?= json_encode($f['pesan'], JSON_HEX_TAG) ?>, <?= json_encode($f['tipe']) ?>));</script><?php endif; ?>
<?php
$role   = $role ?? (current_user()['role'] ?? 'admin');
$active = $active ?? ($role === 'owner' ? 'dashboard' : 'antrean');
$akun   = current_user() ?? ['nama' => ucfirst($role), 'email' => ''];

$ico = [
  'home'  => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/>',
  'list'  => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/>',
  'cash'  => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
  'box'   => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/>',
  'chart' => '<path d="M4 20V12h4v8"/><path d="M10 20V5h4v15"/><path d="M16 20v-9h4v9"/><path d="M3 20h18"/>',
  'tag'   => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.2"/>',
  'out'   => '<path d="M10 17l-5-5 5-5"/><path d="M5 12h12"/><path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4"/>',
];
$menus = [
  'admin' => [
    'dashboard' => ['Dashboard',            'admin-dashboard.php', 'home'],
    'antrean'   => ['Antrean Pesanan',      'admin-orders.php',    'list'],
    'kasir'     => ['Kasir & Pengambilan',  'admin-cashier.php',   'cash'],
    'stok'      => ['Pemantauan Stok',      'admin-stock.php',     'box'],
  ],
  'owner' => [
    'dashboard' => ['Dashboard Eksekutif',     'owner-dashboard.php', 'home'],
    'laporan'   => ['Laporan & Analitik',      'owner-report.php',    'chart'],
    'bahan'     => ['Manajemen Bahan',         'owner-inventory.php', 'box'],
    'layanan'   => ['Master Layanan & Harga',  'owner-services.php',  'tag'],
  ],
];
$menu = $menus[$role] ?? $menus['admin'];
$labelPeran = ['admin' => 'Admin / Operator', 'owner' => 'Owner'][$role] ?? $role;
?>
<aside class="sidebar">
  <a href="<?= home_for_role($role) ?>" class="sidebar__brand">
    <img src="assets/images/Icon.png" alt="" class="sidebar__logo">
    <span>EasyPrint</span>
  </a>

  <p class="sidebar__label">WORKSPACE</p>
  <nav class="sidebar__nav" aria-label="Menu <?= e($role) ?>">
    <?php foreach ($menu as $key => [$label, $href, $icon]): $on = $key === $active; ?>
      <a href="<?= $href ?>" class="sidebar__link<?= $on ? ' is-active' : '' ?>" <?= $on ? 'aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $ico[$icon] ?></svg>
        <?= e($label) ?>
      </a>
    <?php endforeach; ?>
    <a href="logout.php" class="sidebar__link sidebar__link--out">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $ico['out'] ?></svg>
      Keluar
    </a>
  </nav>

  <div class="sidebar__user">
    <span class="avatar"><?= e(inisial($akun['nama'])) ?></span>
    <div>
      <strong><?= e($akun['nama']) ?></strong>
      <span><?= e($labelPeran) ?> · <?= e($akun['email']) ?></span>
    </div>
  </div>
</aside>

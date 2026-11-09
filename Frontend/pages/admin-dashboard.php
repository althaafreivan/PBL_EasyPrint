<?php
require_once __DIR__ . '/../includes/helpers.php';
$data = require __DIR__ . '/../includes/mock/operations.php';
$active = 'dashboard'; $role = 'admin'; $pageTitle = 'Dashboard Operasional';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Dashboard Operasional'; $subtitle = 'Ringkasan pekerjaan harian Admin / Operator.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <section class="opk" aria-label="Ringkasan pesanan">
      <?php foreach ($data['ringkasan'] as $r): ?>
        <article class="card opk__item">
          <div><span class="opk__label"><?= e($r['label']) ?></span><strong class="opk__value"><?= e($r['nilai']) ?></strong></div>
          <span class="opk__dot opk__dot--<?= $r['tipe'] ?>" aria-hidden="true"></span>
        </article>
      <?php endforeach; ?>
    </section>

    <h2 class="ops-title">Prioritas Pekerjaan</h2>
    <div class="ops">
      <section class="card ops__table" aria-label="Pesanan prioritas">
        <table class="opt">
          <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Masuk</th><th scope="col" colspan="2">Status</th></tr></thead>
          <tbody>
            <?php foreach ($data['prioritas'] as $p): ?>
              <tr>
                <td class="opt__code"><?= e($p['kode']) ?></td>
                <td><?= e($p['pelanggan']) ?></td>
                <td><?= e($p['masuk']) ?></td>
                <td><span class="badge badge--<?= strtolower($p['status']) ?>"><?= e($p['status']) ?></span></td>
                <td><a href="admin-orders.php" class="opt__link">Detail →</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <aside class="card ops__stock" aria-label="Ketersediaan bahan">
        <h3>Ketersediaan Bahan</h3>
        <p>Informasi untuk verifikasi pesanan</p>
        <ul>
          <?php foreach ($data['bahan'] as $b): ?>
            <li><span class="dotx dotx--<?= $b['aman'] ? 'ok' : 'bad' ?>" aria-hidden="true"></span><div><strong><?= e($b['nama']) ?></strong><span><?= e($b['info']) ?></span></div></li>
          <?php endforeach; ?>
        </ul>
      </aside>
    </div>

    <section class="card remind" aria-label="Pengingat operasional">
      <h3>Pengingat Operasional</h3>
      <?php foreach ($data['pengingat'] as $t): ?><p><?= e($t) ?></p><?php endforeach; ?>
    </section>
  </div>
</body>
</html>

<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['admin']);
$res = api_get('/api/stok');
$resLog = api_get('/api/stok/riwayat?limit=8');
$bahan = array_map(fn($b) => [
  'id' => (int) $b['id'], 'nama' => $b['nama_bahan'], 'satuan' => $b['satuan'],
  'stok' => (float) $b['stok'], 'reorder' => (float) $b['reorder_point'], 'status' => $b['status'],
], $res['data']['data'] ?? []);
$kritis = count(array_filter($bahan, fn($b) => $b['stok'] <= $b['reorder']));
$masuk = array_sum(array_map(fn($b) => (float) $b['masuk_bulan_ini'], $res['data']['data'] ?? []));
$transaksiMasuk = count(array_filter($resLog['data']['data'] ?? [], fn($r) => (float) $r['jumlah'] > 0));
$active = 'stok'; $role = 'admin'; $pageTitle = 'Stok Bahan Baku';

$kpi = [
  ['Bahan kritis', $kritis . ' item', 'red', $kritis ? 'Perlu restock' : 'Aman'],
  ['Total item',   count($bahan) . ' SKU', 'blue', null],
  ['Stok masuk',   angka($masuk) . ' unit', 'green', 'Bulan ini'],
];

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';
?>
  <div class="admin__main">
    <?php $title = 'Stok Bahan Baku'; $subtitle = 'Pantau kertas, tinta, dan bahan operasional.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>
    <section class="kpis kpis--3" aria-label="Ringkasan stok">
      <?php foreach ($kpi as [$label, $nilai, $warna, $pill]): ?>
        <article class="card kpi">
          <span class="kpi__label"><?= $label ?></span>
          <span class="kpi__icon kpi__icon--<?= $warna ?>" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><rect x="4" y="11" width="4" height="9" rx="1"/><rect x="10" y="4" width="4" height="16" rx="1"/><rect x="16" y="8" width="4" height="12" rx="1"/></svg>
          </span>
          <strong class="kpi__value"><?= $nilai ?></strong>
          <?php if ($pill): ?><span class="pill pill--soft"><?= $pill ?></span><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>

    <h2 class="sec-title">Inventaris</h2>
    <div class="toolbar">
      <label class="search search--md">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-3.5-3.5"/></svg>
        <input type="search" id="cari" placeholder="Cari bahan..." autocomplete="off" aria-label="Cari bahan">
      </label>
      <button type="button" class="btn-main" id="btnRestock">+ Restock</button>
    </div>

    <section class="card table-card table-card--stock" aria-label="Daftar stok bahan">
      <table class="table table--plain">
        <colgroup><col style="width:26%"><col style="width:12%"><col style="width:12%"><col style="width:18%"><col style="width:16%"><col></colgroup>
        <thead>
          <tr>
            <th scope="col">Nama bahan</th>
            <th scope="col">Stok</th>
            <th scope="col">Satuan</th>
            <th scope="col">Reorder point</th>
            <th scope="col">Status</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody id="stokBody"></tbody>
      </table>
      <p class="table__empty" id="emptyStok" hidden>Bahan tidak ditemukan.</p>
    </section>

    <aside class="note-box">
      <span class="label">Log otomatis</span>
      <p>Stok dipotong otomatis (berdasarkan konfigurasi bahan per layanan) saat pesanan diubah menjadi <b>Diproses</b>. Mutasi terbaru:</p>
      
<ul class="stk-log">
  <?php foreach ($resLog['data']['data'] ?? [] as $r): ?>
    <?php
      $jumlah = (float) $r['jumlah'];
      $positif = $jumlah > 0;
      $negatif = $jumlah < 0;
      $kelas = $positif ? 'in' : ($negatif ? 'out' : '');
      $tanda = $positif ? '+' : ($negatif ? '−' : '');
    ?>
    <li>
      <time><?= e(tgl_jam($r['created_at'])) ?></time>
      <span class="stk-log__q <?= $kelas ? 'stk-log__q--' . $kelas : '' ?>">
        <?= $tanda ?><?= e(angka(abs($jumlah), 4)) ?> <?= e($r['satuan']) ?>
      </span>
      <span>
        <strong><?= e($r['nama_bahan']) ?></strong>
        · <?= e($r['keterangan'] ?: ucfirst($r['jenis'])) ?>
        <?= $r['oleh'] ? ' · ' . e(explode(' ', $r['oleh'])[0]) : '' ?>
      </span>
    </li>
  <?php endforeach; ?>
</ul>

    </aside>

    <dialog class="dlg" id="dlgStok" aria-labelledby="dlgStokTitle">
      <form id="formStok">
        <h2 id="dlgStokTitle">Restock bahan</h2>
        <div class="fld"><label for="stokBahan">Bahan</label><select id="stokBahan"></select></div>
        <div class="fld"><label for="stokJumlah" id="stokJumlahLabel">Jumlah tambahan</label><input type="number" id="stokJumlah" min="0" step="any" value="10" required></div>
        <div class="fld"><label for="stokKet">Keterangan</label><input type="text" id="stokKet" maxlength="255" placeholder="Contoh: pembelian dari supplier / hasil stock opname"></div>
        <p class="field-error" id="stokErr" role="alert" hidden></p>
        <div class="dlg__actions">
          <button type="button" class="btn-ghost" id="btnBatalStok">Batal</button>
          <button type="submit" class="btn-main">Simpan</button>
        </div>
      </form>
    </dialog>
  </div>

  <script>window.BAHAN = <?= json_encode($bahan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
  <script src="assets/js/admin-stock.js"></script>
</body>
</html>
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['owner']);
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$res = api_get('/api/laporan/transaksi?' . http_build_query(['dari' => $dari, 'sampai' => $sampai]));
$resDash = api_get('/api/dashboard');
$L = $res['data'];
$R = $L['ringkasan'] ?? [];
$singkat = fn($n) => (float) $n >= 1e6
  ? 'Rp ' . str_replace('.', ',', rtrim(rtrim(number_format((float) $n / 1e6, 2, '.', ''), '0'), '.')) . ' jt'
  : rupiah($n);
$data = [
  'periode' => $L['periode'] ?? ['dari' => $dari, 'sampai' => $sampai],
  'kpi' => [
    'penjualan' => $singkat($R['penjualan'] ?? 0),
    'pesanan'   => (string) ($R['pesanan_selesai'] ?? 0),
    'masuk'     => (int) ($R['pesanan_masuk'] ?? 0),
    'ditolak'   => (int) ($R['pesanan_ditolak'] ?? 0),
    'bahan'     => $singkat($R['nilai_bahan_keluar'] ?? 0),
    'teratas'   => $L['layanan_teratas'][0]['nama_layanan'] ?? '-',
  ],
  'transaksi' => array_map(fn($t) => [
    'id' => (int) $t['pesanan_id'], 'tanggal' => substr($t['created_at'], 0, 10), 'invoice' => 'INV-' . $t['kode_pesanan'],
    'pelanggan' => $t['nama_pelanggan'], 'layanan' => $t['layanan'], 'total' => (float) $t['total_harga'],
  ], $L['transaksi'] ?? []),
  'sirkulasi' => $L['sirkulasi_bahan'] ?? [],
  'teratas' => $L['layanan_teratas'] ?? [],
  'bulanan' => array_slice(array_reverse($resDash['data']['rekap_omzet'] ?? []), 0, 6),
];
$active = 'laporan'; $role = 'owner'; $pageTitle = 'Laporan & Analitik';

include __DIR__ . '/../components/admin-head.php';
include __DIR__ . '/../components/sidebar-admin.php';

$ikon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><rect x="4" y="11" width="4" height="9" rx="1"/><rect x="10" y="4" width="4" height="16" rx="1"/><rect x="16" y="8" width="4" height="12" rx="1"/></svg>';
?>
  <div class="admin__main">
    <?php $title = 'Laporan & Analitik'; $subtitle = 'Evaluasi transaksi penjualan dan sirkulasi bahan.'; include __DIR__ . '/../components/topbar-admin.php'; ?>

    <h2 class="sec-title sec-title--sm">Filter laporan</h2>
    <?php if (!$res['ok']): ?><p class="ep-alert ep-alert--err" role="alert"><?= e(api_message($res)) ?></p><?php endif; ?>
    <form class="filter-bar" id="filterForm" method="get" action="owner-report.php">
      <div class="fld">
        <label for="dari">Dari</label>
        <input type="date" id="dari" name="dari" value="<?= e($data['periode']['dari']) ?>" max="<?= date('Y-m-d') ?>">
      </div>
      <div class="fld">
        <label for="sampai">Sampai</label>
        <input type="date" id="sampai" name="sampai" value="<?= e($data['periode']['sampai']) ?>" max="<?= date('Y-m-d') ?>">
      </div>
      <button type="submit" class="btn-main btn-main--lg">Terapkan</button>
      <button type="button" class="btn-ghost btn-ghost--export" id="btnEkspor">Ekspor PDF / Excel</button>
    </form>
    <p class="filter-error" id="filterError" role="alert" hidden>Tanggal "Dari" tidak boleh setelah tanggal "Sampai".</p>

    <section class="kpis kpis--rep" aria-label="Ringkasan laporan">
      <article class="card kpi">
        <span class="kpi__label">Penjualan</span>
        <span class="kpi__icon kpi__icon--green" aria-hidden="true"><?= $ikon ?></span>
        <strong class="kpi__value"><?= e($data['kpi']['penjualan']) ?></strong>
        <span class="pill pill--soft">Selesai &amp; diambil</span>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Pesanan selesai</span>
        <span class="kpi__icon kpi__icon--blue" aria-hidden="true"><?= $ikon ?></span>
        <strong class="kpi__value"><?= e($data['kpi']['pesanan']) ?></strong>
        <span class="pill pill--soft"><?= $data['kpi']['masuk'] ?> masuk · <?= $data['kpi']['ditolak'] ?> ditolak</span>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Nilai bahan keluar</span>
        <span class="kpi__icon kpi__icon--amber" aria-hidden="true"><?= $ikon ?></span>
        <strong class="kpi__value"><?= e($data['kpi']['bahan']) ?></strong>
        <span class="pill pill--soft">Harga pokok bahan</span>
      </article>
      <article class="card kpi">
        <span class="kpi__label">Layanan teratas</span>
        <strong class="kpi__value kpi__value--sm"><?= e($data['kpi']['teratas']) ?></strong>
      </article>
    </section>

    <section class="card panel rep-table" aria-label="Rincian transaksi">
      <h2>Rincian transaksi <small class="rep-count"><?= count($data['transaksi']) ?> transaksi · <?= e(tgl($data['periode']['dari'])) ?> – <?= e(tgl($data['periode']['sampai'])) ?></small></h2>
      <div class="ep-scroll">
      <table class="table table--plain table--tiny">
        <colgroup><col style="width:15.7%"><col style="width:17.6%"><col style="width:18.7%"><col style="width:33%"><col></colgroup>
        <thead>
          <tr>
            <th scope="col">Tanggal</th>
            <th scope="col">Invoice</th>
            <th scope="col">Pelanggan</th>
            <th scope="col">Layanan</th>
            <th scope="col">Total</th>
          </tr>
        </thead>
        <tbody id="trxBody"></tbody>
      </table>
      </div>
      <p class="table__empty" id="emptyTrx" hidden>Tidak ada transaksi pada periode ini.</p>
    </section>

    <div class="rep-grid">
      <section class="card panel" aria-label="Sirkulasi bahan">
        <h2>Sirkulasi bahan</h2>
        <table class="table table--plain table--tiny rep-circ">
          <thead><tr><th scope="col">Bahan</th><th scope="col">Masuk</th><th scope="col">Keluar</th><th scope="col">Stok kini</th></tr></thead>
          <tbody>
            <?php foreach ($data['sirkulasi'] as $c): ?>
              <tr><td><?= e($c['nama_bahan']) ?></td><td class="rep-in">+<?= e(angka($c['masuk'])) ?></td><td class="rep-out">−<?= e(angka($c['keluar'])) ?></td><td><?= e(angka($c['stok_saat_ini'])) ?> <?= e($c['satuan']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
      <section class="card panel" aria-label="Layanan teratas & rekap bulanan">
        <h2>Layanan teratas</h2>
        <ul class="circ">
          <?php foreach ($data['teratas'] as $t): ?>
            <li><span><?= (int) $t['peringkat'] ?>. <?= e($t['nama_layanan']) ?> <small>(<?= e(angka($t['kuantitas'], 0)) ?> <?= e($t['satuan']) ?>)</small></span><strong><?= rupiah($t['nilai']) ?></strong></li>
          <?php endforeach; ?>
          <?php if (!$data['teratas']): ?><li><span>Belum ada transaksi selesai.</span></li><?php endif; ?>
        </ul>
        <h2 style="margin-top:22px">Rekap bulanan</h2>
        <ul class="circ">
          <?php foreach ($data['bulanan'] as $b): ?>
            <li><span><?= e(BULAN_PANJANG[(int) $b['bulan']] . ' ' . $b['tahun']) ?> <small>(<?= (int) $b['total_pesanan'] ?> pesanan)</small></span><strong><?= rupiah($b['pendapatan']) ?></strong></li>
          <?php endforeach; ?>
        </ul>
        <p class="rep-note">Laporan dapat diekspor untuk kebutuhan pembukuan dan evaluasi berkala pemilik.</p>
      </section>
    </div>

    <dialog class="dlg" id="dlgEkspor" aria-labelledby="dlgEksporTitle">
      <div class="dlg__body">
        <h2 id="dlgEksporTitle">Ekspor laporan</h2>
        <p class="rep-note">Pilih format untuk rincian transaksi pada periode yang ditampilkan.</p>
        <div class="export-opts">
          <button type="button" class="btn-main" id="btnPdf">PDF (cetak halaman)</button>
          <button type="button" class="btn-main" id="btnCsv">Excel (.csv)</button>
        </div>
        <div class="dlg__actions"><button type="button" class="btn-ghost" id="btnBatalEkspor">Batal</button></div>
      </div>
    </dialog>
  </div>

  <script>
    window.TRANSAKSI = <?= json_encode($data['transaksi'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    window.PERIODE = <?= json_encode($data['periode']) ?>;
  </script>
  <script src="assets/js/owner-report.js"></script>
</body>
</html>
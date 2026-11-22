<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['pelanggan']);
$nav = 'pesanan'; $pageTitle = 'Pesanan Berhasil';
$ico = fn($p, $s = 16) => ico($p, $s);

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$res = $id ? api_get("/api/orders/$id") : ['ok' => false];
if (!$res['ok']) { header('Location: orders.php'); exit; }
$p = $res['data']['pesanan'];
$namaLayanan = implode(' + ', array_column($res['data']['items'], 'nama_layanan'));
include __DIR__ . '/../components/head-app.php';
include __DIR__ . '/../components/navbar-app.php';
?>
  <main class="page">
    <section class="card success" aria-labelledby="okTitle">
      <span class="success__tick"><?= $ico('<path d="m5 12 5 5L20 7"/>', 24) ?></span>
      <h1 id="okTitle">Pesanan berhasil dikirim!</h1>
      <p class="success__lead">Admin akan memeriksa berkas Anda. Notifikasi muncul di ikon lonceng setiap kali status berubah.</p>

      <div class="success__sum">
        <div>Nomor pesanan<strong class="success__code"><?= e(kode($p['kode_pesanan'])) ?></strong></div>
        <div>Layanan<strong><?= e($namaLayanan) ?></strong></div>
        <div>Total biaya<strong><?= rupiah($p['total_harga']) ?></strong></div>
        <span class="pill <?= kelas_status($p['status']) ?>"><?= e(label_status($p['status'])) ?></span>
      </div>

      <ol class="next3" aria-label="Langkah berikutnya">
        <li><span class="ico"><?= $ico('<circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>', 15) ?></span><strong>1. Verifikasi</strong>Admin memeriksa berkas &amp; stok bahan</li>
        <li><span class="ico"><?= $ico('<path d="M7 9V4h10v5"/><rect x="4" y="9" width="16" height="8" rx="2"/><path d="M7 14h10v6H7z"/>', 15) ?></span><strong>2. Diproses</strong>Pesanan dicetak &amp; difinishing</li>
        <li><span class="ico"><?= $ico('<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>', 15) ?></span><strong>3. Ambil &amp; bayar</strong>Tunjukkan nomor pesanan di kasir</li>
      </ol>

      <div class="success__actions">
        <a href="<?= link_lacak((int) $p['id']) ?>" class="btn-fill">Lihat status pesanan</a>
        <a href="dashboard.php" class="btn-outline">Kembali ke dashboard</a>
      </div>
    </section>
  </main>
  <script>sessionStorage.removeItem('ep_last');</script>
</body>
</html>

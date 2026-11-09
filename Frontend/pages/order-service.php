<?php
$nav = 'pesanan';
$layanan = [
  'print'    => ['Print Dokumen', 'PDF, Word, dan dokumen lainnya',                  'Rp 500',   'Mulai Rp 500',   '<path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="7" rx="1"/><path d="M6 17H4a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-2"/>'],
  'fotokopi' => ['Fotokopi',      'Hitam-putih maupun warna',                        'Rp 300',   'Mulai Rp 300',   '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>'],
  'jilid'    => ['Jilid',         'Finishing dokumen: jilid spiral & soft cover',    'Rp 5.000', 'Mulai Rp 5.000', '<rect x="5" y="4" width="14" height="16" rx="2"/><path d="M9 8h6M9 12h6"/>'],
];
$dipilih = $_GET['layanan'] ?? 'print';
if (!isset($layanan[$dipilih])) { $dipilih = 'print'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Buat Pesanan - Restu Ibu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/pill.css">
  <link rel="stylesheet" href="assets/css/order-invoice.css">
</head>
<body class="app">
  <?php include __DIR__ . '/../components/navbar-app.php'; ?>

  <main class="page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="dashboard.php">Beranda</a> / <a href="tracking.php">Pesanan Saya</a> / <span aria-current="page">Buat Pesanan</span>
    </nav>
    <h1 class="greet">Buat Pesanan</h1>
    <p class="greet__sub">Pilih layanan yang ingin dipesan.</p>

    <ol class="wizard card" aria-label="Langkah pesanan">
      <li class="is-current" aria-current="step">
        <span class="wizard__dot" aria-hidden="true">1</span>
        <span><strong>Layanan dipilih</strong><small>Pilih jenis layanan</small></span>
      </li>
      <li>
        <span class="wizard__dot" aria-hidden="true">2</span>
        <span><strong>Detail pesanan</strong><small>Spesifikasi &amp; berkas</small></span>
      </li>
      <li>
        <span class="wizard__dot" aria-hidden="true">3</span>
        <span><strong>Review &amp; kirim</strong><small>Konfirmasi pesanan</small></span>
      </li>
    </ol>

    <form class="order-grid order-grid--pick" id="pickForm">
      <section class="card of-card" aria-label="Pilih layanan">
        <h2>Pilih layanan</h2>
        <div class="pick-grid" role="radiogroup" aria-label="Pilih layanan">
          <?php foreach ($layanan as $key => [$nama, $desc, $harga, $mulai, $ikon]): ?>
            <label class="pick">
              <input type="radio" name="layanan" value="<?= $key ?>" data-nama="<?= htmlspecialchars($nama) ?>" data-mulai="<?= $mulai ?>" <?= $key === $dipilih ? 'checked' : '' ?>>
              <span class="pick__radio" aria-hidden="true"></span>
              <span class="pick__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $ikon ?></svg></span>
              <span class="pick__text"><strong><?= htmlspecialchars($nama) ?></strong><small><?= htmlspecialchars($desc) ?></small></span>
              <span class="pick__price"><?= $harga ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </section>

      <aside class="card rs" aria-label="Ringkasan">
        <h2>Ringkasan</h2>
        <p class="rs__lbl">Layanan yang dipilih</p>
        <div class="chosen">
          <span class="chosen__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="7" rx="1"/><path d="M6 17H4a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-2"/></svg>
          </span>
          <div><strong id="chosenNama">Print Dokumen</strong><small id="chosenHarga">Mulai Rp 500</small></div>
        </div>
        <button type="submit" class="btn-send">Lanjut ke detail →</button>
        <a href="dashboard.php" class="btn-pill btn-pill--block">Batal</a>
      </aside>
    </form>
  </main>

  <script>
    (() => {
      const radios = document.querySelectorAll('input[name="layanan"]');
      const tampil = () => {
        const r = document.querySelector('input[name="layanan"]:checked');
        document.getElementById('chosenNama').textContent = r.dataset.nama;
        document.getElementById('chosenHarga').textContent = r.dataset.mulai;
      };
      radios.forEach((r) => r.addEventListener('change', tampil));
      tampil();
      document.getElementById('pickForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const key = document.querySelector('input[name="layanan"]:checked').value;
        sessionStorage.setItem('ep_draft', JSON.stringify({ layanan: key }));
        location.href = 'order-form.php';
      });
    })();
  </script>
</body>
</html>
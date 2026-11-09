<?php $nav = 'pesanan'; ?>
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
    <p class="greet__sub">Lengkapi spesifikasi cetak dan unggah dokumen untuk mendapatkan estimasi harga.</p>

    <!-- LANGKAH -->
    <ol class="wizard card" aria-label="Langkah pesanan">
      <li class="is-done">
        <span class="wizard__dot" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
        </span>
        <span><strong>Layanan dipilih</strong><small id="wizLayanan">Print Dokumen</small></span>
      </li>
      <li class="is-current" aria-current="step">
        <span class="wizard__dot" aria-hidden="true">2</span>
        <span><strong>Detail pesanan</strong><small>Spesifikasi &amp; berkas</small></span>
      </li>
      <li>
        <span class="wizard__dot" aria-hidden="true">3</span>
        <span><strong>Review &amp; kirim</strong><small>Konfirmasi pesanan</small></span>
      </li>
    </ol>

    <form class="order-grid" id="orderForm" novalidate>
      <!-- KIRI -->
      <section class="card of-card" aria-label="Detail pesanan">
        <h2>Layanan yang dipilih</h2>
        <div class="svc-box">
          <span class="svc-box__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
          </span>
          <div class="svc-box__text">
            <strong id="svcNama">Print Dokumen</strong>
            <small id="svcDesc">Cetak PDF, Word, dan dokumen lainnya</small>
          </div>
          <div class="svc-box__price">
            <strong id="svcPrice">Rp 500</strong>
            <small>per lembar</small>
          </div>
        </div>

        <h2>Spesifikasi cetak</h2>
        <div class="spec">
          <div>
            <label class="of-label" for="lembar">Jumlah lembar</label>
            <input class="of-control" type="number" id="lembar" min="1" value="40">
          </div>
          <div>
            <label class="of-label" for="eksemplar">Jumlah eksemplar</label>
            <input class="of-control" type="number" id="eksemplar" min="1" value="1">
          </div>
          <div>
            <label class="of-label" for="kertas">Ukuran kertas</label>
            <select class="of-control" id="kertas">
              <option value="A4">A4</option>
              <option value="F4">F4</option>
              <option value="A3">A3</option>
            </select>
          </div>

          <div>
            <span class="of-label" id="lblMode">Mode cetak</span>
            <div class="seg" role="radiogroup" aria-labelledby="lblMode">
              <label><input type="radio" name="mode" value="bw" checked><span>Hitam-putih</span></label>
              <label><input type="radio" name="mode" value="color"><span>Warna</span></label>
            </div>
          </div>
          <div>
            <label class="of-label" for="jilid">Jilid / finishing</label>
            <select class="of-control" id="jilid">
              <option value="none">Tanpa jilid</option>
              <option value="spiral" selected>Spiral</option>
              <option value="hard">Hard cover</option>
            </select>
          </div>
          <div>
            <span class="of-label" id="lblDuplex">Cetak dua sisi</span>
            <div class="radios" role="radiogroup" aria-labelledby="lblDuplex">
              <label><input type="radio" name="duplex" value="tidak" checked> Tidak</label>
              <label><input type="radio" name="duplex" value="ya"> Ya</label>
            </div>
          </div>
        </div>

        <h2>Berkas dokumen</h2>
        <div class="dz" id="dropzone">
          <span class="dz__check" id="dzCheck" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
          </span>
          <span class="dz__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V5"/><path d="m7 10 5-5 5 5"/><path d="M5 20h14"/></svg>
          </span>
          <div class="dz__text">
            <strong>Tarik dan letakkan berkas di sini</strong>
            <small>atau pilih dari perangkat · PDF, DOCX · Maks. 20 MB</small>
          </div>
          <input type="file" id="berkas" class="sr-only" accept=".pdf,.doc,.docx">
          <label for="berkas" class="btn-pill">Pilih berkas</label>
        </div>
        <p class="field-error" id="berkasError" role="alert" hidden></p>

        <h2>Catatan untuk operator</h2>
        <input class="of-control" type="text" id="catatan" value="Mohon potong dan jilid dengan rapi" aria-label="Catatan untuk operator">
      </section>

      <!-- KANAN -->
      <aside class="card rs" aria-label="Ringkasan pesanan">
        <div class="rs__head">
          <h2>Ringkasan pesanan</h2>
          <span class="pill pill--blue pill--caps">Draf pesanan</span>
        </div>

        <span class="rs__lbl">Layanan</span>
        <p class="rs__name" id="sumLayanan">Print Dokumen A4</p>
        <p class="rs__lbl" id="sumSpek">Hitam-putih · Jilid spiral</p>

        <hr>
        <span class="rs__lbl rs__lbl--b">Berkas</span>
        <div class="rs-file">
          <span class="rs-file__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
          </span>
          <div>
            <strong id="fileName">materi-kuliah.pdf</strong>
            <small id="fileMeta">2,4 MB · PDF</small>
          </div>
        </div>

        <hr>
        <h3 class="rs__sub">Rincian harga</h3>
        <div class="rs-row"><span id="rowCetakLabel">40 lembar × Rp 500</span><strong id="rowCetak">Rp 20.000</strong></div>
        <div class="rs-row" id="rowJilidWrap"><span id="rowJilidLabel">Jilid spiral × 1</span><strong id="rowJilid">Rp 5.000</strong></div>

        <hr>
        <div class="rs-row"><span>Subtotal</span><strong id="sumSubtotal">Rp 25.000</strong></div>
        <div class="rs-row"><span>Biaya tambahan</span><strong>Rp 0</strong></div>

        <div class="rs-total">
          <small>Estimasi total</small>
          <strong id="sumTotal">Rp 25.000</strong>
        </div>
        <p class="rs-note">Harga final akan dikonfirmasi admin setelah memeriksa berkas dan ketersediaan bahan.</p>

        <button type="submit" class="btn-send">Selanjutnya</button>
        <p class="rs-foot">Tinjau rincian pesanan di langkah berikutnya.</p>
      </aside>
    </form>
  </main>

  <script src="assets/js/order-form.js"></script>
</body>
</html>
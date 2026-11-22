<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$judulKategori = [
    'cetak' => 'Print Dokumen',
    'fotokopi' => 'Fotokopi',
    'finishing' => 'Jilid & Finishing'
];

$steps = [
    [
        'img'   => 'Step1_Container.png',
        'title' => 'Daftar atau Masuk',
        'desc'  => 'Buat akun baru atau masuk untuk menggunakan layanan secara cepat',
    ],
    [
        'img'   => 'Step2_Container.png',
        'title' => 'Isi Formulir Pesanan',
        'desc'  => 'Unggah dokumen Anda, pilih opsi cetak, dan isi detail pesanan dengan mudah.',
    ],
    [
        'img'   => 'Step3_Container.png',
        'title' => 'Pesanan Selesai',
        'desc'  => 'Tunggu konfirmasi dari admin, dan Anda akan langsung tahu saat pesanan siap diambil di outlet.',
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Easyprint - Solusi Cetak Dokumen</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/integrasi.css">
</head>

<body>

  <?php include __DIR__ . '/../components/navbar.php'; ?>

  <main>

    <!-- HERO -->
    <section class="hero">
      <div class="hero__text">

        <span class="hero__badge">
          <svg viewBox="0 0 24 24" width="22" height="22"
            fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M21 11.1V12a9 9 0 1 1-5.3-8.2"/>
            <path d="m9 11 3 3L22 4"/>
          </svg>
          Pesan cetak online, ambil di outlet
        </span>

        <h1>
          Solusi Cetak Dokumen <em>Cepat</em> &amp; Terintegrasi
        </h1>

        <p>
          Sistem informasi ini dirancang untuk membantu pengelolaan
          proses bisnis pada UMKM Percetakan, khususnya dalam pencatatan
          pesanan, transaksi penjualan, dan pengelolaan stok.
          Sistem dikembangkan berdasarkan hasil observasi terhadap
          proses bisnis yang berjalan pada UMKM tersebut.
        </p>

        <a href="<?= current_user() ? home_for_role(current_user()['role']) : 'login.php' ?>"
           class="btn btn--primary btn--lg">

          Mulai Cetak Sekarang

          <svg viewBox="0 0 24 24" width="18" height="18"
            fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M7 17 17 7"/>
            <path d="M8 7h9v9"/>
          </svg>

        </a>
      </div>

      <div class="hero__media">

        <img src="assets/images/Hero_Media.png"
             alt="Printer HP di ruang cetak"
             class="hero__img">

        <div class="chip chip--stock">
          <span class="chip__icon chip__icon--chart" aria-hidden="true"></span>
          <span>Pembayaran:<br>Tunai di outlet</span>
        </div>

        <div class="chip chip--status">
          <span class="chip__icon chip__icon--ok" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="20" height="20"
              fill="none" stroke="#fff" stroke-width="3"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="m5 12 5 5L20 7"/>
            </svg>
          </span>
          <span>Status:<br>Selesai 100%</span>
        </div>

      </div>
    </section>


    <!-- STEPS -->
    <section class="steps" aria-label="Cara kerja">

      <span class="deco deco--circle-left" aria-hidden="true"></span>
      <span class="deco deco--circle-right" aria-hidden="true"></span>
      <span class="deco deco--wave-top" aria-hidden="true"></span>
      <span class="deco deco--wave-bottom" aria-hidden="true"></span>

      <div class="steps__row">

        <?php foreach ($steps as $i => $step): $n = $i + 1; ?>

          <article class="step">

            <div class="step__art">
              <img src="assets/images/<?= $step['img'] ?>" alt="">
            </div>

            <div class="step__card">
              <h3>
                <?= $n ?>. <?= htmlspecialchars($step['title']) ?>
              </h3>

              <p><?= htmlspecialchars($step['desc']) ?></p>

              <span class="step__badge"><?= $n ?></span>
            </div>

          </article>

          <?php if ($n < count($steps)): ?>
            <svg class="steps__arrow"
                 viewBox="0 0 60 24"
                 width="60" height="24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2.5"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 aria-hidden="true">

              <path d="M2 12h54"/>
              <path d="m46 3 10 9-10 9"/>

            </svg>
          <?php endif; ?>

        <?php endforeach; ?>

      </div>
    </section>


    <!-- KATALOG LAYANAN DARI REST API MENGGUNAKAN FETCH API -->
    <section class="lp-katalog"
             id="layanan"
             aria-labelledby="katalogTitle">

      <div class="lp-katalog__head">

        <h2 id="katalogTitle">
          Katalog Layanan &amp; Harga Dasar
        </h2>

        <p>
          Harga per satuan. Estimasi total dihitung otomatis
          saat Anda membuat pesanan.
        </p>

      </div>

      <!-- Tempat kartu katalog dibuat oleh JavaScript -->
      <div class="lp-katalog__grid"
           id="katalogGrid"
           aria-live="polite">

        <p class="lp-katalog__loading" id="katalogLoading">
          Memuat katalog layanan...
        </p>

      </div>

      <div class="lp-info">

        <div>
          <strong>Lokasi outlet</strong>
          <span><?= e(ALAMAT_TOKO) ?></span>
        </div>

        <div>
          <strong>Jam operasional</strong>
          <span><?= e(JAM_BUKA) ?> · <?= e(HARI_BUKA) ?></span>
        </div>

        <div>
          <strong>Pembayaran</strong>
          <span>Tunai / QRIS di outlet saat pengambilan</span>
        </div>

      </div>

    </section>

  </main>

  <?php include __DIR__ . '/../components/footer.php'; ?>


  <!-- Helper REST API yang sudah tersedia di project -->
  <script src="assets/js/api.js"></script>

  <!-- Fetch API katalog layanan -->
  <script>
  document.addEventListener('DOMContentLoaded', async function () {

      const grid = document.getElementById('katalogGrid');

      const judulKategori = {
          cetak: 'Print Dokumen',
          fotokopi: 'Fotokopi',
          finishing: 'Jilid & Finishing'
      };

      try {

          // Request GET ke endpoint layanan melalui API proxy.
          // Request berjalan otomatis ketika halaman dimuat.
          const response = await EPAPI.call('/api/layanan', {
              method: 'GET'
          });

          if (!response.ok) {
              throw new Error(
                  response.data.message || 'Gagal mengambil data layanan.'
              );
          }

          // Backend mengembalikan { status: success, data: [...] }
          const layanan = response.data.data;

          if (!Array.isArray(layanan) || layanan.length === 0) {
              grid.innerHTML = `
                  <p class="lp-katalog__err">
                      Belum ada layanan yang tersedia.
                  </p>
              `;
              return;
          }

          // Kelompokkan layanan berdasarkan kategori.
          const katalog = {};

          layanan.forEach(function (item) {

              const kategori = item.kategori;

              if (!katalog[kategori]) {
                  katalog[kategori] = [];
              }

              katalog[kategori].push(item);

          });

          // Format harga Rupiah.
          const formatRupiah = function (harga) {
              return 'Rp ' + new Intl.NumberFormat('id-ID')
                  .format(Number(harga) || 0);
          };

          // Buat kartu HTML dari data API.
          let html = '';

          Object.entries(judulKategori).forEach(function ([kategori, judul]) {

              const daftarLayanan = katalog[kategori];

              if (!daftarLayanan || daftarLayanan.length === 0) {
                  return;
              }

              html += `
                  <article class="lp-katalog__card">

                      <h3>${EPAPI.esc(judul)}</h3>

                      <ul>
              `;

              daftarLayanan.forEach(function (item) {

                  html += `
                      <li>

                          <span>
                              ${EPAPI.esc(item.nama_layanan)}

                              ${
                                  item.deskripsi
                                  ? `<small>${EPAPI.esc(item.deskripsi)}</small>`
                                  : ''
                              }

                          </span>

                          <strong>
                              ${formatRupiah(item.harga)}

                              <small>
                                  / ${EPAPI.esc(item.satuan)}
                              </small>
                          </strong>

                      </li>
                  `;

              });

              html += `
                      </ul>

                  </article>
              `;

          });

          if (!html) {
              grid.innerHTML = `
                  <p class="lp-katalog__err">
                      Data layanan belum memiliki kategori yang sesuai.
                  </p>
              `;
              return;
          }

          // Tampilkan hasil ke halaman.
          grid.innerHTML = html;

          console.log(
              '[EasyPrint] Katalog berhasil dimuat:',
              layanan
          );

      } catch (error) {

          console.error(
              '[EasyPrint] Gagal memuat katalog:',
              error
          );

          grid.innerHTML = `
              <p class="lp-katalog__err" role="alert">
                  Katalog belum dapat dimuat.
                  Silakan muat ulang halaman.
              </p>
          `;

      }

  });
  </script>

</body>
</html>

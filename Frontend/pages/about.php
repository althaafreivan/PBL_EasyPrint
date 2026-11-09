<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Tentang Kami'; $navPublic = 'tentang';
include __DIR__ . '/../components/head-pub.php';
include __DIR__ . '/../components/navbar-public.php';
$svg = fn($p) => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
$utama = [
  ['Praktis',   $svg('<path d="m5 12 5 5L20 7"/>'), 'Alur pemesanan dibuat sederhana agar kebutuhan cetak lebih mudah diatur.'],
  ['Jelas',     $svg('<path d="M5 7h14M5 12h14M5 17h14"/>'), 'Informasi layanan dan harga ditampilkan sebelum pesanan dikonfirmasi.'],
  ['Terpantau', $svg('<path d="M7 17 17 7"/><path d="M8 7h9v9"/>'), 'Pantau perkembangan pesanan melalui fitur tracking yang tersedia.'],
];
$layanan = [
  ['Print Dokumen',    'Cetak PDF, Word, dan dokumen lainnya.'],
  ['Fotokopi',         'Hitam-putih maupun warna.'],
  ['Jilid & Finishing', 'Finishing dokumen agar lebih rapi.'],
];
$ikonDok = $svg('<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/>');
?>
  <main class="pmain">
    <section class="card about-hero" aria-labelledby="aboutTitle">
      <div>
        <span class="tagline">Tentang kami</span>
        <h1 id="aboutTitle">Mitra cetak untuk kebutuhan sehari-hari.</h1>
        <p>Restu Ibu Print &amp; Fotocopy membantu kebutuhan dokumen, fotokopi, dan penjilidan melalui proses pemesanan yang praktis.</p>
        <a href="index.php#layanan" class="pbtn pbtn--primary pbtn--lg">Lihat Layanan</a>
      </div>
      <div class="about-art" aria-hidden="true">
        <svg viewBox="0 0 360 240" fill="none">
          <circle cx="250" cy="110" r="78" fill="#dbe4fb"/>
          <rect x="60" y="22" width="92" height="118" rx="8" fill="#fff" stroke="#d5dcf1"/>
          <rect x="84" y="46" width="92" height="118" rx="8" fill="#fff" stroke="#d5dcf1"/>
          <path d="M100 76h58M100 92h58M100 108h40" stroke="#d5dcf1" stroke-width="3" stroke-linecap="round"/>
          <rect x="150" y="112" width="160" height="72" rx="10" fill="#27325a"/>
          <circle cx="292" cy="130" r="5" fill="#3aa575"/>
          <rect x="164" y="170" width="132" height="44" rx="6" fill="#fff" stroke="#d5dcf1"/>
          <path d="M178 186h70M178 198h50" stroke="#d5dcf1" stroke-width="3" stroke-linecap="round"/>
          <rect x="34" y="150" width="104" height="30" rx="15" fill="#fff" stroke="#d5dcf1"/>
          <text x="86" y="170" text-anchor="middle" font-family="Inter, sans-serif" font-size="11" font-weight="700" fill="#4466c8">Praktis &amp; mudah</text>
        </svg>
      </div>
    </section>

    <h2 class="h2">Apa yang kami utamakan</h2>
    <div class="grid3">
      <?php foreach ($utama as [$judul, $ikon, $teks]): ?>
        <article class="card feat"><span class="feat__ico"><?= $ikon ?></span><h3><?= e($judul) ?></h3><p><?= e($teks) ?></p></article>
      <?php endforeach; ?>
    </div>

    <h2 class="h2">Layanan untuk kebutuhan Anda</h2>
    <div class="grid3">
      <?php foreach ($layanan as [$judul, $teks]): ?>
        <article class="card feat feat--svc"><span class="feat__ico"><?= $ikonDok ?></span><h3><?= e($judul) ?></h3><p><?= e($teks) ?></p></article>
      <?php endforeach; ?>
    </div>

    <section class="cta-band" aria-label="Mulai pesanan">
      <div>
        <h2>Punya kebutuhan cetak?</h2>
        <p>Mulai dengan memilih layanan yang sesuai kebutuhan Anda.</p>
      </div>
      <a href="login.php" class="pbtn pbtn--primary pbtn--lg">Mulai Pesanan</a>
    </section>
  </main>
  <?php include __DIR__ . '/../components/footer-public.php'; ?>
</body>
</html>

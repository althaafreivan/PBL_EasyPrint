<?php
// Registrasi pelanggan: validasi di klien (HTML) + server (PHP) lalu POST /api/register.
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_user()) {
  header('Location: ' . home_for_role(current_user()['role']));
  exit;
}

$errors = [];
$old = ['nama' => '', 'email' => '', 'telepon' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old = [
    'nama' => trim((string) ($_POST['nama'] ?? '')),
    'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
    'telepon' => trim((string) ($_POST['telepon'] ?? '')),
  ];
  $pw = (string) ($_POST['password'] ?? '');
  if (!csrf_valid()) $errors[] = 'Sesi formulir kedaluwarsa. Silakan coba lagi.';
  if ($old['nama'] === '') $errors[] = 'Nama wajib diisi.';
  if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
  if (!preg_match('/^0[0-9]{8,14}$/', preg_replace('/[\s\-]/', '', $old['telepon']))) $errors[] = 'Nomor telepon harus diawali 0 dan berisi 9-15 digit.';
  if (strlen($pw) < 8) $errors[] = 'Password minimal 8 karakter.';
  if ($pw !== (string) ($_POST['konfirmasi'] ?? '')) $errors[] = 'Konfirmasi password tidak sama.';
  if (empty($_POST['setuju'])) $errors[] = 'Anda perlu menyetujui ketentuan penggunaan.';

  if (!$errors) {
    $res = callBackendApi('/api/register', 'POST', [
      'nama' => $old['nama'], 'email' => $old['email'], 'password' => $pw,
      'no_telepon' => preg_replace('/[\s\-]/', '', $old['telepon']),
    ]);
    if ($res['ok']) {
      // Langsung masuk setelah daftar.
      $login = callBackendApi('/api/login', 'POST', ['email' => $old['email'], 'password' => $pw]);
      if ($login['ok']) {
        login_lokal($login['data']['access_token'], $login['data']['user']);
        flash('Selamat datang, akun Anda berhasil dibuat!');
        header('Location: dashboard.php');
      } else {
        header('Location: login.php?daftar=1');
      }
      exit;
    }
    $errors[] = api_message($res, 'Registrasi gagal.');
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrasi Pelanggan - EasyPrint</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="assets/css/integrasi.css">
</head>

<body class="auth auth--register">

  <aside class="auth__side">
    <a href="index.php" class="auth__brand">
      <img src="assets/images/Icon.png" alt="" class="auth__logo">
      <span>EasyPrint</span>
    </a>

    <div class="auth__intro">
      <h1>Buat akun pelanggan</h1>
      <p>Pesan jasa cetak langsung dari web.</p>
      <span class="tag tag--dark">Gratis untuk pelanggan</span>
    </div>
  </aside>

  <main class="auth__main">
    <div class="auth__card">

      <a href="index.php" class="back-home">
        <span aria-hidden="true">&larr;</span>
        Kembali ke Beranda
      </a>

      <h2 class="auth__title auth__title--sm">Registrasi Pelanggan</h2>
      <p class="auth__subtitle auth__subtitle--sm">
        Lengkapi data berikut untuk membuat akun.
      </p>

      <?php if ($errors): ?>
        <div class="form-alert form-alert--err" role="alert"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div>
      <?php endif; ?>

      <form action="register.php" method="post" class="form form--tight">
        <?= csrf_field() ?>
        <div class="field">
          <label for="nama">Nama</label>
          <div class="input">
            <input
              type="text"
              id="nama"
              name="nama"
              placeholder="Nama lengkap"
              autocomplete="name"
              maxlength="100"
              value="<?= e($old['nama']) ?>"
              required
            >
          </div>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <div class="input">
            <input
              type="email"
              id="email"
              name="email"
              placeholder="nama@email.com"
              autocomplete="email"
              maxlength="100"
              value="<?= e($old['email']) ?>"
              required
            >
          </div>
        </div>

        <div class="field">
          <label for="telepon">No. Telepon</label>
          <div class="input">
            <input
              type="tel"
              id="telepon"
              name="telepon"
              placeholder="08xxxxxxxxxx"
              autocomplete="tel"
              pattern="0[0-9\s\-]{8,18}"
              title="Nomor telepon diawali 0, contoh 081234567890"
              value="<?= e($old['telepon']) ?>"
              required
            >
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="password">Password</label>
            <div class="input">
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                minlength="8"
                required
              >
            </div>
          </div>

          <div class="field">
            <label for="konfirmasi">Konfirmasi Password</label>
            <div class="input">
              <input
                type="password"
                id="konfirmasi"
                name="konfirmasi"
                placeholder="Ulangi password"
                autocomplete="new-password"
                required
              >
            </div>
          </div>
        </div>

        <label class="check">
          <input type="checkbox" name="setuju" required>
          <span>Saya setuju dengan ketentuan penggunaan.</span>
        </label>

        <button type="submit" class="btn-submit">Buat Akun</button>

        <p class="form__switch">
          Sudah punya akun?
          <a href="login.php" class="link">Masuk</a>
        </p>
      </form>

    </div>
  </main>

</body>
</html>

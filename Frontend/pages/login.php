<?php
// Login multi-role: kirim ke POST /api/login, simpan token di sesi, arahkan sesuai peran.
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_user()) {
  header('Location: ' . home_for_role(current_user()['role']));
  exit;
}

$error = '';
$info = match (true) {
  isset($_GET['expired']) => 'Sesi Anda berakhir. Silakan masuk kembali.',
  isset($_GET['keluar'])  => 'Anda telah keluar.',
  isset($_GET['daftar'])  => 'Akun berhasil dibuat. Silakan masuk.',
  default => '',
};
$email = '';
$next = (string) ($_POST['next'] ?? $_GET['next'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim((string) ($_POST['email'] ?? '')));
  $password = (string) ($_POST['password'] ?? '');
  if (!csrf_valid()) {
    $error = 'Sesi formulir kedaluwarsa. Silakan coba lagi.';
  } elseif ($email === '' || $password === '') {
    $error = 'Email dan password wajib diisi.';
  } else {
    $res = callBackendApi('/api/login', 'POST', ['email' => $email, 'password' => $password]);
    if ($res['ok'] && !empty($res['data']['access_token'])) {
      login_lokal($res['data']['access_token'], $res['data']['user']);
      $role = $res['data']['user']['role'];
      // Hanya izinkan redirect "next" ke halaman lokal.
      $tujuan = preg_match('#^/?[a-z0-9\-]+\.php(\?[^\s]*)?$#i', ltrim($next, '/')) ? ltrim($next, '/') : home_for_role($role);
      header('Location: ' . $tujuan);
      exit;
    }
    $error = api_message($res, 'Login gagal.');
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk - EasyPrint</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="assets/css/integrasi.css">
</head>

<body class="auth auth--login">

  <aside class="auth__side">
    <a href="index.php" class="auth__brand">
      <img src="assets/images/Icon.png" alt="" class="auth__logo">
      <span>EasyPrint</span>
    </a>

    <div class="auth__intro">
      <h1>Selamat datang kembali.</h1>
      <p>Masuk untuk mengelola pesanan Anda.</p>

      <div class="auth__feature">
        <h2>Pesan lebih cepat.</h2>
        <p>
          Pantau status dan lihat nota<br>
          langsung dari dashboard pelanggan.
        </p>

        <div class="auth__tags">
          <span class="tag tag--light">Tracking Pesanan</span>
          <span class="tag tag--light">Nota Digital</span>
        </div>
      </div>
    </div>
  </aside>

  <main class="auth__main">
    <div class="auth__form-wrap">

      <a href="index.php" class="back-home">
        <span aria-hidden="true">&larr;</span>
        Kembali ke Beranda
      </a>

      <h2 class="auth__title">Masuk ke akun</h2>
      <p class="auth__subtitle">
        Gunakan email dan password akun Anda.
      </p>

      <?php if ($error): ?><p class="form-alert form-alert--err" role="alert"><?= e($error) ?></p><?php endif; ?>
      <?php if ($info && !$error): ?><p class="form-alert" role="status"><?= e($info) ?></p><?php endif; ?>

      <form action="login.php" method="post" class="form" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="field">
          <label for="email">Email</label>

          <div class="input input--icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" 
            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" 
            stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" 
            width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>

            <input
              type="email"
              id="email"
              name="email"
              placeholder="nama@email.com"
              autocomplete="email"
              value="<?= e($email) ?>"
              required
            >
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>

          <div class="input">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Masukkan password"
              autocomplete="current-password"
              required
            >
          </div>
        </div>

        <p class="link link--small">Lupa password? Hubungi admin outlet di <?= e(TELP_TOKO) ?>.</p>

        <button type="submit" class="btn-submit">Masuk</button>

        <p class="form__switch">
          Belum punya akun?
          <a href="register.php" class="link">Daftar pelanggan</a>
        </p>
      </form>

    </div>
  </main>

</body>
</html>

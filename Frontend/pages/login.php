<?php
// Login demo: peran ditentukan dari awal email (belum terhubung backend)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email  = strtolower(trim($_POST['email'] ?? ''));
  $tujuan = str_starts_with($email, 'owner') ? 'owner-dashboard.php'
          : (str_starts_with($email, 'admin') ? 'admin-orders.php' : 'dashboard.php');
  header('Location: ' . $tujuan);
  exit;
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

      <form action="login.php" method="post" class="form">
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

        <a href="forgot-password.php" class="link link--small">
            Lupa password?
        </a>

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


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

      <form action="#" method="post" class="form form--tight">
        <div class="field">
          <label for="nama">Nama</label>
          <div class="input">
            <input
              type="text"
              id="nama"
              name="nama"
              placeholder="Nama lengkap"
              autocomplete="name"
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
                placeholder="Buat password"
                autocomplete="new-password"
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

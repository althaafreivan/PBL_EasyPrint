
<?php
$pesan = '';
$jenisPesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (empty($email)) {
        $pesan = 'Email wajib diisi.';
        $jenisPesan = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
        $jenisPesan = 'error';
    } else {
        // Simulasi sementara, belum terhubung backend.
        // Pesan dibuat umum agar tidak membocorkan apakah email terdaftar.
        $pesan = 'Jika email terdaftar, instruksi reset password akan dikirimkan.';
        $jenisPesan = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Lupa Password - EasyPrint</title>

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
            <h1>Lupa password Anda?</h1>
            <p>Jangan khawatir, kami siap membantu Anda mendapatkan kembali akses akun.</p>

            <div class="auth__feature">
                <h2>Akses kembali akun Anda.</h2>
                <p>
                    Atur ulang password dengan mudah
                    dan lanjutkan aktivitas Anda di EasyPrint.
                </p>

                <div class="auth__tags">
                    <span class="tag tag--light">Akun Aman</span>
                    <span class="tag tag--light">Reset Password</span>
                </div>
            </div>
        </div>
    </aside>

    <main class="auth__main">
        <div class="auth__form-wrap">

            <a href="login.php" class="back-home">
                <span aria-hidden="true">&larr;</span>
                Kembali ke Login
            </a>

            <h2 class="auth__title">Lupa password?</h2>

            <p class="auth__subtitle">
                Masukkan alamat email yang terdaftar pada akun EasyPrint.
                Kami akan membantu proses pengaturan ulang password Anda.
            </p>

            <?php if ($pesan !== ''): ?>
                <div class="auth-message auth-message--<?= htmlspecialchars($jenisPesan) ?>"
                     role="status">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form action="forgot-password.php" method="post" class="form">

                <div class="field">
                    <label for="email">Email</label>

                    <div class="input input--icon">
                        <svg viewBox="0 0 24 24"
                             width="22"
                             height="22"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3 7 9 6 9-6"/>
                        </svg>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Kirim Instruksi Reset
                </button>

                <p class="form__switch">
                    Ingat password Anda?
                    <a href="login.php" class="link">Masuk sekarang</a>
                </p>

            </form>

        </div>
    </main>

</body>
</html>

<?php
// Profil pengguna: GET /api/me + PATCH /api/me (ubah nama, telepon, password)
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['pelanggan']);
$res = api_get('/api/me');
$me = $res['data']['user'] ?? current_user();
$nav = ''; $pageTitle = 'Profil Saya';
include __DIR__ . '/../components/head-app.php';
include __DIR__ . '/../components/navbar-app.php';
?>
  <main class="page">
    <p class="label" style="font-weight:500;font-size:11.5px"><a href="dashboard.php">Beranda</a> / Profil</p>
    <h1 class="greet">Profil Saya</h1>
    <p class="greet__sub">Nomor telepon dipakai untuk cek pesanan tanpa login dan saat pengambilan di kasir.</p>

    <form class="card ep-profile" id="formProfil" novalidate>
      <div class="ep-profile__grid">
        <div class="pfld-lite">
          <label for="pNama">Nama lengkap</label>
          <input class="of-control" id="pNama" maxlength="100" required value="<?= e($me['nama']) ?>">
        </div>
        <div class="pfld-lite">
          <label for="pEmail">Email</label>
          <input class="of-control" id="pEmail" value="<?= e($me['email']) ?>" disabled>
        </div>
        <div class="pfld-lite">
          <label for="pTel">No. telepon</label>
          <input class="of-control" id="pTel" type="tel" maxlength="20" value="<?= e($me['no_telepon'] ?? '') ?>" placeholder="08xxxxxxxxxx">
        </div>
        <div class="pfld-lite">
          <label>Terdaftar sejak</label>
          <input class="of-control" value="<?= e(tgl($me['created_at'] ?? null, true)) ?>" disabled>
        </div>
      </div>

      <h2 class="ep-profile__sub">Ganti password <small>(opsional)</small></h2>
      <div class="ep-profile__grid">
        <div class="pfld-lite">
          <label for="pLama">Password lama</label>
          <input class="of-control" id="pLama" type="password" autocomplete="current-password">
        </div>
        <div class="pfld-lite">
          <label for="pBaru">Password baru</label>
          <input class="of-control" id="pBaru" type="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter">
        </div>
      </div>
      <p class="field-error" id="pErr" role="alert" hidden></p>
      <button type="submit" class="btn-cta" id="pSimpan">Simpan perubahan</button>
    </form>
  </main>
  <script>
    (() => {
      const $ = (id) => document.getElementById(id);
      $('formProfil').addEventListener('submit', async (e) => {
        e.preventDefault();
        const err = (m) => { $('pErr').textContent = m || ''; $('pErr').hidden = !m; };
        err('');
        if (!$('pNama').value.trim()) return err('Nama wajib diisi.');
        if ($('pBaru').value && $('pBaru').value.length < 8) return err('Password baru minimal 8 karakter.');
        const body = { nama: $('pNama').value.trim(), no_telepon: $('pTel').value.trim() };
        if ($('pBaru').value) { body.password_baru = $('pBaru').value; body.password_lama = $('pLama').value; }
        const res = await EPAPI.sibuk($('pSimpan'), () => EPAPI.call('/api/me', { method: 'PATCH', body }));
        if (!res.ok) return err(res.data.message);
        $('pLama').value = $('pBaru').value = '';
        EPAPI.toast(res.data.message);
      });
    })();
  </script>
</body>
</html>

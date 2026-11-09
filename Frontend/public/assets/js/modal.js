/* Util popup bersama: window.EP.konfirmasiHapus({ judul, pesan }) -> Promise<boolean>
   Menggantikan confirm() bawaan browser dengan popup sesuai desain. */
(() => {
  const trash = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/></svg>';
  let dlg;

  function bangun() {
    dlg = document.createElement('dialog');
    dlg.className = 'mdl mdl--sm';
    dlg.setAttribute('aria-labelledby', 'epHapusJudul');
    dlg.innerHTML = `
      <form method="dialog" class="mdl__confirm">
        <span class="mdl__ico">${trash}</span>
        <h2 id="epHapusJudul"></h2>
        <p class="mdl__sub" id="epHapusPesan"></p>
        <div class="mdl__actions mdl__actions--split">
          <button type="submit" value="batal" class="mdl__btn">Batal</button>
          <button type="submit" value="ya" class="mdl__btn mdl__btn--danger">Ya, hapus</button>
        </div>
      </form>`;
    document.body.appendChild(dlg);
  }

  function konfirmasiHapus({ judul = 'Hapus data ini?', pesan = '', info = false } = {}) {
    if (!dlg) bangun();
    dlg.querySelector('#epHapusJudul').textContent = judul;
    dlg.querySelector('#epHapusPesan').textContent = pesan;
    // mode info: hanya pemberitahuan, tanpa tombol hapus
    dlg.querySelector('[value="ya"]').hidden = info;
    dlg.querySelector('[value="batal"]').textContent = info ? 'Mengerti' : 'Batal';
    return new Promise((resolve) => {
      dlg.addEventListener('close', () => resolve(dlg.returnValue === 'ya'), { once: true });
      dlg.returnValue = 'batal';
      dlg.showModal();
      dlg.querySelector('[value="batal"]').focus();
    });
  }

  window.EP = Object.assign(window.EP || {}, { konfirmasiHapus });
})();

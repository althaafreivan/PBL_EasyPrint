/* EasyPrint - helper bersama untuk memanggil REST API lewat api-proxy.php.
   EPAPI.call('/api/orders/status', { method: 'PATCH', body: {...} }) -> Promise<{ok, status, data}>
   EPAPI.call('/api/orders', { method: 'POST', form: formData })     -> multipart (upload berkas)  */
(() => {
  if (window.EPAPI) return;

  async function call(path, { method = 'GET', body, form, query } = {}) {
    const qs = new URLSearchParams({ path, ...(query || {}) });
    const opt = { method, headers: { 'X-Requested-With': 'EasyPrint', Accept: 'application/json' }, credentials: 'same-origin' };
    if (form) opt.body = form;
    else if (body !== undefined) {
      opt.headers['Content-Type'] = 'application/json';
      opt.body = JSON.stringify(body);
    }
    let res;
    try {
      res = await fetch('api-proxy.php?' + qs.toString(), opt);
    } catch (e) {
      return { ok: false, status: 0, data: { message: 'Tidak dapat terhubung ke server. Periksa koneksi Anda.' } };
    }
    let data = {};
    try { data = await res.json(); } catch (e) { data = { message: 'Respons server tidak valid.' }; }
    if (res.status === 401) {
      toast('Sesi berakhir. Silakan masuk kembali.', 'err');
      setTimeout(() => { location.href = 'login.php?expired=1'; }, 1200);
    }
    return { ok: res.ok, status: res.status, data };
  }

  let wrap;
  function toast(pesan, tipe = 'ok') {
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'ep-toasts';
      wrap.setAttribute('role', 'status');
      wrap.setAttribute('aria-live', 'polite');
      document.body.appendChild(wrap);
    }
    const t = document.createElement('div');
    t.className = 'ep-toast ep-toast--' + tipe;
    t.textContent = pesan;
    wrap.appendChild(t);
    setTimeout(() => t.classList.add('is-out'), 3800);
    setTimeout(() => t.remove(), 4300);
  }

  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0));
  const angka = (n, d = 2) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: d }).format(Number(n) || 0);

  /** Dialog input teks (mis. alasan penolakan). Resolve string atau null bila batal. */
  function prompt({ judul, pesan = '', label = 'Keterangan', placeholder = '', tombol = 'Simpan', bahaya = false, wajib = true, pilihan = null }) {
    return new Promise((resolve) => {
      const dlg = document.createElement('dialog');
      dlg.className = 'mdl mdl--sm';
      const field = pilihan
        ? `<select class="ep-input" id="epPromptVal">${pilihan.map((p) => `<option>${esc(p)}</option>`).join('')}</select>`
        : `<textarea class="ep-input" id="epPromptVal" rows="3" placeholder="${esc(placeholder)}" ${wajib ? 'required' : ''}></textarea>`;
      dlg.innerHTML = `
        <form method="dialog" class="ep-prompt">
          <h2>${esc(judul)}</h2>
          ${pesan ? `<p class="mdl__sub">${esc(pesan)}</p>` : ''}
          <label class="ep-label" for="epPromptVal">${esc(label)}</label>
          ${field}
          <div class="mdl__actions mdl__actions--split">
            <button type="button" class="mdl__btn" value="batal">Batal</button>
            <button type="submit" class="mdl__btn ${bahaya ? 'mdl__btn--danger' : 'mdl__btn--primary'}" value="ok">${esc(tombol)}</button>
          </div>
        </form>`;
      document.body.appendChild(dlg);
      const val = dlg.querySelector('#epPromptVal');
      dlg.querySelector('[value="batal"]').addEventListener('click', () => dlg.close('batal'));
      dlg.querySelector('form').addEventListener('submit', (e) => {
        if (wajib && !val.value.trim()) { e.preventDefault(); val.focus(); }
      });
      dlg.addEventListener('close', () => {
        const hasil = dlg.returnValue === 'ok' ? val.value.trim() : null;
        dlg.remove();
        resolve(hasil);
      });
      dlg.showModal();
      val.focus();
    });
  }

  /** Konfirmasi ya/tidak memakai popup modal.js bila tersedia. */
  function konfirmasi(opsi) {
    if (window.EP && window.EP.konfirmasiHapus) return window.EP.konfirmasiHapus(opsi);
    return Promise.resolve(window.confirm(opsi.judul + (opsi.pesan ? '\n' + opsi.pesan : '')));
  }

  /** Tombol sibuk saat request berjalan. */
  async function sibuk(btn, fn) {
    if (!btn) return fn();
    const teks = btn.innerHTML;
    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');
    try { return await fn(); } finally {
      btn.disabled = false;
      btn.removeAttribute('aria-busy');
      btn.innerHTML = teks;
    }
  }

  window.EPAPI = { call, toast, esc, rupiah, angka, prompt, konfirmasi, sibuk };
})();

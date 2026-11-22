/* Simpan berkas draf pesanan di IndexedDB agar tetap tersedia dari langkah 2 (form)
   ke langkah 3 (review) tanpa upload ganda. sessionStorage hanya bisa menyimpan teks. */
window.EPDraftFile = (() => {
  const DB = 'easyprint', STORE = 'draft', KEY = 'berkas';

  function buka() {
    return new Promise((resolve, reject) => {
      if (!('indexedDB' in window)) return reject(new Error('IndexedDB tidak didukung'));
      const req = indexedDB.open(DB, 1);
      req.onupgradeneeded = () => req.result.createObjectStore(STORE);
      req.onsuccess = () => resolve(req.result);
      req.onerror = () => reject(req.error);
    });
  }

  async function jalankan(mode, fn) {
    const db = await buka();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(STORE, mode);
      const req = fn(tx.objectStore(STORE));
      tx.oncomplete = () => { db.close(); resolve(req && req.result); };
      tx.onerror = () => { db.close(); reject(tx.error); };
    });
  }

  return {
    simpan: (file) => jalankan('readwrite', (s) => s.put(file, KEY)).catch(() => null),
    ambil: () => jalankan('readonly', (s) => s.get(KEY)).catch(() => null),
    hapus: () => jalankan('readwrite', (s) => s.delete(KEY)).catch(() => null),
  };
})();

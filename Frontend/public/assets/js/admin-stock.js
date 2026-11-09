(() => {
  const bahan = (window.BAHAN || []).map((b) => ({ ...b }));
  let masuk = 2; // jumlah pasokan masuk bulan ini (data contoh)

  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const kritis = (b) => b.stok <= b.reorder;
  const persen = (b) => Math.max(0, Math.min(100, Math.round((b.stok / (b.reorder * 2)) * 100)));

  function render() {
    $('stokBody').innerHTML = bahan.map((b) => `
      <tr>
        <td class="nm">${esc(b.nama)}</td>
        <td>${esc(b.satuan)}</td>
        <td class="r nm">${b.stok}</td>
        <td class="r">${b.reorder}</td>
        <td><span class="lvl lvl--${kritis(b) ? 'bad' : 'ok'}" role="img" aria-label="Level stok ${persen(b)} persen"><i style="width:${persen(b)}%"></i></span></td>
        <td><span class="pill ${kritis(b) ? 'pill--red' : 'pill--green'}">${kritis(b) ? 'Kritis' : 'Aman'}</span></td>
      </tr>`).join('');

    const k = bahan.filter(kritis);
    $('kKritis').textContent = `${k.length} item`;
    $('kMasuk').textContent = `${masuk} kali`;
    $('alertBar').hidden = k.length === 0;
    $('alertJudul').textContent = `Peringatan stok kritis (${k.length} bahan)`;
    $('alertIsi').textContent = k.map((b) => `${b.nama} ${b.stok} / ${b.reorder} ${b.satuan}`).join(' · ') + ' (stok <= reorder point). Segera lakukan restock.';

    const dipilih = $('rsBahan').value;
    $('rsBahan').innerHTML = bahan.map((b) => `<option value="${b.id}">${esc(b.nama)}</option>`).join('');
    if (dipilih) $('rsBahan').value = dipilih;
  }

  $('restockForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const b = bahan.find((x) => x.id === Number($('rsBahan').value));
    const n = parseInt($('rsJumlah').value, 10);
    if (!b || !(n > 0)) return;
    b.stok += n;
    masuk += 1;
    const [y, m, d] = $('rsTgl').value.split('-');
    $('rsMsg').textContent = `Tercatat ${d}/${m}/${y}: +${n} ${b.satuan} ${b.nama}. Stok sekarang ${b.stok} ${b.satuan}.`;
    render();
  });

  render();
})();
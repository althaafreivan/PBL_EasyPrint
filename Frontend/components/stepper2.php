<?php
/**
 * Stepper status pesanan dengan keterangan waktu.
 * ep_steps($status, $waktu)  -> daftar langkah [label, keterangan, state]
 * ep_stepper2($steps)        -> cetak <ol> stepper
 * state: done | current | hold | reject | '' (belum)
 */
function ep_steps(string $status, array $waktu = []): array {
  $teks = ['Pesanan diterima', 'Berkas diperiksa', 'Sedang dikerjakan', 'Siap diambil di outlet', 'Pesanan diterima pelanggan'];
  if ($status === 'Ditolak') {
    return [
      ['Pending',  $waktu[0] ?? '-', 'done'],
      ['Ditolak',  $waktu[1] ?? '-', 'reject'],
      ['Diproses', '-', ''],
      ['Selesai',  '-', ''],
    ];
  }
  $labels = ['Pending', 'Diverifikasi', 'Diproses', 'Selesai', 'Diambil'];
  $aktif  = ['Pending' => 0, 'Tertunda' => 2, 'Diproses' => 2, 'Selesai' => 3, 'Diambil' => 4][$status] ?? 0;
  $out = [];
  foreach ($labels as $i => $label) {
    $state = $i < $aktif ? 'done' : ($i === $aktif ? 'current' : '');
    if ($status === 'Diambil') $state = 'done';
    if ($status === 'Tertunda' && $i === 2) { $state = 'hold'; $label = 'Tertunda'; }
    $out[] = [$label, ($state !== '' && isset($waktu[$i])) ? $waktu[$i] : $teks[$i], $state];
  }
  return $out;
}
function ep_stepper2(array $steps): void {
  $ikon = [
    'done'   => '<path d="m5 12 5 5L20 7"/>',
    'hold'   => '<circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 1.5"/>',
    'reject' => '<path d="M7 7l10 10"/><path d="M17 7 7 17"/>',
  ];
  echo '<ol class="stepper2" style="--n:' . count($steps) . '" aria-label="Progres pesanan">';
  foreach ($steps as [$label, $ket, $state]) {
    echo '<li class="stepper2__item' . ($state ? ' is-' . $state : '') . '"' . ($state === 'current' || $state === 'hold' ? ' aria-current="step"' : '') . '>';
    echo '<span class="stepper2__dot" aria-hidden="true">';
    if (isset($ikon[$state])) echo '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">' . $ikon[$state] . '</svg>';
    echo '</span><strong>' . htmlspecialchars($label) . '</strong><small>' . htmlspecialchars($ket) . '</small></li>';
  }
  echo '</ol>';
}

<?php
/**
 * Stepper status pesanan dengan keterangan waktu (6 status DEC-02 + Ditolak).
 * ep_steps_riwayat($status, $riwayat) -> daftar langkah [label, keterangan, state] dari data API
 * ep_steps($status, $waktu)           -> versi lama (array waktu per indeks), tetap tersedia
 * ep_stepper2($steps)                 -> cetak <ol> stepper
 * state: done | current | hold | reject | '' (belum)
 */

const EP_LANGKAH = ['Pending', 'Diverifikasi', 'Diproses', 'Selesai', 'Diambil'];
const EP_TEKS = ['Pesanan diterima', 'Berkas diperiksa', 'Sedang dikerjakan', 'Siap diambil di outlet', 'Pesanan diterima pelanggan'];

/** @param array $riwayat item berisi status_baru|status dan created_at|waktu */
function ep_steps_riwayat(string $status, array $riwayat): array
{
  $waktu = [];
  foreach ($riwayat as $r) {
    $s = $r['status_baru'] ?? $r['status'] ?? '';
    $waktu[str_starts_with($s, 'Tertunda') ? 'Tertunda' : $s] = tgl_jam($r['created_at'] ?? $r['waktu'] ?? null);
  }

  if ($status === 'Ditolak') {
    return [
      ['Pending',  $waktu['Pending'] ?? '-', 'done'],
      ['Ditolak',  $waktu['Ditolak'] ?? '-', 'reject'],
      ['Diproses', '-', ''],
      ['Selesai',  '-', ''],
    ];
  }

  $aktif = ['Pending' => 0, 'Diverifikasi' => 1, 'Diproses' => 2, 'Selesai' => 3, 'Diambil' => 4][$status]
    ?? (str_starts_with($status, 'Tertunda') ? 2 : 0);
  $out = [];
  foreach (EP_LANGKAH as $i => $label) {
    $state = $i < $aktif ? 'done' : ($i === $aktif ? 'current' : '');
    if ($status === 'Diambil') {
      $state = 'done';
    }
    $ket = $state !== '' && isset($waktu[$label]) ? $waktu[$label] : EP_TEKS[$i];
    if ($i === 2 && str_starts_with($status, 'Tertunda')) {
      $state = 'hold';
      $label = 'Tertunda';
      $ket = isset($waktu['Tertunda']) ? 'Sejak ' . $waktu['Tertunda'] : 'Menunggu bahan';
    } elseif ($state === 'current' && $status !== 'Diambil') {
      $ket = ($waktu[$label] ?? null) ? $waktu[$label] : EP_TEKS[$i];
    }
    $out[] = [$label, $ket, $state];
  }
  return $out;
}

function ep_steps(string $status, array $waktu = []): array
{
  $riwayat = [];
  foreach (EP_LANGKAH as $i => $s) {
    if (isset($waktu[$i])) {
      $riwayat[] = ['status' => $s, 'waktu' => null];
    }
  }
  return ep_steps_riwayat($status, $riwayat);
}

function ep_stepper2(array $steps): void
{
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

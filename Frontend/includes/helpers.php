<?php
// frontend/includes/helpers.php - Format tampilan (rupiah, tanggal, status, notifikasi)

function rupiah($n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Angka stok tanpa nol berlebih: 14.9900 -> "14,99", 50.0000 -> "50". */
function angka($n, int $desimal = 2): string
{
    $s = number_format((float) $n, $desimal, ',', '.');
    return str_contains($s, ',') ? rtrim(rtrim($s, '0'), ',') : $s;
}

/** Ikon SVG garis sederhana. */
function ico(string $path, int $size = 16): string
{
    return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

const BULAN_PENDEK = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const BULAN_PANJANG = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const HARI_PENDEK = [1 => 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

function waktu($ts): ?DateTime
{
    if (!$ts) {
        return null;
    }
    try {
        return new DateTime((string) $ts);
    } catch (Exception) {
        return null;
    }
}

/** 2026-11-09 17:18:29 -> "09 Nov 2026" */
function tgl($ts, bool $panjang = false): string
{
    $d = waktu($ts);
    if (!$d) {
        return '-';
    }
    $b = $panjang ? BULAN_PANJANG : BULAN_PENDEK;
    return $d->format('d') . ' ' . $b[(int) $d->format('n')] . ' ' . $d->format('Y');
}

/** -> "17.18" */
function jam($ts): string
{
    $d = waktu($ts);
    return $d ? $d->format('H.i') : '-';
}

/** -> "09 Nov, 17.18" */
function tgl_jam($ts): string
{
    $d = waktu($ts);
    return $d ? $d->format('d') . ' ' . BULAN_PENDEK[(int) $d->format('n')] . ', ' . $d->format('H.i') : '-';
}

function kode($k): string
{
    return '#' . ltrim((string) $k, '#');
}

// --------------------------------------------------------------- status pesanan

const STATUS_LABEL_PENDEK = ['Tertunda - Stok Tidak Tersedia' => 'Tertunda'];

function label_status(string $status): string
{
    return STATUS_LABEL_PENDEK[$status] ?? $status;
}

function is_tertunda(string $status): bool
{
    return str_starts_with($status, 'Tertunda');
}

// Kelas pill per status pesanan (pelanggan).
function kelas_status(string $status): string
{
    if (is_tertunda($status)) {
        return 'pill--tertunda';
    }
    return [
        'Pending' => 'pill--yellow', 'Diverifikasi' => 'pill--soft', 'Diproses' => 'pill--blue',
        'Selesai' => 'pill--green', 'Diambil' => 'pill--green', 'Ditolak' => 'pill--red',
    ][$status] ?? 'pill--blue';
}

/** slug untuk filter (data-status) */
function slug_status(string $status): string
{
    return is_tertunda($status) ? 'tertunda' : strtolower($status);
}

function link_lacak(int $id): string
{
    return 'tracking.php?id=' . $id;
}

/** Pesanan yang sudah punya nota (sudah dikerjakan). */
function punya_nota(string $status): bool
{
    return in_array($status, ['Selesai', 'Diambil'], true);
}

// --------------------------------------------------------------- notifikasi

/** Ambil status tujuan dari teks notifikasi "... menjadi X." */
function status_dari_notif(array $n): ?string
{
    if (($n['judul'] ?? '') === 'Pesanan Dibuat') {
        return 'Pending';
    }
    if (preg_match('/menjadi (Tertunda - Stok Tidak Tersedia|Pending|Diverifikasi|Diproses|Tertunda|Selesai|Diambil|Ditolak)/', $n['pesan'] ?? '', $m)) {
        return $m[1];
    }
    return null;
}

function grup_waktu($ts): string
{
    $d = waktu($ts);
    if (!$d) {
        return 'Sebelumnya';
    }
    $hari = (new DateTime('today'))->diff((clone $d)->setTime(0, 0))->days;
    return $hari === 0 ? 'Hari ini' : ($hari === 1 ? 'Kemarin' : 'Sebelumnya');
}

function waktu_relatif($ts): string
{
    $d = waktu($ts);
    if (!$d) {
        return '';
    }
    $detik = time() - $d->getTimestamp();
    if ($detik < 60) {
        return 'baru saja';
    }
    if ($detik < 3600) {
        return floor($detik / 60) . ' mnt lalu';
    }
    if (grup_waktu($ts) === 'Hari ini') {
        return floor($detik / 3600) . ' jam lalu';
    }
    return grup_waktu($ts) === 'Kemarin' ? $d->format('H.i') : $d->format('j') . ' ' . BULAN_PENDEK[(int) $d->format('n')];
}

/** Bentuk data notifikasi API -> format kartu panel pelanggan (app-nav.js). */
function notif_pelanggan(array $rows): array
{
    $ikon = ['Pending' => 'clock', 'Diverifikasi' => 'check', 'Diproses' => 'printer', 'Tertunda' => 'clock',
             'Tertunda - Stok Tidak Tersedia' => 'clock', 'Selesai' => 'check', 'Diambil' => 'cash', 'Ditolak' => 'alert'];
    $tag = ['Selesai' => 'Siap diambil', 'Diambil' => 'Lunas'];
    $warna = ['Pending' => 'yellow', 'Diverifikasi' => 'blue', 'Diproses' => 'blue', 'Selesai' => 'green',
              'Diambil' => 'green', 'Ditolak' => 'red'];
    $judul = ['Pending' => 'Pesanan diterima', 'Diverifikasi' => 'Berkas sudah diverifikasi', 'Diproses' => 'Pesanan sedang diproses',
              'Tertunda' => 'Pesanan tertunda', 'Tertunda - Stok Tidak Tersedia' => 'Pesanan tertunda – stok habis',
              'Selesai' => 'Pesanan siap diambil', 'Diambil' => 'Pesanan sudah diambil', 'Ditolak' => 'Pesanan ditolak'];
    $out = [];
    foreach ($rows as $n) {
        $st = status_dari_notif($n);
        $out[] = [
            'id' => (int) $n['id'],
            'grup' => grup_waktu($n['created_at']),
            'ikon' => $st ? $ikon[$st] : 'info',
            'baru' => !$n['is_read'],
            'judul' => $st ? $judul[$st] : $n['judul'],
            'isi' => $n['pesan'],
            'meta' => $n['kode_pesanan'] ? kode($n['kode_pesanan']) : NAMA_TOKO,
            'waktu' => waktu_relatif($n['created_at']),
            'tag' => $st ? ($tag[$st] ?? label_status($st)) : 'Info',
            'warna' => $st ? ($warna[$st] ?? 'tertunda') : 'blue',
            'url' => $n['pesanan_id'] ? link_lacak((int) $n['pesanan_id']) : '',
        ];
    }
    return $out;
}

/** Bentuk data notifikasi API -> format kartu panel admin/owner (notifications.js). */
function notif_staff(array $rows, string $role): array
{
    $out = [];
    foreach ($rows as $n) {
        $st = status_dari_notif($n);
        $stok = str_starts_with((string) $n['judul'], 'Stok');
        if ($stok) {
            [$ikon, $tag, $warna] = ['warning', $n['judul'] === 'Stok Habis' ? 'Stok habis' : 'Perlu restock', 'yellow'];
            $url = $role === 'owner' ? 'owner-inventory.php' : 'admin-stock.php';
        } elseif ($n['judul'] === 'Pesanan Dibuat') {
            [$ikon, $tag, $warna] = ['box', 'Pesanan baru', 'soft'];
            $url = 'admin-orders.php?status=Pending';
        } else {
            [$ikon, $tag, $warna] = [$st === 'Diambil' ? 'truck' : 'check', $st ? label_status($st) : 'Info', 'green'];
            $url = 'admin-orders.php';
        }
        $out[] = [
            'id' => (int) $n['id'],
            'grup' => ['Hari ini' => 'hari-ini', 'Kemarin' => 'kemarin'][grup_waktu($n['created_at'])] ?? 'sebelumnya',
            'ikon' => $ikon,
            'baru' => !$n['is_read'],
            'judul' => $n['judul'] === 'Pesanan Dibuat' ? 'Pesanan baru masuk' : $n['judul'],
            'isi' => $n['pesan'],
            'meta' => $n['kode_pesanan'] ? kode($n['kode_pesanan']) : 'Gudang bahan baku',
            'waktu' => waktu_relatif($n['created_at']),
            'tag' => $tag,
            'warna' => $warna,
            'url' => $url,
        ];
    }
    return $out;
}

<?php
// backend/controllers/NotifikasiController.php
// CP-3: Notifikasi per pengguna (status pesanan, pesanan baru, stok kritis)

function notif_index(): void
{
    $actor = require_user();
    $stmt = db()->prepare(
        'SELECT n.id, n.pesanan_id, p.kode_pesanan, p.status AS status_pesanan,
                n.judul, n.pesan, n.created_at, nu.is_read
         FROM public.notifikasi_users nu
         JOIN public.notifikasi n ON n.id = nu.notifikasi_id
         LEFT JOIN public.pesanan p ON p.id = n.pesanan_id
         WHERE nu.user_id = :uid
         ORDER BY n.created_at DESC, n.id DESC
         LIMIT :limit'
    );
    $stmt->execute(['uid' => $actor['id'], 'limit' => int_query('limit', 50, 1, 200)]);
    $rows = $stmt->fetchAll();
    $belum = count(array_filter($rows, fn ($r) => !$r['is_read']));
    json_success(['data' => $rows, 'belum_dibaca' => $belum]);
}

// PATCH /api/notifications/read  {id_notifikasi} atau {semua: true}
function notif_read(): void
{
    $actor = require_user();
    $data = body();

    if (!empty($data['semua'])) {
        $stmt = db()->prepare('UPDATE public.notifikasi_users SET is_read = TRUE WHERE user_id = :uid AND NOT is_read');
        $stmt->execute(['uid' => $actor['id']]);
        json_success(['message' => 'Semua notifikasi ditandai sudah dibaca.', 'jumlah' => $stmt->rowCount()]);
    }

    $id = filter_var($data['id_notifikasi'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        fail('id_notifikasi wajib berupa angka positif.');
    }
    $stmt = db()->prepare(
        'UPDATE public.notifikasi_users SET is_read = TRUE WHERE notifikasi_id = :id AND user_id = :uid'
    );
    $stmt->execute(['id' => $id, 'uid' => $actor['id']]);
    if (!$stmt->rowCount()) {
        fail('Notifikasi tidak ditemukan.', 404);
    }
    json_success(['message' => 'Notifikasi ditandai sudah dibaca.']);
}

// DELETE /api/notifications/{id}  atau  DELETE /api/notifications (semua milik user)
function notif_destroy(string $id = ''): void
{
    $actor = require_user();
    if ($id === '') {
        $stmt = db()->prepare('DELETE FROM public.notifikasi_users WHERE user_id = :uid');
        $stmt->execute(['uid' => $actor['id']]);
        json_success(['message' => 'Semua notifikasi dibersihkan.', 'jumlah' => $stmt->rowCount()]);
    }
    $stmt = db()->prepare('DELETE FROM public.notifikasi_users WHERE notifikasi_id = :id AND user_id = :uid');
    $stmt->execute(['id' => (int) $id, 'uid' => $actor['id']]);
    if (!$stmt->rowCount()) {
        fail('Notifikasi tidak ditemukan.', 404);
    }
    json_success(['message' => 'Notifikasi dihapus.']);
}

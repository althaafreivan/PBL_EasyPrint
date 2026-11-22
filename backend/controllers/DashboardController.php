<?php
// backend/controllers/DashboardController.php
// CP-3: Dashboard admin (operasional) & owner (eksekutif) + Laporan transaksi

const STATUS_SELESAI = "('Selesai', 'Diambil')";

// GET /api/dashboard
function dashboard(): void
{
    require_user(['admin', 'owner']);
    $pdo = db();

    // Query lama CP-3 (dipertahankan untuk kompatibilitas).
    $rekapBulanan = $pdo->query(
        'SELECT EXTRACT(YEAR FROM created_at)::integer AS tahun,
                EXTRACT(MONTH FROM created_at)::integer AS bulan,
                SUM(total_harga) AS pendapatan,
                COUNT(id) AS total_pesanan
         FROM public.pesanan
         WHERE status IN ' . STATUS_SELESAI . '
         GROUP BY 1, 2
         ORDER BY tahun, bulan'
    )->fetchAll();

    $stokKritis = $pdo->query(
        'SELECT id, nama_bahan, stok, satuan, reorder_point, status
         FROM public.bahan_baku
         WHERE stok <= reorder_point
         ORDER BY (stok / NULLIF(reorder_point, 0)) ASC NULLS FIRST, nama_bahan'
    )->fetchAll();

    // CTE: pendapatan bulan ini vs bulan lalu (perbandingan periode untuk owner).
    $kpi = $pdo->query(
        "WITH per_bulan AS (
             SELECT date_trunc('month', created_at) AS bulan,
                    SUM(total_harga) AS pendapatan,
                    COUNT(*)         AS pesanan
             FROM public.pesanan
             WHERE status IN " . STATUS_SELESAI . "
             GROUP BY 1
         )
         SELECT
             COALESCE((SELECT pendapatan FROM per_bulan WHERE bulan = date_trunc('month', CURRENT_DATE)), 0) AS pendapatan_bulan_ini,
             COALESCE((SELECT pendapatan FROM per_bulan WHERE bulan = date_trunc('month', CURRENT_DATE) - INTERVAL '1 month'), 0) AS pendapatan_bulan_lalu,
             -- Periode setara bulan lalu (tgl 1 s.d. tanggal yang sama) agar perbandingan adil.
             (SELECT COALESCE(SUM(total_harga), 0) FROM public.pesanan
               WHERE status IN " . STATUS_SELESAI . "
                 AND created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '1 month'
                 AND created_at <  (CURRENT_DATE + 1) - INTERVAL '1 month') AS pendapatan_periode_sama_bulan_lalu,
             COALESCE((SELECT pesanan FROM per_bulan WHERE bulan = date_trunc('month', CURRENT_DATE)), 0) AS pesanan_selesai_bulan_ini,
             (SELECT COUNT(*) FROM public.pesanan WHERE status IN ('Diverifikasi', 'Diproses')) AS pesanan_diproses,
             (SELECT COUNT(*) FROM public.pesanan
               WHERE status IN ('Diverifikasi', 'Diproses')
                 AND created_at >= date_trunc('month', CURRENT_DATE)) AS pesanan_diproses_bulan_ini,
             (SELECT COUNT(*) FROM public.bahan_baku WHERE stok <= reorder_point) AS bahan_kritis"
    )->fetch();

    $pembanding = (float) $kpi['pendapatan_periode_sama_bulan_lalu'];
    $kpi['pertumbuhan_persen'] = $pembanding > 0
        ? round(((float) $kpi['pendapatan_bulan_ini'] - $pembanding) / $pembanding * 100, 1)
        : null;

    // Tren 7 hari: generate_series + LEFT JOIN agar hari tanpa pesanan tetap muncul (0).
    $tren = $pdo->query(
        "SELECT to_char(h.tgl, 'YYYY-MM-DD') AS tanggal,
                EXTRACT(ISODOW FROM h.tgl)::integer AS hari_ke,
                COUNT(p.id) AS jumlah_pesanan,
                COALESCE(SUM(p.total_harga) FILTER (WHERE p.status <> 'Ditolak'), 0) AS nilai
         FROM generate_series(CURRENT_DATE - 6, CURRENT_DATE, INTERVAL '1 day') AS h(tgl)
         LEFT JOIN public.pesanan p ON p.created_at::date = h.tgl::date
         GROUP BY h.tgl
         ORDER BY h.tgl"
    )->fetchAll();

    // Layanan terpopuler 30 hari: GROUP BY + HAVING + persentase via window function.
    $populer = $pdo->query(
        "SELECT l.id, l.nama_layanan,
                COUNT(DISTINCT dp.pesanan_id) AS jumlah_pesanan,
                SUM(dp.subtotal)              AS nilai,
                ROUND(100.0 * COUNT(DISTINCT dp.pesanan_id)
                      / SUM(COUNT(DISTINCT dp.pesanan_id)) OVER (), 1) AS persen
         FROM public.detail_pesanan dp
         JOIN public.layanan l ON l.id = dp.layanan_id
         JOIN public.pesanan p ON p.id = dp.pesanan_id
         WHERE p.status <> 'Ditolak'
           AND p.created_at >= CURRENT_DATE - INTERVAL '30 days'
         GROUP BY l.id, l.nama_layanan
         HAVING COUNT(DISTINCT dp.pesanan_id) > 0
         ORDER BY jumlah_pesanan DESC, nilai DESC
         LIMIT 5"
    )->fetchAll();

    $perStatus = $pdo->query(
        'SELECT status, COUNT(*) AS jumlah FROM public.pesanan GROUP BY status'
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    // Prioritas pekerjaan admin: pesanan yang masih perlu ditindaklanjuti, terlama dulu.
    $prioritas = $pdo->query(
        "SELECT p.id, p.kode_pesanan, u.nama AS nama_pelanggan, p.status, p.created_at, p.total_harga
         FROM public.pesanan p
         JOIN public.users u ON u.id = p.user_id
         WHERE p.status IN ('Pending', 'Diverifikasi', 'Diproses', 'Tertunda', 'Tertunda - Stok Tidak Tersedia')
         ORDER BY CASE p.status WHEN 'Pending' THEN 1 WHEN 'Diverifikasi' THEN 2
                  WHEN 'Tertunda - Stok Tidak Tersedia' THEN 3 WHEN 'Tertunda' THEN 3 ELSE 4 END,
                  p.created_at
         LIMIT 8"
    )->fetchAll();

    json_success([
        'rekap_omzet' => $rekapBulanan,
        'stok_kritis' => $stokKritis,
        'kpi' => $kpi,
        'tren_7_hari' => $tren,
        'layanan_populer' => $populer,
        'per_status' => $perStatus,
        'prioritas' => $prioritas,
    ]);
}

// GET /api/laporan/transaksi?dari=YYYY-MM-DD&sampai=YYYY-MM-DD
function laporan_transaksi(): void
{
    require_user(['owner', 'admin']);
    $dari = date_query('dari') ?? date('Y-m-01');
    $sampai = date_query('sampai') ?? date('Y-m-d');
    if ($dari > $sampai) {
        fail('Tanggal "dari" tidak boleh setelah tanggal "sampai".');
    }
    $pdo = db();
    $p = ['dari' => $dari, 'sampai' => $sampai];

    // Fungsi fn_ringkasan_pesanan (dari dump tim): seluruh pesanan masuk pada periode.
    $stmt = $pdo->prepare('SELECT jumlah_pesanan, total_nilai_pesanan FROM public.fn_ringkasan_pesanan(:dari, :sampai)');
    $stmt->execute($p);
    $masuk = $stmt->fetch();

    // Agregasi bersyarat (FILTER) dalam satu kali scan tabel pesanan.
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FILTER (WHERE status IN " . STATUS_SELESAI . ") AS pesanan_selesai,
                COALESCE(SUM(total_harga) FILTER (WHERE status IN " . STATUS_SELESAI . "), 0) AS penjualan,
                COUNT(*) FILTER (WHERE status = 'Ditolak') AS ditolak
         FROM public.pesanan
         WHERE created_at >= :dari AND created_at < CAST(:sampai AS date) + 1"
    );
    $stmt->execute($p);
    $ringkas = $stmt->fetch();

    // View v_detail_pesanan (dari dump tim): rincian transaksi selesai.
    $stmt = $pdo->prepare(
        "SELECT v.pesanan_id, v.kode_pesanan, v.nama_pelanggan,
                STRING_AGG(v.nama_layanan, ', ' ORDER BY v.layanan_id) AS layanan,
                SUM(v.jumlah) AS total_item, v.total_harga, v.status, v.created_at
         FROM public.v_detail_pesanan v
         WHERE v.created_at >= :dari AND v.created_at < CAST(:sampai AS date) + 1
           AND v.status IN ('Selesai', 'Diambil')
         GROUP BY v.pesanan_id, v.kode_pesanan, v.nama_pelanggan, v.total_harga, v.status, v.created_at
         ORDER BY v.created_at DESC"
    );
    $stmt->execute($p);
    $transaksi = $stmt->fetchAll();

    // CTE + RANK(): layanan teratas berdasarkan nilai penjualan pada periode.
    $stmt = $pdo->prepare(
        "WITH penjualan_layanan AS (
             SELECT v.layanan_id, v.nama_layanan, v.satuan,
                    SUM(v.jumlah)   AS kuantitas,
                    SUM(v.subtotal) AS nilai
             FROM public.v_detail_pesanan v
             WHERE v.created_at >= :dari AND v.created_at < CAST(:sampai AS date) + 1
               AND v.status IN ('Selesai', 'Diambil')
             GROUP BY v.layanan_id, v.nama_layanan, v.satuan
         )
         SELECT layanan_id, nama_layanan, satuan, kuantitas, nilai,
                RANK() OVER (ORDER BY nilai DESC) AS peringkat
         FROM penjualan_layanan
         ORDER BY peringkat
         LIMIT 5"
    );
    $stmt->execute($p);
    $teratas = $stmt->fetchAll();

    // Sirkulasi bahan: stok masuk & keluar per bahan pada periode (LEFT JOIN agar semua bahan tampil).
    $stmt = $pdo->prepare(
        "SELECT b.id, b.nama_bahan, b.satuan, b.stok AS stok_saat_ini,
                COALESCE(SUM(r.jumlah) FILTER (WHERE r.jumlah > 0), 0)  AS masuk,
                COALESCE(SUM(-r.jumlah) FILTER (WHERE r.jumlah < 0), 0) AS keluar
         FROM public.bahan_baku b
         LEFT JOIN public.riwayat_stok r
                ON r.bahan_baku_id = b.id
               AND r.created_at >= :dari AND r.created_at < CAST(:sampai AS date) + 1
         GROUP BY b.id, b.nama_bahan, b.satuan, b.stok
         ORDER BY keluar DESC, b.nama_bahan"
    );
    $stmt->execute($p);
    $sirkulasi = $stmt->fetchAll();

    // Nilai bahan keluar (rupiah) = SUM(keluar x harga_satuan) via subquery.
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(x.keluar * b.harga_satuan), 0)
         FROM (SELECT bahan_baku_id, SUM(-jumlah) AS keluar
               FROM public.riwayat_stok
               WHERE jumlah < 0 AND jenis = 'keluar'
                 AND created_at >= :dari AND created_at < CAST(:sampai AS date) + 1
               GROUP BY bahan_baku_id) x
         JOIN public.bahan_baku b ON b.id = x.bahan_baku_id"
    );
    $stmt->execute($p);
    $nilaiBahan = $stmt->fetchColumn();

    json_success([
        'periode' => ['dari' => $dari, 'sampai' => $sampai],
        'ringkasan' => [
            'pesanan_masuk' => (int) $masuk['jumlah_pesanan'],
            'nilai_pesanan_masuk' => $masuk['total_nilai_pesanan'],
            'pesanan_selesai' => (int) $ringkas['pesanan_selesai'],
            'pesanan_ditolak' => (int) $ringkas['ditolak'],
            'penjualan' => $ringkas['penjualan'],
            'nilai_bahan_keluar' => $nilaiBahan,
        ],
        'transaksi' => $transaksi,
        'layanan_teratas' => $teratas,
        'sirkulasi_bahan' => $sirkulasi,
    ]);
}

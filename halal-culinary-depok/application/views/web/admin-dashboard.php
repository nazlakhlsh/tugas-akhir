<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Panel'); ?> - HalalCulinary Depok</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
            --background-color: #f4f7f6;
            --text-color: #333;
            --card-bg-color: #ffffff;
            --border-color: #e0e0e0;
            --header-height: 60px;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .site-header {
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: white;
            height: var(--header-height);
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-sizing: border-box;
        }

        .site-header .logo a,
        .site-header .user-nav a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        /* === BAGIAN PERBAIKAN ADA DI SINI === */
        .site-header .user-nav {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        /* ===================================== */

        .site-header .logo {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            height: calc(100vh - var(--header-height));
            position: fixed;
            top: var(--header-height);
            left: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            transition: background-color 0.2s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--secondary-color);
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 250px;
            margin-top: var(--header-height);
            padding: 30px;
        }

        .content-container {
            background-color: var(--card-bg-color);
            padding: 30px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .content-container h1,
        .content-container h2 {
            margin-top: 0;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.95rem;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f4f7f6;
            font-weight: 600;
        }

        .table img {
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }

        .btn-warning {
            background-color: #f39c12;
            color: white;
        }

        .btn-danger {
            background-color: #c0392b;
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            text-transform: capitalize;
            display: inline-block;
        }

        /* REVISI: Tambahkan class untuk status dalam Bahasa Indonesia */

        .status-terverifikasi {
            background-color: #28a745;
            /* Hijau */
        }

        .status-pending {
            background-color: #ffc107;
            /* Kuning */
            color: #333;
        }

        .status-ditolak {
            background-color: #dc3545;
            /* Merah */
        }
    </style>
</head>

<body>

    <header class="site-header">
        <div class="logo">
            <a href="<?= site_url('admin') ?>"><i class="fas fa-shield-halved"></i> <span>Admin Panel</span></a>
        </div>
        <nav class="user-nav">
            <a href="<?= site_url() ?>">
                <i class="fas fa-home"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <a href="<?= site_url('auth/logout') ?>">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </header>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Dashboard Admin</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= site_url('admin') ?>" class="<?= ($active_page == 'dashboard') ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard </a></li>
            <li><a href="<?= site_url('admin/manajemen_kuliner') ?>" class="<?= ($active_page == 'manajemen_kuliner' || $active_page == 'detail_kuliner') ? 'active' : '' ?>"><i class="fas fa-utensils"></i> Kelola Kuliner </a></li>
            <li><a href="<?= site_url('admin/manajemen_users') ?>" class="<?= ($active_page == 'manajemen_users') ? 'active' : '' ?>"><i class="fas fa-users"></i> Kelola User </a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="content-container">
            <h1><?= htmlspecialchars($title); ?></h1>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
            <?php endif; ?>

            <?php if ($active_page == 'dashboard') : ?>

                <h2>Statistik Sistem</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
                    <div style="background-color: var(--primary-color); color: white; padding: 20px; border-radius: 5px;">
                        <h3 style="margin:0; font-size: 2rem;"><?= $statistics['total_kuliner']; ?></h3>
                        <p>Total Outlet</p>
                    </div>
                    <div style="background-color: #28a745; color: white; padding: 20px; border-radius: 5px;">
                        <h3 style="margin:0; font-size: 2rem;"><?= $statistics['verified_kuliner']; ?></h3>
                        <p>Terverifikasi</p>
                    </div>
                    <div style="background-color: #ffc107; color: #333; padding: 20px; border-radius: 5px;">
                        <h3 style="margin:0; font-size: 2rem;"><?= $statistics['pending_kuliner']; ?></h3>
                        <p>Pending</p>
                    </div>
                    <div style="background-color: var(--secondary-color); color: white; padding: 20px; border-radius: 5px;">
                        <h3 style="margin:0; font-size: 2rem;"><?= $statistics['total_users']; ?></h3>
                        <p>Total User</p>
                    </div>
                </div>

                <h2>Menunggu Verifikasi (<?= count($pending_kuliner); ?>)</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Usaha</th>
                            <th>Pemilik</th>
                            <th>Kategori</th>
                            <th>Tgl Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_kuliner)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">👍 Semua pendaftaran sudah diproses.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending_kuliner as $kuliner): ?>
                                <tr>
                                    <td><?= htmlspecialchars($kuliner->nama_kuliner); ?></td>
                                    <td><?= htmlspecialchars($kuliner->nama_pemilik); ?></td>
                                    <td><?= htmlspecialchars($kuliner->nama_kategori); ?></td>
                                    <td><?= date('d M Y, H:i', strtotime($kuliner->created_at)); ?></td>
                                    <td><a href="<?= site_url('admin/detail_kuliner/' . $kuliner->id); ?>" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Detail</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($active_page == 'manajemen_kuliner') : ?>

                <div style="margin-bottom: 20px;">
                    <strong>Filter Status:</strong>
                    <a href="<?= site_url('admin/manajemen_kuliner/all'); ?>" class="btn btn-sm" style="background-color: <?= ($current_status == 'all') ? 'var(--primary-color)' : '#7f8c8d'; ?>; color:white;">Semua</a>
                    <a href="<?= site_url('admin/manajemen_kuliner/terverifikasi'); ?>" class="btn btn-sm" style="background-color: <?= ($current_status == 'terverifikasi') ? '#28a745' : '#7f8c8d'; ?>; color:white;">Terverifikasi</a>
                    <a href="<?= site_url('admin/manajemen_kuliner/pending'); ?>" class="btn btn-sm" style="background-color: <?= ($current_status == 'pending') ? '#ffc107' : '#7f8c8d'; ?>; color:<?= ($current_status == 'pending') ? '#333' : 'white'; ?>;">Pending</a>
                    <a href="<?= site_url('admin/manajemen_kuliner/ditolak'); ?>" class="btn btn-sm" style="background-color: <?= ($current_status == 'ditolak') ? '#dc3545' : '#7f8c8d'; ?>; color:white;">Ditolak</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Usaha</th>
                            <th>Pemilik</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($list_kuliner)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">Tidak ada data untuk status '<?= htmlspecialchars($current_status); ?>'.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($list_kuliner as $kuliner): ?>
                                <tr>
                                    <td><img src="<?= base_url('uploads/kuliner/' . ($kuliner->foto_utama ?: 'default.png')); ?>" width="80" alt="Foto"></td>
                                    <td><?= htmlspecialchars($kuliner->nama_kuliner); ?></td>
                                    <td><?= htmlspecialchars($kuliner->nama_pemilik); ?></td>
                                    <td><?= htmlspecialchars($kuliner->nama_kategori); ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($kuliner->status_verifikasi); ?>">
                                            <?= htmlspecialchars($kuliner->status_verifikasi); ?>
                                        </span>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <a href="<?= site_url('admin/detail_kuliner/' . $kuliner->id); ?>" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($active_page == 'detail_kuliner') : ?>

                <table class="table">
                    <tr>
                        <th colspan="2" style="background-color: #f4f7f6;">Informasi Pemilik</th>
                    </tr>
                    <tr>
                        <th style="width: 30%;">Nama Pemilik</th>
                        <td><?= htmlspecialchars($kuliner->nama_pemilik) ?></td>
                    </tr>

                    <tr>
                        <th colspan="2" style="background-color: #f4f7f6;">Informasi Usaha</th>
                    </tr>
                    <tr>
                        <th>Nama Usaha</th>
                        <td><?= htmlspecialchars($kuliner->nama_kuliner) ?></td>
                    </tr>

                    <tr>
                        <th>Foto Utama</th>
                        <td>
                            <?php if (!empty($kuliner->foto_utama)): ?>
                                <a href="<?= base_url('uploads/kuliner/' . $kuliner->foto_utama) ?>" target="_blank"><img src="<?= base_url('uploads/kuliner/' . $kuliner->foto_utama) ?>" alt="Foto Utama" width="250"></a>
                            <?php else: ?>
                                <em>Tidak ada gambar</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Foto Menu</th>
                        <td>
                            <?php if (!empty($kuliner->foto_menu)): ?>
                                <a href="<?= base_url('uploads/kuliner/' . $kuliner->foto_menu) ?>" target="_blank"><img src="<?= base_url('uploads/kuliner/' . $kuliner->foto_menu) ?>" alt="Foto Menu" width="250"></a>
                            <?php else: ?>
                                <em>Tidak ada gambar menu</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Status Verifikasi</th>
                        <td><span class="status-badge status-<?= strtolower($kuliner->status_verifikasi); ?>"><?= htmlspecialchars(ucfirst($kuliner->status_verifikasi)) ?></span></td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td><?= htmlspecialchars($kuliner->nama_kategori) ?></td>
                    </tr>
                    <tr>
                        <th>Jam Operasional</th>
                        <td><?= htmlspecialchars($kuliner->jam_buka ?: '-') ?></td>
                    </tr>

                    <tr>
                        <th colspan="2" style="background-color: #f4f7f6;">Detail Lokasi</th>
                    </tr>
                    <tr>
                        <th>Alamat Lengkap</th>
                        <td style="line-height: 1.6;"><?= nl2br(htmlspecialchars($kuliner->alamat)) ?></td>
                    </tr>
                    <tr>
                        <th>Koordinat</th>
                        <td><?= htmlspecialchars($kuliner->latitude) ?>, <?= htmlspecialchars($kuliner->longitude) ?></td>
                    </tr>

                    <tr>
                        <th colspan="2" style="background-color: #f4f7f6;">Sertifikasi Halal</th>
                    </tr>
                    <tr>
                        <th>Status Sertifikat</th>
                        <td><?= htmlspecialchars($kuliner->status_sertifikat ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Nomor Sertifikat</th>
                        <td><?= htmlspecialchars($kuliner->nomor_sertifikat ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Berlaku Sampai</th>
                        <td><?= !empty($kuliner->tanggal_berlaku) ? date('d F Y', strtotime($kuliner->tanggal_berlaku)) : '-' ?></td>
                    </tr>
                </table>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                    <a href="<?= site_url('admin/manajemen_kuliner'); ?>" class="btn" style="background-color: #7f8c8d; color: white;"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <?php if ($kuliner->status_verifikasi == 'pending'): ?>
                        <a href="<?= site_url('admin/verifikasi/' . $kuliner->id); ?>" class="btn btn-success"><i class="fas fa-check"></i> Setujui</a>
                        <a href="<?= site_url('admin/tolak/' . $kuliner->id); ?>" class="btn btn-warning"><i class="fas fa-times"></i> Tolak</a>
                    <?php endif; ?>
                    <a href="<?= site_url('admin/hapus_kuliner/' . $kuliner->id); ?>" class="btn btn-danger" onclick="return confirm('PERINGATAN! Anda akan menghapus data ini secara permanen. Lanjutkan?');"><i class="fas fa-trash"></i> Hapus Permanen</a>
                </div>

            <?php elseif ($active_page == 'manajemen_users') : ?>

                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        foreach ($users_list as $user_item):
                        ?>
                            <tr>
                                <td><?= $nomor; ?></td>
                                <td><?= htmlspecialchars($user_item->nama_lengkap); ?></td>
                                <td><?= htmlspecialchars($user_item->email); ?></td>
                                <td><span class="btn-sm" style="color:white; border-radius:12px; background-color: <?= ($user_item->role == 'admin') ? '#c0392b' : 'var(--secondary-color)'; ?>;"><?= htmlspecialchars($user_item->role); ?></span></td>
                                <td>
                                    <?php if ($user_item->id != $this->session->userdata('id')): ?>
                                        <a href="<?= site_url('admin/hapus_user/' . $user_item->id); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus user ini?');"><i class="fas fa-trash"></i> Hapus</a>
                                    <?php else: ?>
                                        <span class="btn btn-sm" style="background:#bdc3c7; cursor: not-allowed;">(Akun Anda)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php
                            $nomor++;
                        endforeach;
                        ?>
                    </tbody>
                </table>

            <?php endif; ?>

        </div>
    </main>

</body>

</html>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - HalalCulinary Depok</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #2c5530;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            background-color: #4a7c59;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #2c5530;
        }

        .alert-error {
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }

        .link {
            margin-top: 20px;
        }

        .link a {
            color: #4a7c59;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Buat Akun Baru</h2>
        <?php if (validation_errors()): ?>
            <div class="alert-error"><?= validation_errors(); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('auth/process_register') ?>" method="POST">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?= set_value('nama_lengkap') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= set_value('email') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="passconf">Konfirmasi Password</label>
                <input type="password" name="passconf" id="passconf" required>
            </div>
            <button type="submit" class="btn">Daftar</button>
        </form>
        <div class="link">
            Sudah punya akun? <a href="<?= site_url('auth/login') ?>">Login di sini</a>
        </div>
    </div>
</body>

</html>
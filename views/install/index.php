<!DOCTYPE html>
<html lang="ko"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Neuron AI PHP</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
        }
        .install-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            color: white;
        }
        .install-card h2 {
            color: #ffffff;
            font-weight: 700;
        }
        .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .form-label {
            color: #ffffff !important;
            font-weight: 500;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.7);
            border-color: #6366f1;
            color: white;
            box-shadow: none;
        }
        .btn-primary {
            background: #6366f1;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <h2 class="text-center mb-4">Core Installation</h2>
        <p class="text-muted text-center mb-5">Enter your database and environment settings to get started.</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" style="background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.5);">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="/install" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small">Database Host</label>
                <input type="text" name="db_host" class="form-control" value="localhost" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Database Name</label>
                <input type="text" name="db_name" class="form-control" placeholder="ai_php" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Database User</label>
                <input type="text" name="db_user" class="form-control" placeholder="root" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Database Password</label>
                <input type="password" name="db_pass" class="form-control">
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small">Site URL</label>
                <input type="text" name="app_url" class="form-control" value="http://<?= $_SERVER['HTTP_HOST'] ?>" required>
            </div>

            <!--관리자 정보 입력-->
            <div class="mb-3">
                <label class="form-label text-muted small">Admin User ID</label>
                <input type="text" name="admin_user_id" class="form-control" placeholder="admin" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Admin Username</label>
                <input type="text" name="admin_username" class="form-control" placeholder="Admin Name" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Admin Password</label>
                <input type="password" name="admin_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">Admin Email</label>
                <input type="email" name="admin_email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Install Framework</button>
        </form>
    </div>
</body>
</html>

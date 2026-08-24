<?php
session_start();

// إذا كان المستخدم مسجل دخول، نوجهوه حسب الـ role
if (isset($_SESSION['user_id'])) {
    $allowed_roles = ['super_admin', 'admin_dept', 'admin'];
    if (in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Gestion des Congés</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 380px; }
        .login-card h2 { margin-bottom: 20px; font-size: 20px; color: #0f172a; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; margin-bottom: 5px; color: #475569; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-radius; }
        .btn-submit { width: 100%; padding: 10px; background: #6366f1; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #4f46e5; }
        .error { background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>📅 Gestion des Congés</h2>
        <?php if (isset($_GET['error'])): ?>
            <div class="error">Email ou mot de passe incorrect.</div>
        <?php endif; ?>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label>Adresse Email</label>
                <input type="email" name="email" required placeholder="exemple@domaine.com">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
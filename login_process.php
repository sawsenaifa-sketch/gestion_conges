<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // حفظ بيانات الجلسة
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nom']      = $user['nom'];
            $_SESSION['prenom']   = $user['prenom'];
            $_SESSION['role']     = trim(strtolower($user['role'])); // يحولها لأحرف صغيرة تلقائياً
            $_SESSION['group_id'] = $user['group_id'];

            // 🎯 الشرط المباشر والواضح:
            // أي أدمن (super_admin أو admin) يذهب فوراً لـ admin/dashboard.php
            if (in_array($_SESSION['role'], ['super_admin', 'admin_dept'])) {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit;
        }
    }

    header("Location: index.php?error=1");
    exit;
}
?>
<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

// التأكد من أن المستخدم أدمن
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['super_admin', 'admin_dept', 'admin'])) {
    header("Location: ../user/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error   = '';

// 1. تحديث البيانات عند الضغط على زر الحفظ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']);
    $prenom   = trim($_POST['prenom']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($nom) && !empty($prenom) && !empty($email)) {
        if (!empty($password)) {
            // تحديث البيانات مع كلمة السر الجديدة
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $hashed_password, $user_id]);
        } else {
            // تحديث البيانات فقط بدون تغيير كلمة السر
            $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $user_id]);
        }

        // تحديث الـ Session
        $_SESSION['nom']    = $nom;
        $_SESSION['prenom'] = $prenom;
        
        $message = "Profil mis à jour avec succès !";
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

// 2. جلب بيانات الأدمن الحالي
$stmt = $pdo->prepare("SELECT u.*, g.nom as group_nom FROM users u LEFT JOIN `groups` g ON g.id = u.group_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Gestion Congés</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: #f8fafc; color: #334155; }
        .page-container { display: flex; width: 100vw; min-height: 100vh; }
        .custom-main { flex: 1; padding: 30px; overflow-y: auto; }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border 0.2s; }
        .form-control:focus { border-color: #6366f1; }
        .form-control[readonly] { background-color: #f1f5f9; cursor: not-allowed; color: #64748b; }

        .btn-submit { background-color: #6366f1; color: white; border: none; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background-color: #4f46e5; }
    </style>
</head>
<body>
    <div class="page-container">
        
        <!-- Sidebar الموحدة -->
        <?php include 'sidebar.php'; ?>

        <main class="custom-main">
            <header style="margin-bottom: 25px;">
                <h1 style="font-size: 24px; font-weight: 700; color: #0f172a;">Mon Profil</h1>
                <p style="color: #64748b; font-size: 13px; margin-top: 4px;">Gérez vos informations personnelles et votre mot de passe</p>
            </header>

            <?php if ($message): ?>
                <div style="background: #dcfce7; color: #15803d; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500;">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 600px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <form method="POST">
                    
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Nom</label>
                            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Prénom</label>
                            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Adresse Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Rôle</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Groupe</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['group_nom'] ?? 'Aucun') ?>" readonly>
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;">

                    <div class="form-group">
                        <label>Nouveau mot de passe <span style="font-weight: normal; color: #94a3b8;">(Laissez vide pour ne pas changer)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>

                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</body>
</html>
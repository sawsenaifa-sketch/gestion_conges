<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireAdmin();

// 1. تحديد ID المجموعة بناءً على نوع المستخدم
$group_id = $_GET['id'] ?? null;

// إذا كان admin_dept يُجبر على رؤية مجموعته فقط
if ($_SESSION['role'] === 'admin_dept') {
    $group_id = $_SESSION['group_id'] ?? null;
}

// إذا لم يتم تحديد مجموعة بالكل
if (!$group_id) {
    header("Location: " . ($_SESSION['role'] === 'super_admin' ? 'groupes.php' : 'dashboard.php'));
    exit;
}

// 2. جلب معلومات المجموعة
$stmtGroup = $pdo->prepare("SELECT * FROM groups WHERE id = ?");
$stmtGroup->execute([$group_id]);
$groupe = $stmtGroup->fetch(PDO::FETCH_ASSOC);

if (!$groupe) {
    header("Location: " . ($_SESSION['role'] === 'super_admin' ? 'groupes.php' : 'dashboard.php'));
    exit;
}

// 3. جلب أعضاء هذا المجموعة
$stmtUsers = $pdo->prepare("SELECT u.*, 
                            (SELECT COUNT(*) FROM leave_requests lr WHERE lr.user_id = u.id AND lr.statut = 'accepte') as total_conges
                            FROM users u 
                            WHERE u.group_id = ? AND u.role = 'user' 
                            ORDER BY u.nom ASC");
$stmtUsers->execute([$group_id]);
$membres = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membres du groupe <?= htmlspecialchars($groupe['nom']) ?> - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #334155; }

        .page-container { display: flex; width: 100vw; min-height: 100vh; }

        .custom-main { flex: 1; padding: 25px 35px; overflow-y: auto; }

        .btn-back {
            display: inline-flex; align-items: center; gap: 8px; color: #6366f1;
            text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px;
        }
        .btn-back:hover { text-decoration: underline; }

        .badge-conge { background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .user-avatar { width: 35px; height: 35px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
    </style>
</head>
<body>
    <div class="page-container">
        
        <!-- استدعاء القائمة الجانبية الموحدة -->
        <?php include 'sidebar.php'; ?>

        <main class="custom-main">
            
            <!-- زر العودة يظهر فقط للـ super_admin -->
            <?php if ($_SESSION['role'] === 'super_admin'): ?>
                <a href="groupes.php" class="btn-back">← Retour aux groupes</a>
            <?php endif; ?>

            <header class="topbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h1>Département : <?= htmlspecialchars($groupe['nom']) ?></h1>
                    <p style="color: #64748b; font-size: 13px; margin-top: 4px;">Liste des employés appartenant à ce groupe</p>
                </div>
                <div class="user-badge" style="font-size: 13px; font-weight: 600;">👋 <?= htmlspecialchars($_SESSION['nom'] ?? 'Admin') ?></div>
            </header>

            <div class="panel" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <h2 style="font-size: 15px; font-weight: 700; margin-bottom: 15px;">Membres du groupe (<?= count($membres) ?>)</h2>

                <?php if (empty($membres)): ?>
                    <p style="color: #94a3b8; text-align: center; padding: 20px 0;">Aucun employé dans ce groupe pour le moment.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #f1f5f9; text-align: left;">
                                <th style="padding: 10px 12px;">Employé</th>
                                <th style="padding: 10px 12px;">Email</th>
                                <th style="padding: 10px 12px;">Congés Validés</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($membres as $m): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px; display: flex; align-items: center; gap: 10px;">
                                    <div class="user-avatar"><?= strtoupper(substr($m['prenom'], 0, 1)) ?></div>
                                    <strong><?= htmlspecialchars($m['prenom'].' '.$m['nom']) ?></strong>
                                </td>
                                <td style="padding: 12px; color: #64748b;"><?= htmlspecialchars($m['email']) ?></td>
                                <td style="padding: 12px;">
                                    <span class="badge-conge"><?= $m['total_conges'] ?> congé(s)</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
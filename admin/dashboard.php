<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';

$role = $_SESSION['role'];
$my_group_id = $_SESSION['group_id'] ?? null;

$columnCheck = $pdo->query("SHOW COLUMNS FROM leave_requests LIKE 'statut'")->fetch();
$statusCol = $columnCheck ? 'statut' : 'status';

if ($role === 'super_admin') {
    $countPending  = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE $statusCol IN ('pending', 'en_attente')")->fetchColumn();
    $countApproved = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE $statusCol IN ('approved', 'accepte', 'approuve')")->fetchColumn();
    $countRejected = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE $statusCol IN ('rejected', 'refuse')")->fetchColumn();

    $stmtRequests = $pdo->prepare("
        SELECT lr.*, u.nom AS user_nom, u.prenom AS user_prenom, g.nom AS group_nom 
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        LEFT JOIN groups g ON u.group_id = g.id
        ORDER BY lr.created_at DESC
    ");
    $stmtRequests->execute();
} else {
    // admin_dept : uniquement les demandes de son groupe
    $stmtP = $pdo->prepare("SELECT COUNT(*) FROM leave_requests lr JOIN users u ON u.id = lr.user_id WHERE u.group_id = ? AND lr.$statusCol IN ('pending','en_attente')");
    $stmtP->execute([$my_group_id]);
    $countPending = $stmtP->fetchColumn();

    $stmtA = $pdo->prepare("SELECT COUNT(*) FROM leave_requests lr JOIN users u ON u.id = lr.user_id WHERE u.group_id = ? AND lr.$statusCol IN ('approved','accepte','approuve')");
    $stmtA->execute([$my_group_id]);
    $countApproved = $stmtA->fetchColumn();

    $stmtR = $pdo->prepare("SELECT COUNT(*) FROM leave_requests lr JOIN users u ON u.id = lr.user_id WHERE u.group_id = ? AND lr.$statusCol IN ('rejected','refuse')");
    $stmtR->execute([$my_group_id]);
    $countRejected = $stmtR->fetchColumn();

    $stmtRequests = $pdo->prepare("
        SELECT lr.*, u.nom AS user_nom, u.prenom AS user_prenom, g.nom AS group_nom 
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        LEFT JOIN groups g ON u.group_id = g.id
        WHERE u.group_id = ?
        ORDER BY lr.created_at DESC
    ");
    $stmtRequests->execute([$my_group_id]);
}
$requests = $stmtRequests->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin</title>
</head>
<body style="margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; background-color: #f8fafc;">

<!-- حاوية Flex تجمع الـ Sidebar والمحتوى جنب بعضهم -->
<div style="display: flex; min-height: 100vh; width: 100%;">

    <!-- استدعاء القائمة الجانبية -->
    <?php include 'sidebar.php'; ?>

    <!-- باقي المحتوى في الجهة اليمين -->
    <main style="flex: 1; padding: 30px; box-sizing: border-box; overflow-x: hidden;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="font-size: 24px; font-weight: bold; color: #0f172a; margin: 0;">Tableau de bord</h2>
            <div style="background: #fffbeb; color: #d97706; padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 13px;">
                👋 <?= htmlspecialchars($_SESSION['prenom'] ?? 'Super Admin') ?>
            </div>
        </div>

        <!-- كروت الإحصائيات -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
            <div style="background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <span style="color: #64748b; font-size: 13px;">En Attente</span>
                <h3 style="font-size: 28px; margin: 5px 0 0 0; font-weight: bold; color: #1e293b;"><?= $countPending ?></h3>
            </div>
            <div style="background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <span style="color: #64748b; font-size: 13px;">Approuvés</span>
                <h3 style="font-size: 28px; margin: 5px 0 0 0; font-weight: bold; color: #1e293b;"><?= $countApproved ?></h3>
            </div>
            <div style="background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #ef4444; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <span style="color: #64748b; font-size: 13px;">Refusés</span>
                <h3 style="font-size: 28px; margin: 5px 0 0 0; font-weight: bold; color: #1e293b;"><?= $countRejected ?></h3>
            </div>
        </div>

        <!-- جدول الطلبات -->
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; color: #0f172a;">Toutes les demandes de congé</h3>
            
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px; font-size: 12px; color: #64748b;">EMPLOYÉ</th>
                        <th style="padding: 12px; font-size: 12px; color: #64748b;">GROUPE</th>
                        <th style="padding: 12px; font-size: 12px; color: #64748b;">DATE DÉBUT</th>
                        <th style="padding: 12px; font-size: 12px; color: #64748b;">DATE FIN</th>
                        <th style="padding: 12px; font-size: 12px; color: #64748b;">STATUT</th>
                        <th style="padding: 12px; font-size: 12px; color: #64748b;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="6" style="padding: 20px; text-align: center; color: #94a3b8;">Aucune demande trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <?php $st = strtolower($req['statut'] ?? $req['status'] ?? 'pending'); ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; font-size: 14px;">
                                <td style="padding: 14px;"><strong><?= htmlspecialchars($req['user_prenom'] . ' ' . $req['user_nom']) ?></strong></td>
                                <td style="padding: 14px; color: #64748b;"><?= htmlspecialchars($req['group_nom'] ?? '-') ?></td>
                                <td style="padding: 14px; color: #334155;"><?= htmlspecialchars($req['start_date'] ?? $req['date_debut'] ?? '') ?></td>
                                <td style="padding: 14px; color: #334155;"><?= htmlspecialchars($req['end_date'] ?? $req['date_fin'] ?? '') ?></td>
                                <td style="padding: 14px;">
                                    <?php if (in_array($st, ['approved', 'accepte', 'approuve'])): ?>
                                        <span style="background: #d1fae5; color: #059669; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Accepté</span>
                                    <?php elseif (in_array($st, ['rejected', 'refuse'])): ?>
                                        <span style="background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Refusé</span>
                                    <?php else: ?>
                                        <span style="background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px; color: #94a3b8; font-size: 12px;">Traité</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</div>

</body>
</html>
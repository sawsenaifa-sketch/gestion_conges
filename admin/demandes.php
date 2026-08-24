<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

// التأكد من أن المستخدم أدمن
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['super_admin', 'admin_dept', 'admin'])) {
    header("Location: ../user/dashboard.php");
    exit;
}

$role        = $_SESSION['role'];
$my_group_id = $_SESSION['group_id'] ?? null;
$message     = '';

// ACCEPTER ou REFUSER une demande
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action == 'accept') {
        $stmt = $pdo->prepare("UPDATE leave_requests SET statut = 'accepte' WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Demande acceptée avec succès.";
    } elseif ($action == 'refuse') {
        $stmt = $pdo->prepare("UPDATE leave_requests SET statut = 'refuse' WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Demande refusée.";
    }
}

// Récupérer les demandes selon le rôle
if ($role === 'super_admin') {
    $stmt = $pdo->prepare("
        SELECT lr.*, u.nom, u.prenom, u.group_id, g.nom as group_nom 
        FROM leave_requests lr 
        JOIN users u ON u.id = lr.user_id 
        LEFT JOIN `groups` g ON g.id = u.group_id 
        ORDER BY 
            CASE lr.statut WHEN 'en_attente' THEN 0 ELSE 1 END,
            lr.created_at DESC
    ");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT lr.*, u.nom, u.prenom, u.group_id, g.nom as group_nom 
        FROM leave_requests lr 
        JOIN users u ON u.id = lr.user_id 
        LEFT JOIN `groups` g ON g.id = u.group_id 
        WHERE u.group_id = ? 
        ORDER BY 
            CASE lr.statut WHEN 'en_attente' THEN 0 ELSE 1 END,
            lr.created_at DESC
    ");
    $stmt->execute([$my_group_id]);
}

$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour vérifier le chevauchement dans le même groupe
function checkConflit($pdo, $group_id, $date_debut, $date_fin, $exclude_user_id) {
    if (!$group_id) return 0;
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT lr.user_id) as nb 
        FROM leave_requests lr 
        JOIN users u ON u.id = lr.user_id 
        WHERE u.group_id = ? 
        AND lr.statut = 'accepte' 
        AND lr.user_id != ?
        AND lr.date_debut <= ? AND lr.date_fin >= ?
    ");
    $stmt->execute([$group_id, $exclude_user_id, $date_fin, $date_debut]);
    return $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes - Gestion Congés</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: #f8fafc; color: #334155; }
        .page-container { display: flex; width: 100vw; min-height: 100vh; }
        .custom-main { flex: 1; padding: 25px 35px; overflow-y: auto; }
        .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-accepted { background: #dcfce7; color: #15803d; }
        .status-refused { background: #fee2e2; color: #b91c1c; }
        .conflict-warning { color: #dc2626; font-size: 11px; font-weight: 600; margin-top: 4px; display: inline-block; }
        .btn-accept { background: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px; margin-right: 5px; }
        .btn-delete { background: #fee2e2; color: #ef4444; padding: 5px 10px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px; }
    </style>
</head>
<body>
    <div class="page-container">
        
        <!-- استدعاء القائمة الجانبية الموحدة -->
        <?php include 'sidebar.php'; ?>

        <main class="custom-main">
            <header class="topbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1 style="font-size: 24px; font-weight: 700; color: #0f172a;">Demandes de congé</h1>
                    <p style="color: #64748b; font-size: 12px; margin-top: 4px;">
                        <?= $role === 'super_admin' ? 'Toutes les demandes' : 'Demandes de votre groupe' ?>
                    </p>
                </div>
                <div class="user-badge" style="font-size: 13px; font-weight: 600; background:#e2e8f0; padding:6px 12px; border-radius:20px;">
                    👋 <?= htmlspecialchars($_SESSION['nom'] ?? 'Admin') ?>
                </div>
            </header>

            <?php if ($message): ?>
                <div class="alert-success" style="background: #dcfce7; color: #15803d; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px;">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="panel" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <div class="panel-header" style="margin-bottom: 15px;">
                    <h2 style="font-size: 15px; font-weight: 700;">Liste des demandes (<?= count($demandes) ?>)</h2>
                </div>

                <?php if (empty($demandes)): ?>
                    <p class="empty-state" style="color: #94a3b8; text-align: center; padding: 20px;">Aucune demande à afficher.</p>
                <?php else: ?>
                    <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #f1f5f9; text-align: left;">
                                <th style="padding: 10px 12px;">EMPLOYÉ</th>
                                <th style="padding: 10px 12px;">GROUPE</th>
                                <th style="padding: 10px 12px;">DU</th>
                                <th style="padding: 10px 12px;">AU</th>
                                <th style="padding: 10px 12px;">MOTIF</th>
                                <th style="padding: 10px 12px;">STATUT</th>
                                <th style="padding: 10px 12px;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($demandes as $d): 
                                $conflit = 0;
                                if ($d['statut'] == 'en_attente') {
                                    $conflit = checkConflit($pdo, $d['group_id'], $d['date_debut'], $d['date_fin'], $d['user_id']);
                                }
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px;"><strong><?= htmlspecialchars($d['prenom'].' '.$d['nom']) ?></strong></td>
                                <td style="padding: 12px; color: #64748b;"><?= htmlspecialchars($d['group_nom'] ?? '-') ?></td>
                                <td style="padding: 12px;"><?= date('d/m/Y', strtotime($d['date_debut'])) ?></td>
                                <td style="padding: 12px;"><?= date('d/m/Y', strtotime($d['date_fin'])) ?></td>
                                <td style="padding: 12px; color: #64748b;"><?= htmlspecialchars($d['motif'] ?: '-') ?></td>
                                <td style="padding: 12px;">
                                    <?php if ($d['statut'] == 'en_attente'): ?>
                                        <span class="status-badge status-pending">En attente</span>
                                        <?php if ($conflit > 0): ?>
                                            <br><span class="conflict-warning">⚠️ <?= $conflit ?> autre(s) du groupe déjà en congé</span>
                                        <?php endif; ?>
                                    <?php elseif ($d['statut'] == 'accepte'): ?>
                                        <span class="status-badge status-accepted">Acceptée</span>
                                    <?php else: ?>
                                        <span class="status-badge status-refused">Refusée</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell" style="padding: 12px;">
                                    <?php if ($d['statut'] == 'en_attente'): ?>
                                        <a href="?action=accept&id=<?= $d['id'] ?>" class="btn-accept" onclick="return confirm('Accepter cette demande ?')">✅ Accepter</a>
                                        <a href="?action=refuse&id=<?= $d['id'] ?>" class="btn-delete" onclick="return confirm('Refuser cette demande ?')">❌ Refuser</a>
                                    <?php else: ?>
                                        <span style="color:#b0b3b8; font-size:12px;">Traitée</span>
                                    <?php endif; ?>
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
<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT id, date_debut, date_fin, motif, statut, created_at 
    FROM leave_requests 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes - Congés</title>

    <style>
        .card-table { padding: 0 !important; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { background-color: #f8fafc; color: #475569; font-weight: 600; padding: 14px 20px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tr:last-child td { border-bottom: none; }
        
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-valide { background-color: #d1fae5; color: #059669; }
        .badge-attente { background-color: #fef3c7; color: #d97706; }
        .badge-refuse { background-color: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

    <div class="app-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <h1 class="header-title">Historique de mes demandes</h1>
            <p class="header-sub">Consultez l'état et l'historique de toutes vos demandes de congé.</p>

            <div class="card card-table">
                <table>
                    <thead>
                        <tr>
                            <th>Date de demande</th>
                            <th>Période du congé</th>
                            <th>Motif</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">
                                    Aucune demande effectuée pour le moment.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td><?= date('d/m/Y à H:i', strtotime($r['created_at'])) ?></td>
                                    <td>
                                        <strong>Du:</strong> <?= date('d/m/Y', strtotime($r['date_debut'])) ?><br>
                                        <strong>Au:</strong> <?= date('d/m/Y', strtotime($r['date_fin'])) ?>
                                    </td>
                                    <td><?= !empty($r['motif']) ? htmlspecialchars($r['motif']) : '-' ?></td>
                                    <td>
                                        <?php if ($r['statut'] === 'valide'): ?>
                                            <span class="badge badge-valide">Validé</span>
                                        <?php elseif ($r['statut'] === 'refuse'): ?>
                                            <span class="badge badge-refuse">Refusé</span>
                                        <?php else: ?>
                                            <span class="badge badge-attente">En attente</span>
                                        <?php endif; ?>
                                    </td>
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
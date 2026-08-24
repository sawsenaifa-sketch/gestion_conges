<?php
require_once '../includes/auth.php';
requireLogin(); 

require_once '../config/db.php';

// إذا دخل أدمن بالخطأ لصفحة الموظفين العاديين يرجعه لصفحة الأدمن
if (in_array($_SESSION['role'], ['super_admin', 'admin_dept', 'admin'])) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب معلومات المستخدم وقسمه
$stmtUser = $pdo->prepare("SELECT u.*, g.nom as group_nom 
                           FROM users u 
                           LEFT JOIN groups g ON g.id = u.group_id 
                           WHERE u.id = ?");
$stmtUser->execute([$user_id]);
$currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

// إحصائيات الطلبات
$pending = $pdo->prepare("SELECT COUNT(*) FROM leave_requests WHERE user_id = ? AND statut = 'en_attente'");
$pending->execute([$user_id]);
$totalPending = $pending->fetchColumn();

$accepted = $pdo->prepare("SELECT COUNT(*) FROM leave_requests WHERE user_id = ? AND statut IN ('accepte', 'valide')");
$accepted->execute([$user_id]);
$totalAccepted = $accepted->fetchColumn();

$rejected = $pdo->prepare("SELECT COUNT(*) FROM leave_requests WHERE user_id = ? AND statut = 'refuse'");
$rejected->execute([$user_id]);
$totalRejected = $rejected->fetchColumn();

// آخر 5 طلبات للمستخدم
$stmtRequests = $pdo->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmtRequests->execute([$user_id]);
$recentRequests = $stmtRequests->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Employé - Dashboard</title>

    <style>
        /* CSS الخاص بالإحصائيات والجدول داخل الـ Dashboard */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 48px; height: 48px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }

        .stat-icon.orange { background: #fef3c7; color: #d97706; }
        .stat-icon.green { background: #dcfce7; color: #15803d; }
        .stat-icon.red { background: #fee2e2; color: #ef4444; }

        .stat-info { display: flex; flex-direction: column; }
        .stat-value { font-size: 22px; font-weight: 700; color: #0f172a; }
        .stat-label { font-size: 13px; color: #64748b; font-weight: 500; }

        .badge-status { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-en_attente { background: #fef3c7; color: #d97706; }
        .badge-accepte, .badge-valide { background: #dcfce7; color: #15803d; }
        .badge-refuse { background: #fee2e2; color: #ef4444; }

        .btn-new-demande {
            background: #6366f1; color: white; padding: 10px 18px; border-radius: 8px;
            text-decoration: none; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-new-demande:hover { background: #4f46e5; }
    </style>
</head>
<body>

    <div class="app-container">
        
        <!-- استدعاء الشريط الجانبي الموحد -->
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h1 class="header-title" style="margin-bottom:0;">Bonjour, <?= htmlspecialchars(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')) ?> 👋</h1>
                    <p style="color: #64748b; font-size: 13px; margin-top: 4px;">Département : <strong><?= htmlspecialchars($currentUser['group_nom'] ?? 'Non assigné') ?></strong></p>
                </div>
                <a href="nouvelle_demande.php" class="btn-new-demande">➕ Demander un congé</a>
            </header>

            <!-- Cards الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orange">⏳</div>
                    <div class="stat-info">
                        <span class="stat-value"><?= $totalPending ?></span>
                        <span class="stat-label">En attente</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">✅</div>
                    <div class="stat-info">
                        <span class="stat-value"><?= $totalAccepted ?></span>
                        <span class="stat-label">Congés Validés</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">❌</div>
                    <div class="stat-info">
                        <span class="stat-value"><?= $totalRejected ?></span>
                        <span class="stat-label">Demandes Refusées</span>
                    </div>
                </div>
            </div>

            <!-- جدول أحدث الطلبات -->
            <div class="card" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 15px; font-weight: 700; color:#0f172a;">Mes récentes demandes</h2>
                    <a href="mes_demandes.php" style="color: #6366f1; text-decoration: none; font-size: 13px; font-weight: 600;">Voir tout →</a>
                </div>

                <?php if (empty($recentRequests)): ?>
                    <p style="color: #94a3b8; text-align: center; padding: 20px 0;">Vous n'avez pas encore soumis de demande de congé.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #f8fafc; text-align: left;">
                                <th style="padding: 10px 12px; color:#475569;">Du</th>
                                <th style="padding: 10px 12px; color:#475569;">Au</th>
                                <th style="padding: 10px 12px; color:#475569;">Motif</th>
                                <th style="padding: 10px 12px; color:#475569;">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRequests as $r): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px;"><?= date('d/m/Y', strtotime($r['date_debut'])) ?></td>
                                <td style="padding: 12px;"><?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                <td style="padding: 12px;"><?= htmlspecialchars($r['motif'] ?? '-') ?></td>
                                <td style="padding: 12px;">
                                    <?php 
                                        $statusClass = 'badge-' . $r['statut'];
                                        $statusText = [
                                            'en_attente' => 'En attente',
                                            'accepte' => 'Accepté',
                                            'valide' => 'Validé',
                                            'refuse' => 'Refusé'
                                        ][$r['statut']] ?? $r['statut'];
                                    ?>
                                    <span class="badge-status <?= $statusClass ?>"><?= $statusText ?></span>
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
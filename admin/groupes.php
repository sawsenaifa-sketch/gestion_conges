<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireAdmin();

if ($_SESSION['role'] !== 'super_admin') {
    header("Location: groupe_details.php?id=" . ($_SESSION['group_id'] ?? ''));
    exit;
}

$message = '';
$error = '';

// Ajouter un groupe / département
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom = trim($_POST['nom'] ?? '');
    if (!empty($nom)) {
        // Vérifier si le groupe existe déjà
        $check = $pdo->prepare("SELECT id FROM groups WHERE nom = ?");
        $check->execute([$nom]);
        if ($check->rowCount() > 0) {
            $error = "Ce groupe existe déjà.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO groups (nom) VALUES (?)");
            $stmt->execute([$nom]);
            $message = "Groupe ajouté avec succès !";
        }
    } else {
        $error = "Veuillez entrer un nom de groupe.";
    }
}

// Supprimer un groupe
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Dissocier les utilisateurs du groupe avant suppression
    $pdo->prepare("UPDATE users SET group_id = NULL WHERE group_id = ?")->execute([$id]);
    
    // Supprimer le groupe
    $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
    $stmt->execute([$id]);
    $message = "Groupe supprimé avec succès.";
}

// Récupérer la liste des groupes avec le nombre de membres
$stmt = $pdo->query("SELECT g.*, COUNT(u.id) as total_membres 
                     FROM groups g 
                     LEFT JOIN users u ON u.group_id = g.id 
                     GROUP BY g.id 
                     ORDER BY g.nom ASC");
$groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Groupes - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #334155; }

        .page-container { display: flex; width: 100vw; min-height: 100vh; }

        .custom-main { flex: 1; padding: 25px 35px; overflow-y: auto; }

        .form-inline {
            display: flex; gap: 10px; margin-bottom: 20px; background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;
        }
        .form-inline input {
            flex: 1; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none;
        }
        .form-inline input:focus { border-color: #6366f1; }
        .btn-add {
            background: #6366f1; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;
        }
        .btn-add:hover { background: #4f46e5; }

        .badge-count { background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        
        .btn-view { background: #e0f2fe; color: #0284c7; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px; margin-right: 5px; }
        .btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px; }
    </style>
</head>
<body>
    <div class="page-container">
        
        <!-- استدعاء القائمة الجانبية الموحدة المكتملة -->
        <?php include 'sidebar.php'; ?>

        <main class="custom-main">
            <header class="topbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Gestion des Groupes / Départements</h1>
                <div class="user-badge" style="font-size: 13px; font-weight: 600;">👋 <?= htmlspecialchars($_SESSION['nom'] ?? 'Admin Super') ?></div>
            </header>

            <?php if ($message): ?>
                <div class="alert-success" style="background: #dcfce7; color: #15803d; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-error" style="background: #fee2e2; color: #b91c1c; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout rapide -->
            <form method="POST" class="form-inline">
                <input type="hidden" name="action" value="add">
                <input type="text" name="nom" placeholder="Nom du nouveau département (ex: Informatique, RH, Finance...)" required>
                <button type="submit" class="btn-add">➕ Ajouter le groupe</button>
            </form>

            <!-- Liste des groupes -->
            <div class="panel" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <h2 style="font-size: 15px; font-weight: 700; margin-bottom: 15px;">Liste des départements (<?= count($groupes) ?>)</h2>

                <?php if (empty($groupes)): ?>
                    <p style="color: #94a3b8; text-align: center; padding: 20px 0;">Aucun groupe créé pour le moment.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #f1f5f9; text-align: left;">
                                <th style="padding: 10px 12px;">Nom du Groupe</th>
                                <th style="padding: 10px 12px;">Nombre de membres</th>
                                <th style="padding: 10px 12px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groupes as $g): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px;"><strong><?= htmlspecialchars($g['nom']) ?></strong></td>
                                <td style="padding: 12px;">
                                    <span class="badge-count"><?= $g['total_membres'] ?> employé(s)</span>
                                </td>
                                <td style="padding: 12px; text-align: right;">
                                    <a href="groupe_details.php?id=<?= $g['id'] ?>" class="btn-view">👁️ Voir membres</a>
                                    <a href="?action=delete&id=<?= $g['id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')">🗑️ Supprimer</a>
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
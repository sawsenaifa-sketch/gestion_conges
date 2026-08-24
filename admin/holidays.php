<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireAdmin();
$isSuperAdmin = ($_SESSION['role'] === 'super_admin');

$message = '';
$error = '';

// 1. Ajouter un jour férié
if ($isSuperAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_holiday'])) {
    $titre = trim($_POST['titre']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $couleur = $_POST['couleur'] ?? '#e74c3c';
    $type = $_POST['type'] ?? 'ferie_national';

    if (!empty($titre) && !empty($date_debut) && !empty($date_fin)) {
        if ($date_fin < $date_debut) {
            $error = "La date de fin ne peut pas être antérieure à la date de début.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO holidays (titre, date_debut, date_fin, couleur, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $date_debut, $date_fin, $couleur, $type]);
            $message = "Le jour férié a été ajouté avec succès.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

// 2. Supprimer un jour férié
if ($isSuperAdmin && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM holidays WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: holidays.php");
    exit();
}

// 3. Récupérer tous les jours fériés
$stmt = $pdo->query("SELECT * FROM holidays ORDER BY date_debut ASC");
$holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jours Fériés - Gestion des Congés</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=1.2">
</head>
<body>
    <div class="layout">
        <!-- Barre de navigation latérale -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <span class="logo-icon">📅</span>
                <span>Gestion Congés</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">📊 Tableau de bord</a>
                <a href="demandes.php" class="nav-item">📋 Demandes</a>
                <a href="groupes.php" class="nav-item">👥 Groupes</a>
                <a href="users.php" class="nav-item">👤 Utilisateurs</a>
                <a href="holidays.php" class="nav-item active">🎉 Jours fériés</a>
                <a href="calendrier.php" class="nav-item">🗓️ Calendrier</a>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="nav-item logout">🚪 Déconnexion</a>
            </div>
        </aside>

        <!-- Contenu Principal -->
        <main class="main-content">
            <header class="topbar">
                <h1>Gestion des Jours Fériés</h1>
                <div class="user-badge">👋 <?= htmlspecialchars($_SESSION['nom'] ?? 'Administrateur') ?></div>
            </header>

            <?php if ($message): ?>
                <div class="alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-danger" style="background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fee2e2;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout -->
            <div class="panel" style="margin-bottom: 24px;">
                <div class="panel-header">
                    <h2>Ajouter un événement / Jour férié</h2>
                </div>
                <form action="holidays.php" method="POST" class="grid-form">
                    <input type="text" name="titre" placeholder="Nom de l'événement (ex: Nouvel An, Aïd...)" required>
                    
                    <div>
                        <label style="font-size: 11px; color: #666; display: block;">Date de début :</label>
                        <input type="date" name="date_debut" required style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-size: 11px; color: #666; display: block;">Date de fin :</label>
                        <input type="date" name="date_fin" required style="width: 100%;">
                    </div>
                    
                    <select name="type">
                        <option value="ferie_national">Jour Férié National</option>
                        <option value="entreprise">Fermeture Entreprise (Leoni)</option>
                    </select>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label style="font-size: 12px; color: #666;">Couleur sur le calendrier :</label>
                        <input type="color" name="couleur" value="#e74c3c" style="height: 38px; padding: 2px; cursor: pointer; border: 1px solid #ccc; border-radius: 6px;">
                    </div>

                    <button type="submit" name="add_holiday" class="btn-primary" style="grid-column: span 3; margin-top: 10px;">+ Ajouter à la liste</button>
                </form>
            </div>

            <!-- Liste des jours fériés -->
            <div class="panel">
                <div class="panel-header">
                    <h2>Liste des jours fériés configurés (<?= count($holidays) ?>)</h2>
                </div>

                <?php if (empty($holidays)): ?>
                    <p class="empty-state">Aucun jour férié n'a été enregistré pour le moment.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Événement</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Type</th>
                                <th>Couleur</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($holidays as $h): ?>
                            <tr>
                                <td><strong>🎉 <?= htmlspecialchars($h['titre']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($h['date_debut'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($h['date_fin'])) ?></td>
                                <td>
                                    <span class="badge" style="text-transform: capitalize;">
                                        <?= str_replace('_', ' ', htmlspecialchars($h['type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="display: inline-block; width: 18px; height: 18px; border-radius: 50%; background-color: <?= htmlspecialchars($h['couleur']) ?>; border: 1px solid #ccc; vertical-align: middle; margin-right: 5px;"></span>
                                    <small style="color: #666; font-size: 11px;"><?= htmlspecialchars($h['couleur']) ?></small>
                                </td>
                                <td class="actions-cell">
                                    <a href="holidays.php?delete=<?= $h['id'] ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                       🗑️ Supprimer
                                    </a>
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
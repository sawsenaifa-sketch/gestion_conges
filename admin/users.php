<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireAdmin();

$message = '';

$current_role = $_SESSION['role'];
$current_group_id = $_SESSION['group_id'] ?? NULL;

// AJOUTER un utilisateur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // إذا كان admin_dept يُجبر الدور على user والمجموعة على مجموعته
    if ($current_role !== 'super_admin') {
        $role = 'user';
        $group_id = $current_group_id;
    } else {
        $role = $_POST['role'];
        $group_id = $_POST['group_id'] ?: NULL;
    }

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $message = "Erreur : cet email existe déjà.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role, group_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $hash, $role, $group_id]);
        $message = "Utilisateur ajouté avec succès.";
    }
}

// MODIFIER un utilisateur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = $_POST['id'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    
    if ($current_role !== 'super_admin') {
        $role = 'user';
        $group_id = $current_group_id;
        $stmt = $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=?, role=?, group_id=? WHERE id=? AND group_id=?");
        $stmt->execute([$nom, $prenom, $email, $role, $group_id, $id, $current_group_id]);
    } else {
        $role = $_POST['role'];
        $group_id = $_POST['group_id'] ?: NULL;
        $stmt = $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=?, role=?, group_id=? WHERE id=?");
        $stmt->execute([$nom, $prenom, $email, $role, $group_id, $id]);
    }
    
    $message = "Utilisateur modifié avec succès.";
}

// SUPPRIMER un utilisateur
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($current_role !== 'super_admin') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND group_id = ? AND role = 'user'");
        $stmt->execute([$id, $current_group_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: users.php?msg=deleted");
    exit;
}

// Récupérer les utilisateurs selon le rôle
if ($current_role === 'super_admin') {
    $stmt = $pdo->query("SELECT u.*, g.nom as group_nom 
                          FROM users u 
                          LEFT JOIN `groups` g ON g.id = u.group_id 
                          ORDER BY u.nom");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $groupes = $pdo->query("SELECT * FROM `groups` ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
} else {
    // 👤 سوسن ترى فقط المستخدمين العاديين التابعين لمجموعتها
    $stmt = $pdo->prepare("SELECT u.*, g.nom as group_nom 
                           FROM users u 
                           LEFT JOIN `groups` g ON g.id = u.group_id 
                           WHERE u.group_id = ? AND u.role = 'user'
                           ORDER BY u.nom");
    $stmt->execute([$current_group_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtG = $pdo->prepare("SELECT * FROM `groups` WHERE id = ?");
    $stmtG->execute([$current_group_id]);
    $groupes = $stmtG->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs - Gestion Congés</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        * { box-sizing: border-box; }
        body { margin:0; font-family:'Segoe UI', system-ui, sans-serif; background:#f8fafc; }
        .page-container { display: flex; width: 100vw; min-height: 100vh; }
        .custom-main { flex: 1; padding: 25px 35px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include 'sidebar.php'; ?>

        <main class="custom-main">
            <header class="topbar">
                <h1>Utilisateurs</h1>
                <div class="user-badge">👋 <?= htmlspecialchars($_SESSION['nom']) ?></div>
            </header>

            <?php if ($message || isset($_GET['msg'])): ?>
                <div class="alert-success"><?= $message ?: "Utilisateur supprimé avec succès." ?></div>
            <?php endif; ?>

            <!-- Formulaire ajout -->
            <div class="panel" style="margin-bottom: 20px;">
                <div class="panel-header">
                    <h2>Ajouter un utilisateur</h2>
                </div>
                <form method="POST" class="grid-form">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="nom" placeholder="Nom" required>
                    <input type="text" name="prenom" placeholder="Prénom" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    
                    <?php if ($current_role === 'super_admin'): ?>
                        <select name="role">
                            <option value="user">Utilisateur</option>
                            <option value="admin_dept">Admin Département</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <select name="group_id">
                            <option value="">-- Aucun groupe --</option>
                            <?php foreach ($groupes as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="role" value="user">
                        <input type="hidden" name="group_id" value="<?= $current_group_id ?>">
                    <?php endif; ?>

                    <button type="submit" class="btn-primary">+ Ajouter</button>
                </form>
            </div>

            <!-- Liste des users -->
            <div class="panel">
                <div class="panel-header">
                    <h2>Liste des utilisateurs (<?= count($users) ?>)</h2>
                </div>

                <?php if (empty($users)): ?>
                    <p class="empty-state">Aucun utilisateur pour le moment.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Groupe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($u['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($u['prenom']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge <?= $u['role'] != 'user' ? 'badge-admin' : '' ?>"><?= $u['role'] ?></span></td>
                                <td><?= htmlspecialchars($u['group_nom'] ?? '-') ?></td>
                                <td class="actions-cell">
                                    <button class="btn-edit" onclick='openEditModal(<?= $u["id"] ?>, <?= json_encode($u["nom"]) ?>, <?= json_encode($u["prenom"]) ?>, <?= json_encode($u["email"]) ?>, <?= json_encode($u["role"]) ?>, <?= json_encode($u["group_id"]) ?>)'>✏️</button>
                                    <a href="?delete=<?= $u['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Modifier -->
    <div id="editModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <h2>Modifier l'utilisateur</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" id="edit_nom" required>
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="edit_prenom" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>

                <?php if ($current_role === 'super_admin'): ?>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="role" id="edit_role">
                            <option value="user">Utilisateur</option>
                            <option value="admin_dept">Admin Département</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Groupe</label>
                        <select name="group_id" id="edit_group_id">
                            <option value="">-- Aucun groupe --</option>
                            <?php foreach ($groupes as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nom, prenom, email, role, group_id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nom').value = nom;
            document.getElementById('edit_prenom').value = prenom;
            document.getElementById('edit_email').value = email;
            if(document.getElementById('edit_role')) {
                document.getElementById('edit_role').value = role;
            }
            if(document.getElementById('edit_group_id')) {
                document.getElementById('edit_group_id').value = group_id || '';
            }
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>
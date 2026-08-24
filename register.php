<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $group_id = $_POST['group_id'] ?? '';

    // Vérification que tous les champs sont remplis (y compris le groupe)
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($group_id)) {
        $error = "Veuillez remplir tous les champs et choisir un département.";
    } else {
        // Vérifier si l'email existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Inscription automatique sous le rôle 'user' et affectation au groupe sélectionné
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role, group_id) VALUES (?, ?, ?, ?, 'user', ?)");
            $stmt->execute([$nom, $prenom, $email, $hash, $group_id]);

            // Connecter automatiquement le nouvel utilisateur
            $newUserId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['nom'] = $nom . ' ' . $prenom;
            $_SESSION['role'] = 'user';
            $_SESSION['group_id'] = $group_id;

            header("Location: user/dashboard.php");
            exit;
        }
    }
}

// Récupérer la liste des groupes/départements créés par l'admin
$groupes = $pdo->query("SELECT id, nom FROM groups ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - Gestion Congés</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <div class="logo-icon">📅</div>
            </div>
            <h1>Créer un compte</h1>
            <p class="subtitle">Rejoignez votre espace de gestion des congés</p>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="Votre nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" placeholder="Votre prénom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Département / Équipe</label>
                    <select name="group_id" required>
                        <option value="">-- Sélectionnez votre département --</option>
                        <?php foreach ($groupes as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= (isset($_POST['group_id']) && $_POST['group_id'] == $g['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="nom@exemple.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="Choisissez un mot de passe" required minlength="6">
                </div>
                
                <button type="submit">Créer mon compte</button>
            </form>

            <p class="switch-text">Déjà un compte ? <a href="index.php">Se connecter</a></p>
        </div>
    </div>
</body>
</html>
<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../admin/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin   = $_POST['date_fin'] ?? '';
    $motif      = trim($_POST['motif'] ?? '');

    if (empty($date_debut) || empty($date_fin)) {
        $error = "Veuillez remplir toutes les dates.";
    } elseif ($date_debut > $date_fin) {
        $error = "La date de début doit être antérieure à la date de fin.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO leave_requests (user_id, date_debut, date_fin, motif, statut) VALUES (?, ?, ?, ?, 'en_attente')");
        if ($stmt->execute([$user_id, $date_debut, $date_fin, $motif])) {
            $message = "Demande de congé envoyée avec succès !";
        } else {
            $error = "Une erreur est survenue lors de l'envoi de la demande.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Demande - Congés</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #334155; }
        .page-container { display: flex; width: 100vw; min-height: 100vh; }
        .custom-sidebar { width: 250px; background-color: #0f172a; color: #fff; display: flex; flex-direction: column; justify-content: space-between; padding: 20px 15px; flex-shrink: 0; }
        .sidebar-brand { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; padding-bottom: 25px; border-bottom: 1px solid #1e293b; }
        .sidebar-menu { display: flex; flex-direction: column; gap: 8px; margin-top: 20px; }
        .menu-link { display: flex; align-items: center; gap: 12px; color: #94a3b8; text-decoration: none; padding: 10px 14px; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .menu-link:hover, .menu-link.active { background-color: #6366f1; color: #ffffff; }
        .custom-main { flex: 1; padding: 25px 35px; overflow-y: auto; }
        
        .card-form { background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 600px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
        .form-control:focus { border-color: #6366f1; }
        .btn-submit { background: #6366f1; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; }
        .btn-submit:hover { background: #4f46e5; }
        .alert-success { background: #dcfce7; color: #15803d; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-danger { background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="page-container">
        <aside class="custom-sidebar">
            <div>
                <div class="sidebar-brand"><span>📅</span> Mon Espace Congés</div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php" class="menu-link">📊 Tableau de bord</a>
                    <a href="demander_conge.php" class="menu-link active">➕ Nouvelle demande</a>
                    <a href="mes_demandes.php" class="menu-link">📋 Mes demandes</a>
                    <a href="calendrier.php" class="menu-link">🗓️ Calendrier</a>
                </nav>
            </div>
            <a href="../logout.php" class="menu-link" style="color: #ef4444;">🚪 Déconnexion</a>
        </aside>

        <main class="custom-main">
            <header style="margin-bottom: 25px;">
                <h1 style="font-size: 22px; font-weight: 700;">Nouvelle demande de congé</h1>
                <p style="color: #64748b; font-size: 13px; margin-top: 4px;">Remplissez le formulaire ci-dessous pour soumettre une demande.</p>
            </header>

            <?php if ($message): ?>
                <div class="alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card-form">
                <form method="POST">
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Date de début</label>
                            <input type="date" name="date_debut" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Date de fin</label>
                            <input type="date" name="date_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Motif du congé</label>
                        <textarea name="motif" class="form-control" rows="4" placeholder="Précisez la raison de votre demande..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">📤 Envoyer la demande</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
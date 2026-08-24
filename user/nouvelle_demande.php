<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireLogin();

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $motif = trim($_POST['motif'] ?? '');

    if ($date_fin < $date_debut) {
        $error = "La date de fin doit être après la date de début.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO leave_requests (user_id, date_debut, date_fin, motif, statut) VALUES (?, ?, ?, ?, 'en_attente')");
        $stmt->execute([$userId, $date_debut, $date_fin, $motif]);
        $message = "Votre demande a été envoyée avec succès. Elle est en attente de validation.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande - Gestion Congés</title>
    
    <style>
        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        .form-control { 
            width: 100%; 
            padding: 10px 14px; 
            border: 1px solid #cbd5e1; 
            border-radius: 8px; 
            font-size: 14px; 
            outline: none; 
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: #6366f1; }
        .btn-submit { 
            background-color: #6366f1; 
            color: white; 
            border: none; 
            padding: 12px 20px; 
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
            width: 100%;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-submit:hover { background-color: #4f46e5; }
    </style>
</head>
<body>

    <div class="app-container">
        
        <!-- استدعاء الشريط الجانبي الموحد -->
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <h1 class="header-title">Nouvelle demande de congé</h1>
            <p class="header-sub">Remplissez le formulaire ci-dessous pour effectuer une nouvelle demande.</p>

            <?php if ($message): ?>
                <div class="alert-success"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <div class="card" style="max-width: 500px;">
                <form method="POST">
                    <div class="form-group">
                        <label>Date de début</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Motif (optionnel)</label>
                        <input type="text" name="motif" class="form-control" placeholder="Ex: Vacances d'été, raisons personnelles...">
                    </div>

                    <button type="submit" class="btn-submit">Envoyer la demande</button>
                </form>
            </div>
        </main>

    </div>

</body>
</html>
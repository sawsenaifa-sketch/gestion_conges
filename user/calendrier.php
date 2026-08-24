<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT id, date_debut as start, DATE_ADD(date_fin, INTERVAL 1 DAY) as end, statut 
    FROM leave_requests 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($requests as $r) {
    $color = '#10b981';
    $title = 'Congé Validé';
    if ($r['statut'] === 'en_attente') {
        $color = '#f59e0b';
        $title = 'En attente';
    } elseif ($r['statut'] === 'refuse') {
        $color = '#ef4444';
        $title = 'Refusé';
    }
    
    $events[] = [
        'id' => $r['id'],
        'title' => $title,
        'start' => $r['start'],
        'end' => $r['end'],
        'color' => $color
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Calendrier - Congés</title>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <style>
        .legend-section { display: flex; gap: 15px; margin-bottom: 20px; }
        .legend-tag { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; }
        .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .fc-toolbar-title { font-size: 18px !important; font-weight: 700; color: #0f172a; }
        .fc-button-primary { background-color: #6366f1 !important; border: none !important; }
    </style>
</head>
<body>

    <div class="app-container">
        
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <h1 class="header-title">Calendrier des Congés</h1>
            <p class="header-sub">Mon calendrier personnel des demandes de congé.</p>

            <div class="legend-section">
                <div class="legend-tag"><span class="dot" style="background:#10b981;"></span> Validé</div>
                <div class="legend-tag"><span class="dot" style="background:#f59e0b;"></span> En attente</div>
                <div class="legend-tag"><span class="dot" style="background:#ef4444;"></span> Refusé</div>
            </div>

            <div class="card">
                <div id="calendar"></div>
            </div>
        </main>

    </div>

    <script>
        var userEvents = <?= json_encode($events) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'fr',
                height: 540,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                buttonText: { today: "Aujourd'hui", month: 'Mois' },
                events: userEvents
            });
            calendar.render();
        });
    </script>
</body>
</html>
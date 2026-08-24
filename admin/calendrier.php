<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireAdmin();

// Récupérer la liste des employés
$stmtUsers = $pdo->query("SELECT id, nom, prenom FROM users ORDER BY nom ASC");
$employees = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les événements (Jours fériés)
$stmtHolidays = $pdo->query("
    SELECT 
        id, 
        titre as title, 
        date_debut as start, 
        DATE_ADD(date_fin, INTERVAL 1 DAY) as end, 
        COALESCE(couleur, '#ef4444') as color, 
        'ferie' as type,
        '0' as user_id,
        'ferie' as type_conge
    FROM holidays
");
$eventsHolidays = $stmtHolidays->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les Congés acceptés
$stmtConges = $pdo->query("
    SELECT 
        lr.id,
        CONCAT(u.prenom, ' ', u.nom, ' (Congé)') as title, 
        lr.date_debut as start, 
        DATE_ADD(lr.date_fin, INTERVAL 1 DAY) as end, 
        lr.user_id,
        'conge' as type_conge,
        '#10b981' as color
    FROM leave_requests lr 
    JOIN users u ON lr.user_id = u.id 
    WHERE lr.statut = 'accepte'
");
$eventsConges = $stmtConges->fetchAll(PDO::FETCH_ASSOC);

$allEvents = array_merge($eventsHolidays, $eventsConges);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier - Admin</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #334155; }

        .page-container { display: flex; width: 100vw; min-height: 100vh; }

        .custom-main { flex: 1; padding: 25px 35px; overflow-y: auto; }
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .top-header h1 { font-size: 22px; font-weight: 700; color: #0f172a; }

        .card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; }

        .filter-section { display: flex; gap: 12px; margin-bottom: 12px; }
        .input-field { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; }

        .legend-section { display: flex; gap: 18px; margin-bottom: 15px; }
        .legend-tag { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; }
        .dot { width: 10px; height: 10px; border-radius: 50%; }

        .fc-toolbar-title { font-size: 18px !important; font-weight: 700; color: #0f172a; }
        .fc-button-primary { background-color: #1e293b !important; border: none !important; }
        .fc-event { border: none !important; border-radius: 4px !important; padding: 3px 6px !important; font-size: 12px !important; font-weight: 600 !important; color: #fff !important; }
    </style>
</head>
<body>

    <div class="page-container">
        
        <!-- استدعاء القائمة الجانبية الموحدة -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="custom-main">
            <div class="top-header">
                <h1>Company Calendar</h1>
                <span style="font-size: 13px; font-weight: 600;">👋 <?= htmlspecialchars($_SESSION['nom'] ?? 'Admin Super') ?></span>
            </div>

            <div class="card">
                <!-- Filters -->
                <div class="filter-section">
                    <select id="employeeFilter" class="input-field">
                        <option value="all">All employees</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['prenom'] . ' ' . $emp['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="typeFilter" class="input-field">
                        <option value="all">All leave types</option>
                        <option value="conge">Congés Acceptés</option>
                        <option value="ferie">Jours Fériés</option>
                    </select>
                </div>

                <!-- Legend -->
                <div class="legend-section">
                    <div class="legend-tag"><span class="dot" style="background:#10b981;"></span> Congés Acceptés</div>
                    <div class="legend-tag"><span class="dot" style="background:#ef4444;"></span> Jours Fériés</div>
                </div>

                <!-- Calendar -->
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <script>
        var rawEvents = <?= json_encode($allEvents) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'en',
                height: 580,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,dayGridDay'
                },
                buttonText: { today: "Today", month: 'Month', week: 'Week', day: 'Day' },
                events: rawEvents
            });
            calendar.render();

            function filterEvents() {
                var empValue = document.getElementById('employeeFilter').value;
                var typeValue = document.getElementById('typeFilter').value;

                var filtered = rawEvents.filter(function(evt) {
                    var matchEmp = (empValue === 'all') || (evt.user_id == empValue) || (evt.type === 'ferie');
                    var matchType = (typeValue === 'all') || (evt.type_conge === typeValue) || (typeValue === 'ferie' && evt.type === 'ferie');
                    return matchEmp && matchType;
                });

                calendar.removeAllEvents();
                calendar.addEventSource(filtered);
            }

            document.getElementById('employeeFilter').addEventListener('change', filterEvents);
            document.getElementById('typeFilter').addEventListener('change', filterEvents);
        });
    </script>
</body>
</html>
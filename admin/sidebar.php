<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'user';
?>

<aside style="width: 250px; min-width: 250px; background-color: #0f172a; color: white; min-height: 100vh; padding: 20px 15px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;">
    <div>
        <div style="font-size: 18px; font-weight: 700; color: #ffffff; padding-bottom: 20px; margin-bottom: 15px; border-bottom: 1px solid #1e293b; display: flex; align-items: center; gap: 10px;">
            <span>📅</span><span>Gestion Congés</span>
        </div>

        <nav style="display: flex; flex-direction: column; gap: 8px;">
            
            <a href="profil.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px; border-radius: 6px; color: <?= $currentPage == 'profil.php' ? '#ffffff' : '#94a3b8' ?>;">
                👤 Mon Profil
            </a>

            <a href="dashboard.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px; border-radius: 6px; color: <?= $currentPage == 'dashboard.php' ? '#ffffff' : '#94a3b8' ?>;">
                📊 Tableau de bord
            </a>

            <a href="demandes.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px; border-radius: 6px; color: <?= $currentPage == 'demandes.php' ? '#ffffff' : '#94a3b8' ?>;">
                📋 Demandes
            </a>

            <!-- زر المستخدمين المرتبط بملف users.php -->
            <a href="users.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px; border-radius: 6px; color: <?= $currentPage == 'users.php' ? '#ffffff' : '#94a3b8' ?>;">
                👥 Utilisateurs
            </a>

            <!-- للـ super_admin: زر المجموعات -->
            <?php if ($role === 'super_admin'): ?>
                <a href="groupes.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px; border-radius: 6px; color: <?= in_array($currentPage, ['groupes.php', 'groupe_details.php']) ? '#ffffff' : '#94a3b8' ?>;">
                    🏢 Groupes
                </a>
            <?php endif; ?>

            <!-- للـ admin_dept: زر مجموعتي -->
            <?php if ($role === 'admin_dept'): ?>
                <a href="groupe_details.php?id=<?= $_SESSION['group_id'] ?? '' ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px; border-radius: 6px; color: <?= $currentPage == 'groupe_details.php' ? '#ffffff' : '#94a3b8' ?>;">
    🏢 Mon Groupe
</a>
            <?php endif; ?>

            <a href="calendrier.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px; border-radius: 6px; color: <?= $currentPage == 'calendrier.php' ? '#ffffff' : '#94a3b8' ?>;">
                🗓️ Calendrier
            </a>

        </nav>
    </div>

    <div>
        <a href="../logout.php" style="display: flex; align-items: center; gap: 12px; color: #ef4444; text-decoration: none; padding: 10px 0;">
            🚪 Déconnexion
        </a>
    </div>
</aside>
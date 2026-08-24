<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- CSS الموحد المدمج لمنع أي مشكلة مسارات -->
<style>
    * { 
        box-sizing: border-box; 
        margin: 0; 
        padding: 0; 
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
    }

    html, body { 
        width: 100%; 
        height: 100%; 
        margin: 0; 
        padding: 0; 
        background-color: #f8fafc; 
        overflow-x: hidden; 
    }

    .app-container { 
        display: flex; 
        width: 100vw; 
        min-height: 100vh; 
    }

    /* Sidebar Styles */
    .sidebar { 
        width: 250px; 
        min-width: 250px; 
        background-color: #0b0f19; 
        color: #ffffff; 
        padding: 20px 15px; 
        display: flex; 
        flex-direction: column; 
        justify-content: space-between; 
        flex-shrink: 0; 
    }

    .sidebar-brand { 
        font-size: 18px; 
        font-weight: 700; 
        color: #ffffff; 
        padding: 10px 10px 25px 10px; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }

    .sidebar-menu { 
        display: flex; 
        flex-direction: column; 
        gap: 8px; 
    }

    .sidebar-menu a { 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        text-decoration: none; 
        padding: 12px 16px; 
        border-radius: 10px; 
        font-size: 14px; 
        font-weight: 500; 
        color: #94a3b8; 
        transition: all 0.2s; 
    }

    .sidebar-menu a:hover { 
        color: #ffffff; 
    }

    .sidebar-menu a.active { 
        background-color: #6366f1 !important; 
        color: #ffffff !important; 
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); 
    }

    .sidebar-footer a { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        color: #ef4444; 
        text-decoration: none; 
        font-size: 14px; 
        font-weight: 600; 
        padding: 12px 16px; 
    }

    /* Main Content Layout */
    .main-content { 
        flex: 1; 
        padding: 35px 45px; 
        background-color: #f8fafc; 
        overflow-y: auto; 
    }

    .header-title { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 5px; }
    .header-sub { font-size: 13px; color: #64748b; margin-bottom: 25px; }
    .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
</style>

<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <span>📅</span> <span>Mon Espace Congés</span>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                📊 <span>Tableau de bord</span>
            </a>
            <a href="nouvelle_demande.php" class="<?= $currentPage == 'nouvelle_demande.php' ? 'active' : '' ?>">
                ➕ <span>Nouvelle demande</span>
            </a>
            <a href="mes_demandes.php" class="<?= $currentPage == 'mes_demandes.php' ? 'active' : '' ?>">
                📑 <span>Mes demandes</span>
            </a>
            <a href="calendrier.php" class="<?= $currentPage == 'calendrier.php' ? 'active' : '' ?>">
                📅 <span>Calendrier</span>
            </a>
        </nav>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php">
            🚪 <span>Déconnexion</span>
        </a>
    </div>
</aside>
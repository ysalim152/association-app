<?php
// Navbar
?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0D3B66;">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/dashboard">
            <span style="color: #1E88E5;">⚽</span> <?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">Tableau de bord</a>
                    </li>
                    <?php if (in_array($_SESSION['user_role'], [ROLE_ADMIN, ROLE_SECRETARY])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="membersDropdown" role="button" data-bs-toggle="dropdown">
                                Membres
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="membersDropdown">
                                <li><a class="dropdown-item" href="/members">Liste</a></li>
                                <li><a class="dropdown-item" href="/members/create">Ajouter</a></li>
                                <li><a class="dropdown-item" href="/members/export">Exporter CSV</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="teamsDropdown" role="button" data-bs-toggle="dropdown">
                                Équipes
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="teamsDropdown">
                                <li><a class="dropdown-item" href="/teams">Liste</a></li>
                                <li><a class="dropdown-item" href="/teams/create">Créer</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="matchesDropdown" role="button" data-bs-toggle="dropdown">
                                Matchs
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="matchesDropdown">
                                <li><a class="dropdown-item" href="/matches/calendar">Calendrier</a></li>
                                <li><a class="dropdown-item" href="/matches/create">Ajouter</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/payments">Paiements</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                Administration
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="/admin/users">Utilisateurs</a></li>
                                <li><a class="dropdown-item" href="/admin/logs">Logs</a></li>
                                <li><a class="dropdown-item" href="/admin/settings">Paramètres</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/profile">Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

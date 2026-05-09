<?php
// Dashboard
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
$csrf_token = SecurityHelper::generateCSRFToken();
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Tableau de bord</h1>
        <p class="text-muted">Bienvenue, <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted">Membres totaux</h6>
                <h2 class="card-text" style="color: #0D3B66;"><?php echo $stats['total_members'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted">Membres payants</h6>
                <h2 class="card-text" style="color: #0D3B66;"><?php echo $stats['members_paid'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted">Recettes (2025)</h6>
                <h2 class="card-text" style="color: #0D3B66;"><?php echo number_format($stats['total_revenue'] ?? 0, 2); ?> €</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted">Équipes</h6>
                <h2 class="card-text" style="color: #0D3B66;"><?php echo $stats['teams_count'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h4>Prochains matchs</h4>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr style="border-color: #0D3B66;">
                        <th>Date</th>
                        <th>Équipe vs Adversaire</th>
                        <th>Lieu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['upcoming_matches'] ?? [] as $match): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($match['match_date'])); ?></td>
                            <td><?php echo htmlspecialchars($match['opponent_name']); ?></td>
                            <td><?php echo htmlspecialchars($match['location']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <h4>Statistiques paiements</h4>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr style="border-color: #0D3B66;">
                        <th>Statut</th>
                        <th>Nombre</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment_stats ?? [] as $ps): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(PAYMENT_STATUSES[$ps['status']] ?? $ps['status']); ?></td>
                            <td><?php echo $ps['count']; ?></td>
                            <td><?php echo number_format($ps['total'] ?? 0, 2); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h4>Derniers membres</h4>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr style="border-color: #0D3B66;">
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date d'adhésion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest_members ?? [] as $m): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($m['email']); ?></td>
                            <td><?php echo htmlspecialchars($m['phone']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($m['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

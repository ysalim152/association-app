<?php
// Calendrier des matchs
$current_year = $year ?? date('Y');
$current_month = $month ?? date('m');

// Mois précédent/suivant
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$month_name = strftime('%B %Y', mktime(0, 0, 0, $current_month, 1, $current_year));
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2>Calendrier des Matchs</h2>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <a href="/matches/calendar?year=<?php echo $prev_year; ?>&month=<?php echo str_pad($prev_month, 2, '0', STR_PAD_LEFT); ?>" class="btn btn-sm btn-outline-primary">← Précédent</a>
            <h4><?php echo htmlspecialchars($month_name); ?></h4>
            <a href="/matches/calendar?year=<?php echo $next_year; ?>&month=<?php echo str_pad($next_month, 2, '0', STR_PAD_LEFT); ?>" class="btn btn-sm btn-outline-primary">Suivant →</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-end mb-3">
            <a href="/matches/create" class="btn btn-success">➕ Ajouter un match</a>
        </div>

        <?php if (empty($matches)): ?>
            <div class="alert alert-info">Aucun match pour cette période.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #0D3B66; color: white;">
                        <tr>
                            <th>Date</th>
                            <th>Équipe</th>
                            <th>Adversaire</th>
                            <th>Lieu</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $match): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($match['match_date'])); ?></td>
                                <td><?php echo htmlspecialchars($match['team_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($match['opponent_name']); ?></td>
                                <td><?php echo htmlspecialchars($match['location']); ?></td>
                                <td><small><?php echo htmlspecialchars(MATCH_TYPES[$match['match_type']] ?? $match['match_type']); ?></small></td>
                                <td>
                                    <span class="badge badge-<?php echo ($match['status'] === MATCH_STATUS_COMPLETED) ? 'success' : 'warning'; ?>">
                                        <?php echo htmlspecialchars(MATCH_STATUSES[$match['status']] ?? $match['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($match['status'] === MATCH_STATUS_COMPLETED): ?>
                                        <strong><?php echo $match['score_team'] ?? '-'; ?> - <?php echo $match['score_opponent'] ?? '-'; ?></strong>
                                    <?php else: ?>
                                        <em>-</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/matches/<?php echo $match['id']; ?>/score" class="btn btn-sm btn-info" title="Éditer score">📊</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

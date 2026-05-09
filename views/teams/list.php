<?php
// Liste des équipes
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2>Équipes</h2>
        <a href="/teams/create" class="btn btn-success">➕ Créer une équipe</a>
    </div>
</div>

<div class="row">
    <?php foreach ($teams as $team): ?>
        <?php
        $team_model = new Team();
        $members_count = $team_model->getMembersCount($team['id']);
        $next_match = $team_model->getNextMatch($team['id']);
        $stats = $team_model->getStats($team['id']);
        ?>
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #0D3B66; color: white;">
                    <h5 class="mb-0"><?php echo htmlspecialchars($team['name']); ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo htmlspecialchars(SPORTS[$team['sport_type']] ?? $team['sport_type']); ?></p>
                    <div class="mb-3">
                        <strong>Membres :</strong> <?php echo $members_count; ?><br>
                        <strong>Matchs :</strong> <?php echo $stats['total_matches'] ?? 0; ?> (V:<?php echo $stats['wins'] ?? 0; ?> D:<?php echo $stats['draws'] ?? 0; ?> P:<?php echo $stats['losses'] ?? 0; ?>)
                    </div>
                    <?php if ($next_match): ?>
                        <div class="alert alert-info mb-0">
                            <small>
                                <strong>Prochain match :</strong> <?php echo date('d/m/Y H:i', strtotime($next_match['match_date'])); ?>
                                vs <?php echo htmlspecialchars($next_match['opponent_name']); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="/teams/<?php echo $team['id']; ?>/members" class="btn btn-sm btn-primary">👥 Membres</a>
                    <a href="/teams/<?php echo $team['id']; ?>/edit" class="btn btn-sm btn-warning">✏️</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

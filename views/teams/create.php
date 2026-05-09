<?php
// Formulaire création équipe
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
$csrf_token = SecurityHelper::generateCSRFToken();
?>
<div class="row">
    <div class="col-md-6 mx-auto">
        <h2>Créer une Équipe</h2>

        <form method="POST" action="/teams/store">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Nom de l'équipe *</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="sport_type" class="form-label">Sport *</label>
                <select class="form-select" id="sport_type" name="sport_type" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach (SPORTS as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">✅ Créer</button>
                <a href="/teams" class="btn btn-secondary">❌ Annuler</a>
            </div>
        </form>
    </div>
</div>

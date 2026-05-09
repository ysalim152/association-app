<?php
// Liste des paiements
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
$csrf_token = SecurityHelper::generateCSRFToken();
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2>Gestion des Paiements d'Adhésion</h2>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="row g-2">
            <div class="col-auto">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = 2020; $y <= date('Y'); $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($year === $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <?php foreach (PAYMENT_STATUSES as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($status === $key) ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <form method="POST" action="/payments/bulk-update" style="display: inline;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <select name="status" class="form-select d-inline-block w-auto">
                <option value="">Marquer comme...</option>
                <?php foreach (PAYMENT_STATUSES as $key => $label): ?>
                    <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-warning" onclick="return confirm('Appliquer aux paiements sélectionnés ?');">Appliquer</button>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead style="background-color: #0D3B66; color: white;">
            <tr>
                <th width="30px"><input type="checkbox" id="selectAll" onclick="selectAllCheckboxes(this)"></th>
                <th>Membre</th>
                <th>Année</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Date limite</th>
                <th>Date paiement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><input type="checkbox" name="payment_ids[]" value="<?php echo $p['id']; ?>" form="bulk-update-form"></td>
                    <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                    <td><?php echo $p['year']; ?></td>
                    <td><?php echo number_format($p['amount'], 2); ?> €</td>
                    <td>
                        <span class="badge badge-<?php echo ($p['status'] === PAYMENT_STATUS_PAID) ? 'success' : (($p['status'] === PAYMENT_STATUS_LATE) ? 'danger' : 'warning'); ?>">
                            <?php echo htmlspecialchars(PAYMENT_STATUSES[$p['status']] ?? $p['status']); ?>
                        </span>
                    </td>
                    <td><?php echo $p['due_date'] ? date('d/m/Y', strtotime($p['due_date'])) : '-'; ?></td>
                    <td><?php echo $p['payment_date'] ? date('d/m/Y', strtotime($p['payment_date'])) : '-'; ?></td>
                    <td>
                        <a href="/payments/<?php echo $p['id']; ?>/paid" class="btn btn-sm btn-success" title="Marquer comme payé">💰</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h4>Statistiques <?php echo $year; ?></h4>
        <div class="row">
            <?php foreach ($stats as $stat): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><?php echo htmlspecialchars(PAYMENT_STATUSES[$stat['status']]); ?></h6>
                            <h4 style="color: #0D3B66;"><?php echo $stat['count']; ?> adhérents</h4>
                            <p class="text-success fw-bold"><?php echo number_format($stat['total'] ?? 0, 2); ?> €</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function selectAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('input[name="payment_ids[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>

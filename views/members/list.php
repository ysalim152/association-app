<?php
// Liste des membres
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
$csrf_token = SecurityHelper::generateCSRFToken();
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2>Gestion des Membres</h2>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <a href="/members/create" class="btn btn-success me-2">➕ Ajouter</a>
        <a href="/members/export" class="btn btn-info">📥 Exporter CSV</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead style="background-color: #0D3B66; color: white;">
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Ville</th>
                <th>Date d'adhésion</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($m['email']); ?></td>
                    <td><?php echo htmlspecialchars($m['phone']); ?></td>
                    <td><?php echo htmlspecialchars($m['city']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($m['created_at'])); ?></td>
                    <td>
                        <a href="/members/<?php echo $m['id']; ?>/edit" class="btn btn-sm btn-warning">✏️</a>
                        <a href="/members/<?php echo $m['id']; ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <nav aria-label="Pagination">
        <ul class="pagination justify-content-center">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <li class="page-item <?php echo ($p === $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="/members?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

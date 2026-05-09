<?php
// Formulaire création membre
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
$csrf_token = SecurityHelper::generateCSRFToken();
$form_data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['form_data']);
unset($_SESSION['errors']);
?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <h2>Ajouter un Membre</h2>

        <form method="POST" action="/members/store" class="needs-validation">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">Prénom *</label>
                    <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>"
                           id="first_name" name="first_name" required value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>">
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Nom *</label>
                    <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>"
                           id="last_name" name="last_name" required value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>">
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                           id="email" name="email" required value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Téléphone *</label>
                    <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                           id="phone" name="phone" required value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="dob" class="form-label">Date de naissance *</label>
                    <input type="date" class="form-control <?php echo isset($errors['dob']) ? 'is-invalid' : ''; ?>"
                           id="dob" name="dob" required value="<?php echo htmlspecialchars($form_data['dob'] ?? ''); ?>">
                    <?php if (isset($errors['dob'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['dob']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="license_number" class="form-label">Numéro de licence</label>
                    <input type="text" class="form-control" id="license_number" name="license_number" value="<?php echo htmlspecialchars($form_data['license_number'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="address" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($form_data['address'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="city" class="form-label">Ville</label>
                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="postal_code" class="form-label">Code Postal</label>
                    <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="75001" value="<?php echo htmlspecialchars($form_data['postal_code'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="create_account" name="create_account" value="1">
                        <label class="form-check-label" for="create_account">
                            Créer un compte utilisateur
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">✅ Créer le membre</button>
                <a href="/members" class="btn btn-secondary">❌ Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php
// Page de login
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
$csrf_token = SecurityHelper::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo APP_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0D3B66 0%, #1E88E5 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 400px; margin: auto; }
        .login-container h2 { color: #0D3B66; margin-bottom: 1.5rem; text-align: center; font-weight: bold; }
        .btn-login { background-color: #0D3B66; border-color: #0D3B66; width: 100%; }
        .btn-login:hover { background-color: #051d33; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Connexion</h2>

        <?php if (isset($_GET['logged_out'])): ?>
            <div class="alert alert-info" role="alert">Vous avez été déconnecté avec succès.</div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="/login">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['form_data']['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <button type="submit" class="btn btn-login btn-primary">Se connecter</button>
        </form>

        <div class="mt-3 text-center text-muted small">
            <p>Admin: admin@association.local / password</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

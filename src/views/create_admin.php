<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer Administrateur - <?php echo APP_NAME; ?></title>
    <style>
        /* Same styles as login.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-container { background: #fff; border-radius: 10px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); width: 100%; max-width: 450px; padding: 40px; }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h1 { color: #2c3e50; font-size: 24px; margin-bottom: 10px; }
        .login-header p { color: #777; font-size: 14px; }
        .logo-placeholder { width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 32px; font-weight: bold; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 14px; transition: border-color 0.3s; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .form-group small { display: block; margin-top: 5px; color: #777; font-size: 12px; }
        .btn-login { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .success-message { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .login-footer { margin-top: 30px; text-align: center; font-size: 13px; color: #777; }
        .login-footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-placeholder">👤</div>
            <h1>Créer un Administrateur</h1>
            <p>Première connexion - Créez votre compte</p>
        </div>

        <?php
        // Vérification de sécurité - empêcher l'accès direct si des utilisateurs existent déjà
        $userModel = new User();
        if ($userModel->hasActiveUsers()):
        ?>
        <div class="error-message">
            ⚠️ <strong>Accès refusé</strong><br>
            La création de compte administrateur n'est disponible que lors de la première installation.<br><br>
            <a href="index.php?route=login" style="color: #721c24; text-decoration: underline;">← Retour à la connexion</a>
        </div>
        <?php else: ?>

        <?php if($error): ?>
        <div class="error-message">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="success-message">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=create_admin">
            <div class="form-group">
                <label for="full_name">📛 Nom complet</label>
                <input type="text" id="full_name" name="full_name" placeholder="Votre nom complet">
            </div>

            <div class="form-group">
                <label for="username">👤 Nom d'utilisateur *</label>
                <input type="text" id="username" name="username" required placeholder="Choisissez un nom d'utilisateur">
                <small>Unique et requis pour la connexion</small>
            </div>

            <div class="form-group">
                <label for="email">📧 Email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com">
            </div>

            <div class="form-group">
                <label for="password">🔒 Mot de passe *</label>
                <input type="password" id="password" name="password" required placeholder="Minimum 6 caractères" minlength="6">
                <small>Minimum 6 caractères</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">🔒 Confirmer le mot de passe *</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Répétez le mot de passe">
            </div>

            <button type="submit" class="btn-login">✅ Créer le compte</button>
        </form>

        <div class="login-footer">
            <p>Déjà un compte ? <a href="index.php?route=login">Se connecter</a></p>
        </div>

        <?php endif; ?>
    </div>
</body>
</html>
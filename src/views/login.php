<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #777;
            font-size: 14px;
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 32px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .info-message {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .login-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #777;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .login-footer a.disabled {
            color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }

        @media screen and (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-placeholder">🔧</div>
            <h1><?php echo APP_NAME; ?></h1>
            <p>Connectez-vous à votre espace</p>
        </div>

        <?php if($error): ?>
        <div class="error-message">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['error']) && $_GET['error'] === 'registration_disabled'): ?>
        <div class="info-message">
            ℹ️ La création de compte est désactivée. Veuillez contacter l'administrateur.
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['registered'])): ?>
        <div class="success-message">
            ✅ Compte créé avec succès ! Connectez-vous.
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=login">
            <div class="form-group">
                <label for="username">👤 Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required autofocus placeholder="Votre nom d'utilisateur">
            </div>

            <div class="form-group">
                <label for="password">🔒 Mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="Votre mot de passe">
            </div>

            <button type="submit" class="btn-login">🔐 Se connecter</button>
        </form>

        <div class="login-footer">
            <p>© <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - v<?php echo APP_VERSION; ?></p>
            
            <?php if($allowRegistration): ?>
            <p style="margin-top: 10px;">
                <a href="index.php?route=create_admin">👤 Créer un compte administrateur</a>
            </p>
            <p style="margin-top: 5px; font-size: 11px; color: #999;">
                ⚠️ Cette option n'est disponible que pour la première connexion
            </p>
            <?php else: ?>
            <p style="margin-top: 10px; color: #999;">
                🔒 La création de compte est désactivée
            </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
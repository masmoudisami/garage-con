<?php
class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        // Si déjà connecté, rediriger vers le tableau de bord
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?route=invoices');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Veuillez remplir tous les champs';
            } else {
                $user = $this->userModel->getByUsername($username);

                if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();

                    // Mettre à jour last_login
                    $this->userModel->updateLastLogin($user['id']);

                    // Rediriger vers la page demandée ou le tableau de bord
                    $redirect = $_GET['redirect'] ?? 'invoices';
                    header('Location: index.php?route=' . $redirect);
                    exit;
                } else {
                    $error = 'Nom d\'utilisateur ou mot de passe incorrect';
                }
            }
        }

        // Vérifier s'il existe déjà des utilisateurs (pour afficher/masquer le lien)
        $allowRegistration = !$this->userModel->hasActiveUsers();

        include 'views/login.php';
    }

    public function logout() {
        // Détruire la session
        session_unset();
        session_destroy();

        // Recréer une nouvelle session pour éviter les erreurs
        session_start();

        header('Location: index.php?route=login');
        exit;
    }

    public function checkSession() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Vérifier le timeout de session
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_TIMEOUT)) {
            $this->logout();
            return false;
        }

        // Vérifier si l'utilisateur existe toujours et est actif
        $user = $this->userModel->getById($_SESSION['user_id']);
        if (!$user) {
            $this->logout();
            return false;
        }

        // Mettre à jour le temps de session
        $_SESSION['login_time'] = time();

        return true;
    }

    public function createAdmin() {
        // Vérifier s'il existe déjà des utilisateurs
        if ($this->userModel->hasActiveUsers()) {
            // Rediriger vers la page de connexion avec message d'erreur
            header('Location: index.php?route=login&error=registration_disabled');
            exit;
        }

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');

            if (empty($username) || empty($password)) {
                $error = 'Veuillez remplir tous les champs obligatoires';
            } elseif ($password !== $confirm_password) {
                $error = 'Les mots de passe ne correspondent pas';
            } elseif (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères';
            } else {
                $result = $this->userModel->create($username, $password, $email, $full_name, 'admin');
                
                if ($result['success']) {
                    $success = 'Utilisateur créé avec succès ! Vous pouvez maintenant vous connecter.';
                } else {
                    $error = $result['error'] === 'username_exists' ? 'Ce nom d\'utilisateur existe déjà' : 'Erreur lors de la création';
                }
            }
        }

        include 'views/create_admin.php';
    }
}
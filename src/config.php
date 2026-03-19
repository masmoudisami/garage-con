<?php
/**
 * ============================================================================
 * CONFIGURATION DE L'APPLICATION
 * Garage Auto Service - Gestion de Factures & Devis
 * ============================================================================
 */

// ============================================================================
// CONFIGURATION BASE DE DONNÉES
// ============================================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'db');
define('DB_USER', 'sami');
define('DB_PASS', 'Sm/131301');

// ============================================================================
// CONFIGURATION DE L'APPLICATION
// ============================================================================
define('APP_URL', 'http://localhost');
define('APP_NAME', 'Garage Auto Service');
define('APP_VERSION', '2.0');
define('CURRENCY', 'TND');
define('DEBUG', false); // Mettre à true pour le débogage

// ============================================================================
// CONFIGURATION DE SESSION
// ============================================================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en HTTPS
define('SESSION_TIMEOUT', 3600); // 1 heure en secondes
define('LOGIN_URL', 'index.php?route=login');

// ============================================================================
// FUSEAU HORAIRE ET ENCODAGE
// ============================================================================
date_default_timezone_set('Africa/Tunis');
header('Content-Type: text/html; charset=utf-8');

// ============================================================================
// INFORMATIONS DU GARAGISTE (Pour factures et devis)
// ============================================================================
define('GARAGE_NAME', 'GARAGE AUTO SERVICE');
define('GARAGE_ADDRESS', 'Avenue Habib Bourguiba, Tunis 1000');
define('GARAGE_PHONE', '+216 71 123 456');
define('GARAGE_EMAIL', 'contact@garage-auto.tn');
define('GARAGE_LOGO', 'assets/logo.png');
define('GARAGE_MATRICULE', 'M1234567');

// ============================================================================
// PARAMÈTRES PAR DÉFAUT
// ============================================================================
define('DEFAULT_TVA_RATE', 19.00);
define('DEFAULT_DROIT_TIMBRE', 0.000);

// ============================================================================
// SÉCURITÉ
// ============================================================================
// Empêcher le clicjacking
header('X-Frame-Options: SAMEORIGIN');
// Protection XSS
header('X-XSS-Protection: 1; mode=block');
// Protection MIME type sniffing
header('X-Content-Type-Options: nosniff');

// ============================================================================
// GESTION DES ERREURS
// ============================================================================
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// ============================================================================
// FIN DE LA CONFIGURATION
// ============================================================================
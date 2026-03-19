<?php
/**
 * ============================================================================
 * APPLICATION DE GESTION DE FACTURES & DEVIS
 * Garage Auto Service - Mécanicien
 * ============================================================================
 * 
 * Point d'entrée principal (Routeur)
 * Toutes les requêtes passent par ce fichier
 * 
 * @version 2.0
 * @author Garage Auto Service
 * @package MechanicApp
 */

// ============================================================================
// CONFIGURATION INITIALE
// ============================================================================

// Démarrage de la session
session_start();

// Signaler toutes les erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ============================================================================
// CHARGEMENT DES FICHIERS DE CONFIGURATION
// ============================================================================

require_once 'config.php';

// ============================================================================
// CHARGEMENT DES MODÈLES (MODELS)
// ============================================================================

require_once 'models/Database.php';
require_once 'models/Invoice.php';
require_once 'models/Devis.php';
require_once 'models/Client.php';
require_once 'models/RepairType.php';
require_once 'models/User.php';

// ============================================================================
// CHARGEMENT DES CONTRÔLEURS (CONTROLLERS)
// ============================================================================

require_once 'controllers/InvoiceController.php';
require_once 'controllers/DevisController.php';
require_once 'controllers/ClientController.php';
require_once 'controllers/RepairTypeController.php';
require_once 'controllers/AuthController.php';

// ============================================================================
// RÉCUPÉRATION DES PARAMÈTRES DE LA REQUÊTE
// ============================================================================

$route = $_GET['route'] ?? 'login';
$id = $_GET['id'] ?? null;

// ============================================================================
// ROUTES PUBLIQUES (PAS BESOIN D'ÊTRE CONNECTÉ)
// ============================================================================

$publicRoutes = ['login', 'logout', 'create_admin'];

// ============================================================================
// VÉRIFICATION DE L'AUTHENTIFICATION
// ============================================================================

if (!in_array($route, $publicRoutes)) {
    $auth = new AuthController();
    if (!$auth->checkSession()) {
        // Rediriger vers la page de connexion
        header('Location: index.php?route=login&redirect=' . urlencode($route));
        exit;
    }
}

// ============================================================================
// GESTION DES ROUTES
// ============================================================================

try {
    switch ($route) {
        // ====================================================================
        // ROUTES AUTHENTIFICATION
        // ====================================================================
        
        /**
         * Page de connexion
         */
        case 'login':
            (new AuthController())->login();
            break;
        
        /**
         * Déconnexion
         */
        case 'logout':
            (new AuthController())->logout();
            break;
        
        /**
         * Création du premier administrateur
         */
        case 'create_admin':
            (new AuthController())->createAdmin();
            break;
        
        // ====================================================================
        // ROUTES FACTURES
        // ====================================================================
        
        /**
         * Liste des factures (Tableau de bord)
         */
        case 'invoices':
            (new InvoiceController())->index();
            break;
        
        /**
         * Créer une nouvelle facture
         */
        case 'invoices_create':
            (new InvoiceController())->create();
            break;
        
        /**
         * Modifier une facture existante
         */
        case 'invoices_edit':
            (new InvoiceController())->edit($id);
            break;
        
        /**
         * Supprimer une facture
         */
        case 'invoices_delete':
            (new InvoiceController())->delete($id);
            break;
        
        /**
         * Imprimer une facture (PDF)
         */
        case 'invoices_print':
            (new InvoiceController())->print($id);
            break;
        
        // ====================================================================
        // ROUTES DEVIS
        // ====================================================================
        
        /**
         * Liste des devis
         */
        case 'devis':
            (new DevisController())->index();
            break;
        
        /**
         * Créer un nouveau devis
         */
        case 'devis_create':
            (new DevisController())->create();
            break;
        
        /**
         * Modifier un devis existant
         */
        case 'devis_edit':
            (new DevisController())->edit($id);
            break;
        
        /**
         * Supprimer un devis
         */
        case 'devis_delete':
            (new DevisController())->delete($id);
            break;
        
        /**
         * Imprimer un devis (PDF)
         */
        case 'devis_print':
            (new DevisController())->print($id);
            break;
        
        /**
         * Convertir un devis en facture
         */
        case 'devis_convert':
            (new DevisController())->convert($id);
            break;
        
        /**
         * Mettre à jour le statut d'un devis
         */
        case 'devis_update_status':
            $status = $_GET['status'] ?? 'draft';
            (new DevisController())->updateStatus($id, $status);
            break;
        
        /**
         * Recherche de clients (JSON - pour devis)
         */
        case 'devis_search_clients':
            (new DevisController())->searchClients();
            break;
        
        // ====================================================================
        // ROUTES CLIENTS
        // ====================================================================
        
        /**
         * Gérer les clients (liste + formulaire)
         */
        case 'clients_create':
            (new ClientController())->create();
            break;
        
        /**
         * Modifier un client
         */
        case 'clients_edit':
            (new ClientController())->edit($id);
            break;
        
        /**
         * Supprimer un client
         */
        case 'clients_delete':
            (new ClientController())->delete($id);
            break;
        
        // ====================================================================
        // ROUTES HISTORIQUE CLIENT
        // ====================================================================
        
        /**
         * Historique des réparations par client
         */
        case 'client_history':
            (new InvoiceController())->clientHistory();
            break;
        
        // ====================================================================
        // ROUTES RECHERCHE CLIENTS (JSON)
        // ====================================================================
        
        /**
         * Recherche de clients (JSON - pour factures)
         */
        case 'search_clients':
            (new InvoiceController())->searchClients();
            break;
        
        // ====================================================================
        // ROUTES TYPES DE RÉPARATION
        // ====================================================================
        
        /**
         * Gérer les types de réparation (liste + formulaire)
         */
        case 'types_create':
            (new RepairTypeController())->create();
            break;
        
        /**
         * Modifier un type de réparation
         */
        case 'types_edit':
            (new RepairTypeController())->edit($id);
            break;
        
        /**
         * Supprimer un type de réparation
         */
        case 'types_delete':
            (new RepairTypeController())->delete($id);
            break;
        
        // ====================================================================
        // ROUTE PAR DÉFAUT
        // ====================================================================
        
        /**
         * Redirection vers le tableau de bord
         */
        default:
            (new InvoiceController())->index();
            break;
    }
} catch (Exception $e) {
    // ========================================================================
    // GESTION DES ERREURS
    // ========================================================================
    
    // Journaliser l'erreur
    error_log("[" . date('Y-m-d H:i:s') . "] Erreur dans l'application: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Affichage selon le mode
    if (defined('DEBUG') && DEBUG) {
        // Mode développement - afficher les détails
        echo "<!DOCTYPE html>";
        echo "<html lang='fr'><head><meta charset='UTF-8'><title>Erreur</title>";
        echo "<style>body{font-family:Arial,sans-serif;margin:40px;background:#f4f4f4;}";
        echo ".error{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:20px;border-radius:5px;}";
        echo "pre{background:#fff;padding:15px;overflow:auto;}</style></head><body>";
        echo "<div class='error'>";
        echo "<h1>⚠️ Une erreur est survenue</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Fichier:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Ligne:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
        echo "<h3>Stack Trace:</h3><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "<p><a href='index.php?route=invoices'>← Retour au tableau de bord</a></p>";
        echo "</div></body></html>";
    } else {
        // Mode production - message générique
        echo "<!DOCTYPE html>";
        echo "<html lang='fr'><head><meta charset='UTF-8'><title>Erreur</title>";
        echo "<style>body{font-family:Arial,sans-serif;margin:40px;text-align:center;}";
        echo ".error{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:30px;border-radius:5px;display:inline-block;}</style></head><body>";
        echo "<div class='error'>";
        echo "<h1>⚠️ Une erreur est survenue</h1>";
        echo "<p>Veuillez réessayer ultérieurement.</p>";
        echo "<p><a href='index.php?route=invoices'>← Retour au tableau de bord</a></p>";
        echo "</div></body></html>";
    }
}

// ============================================================================
// FIN DU SCRIPT
// ============================================================================
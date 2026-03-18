<?php

// ============================================================
// FAILLE CONFIG-01 : Affichage des erreurs activé en production
// ============================================================
ini_set('display_errors', 0);      // ❌ FAILLE: révèle les chemins, structure BDD
error_reporting(E_ALL);

// ============================================================
// FAILLE CONFIG-02 : Informations de connexion BDD en dur dans le code
// ============================================================
define('DB_HOST', getenv('MYSQL_HOST') ?: 'db');
define('DB_NAME', getenv('MYSQL_DB')   ?: 'securite_lab');
define('DB_USER', getenv('MYSQL_USER') ?: 'labuser');
define('DB_PASS', getenv('MYSQL_PASS') ?: 'labpass123');  // ❌ FAILLE: mot de passe exposé

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // NOTE: ici PDO est utilisé mais les requêtes dans le reste du code ne le sont PAS
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

// ============================================================
// FAILLE SESSION-01 : Configuration de session non sécurisée
// ============================================================
// Aucun de ces paramètres n'est défini :
// session.cookie_httponly = 1
// session.cookie_secure   = 1
// session.cookie_samesite = Strict

// À placer dans votre config.php, AVANT session_start()
session_set_cookie_params([
    'lifetime' => 0,              // Expire à la fermeture du navigateur
    'path' => '/',                // Valide sur tout le site
    'secure' => true,             // ⚠️ HTTPS uniquement (false en dev local)
    'httponly' => true,           // JavaScript ne peut PAS lire ce cookie
    'samesite' => 'Strict'        // Bloque les requêtes cross-site
]);

session_start();

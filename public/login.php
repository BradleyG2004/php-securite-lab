<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    // ============================================================
    // FAILLE SQLi-01 : Injection SQL directe dans la requête
    // Les données $_POST sont concaténées sans protection
    // Test : username = ' OR '1'='1  / password = anything
    // ============================================================
    // $username = $_POST['username'];   // ❌ FAILLE: non filtré, non échappé
    // $password = $_POST['password'];   // ❌ FAILLE: non filtré

    // $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    // // ❌ FAILLE SQLi-01 : concaténation directe = injection SQL

    // $result = $pdo->query($query);
    // $user = $result->fetch(PDO::FETCH_ASSOC);

    // Récupération des données (sans modification)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Requête préparée
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");

    // Exécution avec paramètres liés
    $stmt->execute([
        ':username' => $username,
        ':password' => $password
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ============================================================
        // FAILLE SESSION-02 : Pas de régénération de l'ID de session
        // après l'authentification → session fixation possible
        // ============================================================
        // session_regenerate_id(true);  ← absent intentionnellement

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];

        // ============================================================
        // FAILLE SESSION-03 : Pas de timestamp de connexion
        // → pas d'expiration de session possible côté serveur
        // ============================================================

        header('Location: dashboard.php');
        exit;
    } else {
        // ============================================================
        // FAILLE INFO-01 : Message d'erreur trop précis
        // Permet de confirmer qu'un utilisateur existe
        // ============================================================
        $error = "Nom d'utilisateur ou mot de passe incorrect.";

        // ============================================================
        // FAILLE BRUTEFORCE-01 : Aucune limitation des tentatives
        // Un attaquant peut tester des milliers de mots de passe
        // ============================================================
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #c0392b; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #c0392b; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .error { background: #ffe0e0; padding: 10px; border-radius: 4px; margin-bottom: 16px; color: #c0392b; }
        a { color: #c0392b; }
        .hint { font-size: 12px; color: #888; margin-top: 20px; background: #fff8e1; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
<h1>🔐 Connexion</h1>

<?php if ($error): ?>
    <div class="error"><?= $error ?></div>
<?php endif; ?>

<!-- ============================================================
     FAILLE CSRF-01 : Absence de token CSRF sur le formulaire
     Un site tiers peut soumettre ce formulaire à l'insu de l'utilisateur
     ============================================================ -->
<form method="POST">
    <label>Nom d'utilisateur</label>
    <input type="text" name="username" placeholder="admin" required>

    <label>Mot de passe</label>
    <input type="password" name="password" required>

    <button type="submit">Se connecter</button>
</form>

<p><a href="register.php">Pas de compte ? S'inscrire</a></p>

<div class="hint">
    💡 <strong>Indice élève :</strong> Essaie le username : <code>' OR '1'='1' --</code>
    et n'importe quel mot de passe...
</div>

</body>
</html>

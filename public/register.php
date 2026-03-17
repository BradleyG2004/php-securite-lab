<?php
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    // =========================
    // ✅ VALIDATION
    // =========================

    // Username
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Nom d'utilisateur invalide (3-50 caractères).";
    }

    // Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    // Mot de passe
    if (strlen($password) < 8) {
        $errors[] = "Mot de passe trop court (min 8 caractères).";
    }

    // Vérifier si utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
    $stmt->execute([
        ':email' => $email,
        ':username' => $username
    ]);

    if ($stmt->fetch()) {
        $errors[] = "Utilisateur déjà existant.";
    }

    // =========================
    // ✅ SI PAS D'ERREUR
    // =========================
    if (empty($errors)) {

        // 🔒 Hash du mot de passe (CRITIQUE)
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (:username, :email, :password)
        ");

        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);

        $success = "Compte créé ! <a href='login.php'>Se connecter</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #c0392b; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #c0392b; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .success { background: #e0ffe0; padding: 10px; border-radius: 4px; margin-bottom: 16px; color: #27ae60; }
    </style>
</head>
<body>
<h1>📝 Inscription</h1>

<?php if ($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <label>Nom d'utilisateur</label>
    <input type="text" name="username" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mot de passe</label>
    <input type="password" name="password" required>
    <!-- ❌ FAILLE: aucune règle de complexité -->

    <button type="submit">Créer mon compte</button>
</form>

</body>
</html>

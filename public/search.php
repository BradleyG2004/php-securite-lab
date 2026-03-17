<?php
require_once 'config.php';

$results = [];
$query_str = '';

if (isset($_GET['q'])) {
    // $pdo = getDB();

    // // ============================================================
    // // FAILLE SQLi-03 : Injection SQL dans la recherche
    // // Test : ' UNION SELECT 1,username,password,email,role,created_at FROM users--
    // // ============================================================
    // $query_str = $_GET['q'];  // ❌ FAILLE: non filtré

    // $sql = "SELECT * FROM articles WHERE title LIKE '%$query_str%' OR content LIKE '%$query_str%'";
    // // ❌ FAILLE SQLi-03

    // $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $pdo = getDB();

    // Récupération de l'entrée
    $query_str = $_GET['q'] ?? '';

    // Préparation du pattern LIKE (on ajoute les % ici, pas dans la requête SQL)
    $search = '%' . $query_str . '%';

    // Requête préparée
    $stmt = $pdo->prepare("
        SELECT * 
        FROM articles 
        WHERE title LIKE :search 
        OR content LIKE :search
    ");

    // Exécution sécurisée
    $stmt->execute([
        ':search' => $search
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        nav { background: #c0392b; padding: 12px 20px; border-radius: 6px; margin-bottom: 30px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        input { padding: 10px; width: 70%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #c0392b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .card { background: white; border-radius: 8px; padding: 16px; margin-top: 16px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        h1 { color: #c0392b; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">🏠 Accueil</a>
    <a href="search.php">🔍 Recherche</a>
</nav>

<h1>🔍 Recherche</h1>

<form method="GET">
    <input 
        type="text" 
        name="q" 
        value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
        placeholder="Rechercher..."
    >
    <button type="submit">Rechercher</button>
</form>

<?php if ($query_str !== ''): ?>
    <p>
        Résultats pour : 
        <strong><?= htmlspecialchars($query_str, ENT_QUOTES, 'UTF-8') ?></strong>
    </p>

    <?php if (empty($results)): ?>
        <p>Aucun résultat.</p>
    <?php else: ?>
        <?php foreach ($results as $r): ?>
        <div class="card">
            <h3>
                <a href="article.php?id=<?= urlencode($r['id']) ?>">
                    <?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </h3>
            <p>
                <?= htmlspecialchars(mb_substr($r['content'], 0, 200), ENT_QUOTES, 'UTF-8') ?>...
            </p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>

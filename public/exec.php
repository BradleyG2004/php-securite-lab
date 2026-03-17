<?php
require_once 'config.php';

// ============================================================
// FAILLE CMD-01 : Injection de commande système
// Test : ?host=127.0.0.1; cat /etc/passwd
// Test : ?host=127.0.0.1 && ls /var/www/html
// Test : ?host=`id`
// ============================================================
$output = '';
$host   = '';

if (isset($_GET['host'])) {
    $host = $_GET['host'];

    // validation hostname ou IP
    if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) &&
        !filter_var($host, FILTER_VALIDATE_IP)) {
        die("Host invalide");
    }

    exec("ping -c 1 " . escapeshellarg($host), $output_lines);
    $output = implode("\n", $output_lines);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ping Tool – Lab Sécurité</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #c0392b; }
        input { padding: 10px; width: 60%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #c0392b; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        pre { background: #1e1e1e; color: #0f0; padding: 16px; border-radius: 6px; overflow-x: auto; }
        .hint { background: #fff8e1; padding: 12px; border-radius: 4px; margin-top: 20px; font-size: 13px; }
        a { color: #c0392b; }
    </style>
</head>
<body>

<h1>🖧 Outil Ping (vulnérable)</h1>

<form method="GET">
    <input type="text" name="host" value="<?= htmlspecialchars($host) ?>" placeholder="ex: 127.0.0.1">
    <button type="submit">Ping</button>
</form>

<?php if ($output): ?>
    <pre><?= htmlspecialchars($output) ?></pre>
<?php endif; ?>

<div class="hint">
    💡 <strong>Indice :</strong><br>
    Essaie : <code>127.0.0.1; id</code><br>
    Essaie : <code>127.0.0.1 && cat /etc/passwd</code><br>
    Essaie : <code>127.0.0.1 | ls /var/www/html</code>
</div>

<p><a href="index.php">← Retour</a></p>

</body>
</html>

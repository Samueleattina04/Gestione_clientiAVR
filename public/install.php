<?php
/**
 * AVR Gestionale — Web Installer
 * Visita questa pagina UNA SOLA VOLTA dopo aver caricato i file sul server.
 * ELIMINA QUESTO FILE dopo l'installazione!
 */

define('BASE_PATH', dirname(__DIR__));

// Semplice protezione
$secret = $_GET['token'] ?? '';
if ($secret !== 'avr-install-2024') {
    die('<h2>Accesso negato.</h2><p>Aggiungi ?token=avr-install-2024 all\'URL</p>');
}

$action = $_POST['action'] ?? '';
$results = [];

function run($cmd) {
    $output = [];
    $code = 0;
    exec("cd " . BASE_PATH . " && $cmd 2>&1", $output, $code);
    return ['cmd' => $cmd, 'output' => implode("\n", $output), 'ok' => $code === 0];
}

if ($action === 'install') {
    $results[] = run('php artisan key:generate --force');
    $results[] = run('php artisan migrate --seed --force');
    $results[] = run('php artisan config:cache');
    $results[] = run('php artisan route:cache');
    $results[] = run('php artisan view:cache');
    $results[] = run('chmod -R 775 storage bootstrap/cache');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>AVR Gestionale — Installazione</title>
<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
h1 { color: #C8102E; }
.card { background: white; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.btn { background: #C8102E; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; }
.ok { color: green; font-weight: bold; }
.err { color: red; font-weight: bold; }
pre { background: #1a1a1a; color: #eee; padding: 12px; border-radius: 4px; overflow-x: auto; font-size: 13px; }
.warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-top: 20px; border-radius: 4px; }
</style>
</head>
<body>
<h1>🔧 AVR Gestionale — Installazione</h1>

<?php if (empty($results)): ?>
<div class="card">
    <h2>Benvenuto nell'installer</h2>
    <p>Questo script eseguirà automaticamente:</p>
    <ul>
        <li>✅ Generazione chiave applicazione</li>
        <li>✅ Creazione tabelle database (<code>migrate --seed</code>)</li>
        <li>✅ Ottimizzazione cache</li>
        <li>✅ Permessi cartelle storage</li>
    </ul>
    <p><strong>Assicurati di aver configurato il file <code>.env</code> con i dati del database prima di procedere.</strong></p>
    <form method="POST">
        <input type="hidden" name="action" value="install">
        <button type="submit" class="btn">▶ Avvia Installazione</button>
    </form>
</div>
<?php else: ?>
<div class="card">
    <h2>Risultati installazione</h2>
    <?php foreach ($results as $r): ?>
        <p class="<?= $r['ok'] ? 'ok' : 'err' ?>"><?= $r['ok'] ? '✅' : '❌' ?> <code><?= htmlspecialchars($r['cmd']) ?></code></p>
        <?php if ($r['output']): ?><pre><?= htmlspecialchars($r['output']) ?></pre><?php endif; ?>
    <?php endforeach; ?>

    <?php $allOk = array_reduce($results, fn($c, $r) => $c && $r['ok'], true); ?>
    <?php if ($allOk): ?>
        <p class="ok" style="font-size:18px; margin-top:20px;">🎉 Installazione completata con successo!</p>
        <p><strong>Credenziali di accesso:</strong><br>
        Email: <code>admin@avrinformatica.it</code><br>
        Password: <code>avr2024!</code></p>
        <div class="warning">
            ⚠️ <strong>IMPORTANTE:</strong> Elimina il file <code>public/install.php</code> dal server subito dopo aver effettuato il primo accesso!
        </div>
    <?php else: ?>
        <p class="err">❌ Alcuni passaggi hanno avuto errori. Controlla la configurazione del <code>.env</code> e riprova.</p>
        <form method="POST">
            <input type="hidden" name="action" value="install">
            <button type="submit" class="btn">🔄 Riprova</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>
</body>
</html>

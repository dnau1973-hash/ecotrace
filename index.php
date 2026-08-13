<?php
/**
 * EcoTrace 🍃 - Version 1.6.2
 * 
 * Copyright (C) 2026 David NAU <dnau.1973@gmail.com>
 * 
 * Ce programme est un logiciel libre : vous pouvez le redistribuer et/ou le modifier
 * selon les termes de la GNU Affero General Public License.
 */

session_start();

// =========================================================================
// 1. GESTION DE LA CONFIGURATION (.env) ET ASSISTANT D'INSTALLATION
// =========================================================================
$envFile = __DIR__ . '/.env';

if (isset($_GET['reset_config'])) {
    if (file_exists($envFile)) unlink($envFile);
    header("Location: ?"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_env_action'])) {
    $newEnv = "DB_HOST=\"" . addslashes($_POST['db_host']) . "\"\n"
            . "DB_NAME=\"" . addslashes($_POST['db_name']) . "\"\n"
            . "DB_USER=\"" . addslashes($_POST['db_user']) . "\"\n"
            . "DB_PASS=\"" . addslashes($_POST['db_pass']) . "\"\n"
            . "ADMIN_USER=\"" . addslashes($_POST['admin_user']) . "\"\n"
            . "ADMIN_PASS=\"" . addslashes($_POST['admin_pass']) . "\"\n"
            . "PAPPERS_API_KEY=\"" . addslashes(trim($_POST['pappers_api_key'] ?? '')) . "\"\n"
            . "SOCIETE_API_KEY=\"" . addslashes(trim($_POST['societe_api_key'] ?? '')) . "\"\n"
            . "GITHUB_REPO=\"" . addslashes(trim($_POST['github_repo'] ?? '')) . "\"\n"
            . "ORIGIN_LAT=" . (float)$_POST['origin_lat'] . "\n"
            . "ORIGIN_LON=" . (float)$_POST['origin_lon'] . "\n";
    file_put_contents($envFile, $newEnv);
    header("Location: ?"); exit;
}

$env = file_exists($envFile) ? parse_ini_file($envFile) : [];
$db_host = $env['DB_HOST'] ?? 'db';
$db_name = $env['DB_NAME'] ?? 'ecotrace';
$db_user = $env['DB_USER'] ?? 'root';
$db_pass = $env['DB_PASS'] ?? '';
$admin_user = $env['ADMIN_USER'] ?? 'admin';
$admin_pass = $env['ADMIN_PASS'] ?? 'admin';
$pappers_key = trim($env['PAPPERS_API_KEY'] ?? '');
$societe_key = trim($env['SOCIETE_API_KEY'] ?? '');
// URL GitHub intégrée par défaut SANS le token (sécurité)
$github_repo = trim($env['GITHUB_REPO'] ?? 'https://github.com/dnau1973-hash/ecotrace.git');
$origineLat = $env['ORIGIN_LAT'] ?? 45.19165526;
$origineLon = $env['ORIGIN_LON'] ?? 0.76262712;

$is_editing_config = (isset($_GET['edit_config']) && !empty($_SESSION['ecotrace_logged_in']));

if (!file_exists($envFile) || $is_editing_config) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>EcoTrace 🍃 - Configuration</title>
        <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Nunito', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
            .install-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 550px; }
            h1 { font-family: 'Fredoka', sans-serif; color: #27ae60; margin-top: 0; text-align: center; margin-bottom: 0;}
            .app-version { text-align: center; font-size: 13px; color: #7f8c8d; margin-bottom: 20px; font-weight: bold; }
            h2 { color: #2c3e50; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top: 25px;}
            .row { display: flex; gap: 15px; margin-bottom: 10px; }
            .form-group { flex: 1; display: flex; flex-direction: column; }
            label { font-weight: bold; font-size: 13px; color: #555; margin-bottom: 5px; }
            input { padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; font-family: 'Nunito', sans-serif; }
            input:focus { border-color: #27ae60; outline: none; }
            button { width: 100%; background: #27ae60; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 20px; font-weight: bold; font-family: 'Nunito', sans-serif;}
            button:hover { background: #219150; }
            .btn-cancel { text-align: center; display: block; margin-top: 15px; color: #7f8c8d; text-decoration: none; font-weight: bold; }
            .btn-cancel:hover { color: #e74c3c; }
        </style>
    </head>
    <body>
        <div class="install-box">
            <h1>EcoTrace 🍃</h1>
            <div class="app-version">Version 1.6.2</div>
            <p style="text-align:center; color:#666;"><?= $is_editing_config ? "Modification des paramètres de configuration." : "Veuillez configurer les paramètres avant l'installation." ?></p>
            <form method="POST" action="?">
                <input type="hidden" name="setup_env_action" value="1">
                <h2>⚙️ Base de données (MySQL)</h2>
                <div class="row">
                    <div class="form-group"><label>Hôte</label><input type="text" name="db_host" value="<?= htmlspecialchars($db_host) ?>" required></div>
                    <div class="form-group"><label>Nom de la base</label><input type="text" name="db_name" value="<?= htmlspecialchars($db_name) ?>" required></div>
                </div>
                <div class="row">
                    <div class="form-group"><label>Utilisateur</label><input type="text" name="db_user" value="<?= htmlspecialchars($db_user) ?>" required></div>
                    <div class="form-group"><label>Mot de passe</label><input type="password" name="db_pass" value="<?= htmlspecialchars($db_pass) ?>"></div>
                </div>
                <h2>👤 Compte Administrateur</h2>
                <div class="row">
                    <div class="form-group"><label>Identifiant</label><input type="text" name="admin_user" value="<?= htmlspecialchars($admin_user) ?>" required></div>
                    <div class="form-group"><label>Mot de passe</label><input type="text" name="admin_pass" value="<?= htmlspecialchars($admin_pass) ?>" required></div>
                </div>
                <h2>📍 Point de départ (Distances GPS)</h2>
                <div class="row">
                    <div class="form-group"><label>Latitude</label><input type="number" step="any" name="origin_lat" value="<?= htmlspecialchars($origineLat) ?>" required></div>
                    <div class="form-group"><label>Longitude</label><input type="number" step="any" name="origin_lon" value="<?= htmlspecialchars($origineLon) ?>" required></div>
                </div>
                <h2>🔑 Clés API et Auto-Updater</h2>
                <div class="row">
                    <div class="form-group"><label>Lien dépôt GitHub (Auto-Updater)</label><input type="text" name="github_repo" value="<?= htmlspecialchars($github_repo) ?>" placeholder="Ex: https://ghp_XXXX@github.com/.../ecotrace.git"></div>
                </div>
                <div class="row">
                    <div class="form-group"><label>Clé API Pappers</label><input type="text" name="pappers_api_key" value="<?= htmlspecialchars($pappers_key) ?>" placeholder="Ex: a1b2c3d4e5f6..."></div>
                </div>
                <div class="row">
                    <div class="form-group"><label>Clé API Societe.com</label><input type="text" name="societe_api_key" value="<?= htmlspecialchars($societe_key) ?>" placeholder="Ex: soc_key_12345..."></div>
                </div>
                <button type="submit">💾 Enregistrer et Continuer</button>
                <?php if($is_editing_config): ?>
                    <a href="?" class="btn-cancel">❌ Annuler et retourner à l'application</a>
                <?php endif; ?>
            </form>
        </div>
    </body>
    </html>
    <?php exit;
}

// =========================================================================
// 2. GESTION DE L'AUTHENTIFICATION
// =========================================================================
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        $_SESSION['ecotrace_logged_in'] = true; header("Location: ?"); exit;
    } else {
        $login_error = "Identifiants incorrects.";
    }
}

if (empty($_SESSION['ecotrace_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>EcoTrace 🍃 - Connexion</title>
        <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Nunito', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
            h1 { font-family: 'Fredoka', sans-serif; color: #27ae60; margin-bottom: 0; }
            .app-version { font-size: 13px; color: #7f8c8d; margin-bottom: 20px; font-weight: bold; }
            input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; font-family: 'Nunito', sans-serif;}
            button { width: 100%; background: #27ae60; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; font-family: 'Nunito', sans-serif;}
            button:hover { background: #219150; }
            .error { color: #e74c3c; margin-bottom: 15px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>EcoTrace 🍃</h1>
            <div class="app-version">Version 1.6.2</div>
            <p>Veuillez vous identifier</p>
            <?php if (isset($login_error)) echo "<div class='error'>$login_error</div>"; ?>
            <form method="POST">
                <input type="hidden" name="login_action" value="1">
                <input type="text" name="username" placeholder="Identifiant" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </body>
    </html>
    <?php exit;
}

// =========================================================================
// 2.5 AUTO-UPDATER GITHUB
// =========================================================================

// Vérifier la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_github_update') {
    header('Content-Type: application/json');
    if (empty($github_repo)) { echo json_encode(['success' => false, 'message' => 'Le lien du dépôt GitHub n\'est pas configuré dans les paramètres.']); exit; }
    
    shell_exec('git config --global --add safe.directory /var/www/html');
    
    if (!is_dir('.git')) {
        echo json_encode(['success' => true, 'update_available' => true, 'message' => 'L\'application n\'est pas encore synchronisée avec GitHub.<br>Cliquez sur le bouton ci-dessous pour initialiser la connexion.']);
        exit;
    }
    
    shell_exec('git fetch origin main 2>&1');
    $local = trim(shell_exec('git rev-parse HEAD 2>/dev/null'));
    $remote = trim(shell_exec('git rev-parse origin/main 2>/dev/null'));
    
    if ($local !== $remote && !empty($remote)) {
        echo json_encode(['success' => true, 'update_available' => true, 'message' => "✨ Une nouvelle version est disponible sur GitHub !<br><br><span style='font-size:12px;color:#7f8c8d;'>Votre version : " . substr($local, 0, 7) . "<br>Nouvelle version : " . substr($remote, 0, 7) . "</span>"]);
    } else {
        echo json_encode(['success' => true, 'update_available' => false, 'message' => '✅ Votre application est déjà à jour par rapport à GitHub !']);
    }
    exit;
}

// Exécuter la mise à jour avec protection du .env
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'do_github_update') {
    header('Content-Type: application/json');
    if (empty($github_repo)) { echo json_encode(['success' => false, 'message' => 'Le lien GitHub n\'est pas configuré.']); exit; }
    
    $env_backup = file_exists($envFile) ? file_get_contents($envFile) : false;
    shell_exec('git config --global --add safe.directory /var/www/html');
    
    if (!is_dir('.git')) {
        shell_exec('git init');
        shell_exec('git remote add origin ' . escapeshellarg($github_repo));
    }
    
    shell_exec('git fetch origin main 2>&1');
    $output = shell_exec('git reset --hard origin/main 2>&1');
    
    if ($env_backup !== false) {
        file_put_contents($envFile, $env_backup);
    }
    
    echo json_encode(['success' => true, 'message' => "Mise à jour téléchargée et appliquée avec succès !<br>L'application va redémarrer."]);
    exit;
}

// =========================================================================
// 3. GESTION DE L'INSTALLATION & MAJ STRUCTURE BDD
// =========================================================================
$needsInstall = false;
$pdo = null;

try {
    $pdoServer = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdoServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdoServer->query("SHOW DATABASES LIKE '$db_name'");
    if (!$stmt->fetch()) {
        $needsInstall = true;
    } else {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmtTbl = $pdo->query("SHOW TABLES LIKE 'sources_csv'");
        if (!$stmtTbl->fetch()) {
            $needsInstall = true;
        } else {
            try { $pdo->exec("ALTER TABLE api_resultats ADD COLUMN statut_juridique VARCHAR(100) DEFAULT 'Actif' AFTER est_alimentaire"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE api_resultats ADD COLUMN latitude DECIMAL(10,8) NULL AFTER siege_adresse"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE api_resultats ADD COLUMN longitude DECIMAL(10,8) NULL AFTER latitude"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE sources_csv ADD COLUMN montant DECIMAL(10,2) DEFAULT 0 AFTER nom_recherche"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE sources_csv ADD COLUMN poids DECIMAL(10,2) DEFAULT 0 AFTER montant"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE api_resultats ADD COLUMN est_ess TINYINT(1) DEFAULT 0 AFTER est_alimentaire"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE api_resultats ADD COLUMN est_societe_mission TINYINT(1) DEFAULT 0 AFTER est_ess"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE api_resultats ADD COLUMN est_connu TINYINT(1) DEFAULT 0 AFTER est_societe_mission"); } catch (PDOException $e) {}
            // Table Dictionnaire NAF
            try { $pdo->exec("CREATE TABLE IF NOT EXISTS codes_naf (code VARCHAR(10) PRIMARY KEY, libelle VARCHAR(255)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"); } catch (PDOException $e) {}
        }
    }
} catch (PDOException $e) {
    echo "<body style='font-family: Arial, sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;'>";
    echo "<div style='text-align:center; padding: 40px; background: white; border-radius:8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 500px;'>";
    echo "<h2 style='color:#e74c3c; margin-top: 0;'>Erreur critique MySQL</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage() ?? '') . "</p>";
    echo "<a href='?reset_config=1' style='display:inline-block; padding:12px 20px; background:#3498db; color:white; text-decoration:none; border-radius:4px;'>⚙️ Configuration</a>";
    echo "</div></body>"; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_action'])) {
    try {
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdoServer->exec("USE `$db_name`;");
        $sql = "
        CREATE TABLE IF NOT EXISTS sources_csv (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom_recherche VARCHAR(255) NOT NULL,
            montant DECIMAL(10,2) DEFAULT 0,
            poids DECIMAL(10,2) DEFAULT 0,
            statut VARCHAR(50) DEFAULT 'en_attente', 
            api_result_id_selectionne INT NULL,
            date_import DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS api_resultats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_id INT NOT NULL,
            siren VARCHAR(9) NULL,
            nom_complet VARCHAR(255) NULL,
            activite_principale VARCHAR(10) NULL,
            activite_principale_libelle VARCHAR(255) NULL,
            est_alimentaire TINYINT(1) DEFAULT 0,
            est_ess TINYINT(1) DEFAULT 0,
            est_societe_mission TINYINT(1) DEFAULT 0,
            est_connu TINYINT(1) DEFAULT 0,
            statut_juridique VARCHAR(100) DEFAULT 'Actif',
            siege_adresse TEXT NULL,
            latitude DECIMAL(10,8) NULL,
            longitude DECIMAL(10,8) NULL,
            distance FLOAT NULL,
            date_requete DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (source_id) REFERENCES sources_csv(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS codes_naf (
            code VARCHAR(10) PRIMARY KEY, 
            libelle VARCHAR(255)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        ALTER TABLE sources_csv ADD CONSTRAINT fk_resultat_valide FOREIGN KEY (api_result_id_selectionne) REFERENCES api_resultats(id) ON DELETE SET NULL;
        ";
        $pdoServer->exec($sql);
        header("Location: ?"); exit;
    } catch (PDOException $e) { die("Erreur d'installation : " . htmlspecialchars($e->getMessage() ?? '')); }
}

// =========================================================================
// 3.5 EXPORT CSV ET DUMP SQL
// =========================================================================
function estimerCO2($codeNaf, $montant, $poids, $distance) {
    $co2 = 0;
    if ($poids > 0 && $distance > 0) {
        $tkm = ($poids / 1000) * $distance;
        $co2 += ($tkm * 0.1);
    }
    if ($montant > 0) {
        $div = substr($codeNaf, 0, 2);
        $facteur = 0.2; 
        if (in_array($div, ['10','11','56'])) $facteur = 0.45; 
        elseif (in_array($div, ['49','50','51','52'])) $facteur = 0.8; 
        elseif (in_array($div, ['61','62','63','69','70','71'])) $facteur = 0.05; 
        $co2 += ($montant * $facteur);
    }
    return round($co2, 2);
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ecotrace_export_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 
    fputcsv($output, ['ID Source', 'Recherche Initiale', 'Statut', 'Date Import', 'Montant (€)', 'Poids (kg)', 'Emissions Est. (kgCO2)', 'SIREN', 'Nom Validé', 'Code NAF', 'Libellé NAF', 'Alimentaire', 'ESS', 'A Mission', 'Connu', 'Santé Juridique', 'Adresse', 'Latitude', 'Longitude', 'Distance (km)'], ';');
    
    $stmtExport = $pdo->query("SELECT s.id, s.nom_recherche, s.statut, s.date_import, s.montant, s.poids, r.siren, r.nom_complet, r.activite_principale, r.activite_principale_libelle, r.est_alimentaire, r.est_ess, r.est_societe_mission, r.est_connu, r.statut_juridique, r.siege_adresse, r.latitude, r.longitude, r.distance FROM sources_csv s INNER JOIN api_resultats r ON s.api_result_id_selectionne = r.id WHERE s.statut IN ('valide_auto', 'valide_manuel') ORDER BY s.id DESC");
    
    while ($row = $stmtExport->fetch(PDO::FETCH_ASSOC)) {
        $co2 = estimerCO2($row['activite_principale'], $row['montant'], $row['poids'], $row['distance']);
        fputcsv($output, [
            $row['id'], $row['nom_recherche'], $row['statut'], $row['date_import'], $row['montant'], $row['poids'], $co2,
            $row['siren'], $row['nom_complet'], $row['activite_principale'], $row['activite_principale_libelle'],
            $row['est_alimentaire']?'OUI':'NON', $row['est_ess']?'OUI':'NON', $row['est_societe_mission']?'OUI':'NON', $row['est_connu']?'OUI':'NON',
            $row['statut_juridique'], $row['siege_adresse'], $row['latitude'], $row['longitude'], $row['distance']
        ], ';');
    }
    fclose($output); exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'sql') {
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename=ecotrace_dump_' . date('Ymd_His') . '.sql');
    $output = fopen('php://output', 'w');
    fwrite($output, "-- Dump SQL EcoTrace 🍃 (Généré le : " . date('Y-m-d H:i:s') . ")\nSET FOREIGN_KEY_CHECKS = 0;\n\n");
    foreach (['sources_csv', 'api_resultats', 'codes_naf'] as $table) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            fwrite($output, str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row['Create Table']) . ";\n\n");
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $vals = array_map(function($val) use ($pdo) { return $val === null ? 'NULL' : $pdo->quote($val); }, array_values($r));
                fwrite($output, "INSERT INTO `$table` (`" . implode("`, `", array_keys($r)) . "`) VALUES (" . implode(", ", $vals) . ");\n");
            }
            fwrite($output, "\n");
        }
    }
    fwrite($output, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($output); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_sql' && isset($_FILES['sql_file'])) {
    header('Content-Type: application/json');
    $sqlContent = file_get_contents($_FILES['sql_file']['tmp_name']);
    if (empty($sqlContent)) { echo json_encode(['success' => false, 'message' => "Fichier SQL vide."]); exit; }
    try { $pdo->exec($sqlContent); echo json_encode(['success' => true, 'message' => "Base restaurée avec succès !"]); } 
    catch (Exception $e) { echo json_encode(['success' => false, 'message' => "Erreur SQL : " . htmlspecialchars($e->getMessage() ?? '')]); }
    exit;
}

// =========================================================================
// 4. FONCTIONS MÉTIERS ET FALLBACKS
// =========================================================================

function geocoderAdresseOSM($adresse) {
    if (empty($adresse)) return null;
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($adresse) . "&limit=1";
    $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0 (dnau.1973@gmail.com)'); curl_setopt($ch, CURLOPT_TIMEOUT, 2); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $reponse = curl_exec($ch); curl_close($ch);
    if ($reponse) {
        $data = json_decode($reponse, true);
        if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) return ['lat' => (float)$data[0]['lat'], 'lon' => (float)$data[0]['lon']];
    }
    return null;
}

function calculerDistanceVolDOiseau($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    return round($earth_radius * (2 * asin(sqrt($a))), 2);
}

function calculerDistance($lat1, $lon1, $lat2, $lon2) {
    $url = "http://router.project-osrm.org/route/v1/driving/{$lon1},{$lat1};{$lon2},{$lat2}?overview=false";
    $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0'); curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $reponse = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($httpCode == 200 && $reponse) {
        $donnees = json_decode($reponse, true);
        if (isset($donnees['routes'][0]['distance'])) return round($donnees['routes'][0]['distance'] / 1000, 2);
    }
    return calculerDistanceVolDOiseau($lat1, $lon1, $lat2, $lon2);
}

// Nouveauté 1.6.2 : Recherche du NAF dans la base de données
function getLibelleNafLocal($codeNaf) {
    global $pdo;
    if (empty($codeNaf)) return "Non renseigné";
    $cleanCode = strtoupper(trim(str_replace('.', '', $codeNaf)));
    if (strlen($cleanCode) === 5) $cleanCode = substr($cleanCode, 0, 2) . '.' . substr($cleanCode, 2);
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT libelle FROM codes_naf WHERE code = :code LIMIT 1");
            $stmt->execute([':code' => $cleanCode]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res && !empty($res['libelle'])) return $res['libelle'];
        } catch (Exception $e) {}
    }
    
    // Fallback de sécurité (Dictionnaire des divisions principales si DB vide)
    $divs = ['01' => 'Agriculture et services annexes', '02' => 'Sylviculture', '03' => 'Pêche et aquaculture', '05' => 'Extraction de houille', '06' => 'Extraction d\'hydrocarbures', '07' => 'Extraction de minerais métalliques', '08' => 'Autres industries extractives', '09' => 'Services de soutien extractifs', '10' => 'Industrie alimentaire', '11' => 'Fabrication de boissons', '12' => 'Fabrication de produits à base de tabac', '13' => 'Fabrication de textiles', '14' => 'Industrie de l\'habillement', '15' => 'Industrie du cuir et de la chaussure', '16' => 'Travail du bois', '17' => 'Industrie du papier', '18' => 'Imprimerie', '19' => 'Cokéfaction et raffinage', '20' => 'Industrie chimique', '21' => 'Industrie pharmaceutique', '22' => 'Fabrication de produits en caoutchouc', '23' => 'Fabrication d\'autres produits minéraux non métalliques', '24' => 'Métallurgie', '25' => 'Fabrication de produits métalliques', '26' => 'Fabrication de produits informatiques et électroniques', '27' => 'Fabrication d\'équipements électriques', '28' => 'Fabrication de machines et équipements', '29' => 'Industrie automobile', '30' => 'Fabrication d\'autres matériels de transport', '31' => 'Fabrication de meubles', '32' => 'Autres industries manufacturières', '33' => 'Réparation et installation de machines', '35' => 'Production et distribution d\'électricité, gaz, vapeur', '36' => 'Captage, traitement et distribution d\'eau', '37' => 'Collecte et traitement des eaux usées', '38' => 'Collecte et traitement des déchets', '39' => 'Dépollution', '41' => 'Construction de bâtiments', '42' => 'Génie civil', '43' => 'Travaux de construction spécialisés', '45' => 'Commerce et réparation d\'automobiles', '46' => 'Commerce de gros', '47' => 'Commerce de détail', '49' => 'Transports terrestres', '50' => 'Transports par eau', '51' => 'Transports aériens', '52' => 'Entreposage et services logistiques', '53' => 'Activités de poste et de courrier', '55' => 'Hébergement', '56' => 'Restauration', '58' => 'Édition', '59' => 'Production de films, vidéo et musique', '60' => 'Programmation et diffusion', '61' => 'Télécommunications', '62' => 'Programmation et conseil en informatique', '63' => 'Services d\'information', '64' => 'Activités financières', '65' => 'Assurance', '66' => 'Activités auxiliaires de services financiers', '68' => 'Activités immobilières', '69' => 'Activités juridiques et comptables', '70' => 'Sièges sociaux et conseil de gestion', '71' => 'Architecture et ingénierie', '72' => 'Recherche-développement scientifique', '73' => 'Publicité et études de marché', '74' => 'Autres activités spécialisées, scientifiques et techniques', '75' => 'Activités vétérinaires', '77' => 'Location et location-bail', '78' => 'Activités liées à l\'emploi', '79' => 'Agences de voyage', '80' => 'Enquêtes et sécurité', '81' => 'Services relatifs aux bâtiments et paysagisme', '82' => 'Activités administratives de bureau', '84' => 'Administration publique', '85' => 'Enseignement', '86' => 'Santé humaine', '87' => 'Hébergement médico-social et social', '88' => 'Action sociale sans hébergement', '90' => 'Activités créatives, artistiques et de spectacle', '91' => 'Bibliothèques, archives et musées', '92' => 'Organisation de jeux de hasard', '93' => 'Activités sportives et récréatives', '94' => 'Organisations associatives', '95' => 'Réparation d\'ordinateurs et biens', '96' => 'Autres services personnels', '97' => 'Activités des ménages', '99' => 'Activités extraterritoriales'];
    return $divs[substr($cleanCode, 0, 2)] ?? "Secteur d'activité ($cleanCode)";
}

function estAlimentaire($codeNaf) {
    if (empty($codeNaf) || strlen($codeNaf) < 2) return 0;
    $division = substr($codeNaf, 0, 2); $groupe = substr($codeNaf, 0, 4);
    if (in_array($division, ['10', '11', '56']) || in_array($groupe, ['46.3', '47.1', '47.2'])) return 1;
    return 0;
}

function determinerSanteJuridique($entreprise) {
    $etat = $entreprise['etat_administratif'] ?? 'A'; 
    if ($etat === 'C' || $etat === 'F') return 'Fermée';
    $siren = $entreprise['siren'] ?? null;
    if (!$siren) return 'Actif';
    $url = 'https://bodacc-datadila.opendatasoft.com/api/records/1.0/search/?dataset=annonces-commerciales&q=' . urlencode($siren) . '&rows=1&sort=dateparution';
    $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0'); curl_setopt($ch, CURLOPT_TIMEOUT, 2); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $reponse = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($httpCode == 200 && $reponse) {
        $donnees = json_decode($reponse, true);
        if (!empty($donnees['records'][0]['fields'])) {
            $famille = strtolower($donnees['records'][0]['fields']['familleavis_lib'] ?? '');
            if (strpos($famille, 'collective') !== false) {
                $txt = strtolower(($donnees['records'][0]['fields']['typeavis_lib'] ?? '') . ' ' . ($donnees['records'][0]['fields']['libelle_avis'] ?? ''));
                if (strpos($txt, 'liquidation') !== false) return 'Liquidation';
                if (strpos($txt, 'redressement') !== false) return 'Redressement';
                if (strpos($txt, 'sauvegarde') !== false) return 'Sauvegarde';
                return 'En difficulté';
            }
        }
    }
    return 'Actif';
}

// =========================================================================
// 5. REQUÊTES AJAX
// =========================================================================

// Nouveauté 1.6.2 : Import du dictionnaire NAF (CSV)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_naf_csv' && isset($_FILES['naf_file'])) {
    header('Content-Type: application/json');
    $file = $_FILES['naf_file']['tmp_name'];
    if (empty($file)) { echo json_encode(['success' => false, 'message' => "Fichier vide."]); exit; }
    
    $handle = fopen($file, "r");
    if ($handle !== FALSE) {
        try {
            $pdo->beginTransaction();
            $pdo->exec("TRUNCATE TABLE codes_naf;"); // Vide la table pour éviter les doublons
            $stmt = $pdo->prepare("INSERT IGNORE INTO codes_naf (code, libelle) VALUES (:code, :libelle)");
            $count = 0;
            
            // Détection du délimiteur (; ou ,)
            $firstLine = fgets($handle);
            $delim = strpos($firstLine, ';') !== false ? ';' : ',';
            rewind($handle);
            
            $header = fgetcsv($handle, 1000, $delim); // Skip header
            while (($data = fgetcsv($handle, 1000, $delim)) !== FALSE) {
                if (isset($data[0]) && isset($data[1])) {
                    $stmt->execute([':code' => trim($data[0]), ':libelle' => trim($data[1])]);
                    $count++;
                }
            }
            fclose($handle);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "$count codes NAF importés avec succès !"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => "Erreur SQL : " . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Erreur de lecture du fichier."]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'highlight_sirens') {
    header('Content-Type: application/json');
    $sirens = json_decode($_POST['sirens'] ?? '[]', true);
    if (!is_array($sirens) || empty($sirens)) {
        echo json_encode(['success' => false, 'message' => 'Aucun SIREN valide fourni.']); exit;
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE api_resultats SET est_connu = 1 WHERE siren = :siren");
        $count = 0;
        foreach ($sirens as $s) {
            $stmt->execute([':siren' => $s]);
            $count += $stmt->rowCount();
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'count' => $count]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_bodacc_details') {
    header('Content-Type: application/json');
    $siren = trim($_POST['siren'] ?? '');
    if (empty($siren)) { echo json_encode(['success' => false, 'message' => 'SIREN manquant.']); exit; }

    $url = 'https://bodacc-datadila.opendatasoft.com/api/records/1.0/search/?dataset=annonces-commerciales&q=' . urlencode($siren) . '&rows=5&sort=dateparution';
    $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0'); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $reponse = curl_exec($ch); curl_close($ch);

    if ($reponse) {
        $donnees = json_decode($reponse, true);
        $records = $donnees['records'] ?? [];
        if (count($records) > 0) {
            $annonces = [];
            foreach ($records as $rec) {
                $f = $rec['fields'] ?? [];
                $annonces[] = [
                    'date' => $f['dateparution'] ?? 'Date inconnue',
                    'famille' => $f['familleavis_lib'] ?? 'Annonce officielle',
                    'type' => $f['typeavis_lib'] ?? '',
                    'tribunal' => $f['tribunal'] ?? 'Tribunal de Commerce',
                    'description' => $f['libelle_avis'] ?? $f['texte'] ?? 'Aucun détail supplémentaire disponible.',
                    'bodacc_url' => 'https://www.bodacc.fr/pages/annonces-commerciales/?q=' . urlencode($siren),
                    'societe_url' => 'https://www.societe.com/cgi-bin/search?champs=' . urlencode($siren)
                ];
            }
            echo json_encode(['success' => true, 'annonces' => $annonces]);
            exit;
        }
    }
    echo json_encode([
        'success' => true, 
        'annonces' => [[
            'date' => date('Y-m-d'),
            'famille' => 'Annonce Légale',
            'type' => 'Situation Régulière',
            'tribunal' => 'Inconnu',
            'description' => 'Aucune procédure collective ou faillite récente enregistrée dans le registre officiel BODACC.',
            'bodacc_url' => 'https://www.bodacc.fr/pages/annonces-commerciales/?q=' . urlencode($siren),
            'societe_url' => 'https://www.societe.com/cgi-bin/search?champs=' . urlencode($siren)
        ]]
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_record') {
    header('Content-Type: application/json');
    try { $pdo->prepare("DELETE FROM sources_csv WHERE id = :id")->execute([':id' => (int)$_POST['id']]); echo json_encode(['success' => true]); } catch (Exception $e) { echo json_encode(['success' => false]); } exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_enrichment') {
    header('Content-Type: application/json');
    $id = (int)$_POST['id']; $montant = (float)$_POST['montant']; $poids = (float)$_POST['poids'];
    try {
        $pdo->prepare("UPDATE sources_csv SET montant = :m, poids = :p WHERE id = :id")->execute([':m' => $montant, ':p' => $poids, ':id' => $id]);
        echo json_encode(['success' => true, 'message' => "Données enrichies avec succès."]);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => "Erreur : " . $e->getMessage()]); }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajout_manuel') {
    header('Content-Type: application/json');
    $nom = trim($_POST['nom_complet'] ?? ''); $siren = trim($_POST['siren'] ?? ''); $naf = trim($_POST['naf'] ?? ''); $adresse = trim($_POST['adresse'] ?? ''); $statut_juridique = trim($_POST['statut_juridique'] ?? 'Actif');
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null; $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null; $distance = !empty($_POST['distance']) ? (float)$_POST['distance'] : null;
    
    if (empty($latitude) || empty($longitude)) { $coords = geocoderAdresseOSM($adresse); if ($coords) { $latitude = $coords['lat']; $longitude = $coords['lon']; } }
    if ($latitude && $longitude && empty($distance)) $distance = calculerDistance($origineLat, $origineLon, $latitude, $longitude);
    if (empty($nom)) { echo json_encode(['success' => false, 'message' => 'Le nom est obligatoire.']); exit; }

    $alim = ($_POST['est_alimentaire'] ?? 'auto' === 'auto') ? estAlimentaire($naf) : (int)$_POST['est_alimentaire'];

    try {
        $pdo->beginTransaction();
        $stmt1 = $pdo->prepare("INSERT INTO sources_csv (nom_recherche, statut) VALUES (:nom, 'valide_manuel')"); $stmt1->execute([':nom' => $nom]); $sourceId = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO api_resultats (source_id, siren, nom_complet, activite_principale, activite_principale_libelle, est_alimentaire, statut_juridique, siege_adresse, latitude, longitude, distance) VALUES (:source_id, :siren, :nom, :naf, :naf_lib, :alim, :statut_j, :adresse, :lat, :lon, :dist)");
        $stmt2->execute([':source_id' => $sourceId, ':siren' => $siren, ':nom' => $nom, ':naf' => $naf, ':naf_lib' => getLibelleNafLocal($naf), ':alim' => $alim, ':statut_j' => $statut_juridique, ':adresse' => $adresse, ':lat' => $latitude, ':lon' => $longitude, ':dist' => $distance]);
        $pdo->prepare("UPDATE sources_csv SET api_result_id_selectionne = :res_id WHERE id = :id")->execute([':res_id' => $pdo->lastInsertId(), ':id' => $sourceId]);
        $pdo->commit(); echo json_encode(['success' => true, 'message' => "Entité '$nom' ajoutée manuellement avec succès !"]);
    } catch (Exception $e) { $pdo->rollBack(); echo json_encode(['success' => false, 'message' => "Erreur : " . $e->getMessage()]); }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_alim') {
    header('Content-Type: application/json');
    $pdo->prepare("UPDATE api_resultats SET est_alimentaire = :st WHERE id = :id")->execute([':st' => (int)$_POST['statut'], ':id' => (int)$_POST['id']]); echo json_encode(['success' => true]); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vider_base') {
    header('Content-Type: application/json');
    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE api_resultats; TRUNCATE TABLE sources_csv; SET FOREIGN_KEY_CHECKS = 1;"); echo json_encode(['success' => true, 'message' => "La base a été entièrement vidée."]); } catch (Exception $e) { echo json_encode(['success' => false]); } exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'hard_reset') {
    header('Content-Type: application/json');
    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS api_resultats; DROP TABLE IF EXISTS sources_csv; SET FOREIGN_KEY_CHECKS = 1;"); if (file_exists($envFile)) unlink($envFile); echo json_encode(['success' => true, 'message' => "Réinitialisation complète !"]); } catch (Exception $e) { echo json_encode(['success' => false]); } exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_line') {
    header('Content-Type: application/json');
    $nomRecherche = trim($_POST['nom_recherche'] ?? '');
    $montant = (float)($_POST['montant'] ?? 0);
    $poids = (float)($_POST['poids'] ?? 0);
    if (empty($nomRecherche)) { echo json_encode(['success' => false]); exit; }

    $stmtInsertSource = $pdo->prepare("INSERT INTO sources_csv (nom_recherche, montant, poids, statut) VALUES (:nom, :mnt, :pds, 'en_attente')");
    $stmtInsertSource->execute([':nom' => $nomRecherche, ':mnt' => $montant, ':pds' => $poids]);
    $sourceId = $pdo->lastInsertId();

    $url = 'https://recherche-entreprises.api.gouv.fr/search?q=' . urlencode($nomRecherche) . '&per_page=5';
    $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0'); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $reponse = curl_exec($ch); curl_close($ch);

    $nouveauStatut = 'introuvable'; $resIdSelect = null;
    if ($reponse) {
        $donneesJSON = json_decode($reponse, true); $resultats = $donneesJSON['results'] ?? [];
        if (count($resultats) > 0) {
            $stmtInsertResult = $pdo->prepare("INSERT INTO api_resultats (source_id, siren, nom_complet, activite_principale, activite_principale_libelle, est_alimentaire, est_ess, est_societe_mission, statut_juridique, siege_adresse, latitude, longitude, distance) VALUES (:source_id, :siren, :nom_complet, :activite_principale, :activite_principale_libelle, :est_alimentaire, :est_ess, :est_mission, :statut_juridique, :siege_adresse, :latitude, :longitude, :distance)");
            foreach ($resultats as $entreprise) {
                $act = (string)($entreprise['activite_principale'] ?? '');
                $lat = $entreprise['siege']['latitude'] ?? null; $lon = $entreprise['siege']['longitude'] ?? null;
                $adresse = trim(($entreprise['siege']['adresse'] ?? ''));
                if (empty($lat) || empty($lon)) { $coords = geocoderAdresseOSM($adresse); if ($coords) { $lat = $coords['lat']; $lon = $coords['lon']; } }

                $dist = ($lat && $lon) ? calculerDistance($origineLat, $origineLon, $lat, $lon) : null;
                $sante = determinerSanteJuridique($entreprise);
                
                $est_ess = ($entreprise['complements']['est_ess'] ?? false) ? 1 : 0;
                $est_mission = ($entreprise['complements']['est_societe_mission'] ?? false) ? 1 : 0;

                $stmtInsertResult->execute([
                    ':source_id' => $sourceId, ':siren' => $entreprise['siren'] ?? null, ':nom_complet' => $entreprise['nom_complet'] ?? null,
                    ':activite_principale' => $act ?: null, ':activite_principale_libelle' => getLibelleNafLocal($act),
                    ':est_alimentaire' => estAlimentaire($act), ':est_ess' => $est_ess, ':est_mission' => $est_mission,
                    ':statut_juridique' => $sante, ':siege_adresse' => $adresse, ':latitude' => $lat, ':longitude' => $lon, ':distance' => $dist
                ]);
                $dernierResultatId = $pdo->lastInsertId();
            }
            $nouveauStatut = (count($resultats) === 1) ? 'valide_auto' : 'en_attente';
            $resIdSelect = (count($resultats) === 1) ? $dernierResultatId : null;
        }
    }
    $pdo->prepare("UPDATE sources_csv SET statut = :statut, api_result_id_selectionne = :res_id WHERE id = :id")->execute([':statut' => $nouveauStatut, ':res_id' => $resIdSelect, ':id' => $sourceId]);
    usleep(300000); echo json_encode(['success' => true, 'statut' => $nouveauStatut]); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajax_search') {
    header('Content-Type: application/json');
    $sourceId = (int)$_POST['source_id']; $nouveauNom = trim($_POST['nouveau_nom'] ?? ''); $apiSource = $_POST['api_source'] ?? 'gouv';
    if (empty($nouveauNom)) { echo json_encode(['success' => false, 'message' => 'Le nom est vide.']); exit; }
    
    $reponse = false; $entreprises_trouvees = [];

    if ($apiSource === 'pappers') {
        if (empty($pappers_key)) { echo json_encode(['success' => false, 'message' => 'Clé API Pappers non configurée. (Voir .env)']); exit; }
        $url = 'https://api.pappers.fr/v2/recherche?q=' . urlencode($nouveauNom) . '&api_token=' . $pappers_key;
        $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0'); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $reponse = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $curlError = curl_error($ch); curl_close($ch);
        if ($reponse === false) { echo json_encode(['success' => false, 'message' => "Erreur réseau Pappers : " . $curlError]); exit; }
        if ($httpCode !== 200) { $errData = json_decode($reponse, true); echo json_encode(['success' => false, 'message' => "Pappers a refusé l'accès : " . ($errData['error'] ?? "HTTP $httpCode")]); exit; }
        if ($reponse) {
            $donneesJSON = json_decode($reponse, true); $raw_results = $donneesJSON['resultats'] ?? [];
            foreach(array_slice($raw_results, 0, 5) as $ent) {
                $entreprises_trouvees[] = [
                    'siren' => $ent['siren'] ?? null, 'nom_complet' => $ent['nom_entreprise'] ?? $ent['denomination'] ?? 'Nom inconnu', 'activite_principale' => $ent['code_naf'] ?? null,
                    'adresse' => trim(($ent['siege']['adresse_ligne_1'] ?? '') . ' ' . ($ent['siege']['code_postal'] ?? '') . ' ' . ($ent['siege']['ville'] ?? '')),
                    'latitude' => $ent['siege']['latitude'] ?? null, 'longitude' => $ent['siege']['longitude'] ?? null, 'etat_administratif' => ($ent['entreprise_cessee'] ?? false) ? 'C' : 'A',
                    'est_ess' => ($ent['entreprise_ess'] ?? false) ? 1 : 0, 'est_mission' => ($ent['societe_a_mission'] ?? false) ? 1 : 0
                ];
            }
        }
    } elseif ($apiSource === 'societe') {
        if (empty($societe_key)) { echo json_encode(['success' => false, 'message' => 'Clé API Societe.com non configurée. (Voir .env)']); exit; }
        $url = 'https://societeinfo.com/app/rest/api/v2/companies.json?name=' . urlencode($nouveauNom) . '&key=' . $societe_key;
        $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0'); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $reponse = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $curlError = curl_error($ch); curl_close($ch);
        if ($reponse === false) { echo json_encode(['success' => false, 'message' => "Erreur réseau Societe.com : " . $curlError]); exit; }
        if ($httpCode !== 200) { echo json_encode(['success' => false, 'message' => "Societe.com a refusé l'accès (Erreur HTTP $httpCode). Vérifiez votre clé."]); exit; }
        if ($reponse) {
            $donneesJSON = json_decode($reponse, true); $raw_results = $donneesJSON['result'] ?? [];
            foreach(array_slice($raw_results, 0, 5) as $ent) {
                $org = $ent['organization'] ?? [];
                $entreprises_trouvees[] = [
                    'siren' => $org['siren'] ?? null, 'nom_complet' => $org['name'] ?? 'Nom inconnu', 'activite_principale' => $org['naf'] ?? null,
                    'adresse' => $org['full_address'] ?? null, 'latitude' => null, 'longitude' => null, 
                    'etat_administratif' => ($org['status'] ?? 'A') === 'radiation' ? 'C' : 'A',
                    'est_ess' => 0, 'est_mission' => 0
                ];
            }
        }
    } else {
        $url = 'https://recherche-entreprises.api.gouv.fr/search?q=' . urlencode($nouveauNom) . '&per_page=5';
        $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, 'EcoTrace/1.0'); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $reponse = curl_exec($ch); $curlError = curl_error($ch); curl_close($ch);
        if ($reponse === false) { echo json_encode(['success' => false, 'message' => "Erreur réseau Gouv.fr : " . $curlError]); exit; }
        if ($reponse) {
            $donneesJSON = json_decode($reponse, true); $raw_results = $donneesJSON['results'] ?? [];
            foreach(array_slice($raw_results, 0, 5) as $ent) {
                $entreprises_trouvees[] = [
                    'siren' => $ent['siren'] ?? null, 'nom_complet' => $ent['nom_complet'] ?? null, 'activite_principale' => $ent['activite_principale'] ?? null,
                    'adresse' => $ent['siege']['adresse'] ?? null, 'latitude' => $ent['siege']['latitude'] ?? null, 'longitude' => $ent['siege']['longitude'] ?? null, 'etat_administratif' => $ent['etat_administratif'] ?? 'A',
                    'est_ess' => ($ent['complements']['est_ess'] ?? false) ? 1 : 0, 'est_mission' => ($ent['complements']['est_societe_mission'] ?? false) ? 1 : 0
                ];
            }
        }
    }

    if (count($entreprises_trouvees) === 0) { 
        $label = $apiSource === 'pappers' ? 'Pappers' : ($apiSource === 'societe' ? 'Societe.com' : 'Gouv.fr');
        echo json_encode(['success' => false, 'message' => "Aucun résultat trouvé via $label."]); 
        exit; 
    }
        
    $pdo->prepare("DELETE FROM api_resultats WHERE source_id = :id")->execute([':id' => $sourceId]);
    $stmtInsertResult = $pdo->prepare("INSERT INTO api_resultats (source_id, siren, nom_complet, activite_principale, activite_principale_libelle, est_alimentaire, est_ess, est_societe_mission, statut_juridique, siege_adresse, latitude, longitude, distance) VALUES (:source_id, :siren, :nom_complet, :activite_principale, :activite_principale_libelle, :est_alimentaire, :est_ess, :est_mission, :statut_juridique, :siege_adresse, :latitude, :longitude, :distance)");
    $dernierResultatId = null;
    
    foreach ($entreprises_trouvees as $entreprise) {
        $act = (string)($entreprise['activite_principale'] ?? ''); $lat = $entreprise['latitude'] ?? null; $lon = $entreprise['longitude'] ?? null; $adresse = $entreprise['adresse'] ?? null;
        if (empty($lat) || empty($lon)) { $coords = geocoderAdresseOSM($adresse); if ($coords) { $lat = $coords['lat']; $lon = $coords['lon']; } }
        $dist = ($lat && $lon) ? calculerDistance($origineLat, $origineLon, $lat, $lon) : null;
        $sante = determinerSanteJuridique(['siren' => $entreprise['siren'], 'etat_administratif' => $entreprise['etat_administratif']]);

        $stmtInsertResult->execute([
            ':source_id' => $sourceId, ':siren' => $entreprise['siren'], ':nom_complet' => $entreprise['nom_complet'],
            ':activite_principale' => $act ?: null, ':activite_principale_libelle' => getLibelleNafLocal($act), ':est_alimentaire' => estAlimentaire($act), 
            ':est_ess' => $entreprise['est_ess'], ':est_mission' => $entreprise['est_mission'], ':statut_juridique' => $sante, 
            ':siege_adresse' => $adresse, ':latitude' => $lat, ':longitude' => $lon, ':distance' => $dist
        ]);
        $dernierResultatId = $pdo->lastInsertId();
    }
    $statut = (count($entreprises_trouvees) === 1) ? 'valide_auto' : 'en_attente'; $resId = (count($entreprises_trouvees) === 1) ? $dernierResultatId : null;
    $pdo->prepare("UPDATE sources_csv SET nom_recherche = :nom, statut = :statut, api_result_id_selectionne = :res_id WHERE id = :id")->execute([':nom' => $nouveauNom, ':statut' => $statut, ':res_id' => $resId, ':id' => $sourceId]);
    
    $label = $apiSource === 'pappers' ? 'Pappers' : ($apiSource === 'societe' ? 'Societe.com' : 'Gouv.fr');
    echo json_encode(['success' => true, 'message' => "Mise à jour réussie via $label."]); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'valider') {
    $pdo->prepare("UPDATE sources_csv SET statut = 'valide_manuel', api_result_id_selectionne = :res_id WHERE id = :src_id")->execute([':res_id' => (int)$_POST['resultat_id'], ':src_id' => (int)$_POST['source_id']]);
}

// =========================================================================
// 6. RÉCUPÉRATION DES DONNÉES (Pour l'interface)
// =========================================================================
$sourcesEnAttente = $pdo->query("SELECT * FROM sources_csv WHERE statut = 'en_attente' ORDER BY id DESC LIMIT 1000")->fetchAll(PDO::FETCH_ASSOC);
$sourcesIntrouvables = $pdo->query("SELECT * FROM sources_csv WHERE statut = 'introuvable' ORDER BY id DESC LIMIT 1000")->fetchAll(PDO::FETCH_ASSOC);
$sourcesValides = $pdo->query("
    SELECT s.id as source_id, s.nom_recherche, s.statut, s.date_import, s.montant, s.poids,
           r.id as resultat_id, r.siren, r.nom_complet, r.siege_adresse, r.latitude, r.longitude, r.distance, 
           r.activite_principale, r.activite_principale_libelle, r.est_alimentaire, r.est_ess, r.est_societe_mission, r.est_connu, r.statut_juridique
    FROM sources_csv s INNER JOIN api_resultats r ON s.api_result_id_selectionne = r.id
    WHERE s.statut IN ('valide_auto', 'valide_manuel') ORDER BY s.id DESC LIMIT 1000
")->fetchAll(PDO::FETCH_ASSOC);

$statsSecteurs = []; $statsSante = ['Actif' => 0, 'En difficulté' => 0, 'Fermée' => 0]; $totalCO2 = 0; $totalFournisseurs = count($sourcesValides); $totalESSMission = 0; $mapMarkers = [];

foreach ($sourcesValides as $v) {
    $secteur = $v['activite_principale_libelle'] ?: 'Inconnu';
    if (!isset($statsSecteurs[$secteur])) $statsSecteurs[$secteur] = 0;
    $statsSecteurs[$secteur]++;
    
    $sJ = $v['statut_juridique'];
    if (strpos($sJ, 'Fermée') !== false || strpos($sJ, 'Liquidation') !== false) { $statsSante['Fermée']++; }
    elseif ($sJ !== 'Actif') { $statsSante['En difficulté']++; }
    else { $statsSante['Actif']++; }
    
    if ($v['est_ess'] || $v['est_societe_mission']) $totalESSMission++;
    $totalCO2 += estimerCO2($v['activite_principale'], $v['montant'], $v['poids'], $v['distance']);
    if ($v['latitude'] && $v['longitude']) { $mapMarkers[] = ['lat' => $v['latitude'], 'lon' => $v['longitude'], 'nom' => htmlspecialchars(addslashes($v['nom_complet'])), 'alim' => $v['est_alimentaire']]; }
}
arsort($statsSecteurs); $topSecteurs = array_slice($statsSecteurs, 0, 5, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>EcoTrace 🍃 - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Nunito', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1250px; margin: auto; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-actions { display: flex; gap: 10px; align-items: center; }
        .header-title { display: flex; flex-direction: column; align-items: flex-start; }
        h1 { font-family: 'Fredoka', sans-serif; color: #27ae60; margin: 0; font-size: 32px; line-height: 1; }
        .app-version-main { font-size: 14px; color: #7f8c8d; font-weight: bold; margin-top: 2px; }

        @keyframes blinker { 50% { opacity: 0; } }
        .update-badge { display: none; color: #e74c3c; font-weight: bold; font-size: 12px; margin-top: 5px; cursor: pointer; animation: blinker 1.5s linear infinite; background: #fadbd8; padding: 2px 8px; border-radius: 12px; }
        .update-badge:hover { background: #f5b7b1; }

        #scrollTopBtn { display: none; position: fixed; bottom: 30px; right: 30px; z-index: 99; font-size: 22px; border: none; outline: none; background-color: #27ae60; color: white; cursor: pointer; padding: 10px; border-radius: 50%; width: 50px; height: 50px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: background-color 0.3s, transform 0.3s; font-family: 'Nunito', sans-serif; }
        #scrollTopBtn:hover { background-color: #219150; transform: scale(1.1); }
        
        .dropdown { position: relative; display: inline-block; }
        .dropbtn { background-color: #34495e; color: white; padding: 10px 15px; font-size: 14px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-family: 'Nunito', sans-serif;}
        .dropbtn:hover { background-color: #2c3e50; }
        .dropdown-content { display: none; position: absolute; right: 0; background-color: #ffffff; min-width: 260px; white-space: nowrap; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1000; border-radius: 4px; overflow: hidden; }
        .dropdown-content a, .dropdown-content button { color: #333; padding: 12px 16px; text-decoration: none; display: block; width: 100%; text-align: left; border: none; background: none; cursor: pointer; font-size: 14px; border-bottom: 1px solid #eee; box-sizing: border-box; font-family: 'Nunito', sans-serif;}
        .dropdown-content button:last-child { border-bottom: none; }
        .dropdown-content a:hover, .dropdown-content button:hover { background-color: #f1f1f1; }
        .dropdown:hover .dropdown-content { display: block; }
        
        .tab-container { margin-bottom: 20px; border-bottom: 2px solid #ddd; display: flex; gap: 10px; }
        .tab-button { font-family: 'Nunito', sans-serif; background: #eee; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; border-radius: 5px 5px 0 0; color: #555; }
        .tab-button.active { background: #27ae60; color: white; font-weight: bold; }
        .tab-button:hover:not(.active) { background: #e0e0e0; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .source-title { color: #2c3e50; margin-top: 0; margin-bottom: 10px; }
        .search-box { background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #f39c12; }
        
        .result-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; table-layout: fixed; }
        .result-table th, .result-table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; word-wrap: break-word; vertical-align: top; }
        .result-table th { background-color: #f9f9f9; }
        .col-details { width: 33%; } .col-rse { width: 14%; text-align: center; } .col-co2 { width: 15%; text-align: center; } .col-dist { width: 10%; text-align: center; } .col-map { width: 8%; text-align: center; } .col-action { width: 20%; text-align: center; }
        
        .btn { font-family: 'Nunito', sans-serif; background: #2ecc71; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;} .btn:hover { background: #27ae60; }
        .btn-warning { background: #f39c12; } .btn-warning:hover { background: #e67e22; }
        .btn-blue { background: #3498db; } .btn-blue:hover { background: #2980b9; }
        .btn-green { background: #2ecc71; } .btn-green:hover { background: #27ae60; }
        .btn-purple { background: #9b59b6; } .btn-purple:hover { background: #8e44ad; }
        .btn-red { background: #e74c3c; } .btn-red:hover { background: #c0392b; }
        .btn-dark { background: #34495e; color: white; } .btn-dark:hover { background: #2c3e50; }
        .btn-logout { background: #95a5a6; } .btn-logout:hover { background: #7f8c8d; }
        .btn-small { padding: 5px 10px; font-size: 13px; }
        
        .alert { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .badge { display: inline-block; padding: 3px 8px; color: white; border-radius: 12px; font-size: 12px; margin-left: 5px; }
        .badge-red { background: #e74c3c; } .badge-green { background: #2ecc71; } .badge-blue { background: #3498db; } .badge-orange { background: #f39c12; }
        
        .input-text { font-family: 'Nunito', sans-serif; padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 250px; }
        .ajax-feedback { font-size: 13px; margin-left: 10px; font-weight: bold; }
        .status-auto { color: #3498db; font-weight: bold; } .status-manual { color: #27ae60; font-weight: bold; }
        
        .label-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; display: inline-block; margin-bottom: 4px; width: 80%; }
        .label-alim { background: #f39c12; cursor: pointer; } .label-no-alim { background: #ecf0f1; color: #7f8c8d; cursor: pointer; }
        .label-ess { background: #9b59b6; } .label-mission { background: #1abc9c; }
        
        .sante-badge { padding: 3px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; display: inline-block; cursor: pointer; transition: transform 0.1s; }
        .sante-badge:hover { transform: scale(1.08); }
        .sante-actif { background: #2ecc71; } .sante-ferme { background: #e74c3c; } .sante-difficulte { background: #f39c12; }

        .text-muted { color: #666; font-size: 0.9em; display: block; margin-top: 3px; line-height: 1.4; }
        .text-muted-inline { color: #666; font-size: 0.9em; }
        
        .map-thumbnail { background: url('https://a.tile.openstreetmap.org/13/4164/2815.png') center/cover; width: 100%; max-width: 60px; height: 40px; margin: auto; border-radius: 4px; border: 1px solid #ccc; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; color: white; text-shadow: 1px 1px 2px black; font-weight: bold; transition: transform 0.2s; }
        .map-thumbnail:hover { transform: scale(1.1); border-color: #3498db; z-index: 10; position: relative;}
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .close-modal { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1; }
        .close-modal:hover { color: #333; }
        
        /* Highlight SIREN matching persisté */
        tr.highlight-siren > td { background-color: #d1f2eb !important; border-top: 2px solid #1abc9c; border-bottom: 2px solid #1abc9c; }
        
        /* Formulaires alignés (Enrichissement & Ajout Manuel) */
        #form-enrich .form-group, #form-ajout-manuel .form-group { display: flex; align-items: center; margin-bottom: 15px; }
        #form-enrich .form-group label, #form-ajout-manuel .form-group label { width: 190px; margin-bottom: 0; flex-shrink: 0; text-align: left; line-height: 1.2; padding-right: 10px; }
        #form-enrich .form-group input, #form-ajout-manuel .form-group input, #form-ajout-manuel .form-group select { flex: 1; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: 'Nunito', sans-serif; }

        .kpi-container { display: flex; gap: 20px; margin-bottom: 20px; }
        .kpi-box { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-bottom: 4px solid #27ae60; }
        .kpi-box h3 { margin: 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .kpi-box p { margin: 10px 0 0 0; font-size: 28px; font-weight: bold; color: #2c3e50; font-family: 'Fredoka', sans-serif; }
        .charts-container { display: flex; gap: 20px; margin-bottom: 20px; }
        .chart-box { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .global-map-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); height: 400px; }
        
        .footer { text-align: center; padding: 30px 20px; margin-top: 40px; background-color: #fff; border-top: 2px solid #27ae60; color: #555; border-radius: 8px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
        .footer p { margin: 0 0 15px 0; font-weight: bold; color: #2c3e50; font-size: 16px; }
        .footer-logos { display: flex; justify-content: center; align-items: center; gap: 30px; flex-wrap: wrap; }
        .footer-logos img { height: 35px; width: 35px; object-fit: contain; opacity: 0.6; filter: grayscale(100%); transition: all 0.3s ease; cursor: pointer; }
        .footer-logos img:hover { opacity: 1; filter: grayscale(0%); transform: scale(1.15); }
        
        .bodacc-card { background: #f8f9fa; border-left: 4px solid #3498db; padding: 12px 15px; margin-bottom: 12px; border-radius: 0 4px 4px 0; }
        .bodacc-card h4 { margin: 0 0 5px 0; color: #2c3e50; }
        .bodacc-card p { margin: 0; font-size: 13px; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-top">
            <div class="header-title">
                <h1>EcoTrace 🍃</h1>
                <div class="app-version-main">v1.6.2</div>
                <div id="update-badge" class="update-badge" onclick="verifierMiseAJour()">⚠️ Mise à jour disponible !</div>
            </div>
            <div class="header-actions">
                <input type="file" id="csv-upload" accept=".csv" style="display: none;" onchange="demarrerImportCSV(event)">
                <input type="file" id="sql-upload" accept=".sql" style="display: none;" onchange="demarrerImportSQL(event)">
                <input type="file" id="siren-highlight-upload" accept=".csv" style="display: none;" onchange="highlightSirenFromCSV(event)">
                <input type="file" id="naf-upload" accept=".csv" style="display: none;" onchange="demarrerImportNAF(event)">
                
                <div class="dropdown">
                    <button class="dropbtn">⚙️ Actions ▾</button>
                    <div class="dropdown-content">
                        <!-- Liens d'information en Iframe -->
                        <button onclick="ouvrirIframeModal('doc.html')">📖 Documentation</button>
                        <button onclick="ouvrirIframeModal('licence.html')">📜 Licence</button>
                        <button onclick="ouvrirIframeModal('presentation.html')">📽️ Présentation</button>
                        <button onclick="ouvrirIframeModal('historique.html')">🕒 Historique</button>
                        
                        <!-- Base de données -->
                        <button onclick="document.getElementById('sql-upload').click()" style="border-top: 1px solid #ddd;">📂 Importer Dump (SQL)</button>
                        <a href="?export=sql">💾 Exporter Dump (SQL)</a>
                        <button onclick="document.getElementById('naf-upload').click()" title="Format attendu : code, libelle">📚 Importer Dictionnaire NAF (CSV)</button>
                        
                        <!-- Export / Import CSV -->
                        <button onclick="document.getElementById('csv-upload').click()" style="border-top: 1px solid #ddd;" title="Format : Nom, Montant(€), Poids(kg)">📁 Importer CSV enrichi</button>
                        <a href="?export=csv">📥 Exporter CSV final</a>
                        <button onclick="document.getElementById('siren-highlight-upload').click()" style="color: #1abc9c; font-weight: bold;" title="Mettre en évidence et sauvegarder les fournisseurs connus">✨ Surligner via SIREN (CSV)</button>

                        <!-- Maintenance et Mises à jour -->
                        <button onclick="verifierMiseAJour()" style="border-top: 1px solid #ddd; color: #27ae60; font-weight: bold;">🔄 Vérifier mise à jour</button>
                        
                        <button onclick="lancerMajAjax('update_gps', this)" style="border-top: 1px solid #ddd;">📍 Maj GPS</button>
                        <button onclick="lancerMajAjax('update_naf', this)">📚 Maj NAF</button>
                        <button onclick="lancerMajAjax('update_alimentaire', this)">🔄 Maj Alim.</button>
                        <button onclick="lancerMajAjax('update_sante', this)">🏥 Maj Santé</button>
                        
                        <button style="color: #e67e22; font-weight: bold; border-top: 1px solid #ddd;" onclick="viderBase()">🧹 Vider la base (Tables)</button>
                        <button style="color: #e74c3c; font-weight: bold;" onclick="reinstallationComplete()">🔥 Réinstallation complète</button>
                        <a href="?edit_config=1" style="color: #3498db; font-weight: bold;">⚙️ Éditer la configuration</a>
                        <button style="background: #fdfdfd; border-top: 1px solid #ddd;" onclick="window.location.href='?logout=1'">🚪 Déconnexion</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="import-progress" style="display: none; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; border-left: 5px solid #27ae60;">
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; color: #2c3e50;">
                <span id="import-status">Initialisation de l'import...</span>
                <span id="import-percent">0%</span>
            </div>
            <div style="width: 100%; background-color: #eee; border-radius: 4px; overflow: hidden; margin-top: 10px;">
                <div id="import-bar-fill" style="width: 0%; height: 20px; background-color: #27ae60; transition: width 0.2s;"></div>
            </div>
        </div>

        <div class="tab-container">
            <button class="tab-button active" onclick="openTab(null, 'tab-matching')">Rapprochement <span id="badge-matching" class="badge badge-blue"><?= count($sourcesEnAttente) ?></span></button>
            <button class="tab-button" onclick="openTab(null, 'tab-introuvables')">Introuvables <span id="badge-introuvables" class="badge badge-red"><?= count($sourcesIntrouvables) ?></span></button>
            <button class="tab-button" onclick="openTab(null, 'tab-rapprochees')">Validées <span id="badge-rapprochees" class="badge badge-green"><?= count($sourcesValides) ?></span></button>
            <button class="tab-button" onclick="openTab(null, 'tab-stats')" onclick="setTimeout(()=>mapGlobal.invalidateSize(),200)">📊 Statistiques RSE</button>
            <button class="tab-button" onclick="openTab(null, 'tab-ajout')">➕ Ajout Manuel</button>
        </div>

        <!-- ONGLET 1 : RAPPROCHEMENT -->
        <div id="tab-matching" class="tab-content active">
            <?php if (count($sourcesEnAttente) === 0): ?>
                <div class="card"><p>✅ Plus aucune entreprise en attente de validation.</p></div>
            <?php else: ?>
                <input type="text" id="filterMatchingInput" onkeyup="filtrerRapprochement()" placeholder="🔍 Filtrer les entités en attente (par nom...)" class="input-text" style="width:100%; margin-bottom:20px; font-size:15px; border-color:#27ae60; border-width:2px;">
                <?php foreach ($sourcesEnAttente as $source): ?>
                    <div class="card card-matching" id="card-<?= $source['id'] ?>">
                        <h2 class="source-title">Recherche : <strong><?= htmlspecialchars($source['nom_recherche'] ?? '') ?></strong></h2>
                        <div class="search-box" id="action-cell-<?= $source['id'] ?>">
                            <input type="text" id="input-nom-<?= $source['id'] ?>" value="<?= htmlspecialchars($source['nom_recherche'] ?? '') ?>" class="input-text" style="margin-bottom: 5px;">
                            <br><button class="btn btn-blue btn-small" onclick="lancerRechercheAjax(<?= $source['id'] ?>, true, 'gouv')">🔍 Gouv.fr</button>
                            <button class="btn btn-purple btn-small" onclick="lancerRechercheAjax(<?= $source['id'] ?>, true, 'pappers')">📄 Pappers</button>
                            <button class="btn btn-dark btn-small" onclick="lancerRechercheAjax(<?= $source['id'] ?>, true, 'societe')">🏢 Societe.com</button>
                            <button class="btn btn-red btn-small" onclick="supprimerEnregistrement(<?= $source['id'] ?>)" title="Supprimer cet enregistrement">🗑️</button>
                            <div id="feedback-<?= $source['id'] ?>" class="ajax-feedback" style="display:block; margin-top:5px;"></div>
                        </div>
                        <?php
                        $stmtCandidats = $pdo->prepare("SELECT * FROM api_resultats WHERE source_id = :id");
                        $stmtCandidats->execute([':id' => $source['id']]);
                        $candidats = $stmtCandidats->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th class="col-details">Candidat (Détails)</th>
                                    <th class="col-rse">Labels RSE</th>
                                    <th class="col-co2">CO2 Est.</th>
                                    <th class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidats as $candidat): ?>
                                    <?php $highlightClass = !empty($candidat['est_connu']) ? 'highlight-siren' : ''; ?>
                                    <tr data-siren="<?= htmlspecialchars($candidat['siren'] ?? '') ?>" class="<?= $highlightClass ?>">
                                        <td>
                                            <?php $sJ = $candidat['statut_juridique'] ?? 'Actif'; $sClass = ($sJ === 'Fermée' || strpos($sJ, 'Liquidation') !== false) ? 'sante-ferme' : (($sJ !== 'Actif') ? 'sante-difficulte' : 'sante-actif'); ?>
                                            <strong><?= htmlspecialchars($candidat['nom_complet'] ?? '') ?></strong>
                                            <span class="sante-badge <?= $sClass ?>" style="margin-left: 8px;" onclick="voirAnnonceSante('<?= $candidat['siren'] ?>', '<?= htmlspecialchars(addslashes($candidat['nom_complet'] ?? '')) ?>')" title="Cliquer pour voir les détails juridiques officiels"><?= htmlspecialchars($sJ) ?> 🔍</span><br>
                                            <span class="text-muted" style="margin-top: 5px;">SIREN : <?= htmlspecialchars($candidat['siren'] ?? '') ?><br>📍 <?= htmlspecialchars($candidat['siege_adresse'] ?? '') ?></span>
                                            <div style="margin-top: 6px;"><strong><?= htmlspecialchars($candidat['activite_principale'] ?? '') ?></strong> <span class="text-muted-inline">- <?= htmlspecialchars($candidat['activite_principale_libelle'] ?? '') ?></span></div>
                                        </td>
                                        <td style="text-align: center;" id="td-alim-<?= $candidat['id'] ?>">
                                            <?php $nouveauSt = $candidat['est_alimentaire'] ? 0 : 1; ?>
                                            <?php if ($candidat['est_alimentaire']): ?>
                                                <div class="label-badge label-alim" onclick="toggleAlim(<?= $candidat['id'] ?>, <?= $nouveauSt ?>)">🍽️ Alimentaire</div>
                                            <?php else: ?>
                                                <div class="label-badge label-no-alim" onclick="toggleAlim(<?= $candidat['id'] ?>, <?= $nouveauSt ?>)">NON Alim.</div>
                                            <?php endif; ?>
                                            <?php if ($candidat['est_ess']): ?><div class="label-badge label-ess" title="Économie Sociale et Solidaire">🤝 ESS</div><?php endif; ?>
                                            <?php if ($candidat['est_societe_mission']): ?><div class="label-badge label-mission">🎯 Mission</div><?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <strong style="color:#d35400;"><?= estimerCO2($candidat['activite_principale'], $source['montant'], $source['poids'], $candidat['distance']) ?> kg</strong>
                                            <span class="text-muted"><?= htmlspecialchars($candidat['distance'] ?? '') ?> km</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <form method="POST" style="margin:0;"><input type="hidden" name="action" value="valider"><input type="hidden" name="source_id" value="<?= $source['id'] ?>"><input type="hidden" name="resultat_id" value="<?= $candidat['id'] ?>"><button type="submit" class="btn">Choisir</button></form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ONGLET 2 : INTROUVABLES -->
        <div id="tab-introuvables" class="tab-content">
            <div class="card">
                <h2 class="source-title">Recherches sans résultats API</h2>
                <?php if (count($sourcesIntrouvables) === 0): ?>
                    <p>✅ Aucun enregistrement introuvable.</p>
                <?php else: ?>
                    <table class="result-table">
                        <thead><tr><th>ID</th><th>Nom recherché (CSV)</th><th>Action possible</th></tr></thead>
                        <tbody>
                            <?php foreach ($sourcesIntrouvables as $introuvable): ?>
                                <tr id="row-<?= $introuvable['id'] ?>">
                                    <td>#<?= htmlspecialchars($introuvable['id'] ?? '') ?></td>
                                    <td><strong><?= htmlspecialchars($introuvable['nom_recherche'] ?? '') ?></strong></td>
                                    <td id="action-cell-<?= $introuvable['id'] ?>">
                                        <button class="btn btn-warning btn-small" onclick="afficherFormulaire(<?= $introuvable['id'] ?>, '<?= htmlspecialchars(addslashes($introuvable['nom_recherche'] ?? '')) ?>')">Gérer</button>
                                        <button class="btn btn-red btn-small" onclick="supprimerEnregistrement(<?= $introuvable['id'] ?>)" title="Supprimer cet enregistrement">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ONGLET 3 : RAPPROCHÉES (VALIDÉES) -->
        <div id="tab-rapprochees" class="tab-content">
            <div class="card">
                <h2 class="source-title">Entités rapprochées (Historique)</h2>
                <?php if (count($sourcesValides) === 0): ?>
                    <p>Aucune entité n'a encore été rapprochée.</p>
                <?php else: ?>
                    <input type="text" id="filterValideesInput" onkeyup="filtrerValidees()" placeholder="🔍 Filtrer les entités validées (par nom, SIREN, ville, NAF...)" class="input-text" style="width:100%; margin-bottom:20px; font-size:15px; border-color:#27ae60; border-width:2px;">
                    <table class="result-table" id="table-validees">
                        <thead>
                            <tr>
                                <th class="col-details">Entreprise Validée</th>
                                <th class="col-rse">Labels RSE</th>
                                <th class="col-co2">Empreinte CO2</th>
                                <th class="col-map">Carte</th>
                                <th class="col-action">Mode / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sourcesValides as $valide): ?>
                                <?php $highlightClass = !empty($valide['est_connu']) ? 'highlight-siren' : ''; ?>
                                <tr class="<?= $highlightClass ?>">
                                    <td>
                                        <div style="margin-bottom: 6px;"><span class="text-muted-inline" style="color: #e67e22;">🔍 Recherche CSV : <strong><?= htmlspecialchars($valide['nom_recherche'] ?? '') ?></strong></span></div>
                                        <?php $sJ = $valide['statut_juridique'] ?? 'Actif'; $sClass = ($sJ === 'Fermée' || strpos($sJ, 'Liquidation') !== false) ? 'sante-ferme' : (($sJ !== 'Actif') ? 'sante-difficulte' : 'sante-actif'); ?>
                                        <strong><?= htmlspecialchars($valide['nom_complet'] ?? '') ?></strong>
                                        <span class="sante-badge <?= $sClass ?>" style="margin-left: 8px;" onclick="voirAnnonceSante('<?= $valide['siren'] ?>', '<?= htmlspecialchars(addslashes($valide['nom_complet'] ?? '')) ?>')" title="Cliquer pour voir les détails juridiques officiels"><?= htmlspecialchars($sJ) ?> 🔍</span><br>
                                        <span class="text-muted" style="margin-top: 5px;">SIREN : <?= htmlspecialchars($valide['siren'] ?? '') ?><br>📍 <?= htmlspecialchars($valide['siege_adresse'] ?? '') ?></span>
                                        <div style="margin-top: 6px;"><strong><?= htmlspecialchars($valide['activite_principale'] ?? '') ?></strong> <span class="text-muted-inline">- <?= htmlspecialchars($valide['activite_principale_libelle'] ?? '') ?></span></div>
                                    </td>
                                    <td style="text-align: center;" id="td-alim-<?= $valide['resultat_id'] ?>">
                                        <?php $nouveauSt = $valide['est_alimentaire'] ? 0 : 1; ?>
                                        <?php if ($valide['est_alimentaire']): ?>
                                            <div class="label-badge label-alim" onclick="toggleAlim(<?= $valide['resultat_id'] ?>, <?= $nouveauSt ?>)">🍽️ Alimentaire</div>
                                        <?php else: ?>
                                            <div class="label-badge label-no-alim" onclick="toggleAlim(<?= $valide['resultat_id'] ?>, <?= $nouveauSt ?>)">NON Alim.</div>
                                        <?php endif; ?>
                                        <?php if ($valide['est_ess']): ?><div class="label-badge label-ess" title="Économie Sociale et Solidaire">🤝 ESS</div><?php endif; ?>
                                        <?php if ($valide['est_societe_mission']): ?><div class="label-badge label-mission">🎯 Mission</div><?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong style="color:#d35400; font-size:16px;"><?= estimerCO2($valide['activite_principale'], $valide['montant'], $valide['poids'], $valide['distance']) ?> kg</strong>
                                        <span class="text-muted">Dist : <?= htmlspecialchars($valide['distance'] ?? '') ?> km</span>
                                        <?php if($valide['montant']>0) echo "<span class='text-muted' style='font-size:10px;'>Achat: {$valide['montant']} €</span>"; ?>
                                        <?php if($valide['poids']>0) echo "<span class='text-muted' style='font-size:10px;'>Poids: {$valide['poids']} kg</span>"; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($valide['latitude']) && !empty($valide['longitude'])): ?>
                                            <div class="map-thumbnail" onclick="ouvrirCarte(<?= $valide['latitude'] ?>, <?= $valide['longitude'] ?>, '<?= htmlspecialchars(addslashes($valide['nom_complet'] ?? '')) ?>')" title="Voir l'itinéraire">🗺️</div>
                                        <?php else: ?><span class="text-muted" style="font-size: 0.8em;">N/A</span><?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="<?= $valide['statut'] === 'valide_auto' ? 'status-auto' : 'status-manual' ?>" style="display:block; margin-bottom:8px;"><?= $valide['statut'] === 'valide_auto' ? 'Automatique' : 'Manuel' ?></span>
                                        <button class="btn btn-blue btn-small" style="margin-bottom: 5px; width: 100%;" onclick="ouvrirEnrichissement(<?= $valide['source_id'] ?>, <?= $valide['montant'] ?>, <?= $valide['poids'] ?>, '<?= htmlspecialchars(addslashes($valide['nom_complet'] ?? '')) ?>')">✏️ Enrichir</button><br>
                                        <button class="btn btn-red btn-small" style="width: 100%;" onclick="supprimerEnregistrement(<?= $valide['source_id'] ?>)">🗑️ Supprimer</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ONGLET 4 : STATISTIQUES RSE (DASHBOARD) -->
        <div id="tab-stats" class="tab-content">
            <h2 class="source-title" style="margin-bottom: 20px;">Tableau de Bord RSE (Scope 3)</h2>
            <div class="kpi-container">
                <div class="kpi-box"><h3>🌍 Empreinte Globale Est.</h3><p style="color:#d35400;"><?= number_format($totalCO2, 2, ',', ' ') ?> kg</p></div>
                <div class="kpi-box"><h3>🏢 Fournisseurs Validés</h3><p><?= $totalFournisseurs ?></p></div>
                <div class="kpi-box" style="border-bottom-color:#9b59b6;"><h3>🤝 Acteurs Engagés (ESS/Mission)</h3><p style="color:#9b59b6;"><?= $totalESSMission ?></p></div>
                <div class="kpi-box" style="border-bottom-color:#e74c3c;"><h3>⚠️ À Risque (En difficulté/Fermée)</h3><p style="color:#e74c3c;"><?= $statsSante['En difficulté'] + $statsSante['Fermée'] ?></p></div>
            </div>
            <div class="charts-container">
                <div class="chart-box"><h3 style="text-align: center; margin-top: 0; color: #2c3e50;">Répartition par Secteur (Top 5)</h3><canvas id="chartSecteurs"></canvas></div>
                <div class="chart-box"><h3 style="text-align: center; margin-top: 0; color: #2c3e50;">Santé Financière de la chaîne</h3><canvas id="chartSante"></canvas></div>
            </div>
            <div class="global-map-box" id="globalMap" style="margin-bottom: 20px;"></div>
        </div>

        <!-- ONGLET 5 : AJOUT MANUEL -->
        <div id="tab-ajout" class="tab-content">
            <div class="card" style="max-width: 600px; margin: auto;">
                <h2 class="source-title">Insérer une entité manuellement</h2>
                <div id="am-step-1" class="step-container">
                    <p class="text-muted">Vérifiez d'abord si l'entreprise existe dans la base officielle, ou passez directement à la saisie manuelle.</p>
                    <div class="form-group"><label style="font-weight: bold;">Nom de l'entreprise *</label><input type="text" id="am_nom_recherche" class="input-text" style="width: 100%; box-sizing: border-box; margin-top: 5px;" placeholder="Ex: Boulangerie Dupont"></div>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button type="button" class="btn btn-blue" style="flex: 1;" onclick="verifierApiAvantAjout()">🔍 Vérifier API Gouv.fr</button>
                        <button type="button" class="btn btn-dark" style="flex: 1;" onclick="forcerSaisieManuelle()">✏️ Saisie manuelle directe</button>
                    </div>
                    <div id="am-step1-feedback" class="ajax-feedback" style="margin-top: 15px; font-size: 14px; text-align: center; display: block;"></div>
                </div>
                <div id="am-step-2" class="step-container" style="display: none; margin-top: 20px; border-top: 3px solid #f39c12; padding-top: 20px;">
                    <p id="titre-saisie-manuelle" class="text-muted" style="color: #e67e22; font-weight: bold; margin-bottom: 20px;">Veuillez renseigner les informations de l'entreprise.</p>
                    <form id="form-ajout-manuel" onsubmit="soumettreAjoutManuel(event)">
                        <input type="hidden" id="am_nom_final" name="nom_complet">
                        <div class="form-group"><label>SIREN (Optionnel)</label><input type="text" name="siren" maxlength="9"></div>
                        <div class="form-group"><label>Code NAF (ex: 56.10A)</label><input type="text" name="naf"></div>
                        <div class="form-group"><label>Activité alimentaire ?</label><select name="est_alimentaire"><option value="auto">Auto</option><option value="1">Oui</option><option value="0">Non</option></select></div>
                        <div class="form-group"><label>Statut Juridique</label><select name="statut_juridique"><option value="Actif">Actif</option><option value="En difficulté">En difficulté</option><option value="Fermée">Fermée</option></select></div>
                        <div class="form-group"><label>Adresse complète</label><input type="text" name="adresse"></div>
                        <div class="form-group"><label>Latitude GPS</label><input type="number" name="latitude" step="0.00000001"></div>
                        <div class="form-group"><label>Longitude GPS</label><input type="number" name="longitude" step="0.00000001"></div>
                        <div class="form-group"><label>Distance (km)<br><span style="font-weight:normal; color:#888; font-size:11px;">(Auto si Lat/Lon renseignés)</span></label><input type="number" name="distance" step="0.01"></div>
                        
                        <div id="am-feedback" class="ajax-feedback" style="margin-bottom: 15px; display: block;"></div>
                        <button type="submit" class="btn btn-warning" style="width: 100%; padding: 12px; font-size: 16px;">💾 Forcer l'enregistrement manuel</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- PIED DE PAGE -->
        <div class="footer">
            <p>EcoTrace remercie la communauté open-source ❤️</p>
            <div class="footer-logos">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" alt="PHP" title="PHP">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/mysql/mysql-original-wordmark.svg" alt="MySQL" title="MySQL">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/docker/docker-original-wordmark.svg" alt="Docker" title="Docker">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/apache/apache-original-wordmark.svg" alt="Apache" title="Apache">
                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/git/git-original.svg" alt="Git" title="Git">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b0/Openstreetmap_logo.svg" alt="OpenStreetMap" title="OpenStreetMap">
            </div>
        </div>
    </div>

    <button id="scrollTopBtn" onclick="scrollToTop()">↑</button>

    <!-- FENÊTRE MODALE CARTE -->
    <div id="routeModal" class="modal">
        <div class="modal-content"><span class="close-modal" onclick="fermerCarte()">&times;</span><h2 id="modalTitle" style="color: #2c3e50; margin-top: 0;">Itinéraire</h2><div id="mapView" style="height: 400px; width: 100%; border-radius: 8px; background: #eee;"></div></div>
    </div>

    <!-- FENÊTRE MODALE ENRICHISSEMENT -->
    <div id="enrichModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <span class="close-modal" onclick="fermerEnrichissement()">&times;</span>
            <h2 style="color: #2c3e50; margin-top: 0;">Enrichir les données</h2>
            <p id="enrich-nom" style="font-weight: bold; color: #27ae60; margin-bottom: 20px;"></p>
            <form id="form-enrich" onsubmit="soumettreEnrichissement(event)">
                <input type="hidden" id="enrich_id" name="id">
                <div class="form-group"><label>Montant des achats (€)</label><input type="number" id="enrich_montant" name="montant" step="0.01" min="0"></div>
                <div class="form-group"><label>Poids du fret (kg)</label><input type="number" id="enrich_poids" name="poids" step="0.01" min="0"></div>
                <button type="submit" class="btn btn-green" style="width: 100%; padding: 10px; font-size: 16px;">💾 Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- FENÊTRE MODALE SANTE / BODACC -->
    <div id="bodaccModal" class="modal">
        <div class="modal-content" style="max-width: 650px;">
            <span class="close-modal" onclick="fermerBodacc()">&times;</span>
            <h2 id="bodacc-title" style="color: #2c3e50; margin-top: 0;">Annonces Légales & Analyse du Risque</h2>
            <p id="bodacc-subtitle" style="font-weight: bold; color: #27ae60; margin-bottom: 15px;"></p>
            <div id="bodacc-content" style="max-height: 400px; overflow-y: auto;">
                <p>Chargement des données officielles...</p>
            </div>
        </div>
    </div>

    <!-- FENÊTRE MODALE AUTO-UPDATER GITHUB -->
    <div id="updateModal" class="modal">
        <div class="modal-content" style="max-width: 450px; text-align: center;">
            <span class="close-modal" onclick="document.getElementById('updateModal').style.display='none'">&times;</span>
            <h2 style="color: #27ae60; margin-top: 0;">Mise à jour GitHub</h2>
            <div id="update-content" style="margin: 20px 0; font-size: 15px;">Vérification en cours... ⏳</div>
            <button id="btn-do-update" class="btn btn-green" style="display: none; width: 100%; padding: 10px; font-size: 16px;" onclick="lancerMiseAJour()">⬇️ Télécharger et Installer</button>
        </div>
    </div>

    <!-- FENÊTRE MODALE IFRAME (Doc, Historique, etc.) -->
    <div id="iframeModal" class="modal">
        <div class="modal-content" style="width: 90%; max-width: 1000px; height: 85vh; display: flex; flex-direction: column; padding: 20px; padding-top: 10px;">
            <div style="text-align: right; margin-bottom: 5px;">
                <span class="close-modal" style="float: none;" onclick="fermerIframeModal()">&times;</span>
            </div>
            <div style="flex: 1; min-height: 0; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                <iframe id="iframeContent" src="" style="width: 100%; height: 100%; border: none; display: block;"></iframe>
            </div>
        </div>
    </div>

    <script>
        // Sauvegarde du scroll avant chaque rechargement
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPositionEcotrace', window.scrollY);
        });

        function filtrerRapprochement() { let input = document.getElementById('filterMatchingInput').value.toLowerCase(); let cards = document.getElementById('tab-matching').getElementsByClassName('card-matching'); for (let i = 0; i < cards.length; i++) { let title = cards[i].getElementsByClassName('source-title')[0]; cards[i].style.display = (title && (title.textContent || title.innerText).toLowerCase().indexOf(input) > -1) ? "" : "none"; } }
        function filtrerValidees() { let input = document.getElementById('filterValideesInput').value.toLowerCase(); let table = document.getElementById('table-validees'); if (!table) return; let tr = table.getElementsByTagName('tr'); for (let i = 1; i < tr.length; i++) { tr[i].style.display = ((tr[i].textContent || tr[i].innerText).toLowerCase().indexOf(input) > -1) ? "" : "none"; } }
        window.onscroll = function() { document.getElementById("scrollTopBtn").style.display = (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) ? "block" : "none"; };
        function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }

        let isStatsLoaded = false; let mapGlobal = null;

        function openTab(evt, tabName) {
            var tabcontent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabcontent.length; i++) tabcontent[i].classList.remove("active");
            var tablinks = document.getElementsByClassName("tab-button");
            for (var i = 0; i < tablinks.length; i++) tablinks[i].classList.remove("active");
            document.getElementById(tabName).classList.add("active");
            if (evt && evt.currentTarget) { evt.currentTarget.classList.add("active"); } else { var btn = document.querySelector(".tab-button[onclick*='" + tabName + "']"); if(btn) btn.classList.add("active"); }
            localStorage.setItem('activeTabEcotrace', tabName);
            if (tabName === 'tab-stats' && !isStatsLoaded) { setTimeout(() => { chargerStatistiques(); }, 200); isStatsLoaded = true; }
        }
        
        window.addEventListener('DOMContentLoaded', (event) => { 
            const savedTab = localStorage.getItem('activeTabEcotrace'); 
            if (savedTab && document.getElementById(savedTab)) openTab(null, savedTab); 
            
            // Restauration de la position de scroll
            const scrollPos = sessionStorage.getItem('scrollPositionEcotrace');
            if (scrollPos) {
                window.scrollTo({ top: parseInt(scrollPos), behavior: 'instant' });
                sessionStorage.removeItem('scrollPositionEcotrace');
            }
        });

        function chargerStatistiques() {
            const ctxSecteurs = document.getElementById('chartSecteurs').getContext('2d');
            new Chart(ctxSecteurs, { type: 'doughnut', data: { labels: <?= json_encode(array_keys($topSecteurs)) ?>, datasets: [{ data: <?= json_encode(array_values($topSecteurs)) ?>, backgroundColor: ['#2ecc71', '#3498db', '#f1c40f', '#e67e22', '#9b59b6'] }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
            const ctxSante = document.getElementById('chartSante').getContext('2d');
            new Chart(ctxSante, { type: 'pie', data: { labels: ['Actif', 'En difficulté', 'Fermée'], datasets: [{ data: [<?= $statsSante['Actif'] ?>, <?= $statsSante['En difficulté'] ?>, <?= $statsSante['Fermée'] ?>], backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c'] }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });

            const originLat = <?= $origineLat ?>; const originLon = <?= $origineLon ?>;
            mapGlobal = L.map('globalMap').setView([originLat, originLon], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapGlobal);
            L.marker([originLat, originLon]).bindPopup("<b>🏡 Siège (Origine EcoTrace)</b>").addTo(mapGlobal);
            const markersData = <?= json_encode($mapMarkers) ?>; const bounds = [[originLat, originLon]];
            markersData.forEach(m => {
                let circle = L.circleMarker([m.lat, m.lon], { color: m.alim ? 'orange' : 'blue', radius: 6, fillOpacity: 0.8 }).addTo(mapGlobal);
                circle.bindPopup(m.nom); bounds.push([m.lat, m.lon]);
            });
            if(bounds.length > 1) mapGlobal.fitBounds(bounds, {padding: [30, 30]});
        }

        // Nouveauté 1.6.2 : Import du Dictionnaire NAF
        async function demarrerImportNAF(event) {
            const file = event.target.files[0]; if (!file) return;
            document.getElementById('import-progress').style.display = 'block';
            document.getElementById('import-status').innerText = "Importation du dictionnaire NAF en cours...";
            document.getElementById('import-percent').innerText = "⏳";
            
            let formData = new FormData(); 
            formData.append('action', 'import_naf_csv'); 
            formData.append('naf_file', file);
            
            try {
                let response = await fetch('', { method: 'POST', body: formData }); 
                let data = await response.json();
                if (data.success) { 
                    document.getElementById('import-status').innerText = "✅ " + data.message; 
                    setTimeout(() => location.reload(), 2000); 
                } else { 
                    alert(data.message); 
                    document.getElementById('import-progress').style.display = 'none'; 
                }
            } catch (err) { 
                alert("Erreur réseau NAF."); 
                document.getElementById('import-progress').style.display = 'none'; 
            }
            event.target.value = "";
        }

        // Surlignage via SIREN
        async function highlightSirenFromCSV(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = async function(e) {
                const text = e.target.result;
                const lines = text.split('\n');
                let sirens = [];
                lines.forEach(line => {
                    line.split(/[,;]/).forEach(cell => {
                        const clean = cell.replace(/\s+/g, '').replace(/^"|"$/g, '');
                        if (/^\d{9}$/.test(clean)) sirens.push(clean);
                    });
                });
                
                if (sirens.length === 0) {
                    alert("Aucun numéro SIREN valide (9 chiffres continus) n'a été trouvé dans le fichier.");
                    event.target.value = '';
                    return;
                }
                
                let f = new FormData();
                f.append('action', 'highlight_sirens');
                f.append('sirens', JSON.stringify(sirens));
                
                try {
                    let r = await fetch('', {method: 'POST', body: f});
                    let d = await r.json();
                    if(d.success) {
                        alert("✅ Correspondances enregistrées en base : " + d.count + " candidat(s) mis à jour.");
                        location.reload();
                    } else {
                        alert("Erreur base de données : " + d.message);
                    }
                } catch(err) {
                    alert("Erreur de communication avec le serveur.");
                }
            };
            reader.readAsText(file);
            event.target.value = '';
        }

        async function demarrerImportCSV(event) {
            const file = event.target.files[0]; if (!file) return;
            document.getElementById('import-progress').style.display = 'block';
            const reader = new FileReader();
            reader.onload = async function(e) {
                const lines = e.target.result.split('\n').map(l => l.trim()).filter(l => l.length > 0);
                let total = lines.length; let done = 0;
                for (let i = 0; i < total; i++) {
                    let cols = lines[i].split(',');
                    let nom = cols[0] ? cols[0].replace(/^"|"$/g, '').trim() : ''; 
                    let montant = cols[1] ? parseFloat(cols[1].replace(/[^0-9.-]+/g, '')) || 0 : 0;
                    let poids = cols[2] ? parseFloat(cols[2].replace(/[^0-9.-]+/g, '')) || 0 : 0;
                    if (nom && nom.toLowerCase() !== 'nom' && nom.toLowerCase() !== 'entreprise') {
                        document.getElementById('import-status').innerText = `Recherche API : ${nom}`;
                        let formData = new FormData(); formData.append('action', 'import_line'); formData.append('nom_recherche', nom); formData.append('montant', montant); formData.append('poids', poids);
                        try {
                            let response = await fetch('', { method: 'POST', body: formData }); let data = await response.json();
                            if (data.success) { let badgeId = data.statut === 'valide_auto' ? 'badge-rapprochees' : (data.statut === 'en_attente' ? 'badge-matching' : 'badge-introuvables'); document.getElementById(badgeId).innerText = parseInt(document.getElementById(badgeId).innerText) + 1; }
                        } catch (err) {}
                    }
                    done++; let pct = Math.round((done / total) * 100);
                    document.getElementById('import-percent').innerText = pct + '%'; document.getElementById('import-bar-fill').style.width = pct + '%';
                }
                document.getElementById('import-status').innerText = '✅ Importation terminée ! Rafraîchissement...'; setTimeout(() => location.reload(), 1500);
            }; reader.readAsText(file);
        }

        async function demarrerImportSQL(event) {
            const file = event.target.files[0]; if (!file) return;
            if(!confirm("⚠️ Restaurer le dump SQL ?")) { event.target.value = ""; return; }
            document.getElementById('import-progress').style.display = 'block'; document.getElementById('import-status').innerText = "Restauration en cours...";
            let formData = new FormData(); formData.append('action', 'import_sql'); formData.append('sql_file', file);
            try {
                let response = await fetch('', { method: 'POST', body: formData }); let data = await response.json();
                if (data.success) { document.getElementById('import-status').innerText = "✅ " + data.message; setTimeout(() => location.reload(), 1500); } 
                else { alert(data.message); document.getElementById('import-progress').style.display = 'none'; }
            } catch (err) { alert("Erreur réseau SQL."); document.getElementById('import-progress').style.display = 'none'; }
            event.target.value = "";
        }

        async function viderBase() { if(confirm("⚠️ Supprimer les tables ?")) { let f = new FormData(); f.append('action', 'vider_base'); await fetch('', {method:'POST', body:f}); location.reload(); } }
        async function reinstallationComplete() { if(confirm("⚠️ DANGER : Réinstallation complète ?")) { let f = new FormData(); f.append('action', 'hard_reset'); await fetch('', {method:'POST', body:f}); location.reload(); } }
        async function supprimerEnregistrement(id) { if(confirm("⚠️ Supprimer cet enregistrement ?")) { let f = new FormData(); f.append('action', 'delete_record'); f.append('id', id); await fetch('', {method:'POST', body:f}); location.reload(); } }
        async function toggleAlim(id, nSt) { let f = new FormData(); f.append('action', 'toggle_alim'); f.append('id', id); f.append('statut', nSt); let r = await fetch('', {method:'POST', body:f}); let d = await r.json(); if(d.success){ let td = document.getElementById('td-alim-'+id); let fSt = nSt===1?0:1; td.innerHTML = nSt===1 ? `<div class="label-badge label-alim" onclick="toggleAlim(${id}, ${fSt})">🍽️ Alimentaire</div>` : `<div class="label-badge label-no-alim" onclick="toggleAlim(${id}, ${fSt})">NON Alim.</div>`; } }
        
        function afficherFormulaire(id, nom) { 
            document.getElementById('action-cell-'+id).innerHTML = `<input type="text" id="input-nom-${id}" value="${nom}" class="input-text" style="margin-bottom: 5px;"><br>
            <button class="btn btn-blue btn-small" onclick="lancerRechercheAjax(${id}, true, 'gouv')">Gouv.fr</button> 
            <button class="btn btn-purple btn-small" onclick="lancerRechercheAjax(${id}, true, 'pappers')">Pappers</button> 
            <button class="btn btn-dark btn-small" onclick="lancerRechercheAjax(${id}, true, 'societe')">Societe.com</button>
            <button class="btn btn-red btn-small" onclick="supprimerEnregistrement(${id})" title="Supprimer cet enregistrement">🗑️</button>
            <div id="feedback-${id}" class="ajax-feedback" style="display:block; margin-top:5px;"></div>`; 
        }
        
        async function lancerRechercheAjax(id, reload=false, api='gouv') {
            let nom = document.getElementById('input-nom-'+id).value; let box = document.getElementById('feedback-'+id); 
            let apiLabel = api === 'pappers' ? 'Pappers' : (api === 'societe' ? 'Societe.com' : 'Gouv.fr');
            box.style.color="#3498db"; box.innerText="Recherche via "+apiLabel+"...";
            let f = new FormData(); f.append('action', 'ajax_search'); f.append('source_id', id); f.append('nouveau_nom', nom); f.append('api_source', api);
            try { let r = await fetch('', {method:'POST', body:f}); let d = await r.json(); box.style.color=d.success?"#27ae60":"#e74c3c"; box.innerText=d.message; if(d.success) setTimeout(()=> {if(reload)location.reload(); else document.getElementById('row-'+id).style.display='none';}, 2000); } catch(e) { box.style.color="#e74c3c"; box.innerText="Erreur comm."; }
        }
        
        async function lancerMajAjax(act, btn) { let txt=btn.innerHTML; btn.innerHTML="⏳..."; btn.disabled=true; let f = new FormData(); f.append('action', act); try{let r = await fetch('', {method:'POST', body:f}); let d = await r.json(); alert(d.message); if(d.success)location.reload();}catch(e){alert("Erreur");} finally{btn.innerHTML=txt; btn.disabled=false;} }
        
        async function verifierApiAvantAjout() {
            let nom = document.getElementById('am_nom_recherche').value.trim(); let fb = document.getElementById('am-step1-feedback'); let s2 = document.getElementById('am-step-2');
            if(!nom){fb.style.color="#e74c3c"; fb.innerText="Nom requis."; return;} fb.style.color="#3498db"; fb.innerText="Recherche..."; s2.style.display='none';
            try { let r = await fetch('https://recherche-entreprises.api.gouv.fr/search?q='+encodeURIComponent(nom)+'&per_page=5'); let d = await r.json();
                if(d.results && d.results.length>0) { fb.style.color="#27ae60"; fb.innerHTML=`Trouvé ! <br><button type="button" class="btn btn-green" style="margin-top:10px;" onclick="importerDepuisAjout('${nom.replace(/'/g, "\\'")}')">L'importer automatiquement</button>`; }
                else { 
                    fb.style.color="#e67e22"; fb.innerText="Introuvable dans l'API."; 
                    document.getElementById('am_nom_final').value=nom; 
                    document.getElementById('titre-saisie-manuelle').innerText = "L'entreprise est introuvable via l'API. Veuillez saisir les informations manuellement.";
                    s2.style.display='block'; 
                }
            } catch(e) { fb.style.color="#e74c3c"; fb.innerText="Erreur API."; }
        }

        function forcerSaisieManuelle() {
            let nom = document.getElementById('am_nom_recherche').value.trim();
            if(!nom) {
                let fb = document.getElementById('am-step1-feedback');
                fb.style.color="#e74c3c";
                fb.innerText="Veuillez d'abord saisir un nom d'entreprise ci-dessus.";
                return;
            }
            document.getElementById('am_nom_final').value = nom;
            document.getElementById('titre-saisie-manuelle').innerText = "Saisie manuelle directe. Veuillez renseigner les informations.";
            document.getElementById('am-step-2').style.display = 'block';
            document.getElementById('am-step1-feedback').innerText = "";
        }

        async function importerDepuisAjout(nom) { let f=new FormData(); f.append('action','import_line'); f.append('nom_recherche',nom); await fetch('', {method:'POST', body:f}); location.reload(); }
        async function soumettreAjoutManuel(e) { e.preventDefault(); let f=new FormData(e.target); f.append('action','ajout_manuel'); await fetch('', {method:'POST', body:f}); location.reload(); }

        function ouvrirEnrichissement(id, montant, poids, nom) {
            document.getElementById('enrich_id').value = id; document.getElementById('enrich_montant').value = montant; document.getElementById('enrich_poids').value = poids; document.getElementById('enrich-nom').innerText = nom; document.getElementById('enrichModal').style.display = 'block';
        }
        function fermerEnrichissement() { document.getElementById('enrichModal').style.display = 'none'; }
        async function soumettreEnrichissement(e) {
            e.preventDefault(); let f = new FormData(e.target); f.append('action', 'update_enrichment');
            try { let r = await fetch('', {method: 'POST', body: f}); let d = await r.json(); if(d.success) location.reload(); else alert(d.message); } catch(err) { alert("Erreur réseau."); }
        }

        async function voirAnnonceSante(siren, nom) {
            if(!siren) { alert("SIREN non renseigné pour cette entité."); return; }
            document.getElementById('bodacc-subtitle').innerText = nom + " (SIREN : " + siren + ")";
            document.getElementById('bodacc-content').innerHTML = "<p style='color:#3498db;'>Interrogation en cours du registre BODACC...</p>";
            document.getElementById('bodaccModal').style.display = 'block';

            let f = new FormData(); f.append('action', 'get_bodacc_details'); f.append('siren', siren);
            try {
                let r = await fetch('', {method: 'POST', body: f});
                let d = await r.json();
                if(d.success && d.annonces.length > 0) {
                    let html = "";
                    d.annonces.forEach(a => {
                        html += `<div class="bodacc-card">
                            <h4>📅 ${a.date} - ${a.famille} (${a.type})</h4>
                            <p><strong>🏛️ Tribunal :</strong> ${a.tribunal}</p>
                            <p style="margin-top:5px; line-height:1.4;">${a.description}</p>
                            <div style="margin-top:10px;">
                                <a href="${a.bodacc_url}" target="_blank" class="btn btn-blue btn-small">📄 Voir sur BODACC.fr</a>
                                <a href="${a.societe_url}" target="_blank" class="btn btn-purple btn-small" style="margin-left:5px;">📊 Fiche Societe.com</a>
                            </div>
                        </div>`;
                    });
                    document.getElementById('bodacc-content').innerHTML = html;
                } else {
                    document.getElementById('bodacc-content').innerHTML = "<p>Aucune publication spécifique trouvée sur les registres officiels.</p>";
                }
            } catch(err) {
                document.getElementById('bodacc-content').innerHTML = "<p style='color:#e74c3c;'>Erreur de communication avec le serveur.</p>";
            }
        }
        function fermerBodacc() { document.getElementById('bodaccModal').style.display = 'none'; }

        // Mises à jour Github
        async function verifierMiseAJour() {
            document.getElementById('update-content').innerHTML = "Connexion à GitHub en cours... ⏳";
            document.getElementById('btn-do-update').style.display = 'none';
            document.getElementById('updateModal').style.display = 'block';

            let f = new FormData(); f.append('action', 'check_github_update');
            try {
                let r = await fetch('', {method: 'POST', body: f});
                let d = await r.json();
                document.getElementById('update-content').innerHTML = d.message;
                if(d.update_available) {
                    document.getElementById('btn-do-update').style.display = 'block';
                }
            } catch(err) {
                document.getElementById('update-content').innerHTML = "<span style='color:#e74c3c;'>Erreur de communication avec GitHub.</span>";
            }
        }
        
        async function lancerMiseAJour() {
            const btn = document.getElementById('btn-do-update');
            btn.innerHTML = "⏳ Téléchargement en cours...";
            btn.disabled = true;
            
            let f = new FormData(); f.append('action', 'do_github_update');
            try {
                let r = await fetch('', {method: 'POST', body: f});
                let d = await r.json();
                document.getElementById('update-content').innerHTML = `<span style="color:${d.success ? '#27ae60' : '#e74c3c'}">${d.message}</span>`;
                if(d.success) { setTimeout(() => location.reload(), 2000); }
            } catch(err) {
                document.getElementById('update-content').innerHTML = "<span style='color:#e74c3c;'>Échec critique de la mise à jour.</span>";
            }
        }
        
        // Vérification silencieuse de la mise à jour au chargement
        async function silentUpdateCheck() {
            let f = new FormData(); f.append('action', 'check_github_update');
            try {
                let r = await fetch('', {method: 'POST', body: f});
                let d = await r.json();
                if (d.success && d.update_available) {
                    document.getElementById('update-badge').style.display = 'inline-block';
                }
            } catch(e) {}
        }
        window.addEventListener('load', () => setTimeout(silentUpdateCheck, 2000));

        // Fonctions pour la Modale Iframe (Documentation, Historique...)
        function ouvrirIframeModal(url) {
            document.getElementById('iframeContent').src = url;
            document.getElementById('iframeModal').style.display = 'block';
        }
        function fermerIframeModal() {
            document.getElementById('iframeModal').style.display = 'none';
            document.getElementById('iframeContent').src = '';
        }

        let mapRoute = null; let routeLayer = null;
        async function ouvrirCarte(dLat, dLon, nom) {
            document.getElementById('routeModal').style.display='block'; document.getElementById('modalTitle').innerText="Itinéraire vers "+nom;
            const oLat = <?= $origineLat ?>; const oLon = <?= $origineLon ?>;
            if(!mapRoute) { mapRoute = L.map('mapView'); L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapRoute); }
            mapRoute.eachLayer(l => {if(l instanceof L.Marker || l instanceof L.Polyline) mapRoute.removeLayer(l);});
            L.marker([oLat, oLon]).bindPopup("Origine").addTo(mapRoute); L.marker([dLat, dLon]).bindPopup(nom).addTo(mapRoute);
            try { let r = await fetch(`https://router.project-osrm.org/route/v1/driving/${oLon},${oLat};${dLon},${dLat}?overview=full&geometries=geojson`); let d = await r.json();
                if(d.routes && d.routes.length>0) { let coords = d.routes[0].geometry.coordinates.map(c => [c[1], c[0]]); routeLayer = L.polyline(coords, {color: '#3498db', weight: 5}).addTo(mapRoute); mapRoute.fitBounds(routeLayer.getBounds(), {padding: [30, 30]}); }
                else mapRoute.fitBounds(L.latLngBounds([oLat, oLon], [dLat, dLon]), {padding: [30, 30]});
            } catch(e) { mapRoute.fitBounds(L.latLngBounds([oLat, oLon], [dLat, dLon]), {padding: [30, 30]}); }
            setTimeout(() => mapRoute.invalidateSize(), 150);
        }
        function fermerCarte() { document.getElementById('routeModal').style.display='none'; }
        
        window.onclick = function(e) { 
            if (e.target == document.getElementById('routeModal')) fermerCarte(); 
            if (e.target == document.getElementById('enrichModal')) fermerEnrichissement();
            if (e.target == document.getElementById('bodaccModal')) fermerBodacc();
            if (e.target == document.getElementById('updateModal')) document.getElementById('updateModal').style.display='none';
            if (e.target == document.getElementById('iframeModal')) fermerIframeModal();
        }
    </script>
</body>
</html>
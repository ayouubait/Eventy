<?php
/* ============================================================
   Eventy — API (Hostinger + MySQL/MariaDB) + comptes utilisateurs
   GET  -> renvoie l'état       POST {data:...} -> enregistre
   POST ?action=login {user,pass} -> renvoie un jeton de session
   ------------------------------------------------------------
   À FAIRE UNE FOIS :
   1) hPanel -> Bases de données MySQL -> créez base + utilisateur.
   2) Remplissez le bloc CONFIG (base + comptes + clé secrète).
   3) Déposez ce fichier à côté de index.html dans public_html.
   La table est créée automatiquement à la première requête.
   ============================================================ */

// ===== CONFIG =====
// Les secrets (mot de passe MySQL, comptes utilisateurs, clé) vivent dans
// config.php — fichier IGNORÉ par Git et créé UNE SEULE FOIS sur Hostinger.
// Pour démarrer : copiez config.example.php → config.php et remplissez-le.
$CONFIG_FILE = __DIR__ . '/config.php';
if (!file_exists($CONFIG_FILE)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'config_missing', 'hint' => 'Créez config.php (copie de config.example.php).']);
    exit;
}
require_once $CONFIG_FILE;
// PostgreSQL : remplacez par "pgsql:host=$DB_HOST;dbname=$DB_NAME";
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
// ==================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

function fail($code, $msg) { http_response_code($code); echo json_encode(['error' => $msg]); exit; }

function b64url($s) { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function make_token($user, $secret) { return b64url($user) . '.' . hash_hmac('sha256', $user, $secret); }
function token_user($token, $secret, $users) {
    if (!$token || strpos($token, '.') === false) return null;
    list($b, $sig) = explode('.', $token, 2);
    $user = base64_decode(strtr($b, '-_', '+/'));
    if (!isset($users[$user])) return null;
    if (!hash_equals(hash_hmac('sha256', $user, $secret), (string)$sig)) return null;
    return $user;
}

// ---- LOGIN ----
if (isset($_GET['action']) && $_GET['action'] === 'login') {
    $body = json_decode(file_get_contents('php://input'), true);
    $u = isset($body['user']) ? trim($body['user']) : '';
    $p = isset($body['pass']) ? (string)$body['pass'] : '';
    if (isset($USERS[$u]) && hash_equals((string)$USERS[$u], $p)) {
        echo json_encode(['ok' => true, 'token' => make_token($u, $AUTH_SECRET), 'user' => $u]); exit;
    }
    fail(401, 'bad_credentials');
}

// ---- AUTH requise pour accéder aux données ----
if (!empty($USERS)) {
    $tok = isset($_SERVER['HTTP_X_AUTH']) ? $_SERVER['HTTP_X_AUTH'] : '';
    if (!token_user($tok, $AUTH_SECRET, $USERS)) fail(401, 'unauthorized');
}

// ---- BASE ----
try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS coolshy_state (
        id VARCHAR(32) PRIMARY KEY,
        data MEDIUMTEXT NOT NULL,
        updated_at VARCHAR(32) NOT NULL
    )");
} catch (Throwable $e) {
    fail(500, 'db_connection_failed');
}

// ---- DONNÉES ----
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $row = $pdo->query("SELECT data, updated_at FROM coolshy_state WHERE id = 'main'")->fetch();
        if (!$row) { echo json_encode(['data' => null, 'updated_at' => null]); exit; }
        echo '{"data":' . $row['data'] . ',"updated_at":' . json_encode($row['updated_at']) . '}';
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body) || !array_key_exists('data', $body)) fail(400, 'bad_request');
        $data = json_encode($body['data'], JSON_UNESCAPED_UNICODE);
        if ($data === false) fail(400, 'invalid_json');
        if (strlen($data) > 8000000) fail(413, 'too_large');
        $now = gmdate('Y-m-d\TH:i:s\Z');
        $up = $pdo->prepare("UPDATE coolshy_state SET data = :d, updated_at = :u WHERE id = 'main'");
        $up->execute([':d' => $data, ':u' => $now]);
        if ($up->rowCount() === 0) {
            $exists = $pdo->query("SELECT 1 FROM coolshy_state WHERE id = 'main'")->fetch();
            if (!$exists) {
                $pdo->prepare("INSERT INTO coolshy_state (id, data, updated_at) VALUES ('main', :d, :u)")
                    ->execute([':d' => $data, ':u' => $now]);
            }
        }
        echo json_encode(['ok' => true, 'updated_at' => $now]);
        exit;
    }
    fail(405, 'method_not_allowed');
} catch (Throwable $e) {
    fail(500, 'server_error');
}

<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function json_error(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$expectedToken = getenv('OUVIDORIA_API_TOKEN') ?: '';
if ($expectedToken === '') {
    json_error(503, 'API desativada.');
}

$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$providedToken = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($providedToken === '' && preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
    $providedToken = trim($matches[1]);
}

if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
    json_error(401, 'Não autorizado.');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    json_error(405, 'Método não permitido.');
}

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'ouvidoria';
$username = getenv('DB_USER') ?: 'ouvidoria';
$password = getenv('DB_PASSWORD') ?: '';
$trackId = isset($_GET['trackid']) ? trim((string) $_GET['trackid']) : '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $sql = 'SELECT ticket_id, trackid, name, email, subject, dt, ticket_message, reply_message FROM ouvidoria_api_view';
    $params = [];
    if ($trackId !== '') {
        $sql .= ' WHERE trackid = :trackid';
        $params['trackid'] = $trackId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll();

    foreach ($result as &$row) {
        $row['ticket_message'] = str_replace("<br />\r\n<br />\r\n", ' ', (string) $row['ticket_message']);
    }
    unset($row);

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('Erro na API da Ouvidoria: ' . $e->getMessage());
    json_error(500, 'Erro interno.');
}

<?php
// src/api/accounts.php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM accounts");
    echo json_encode($stmt->fetchAll());
}
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['type'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing name or type']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO accounts (name, type, balance) VALUES (?, ?, ?)");
    $stmt->execute([$data['name'], $data['type'], $data['balance'] ?? 0]);

    echo json_encode(['id' => $pdo->lastInsertId(), 'status' => 'success']);
}
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

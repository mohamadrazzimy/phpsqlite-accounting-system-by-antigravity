<?php
// src/api/transactions.php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch transactions with account names
    $sql = "SELECT t.*, a.name as account_name 
            FROM transactions t 
            LEFT JOIN accounts a ON t.account_id = a.id 
            ORDER BY t.date DESC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
}
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['description']) || !isset($data['amount']) || !isset($data['account_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $pdo->beginTransaction();

    try {
        // Insert transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (date, description, amount, type, account_id) VALUES (datetime('now'), ?, ?, ?, ?)");
        $type = $data['type'] ?? 'Expense'; // Default to Expense
        $stmt->execute([$data['description'], $data['amount'], $type, $data['account_id']]);
        $transactionId = $pdo->lastInsertId();

        // Update account balance
        // Simplified logic: Expense subtracts, Income adds. 
        // Real accounting is more complex (Debits/Credits), but for prototype:
        // Assume 'Asset' accounts: Income adds, Expense subtracts.
        // This is a PROTOTYPE.

        $amount = $data['amount'];
        if ($type === 'Expense') {
            $amount = -$amount;
        }

        $updateStmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
        $updateStmt->execute([$amount, $data['account_id']]);

        $pdo->commit();
        echo json_encode(['id' => $transactionId, 'status' => 'success']);
    }
    catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

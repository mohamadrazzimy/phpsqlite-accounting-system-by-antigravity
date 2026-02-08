<?php
// src/api/db.php

$dbPath = __DIR__ . '/../db/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialize tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS accounts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        type TEXT NOT NULL, -- e.g., Asset, Liability, Equity, Income, Expense
        balance REAL DEFAULT 0.0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        date TEXT NOT NULL,
        description TEXT NOT NULL,
        amount REAL NOT NULL,
        type TEXT NOT NULL, -- e.g., Debit, Credit (simplification: Income/Expense for prototype)
        account_id INTEGER,
        FOREIGN KEY (account_id) REFERENCES accounts(id)
    )");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: cart.php');
        exit;
    }

    try {
        $sql = 'DELETE FROM cart_items WHERE user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        header('Location: cart.php');
        exit;
    } catch (PDOException $e) {
        echo "Ошибка при выполнении запроса: " . $e->getMessage();
        exit;
    }
} else {
    header('Location: cart.php');
    exit;
}
?>

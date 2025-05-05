<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_item_id'])) {
    $cartItemId = $_POST['cart_item_id'];
    $userId = $_SESSION['user_id'];

    try {
        // Удаление товара из корзины
        $sql = 'DELETE FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cart_item_id' => $cartItemId, 'user_id' => $userId]);

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

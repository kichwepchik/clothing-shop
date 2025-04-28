<?php
session_start();
global $pdo;
require 'static/app/database/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $userId = $_SESSION['user_id']; // предположим, что ID пользователя хранится в сессии

    try {
        // Обновление количества товара в корзине
        $sql = 'UPDATE cart_items SET quantity = :quantity WHERE id = :item_id AND user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'quantity' => $quantity,
            'item_id' => $itemId,
            'user_id' => $userId
        ]);

        echo "Количество товара обновлено успешно.";
    } catch (PDOException $e) {
        echo "Ошибка при обновлении количества товара: " . $e->getMessage();
    }
} else {
    echo "Неверный метод запроса.";
}
?>

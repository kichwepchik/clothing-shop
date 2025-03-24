<?php
session_start();

// Включение файла подключения к базе данных
global $pdo;
require 'static/app/database/connect.php';

// Получение параметров из URL
$telegramId = isset($_GET['telegram_id']) ? intval($_GET['telegram_id']) : null;

if ($telegramId === null) {
    echo "Telegram ID не указан.";
    exit;
}




try {
    // Проверка наличия пользователя в базе данных
    $sql = 'SELECT id FROM users WHERE telegram_id = :telegram_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['telegram_id' => $telegramId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];
        // Установка user_id в сессию
        $_SESSION['user_id'] = $userId;

        // Перенаправление пользователя на страницу товаров
        header('Location: items.php');
        exit;
    } else {
        echo "Пользователь не найден.";
        exit;
    }
} catch (PDOException $e) {
    echo "Ошибка при обработке данных пользователя: " . $e->getMessage();
    exit;
}
?>

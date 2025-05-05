<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Получение уведомлений для текущего пользователя
    $stmt = $pdo->prepare("SELECT id, order_id, message, created_at, `read`, type FROM notifications WHERE telegram_id = :telegram_id ORDER BY created_at DESC");
    $stmt->bindParam(':telegram_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Автоматическая отметка уведомлений типа 'info' как прочитанных
    foreach ($notifications as $notification) {
        if ($notification['type'] == 'info' && !$notification['read']) {
            $stmt = $pdo->prepare("UPDATE notifications SET `read` = TRUE WHERE id = :notification_id AND telegram_id = :telegram_id");
            $stmt->bindParam(':notification_id', $notification['id'], PDO::PARAM_INT);
            $stmt->bindParam(':telegram_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
} else {
    $notifications = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="static/styles/styles_for_notification.css">
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
    <h1>Уведомления</h1>
</header>
<nav class="menu" id="mobileMenu">
    <a href="items.php">Каталог</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="notifications-container">
    <?php if (!empty($notifications)): ?>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification <?php echo $notification['type'] === 'status' ? 'status-notification' : ''; ?>">
                <?php if ($notification['type'] === 'status'): ?>
                    <a href="order_details.php?id=<?php echo htmlspecialchars($notification['order_id'], ENT_QUOTES, 'UTF-8'); ?>&notif_id=<?php echo htmlspecialchars($notification['id'], ENT_QUOTES, 'UTF-8'); ?>" class="noti-link">
                        <p><?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </a>
                <?php else: ?>
                    <p><?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Здесь вы будете получать уведомления</p>
    <?php endif; ?>
</div>

<form method="POST" action="clear_notifications.php" class="clear-notifications-form">
    <button type="submit">Очистить уведомления</button>
</form>
<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/menu.js"></script>
</body>
</html>

<?php
require 'static/app/database/connect.php';

function getUnreadNotificationCount($telegram_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE telegram_id = :telegram_id AND `read` = FALSE");
    $stmt->bindParam(':telegram_id', $telegram_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['unread_count'];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['telegram_id']) && is_numeric($_GET['telegram_id'])) {
    $telegram_id = intval($_GET['telegram_id']);
    $unread_count = getUnreadNotificationCount($telegram_id);
    echo json_encode(['unread_count' => $unread_count]);
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>

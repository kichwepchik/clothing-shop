<?php
require_once 'static/app/database/connect.php';
function createNotification($telegram_id, $message) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (telegram_id, message) VALUES (:telegram_id, :message)");
    $stmt->bindParam(':telegram_id', $telegram_id, PDO::PARAM_INT);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->execute();
}

// Пример использования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telegram_id = $_POST['telegram_id'];
    $message = $_POST['message'];
    createNotification($telegram_id, $message);
    echo "Notification sent!";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification</title>
</head>
<body>
<h1>Send Test Notification</h1>
<form method="POST" action="create_notification.php">
    <label for="telegram_id">Telegram ID:</label>
    <input type="text" id="telegram_id" name="telegram_id" value="123456789" required>
    <br>
    <label for="message">Message:</label>
    <textarea id="message" name="message" required>Test Notification</textarea>
    <br>
    <button type="submit">Send Notification</button>
</form>
</body>
</html>

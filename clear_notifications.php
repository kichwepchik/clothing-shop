<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE telegram_id = :telegram_id");
    $stmt->bindParam(':telegram_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
}

header("Location: notifications.php");
exit();
?>

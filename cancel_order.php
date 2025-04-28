<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

function logToFile($message) {
    $logFile = 'debug.log';
    $current = file_get_contents($logFile);
    $current .= $message . "\n";
    file_put_contents($logFile, $current);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    logToFile('Received raw data: ' . $rawData);
    $data = json_decode($rawData, true);
    logToFile('Decoded JSON data: ' . print_r($data, true));

    if (json_last_error() !== JSON_ERROR_NONE) {
        logToFile('JSON decode error: ' . json_last_error_msg());
        echo json_encode(['error' => 'Invalid JSON input.']);
        exit;
    }

    if (!isset($data['order_id']) || !ctype_digit((string) $data['order_id'])) {
        logToFile('Invalid Order ID: ' . print_r($data, true));
        echo json_encode(['error' => 'Invalid Order ID.']);
        exit;
    }

    $orderId = intval($data['order_id']);
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId || !ctype_digit((string) $userId)) {
        logToFile('Invalid User ID: ' . print_r($userId, true));
        echo json_encode(['error' => 'Invalid User ID.']);
        exit;
    }

    $sql = "UPDATE orders SET application_status = 'Отклонён' WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$orderId, $userId])) {
        logToFile('Order cancellation successful');
        echo json_encode(['success' => true]);
    } else {
        logToFile('Failed to update order status: ' . print_r($stmt->errorInfo(), true));
        echo json_encode(['error' => 'Failed to update order status.']);
    }
}
?>

<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

function redirectToError($message) {
    header("Location: error.php?message=" . urlencode($message));
    exit;
}

if (isset($_GET['user_id'])) {
    $_SESSION['user_id'] = filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);
} elseif (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript" src="https://telegram.org/js/telegram-web-app.js"></script>';
    echo '<script type="text/javascript">
        Telegram.WebApp.ready();
        Telegram.WebApp.expand();
        const initData = Telegram.WebApp.initDataUnsafe;
        if (initData.user && initData.user.id) {
            const userId = initData.user.id;
            const userName = initData.user.first_name;
            const userPhotoUrl = initData.user.photo_url;
            fetch("set_user_data.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "user_id=" + userId + "&user_name=" + userName + "&user_photo_url=" + encodeURIComponent(userPhotoUrl)
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    window.location.href = "error.php?message=" + encodeURIComponent("Failed to set user data.");
                }
            }).catch(error => {
                window.location.href = "error.php?message=" + encodeURIComponent("Error: " + error.message);
            });
        } else {
            window.location.href = "error.php?message=" + encodeURIComponent("User data not available from Telegram Web App.");
        }
    </script>';
    exit;
}

$userId = $_SESSION['user_id'];
$personalInfo = [
    'first_name' => '',
    'last_name' => '',
    'phone_number' => '',
    'email' => '',
    'adres' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $phoneNumber = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $adres = filter_input(INPUT_POST, 'adres', FILTER_SANITIZE_STRING);

    if ($userId && $firstName && $lastName && $phoneNumber && $email && $adres) {
        $sql = "SELECT COUNT(*) FROM shipping_info WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $userExists = $stmt->fetchColumn() > 0;

        if ($userExists) {
            $sql = "UPDATE shipping_info SET first_name = ?, last_name = ?, phone_number = ?, email = ?, adres = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$firstName, $lastName, $phoneNumber, $email, $adres, $userId]);
        } else {
            $sql = "INSERT INTO shipping_info (user_id, first_name, last_name, phone_number, email, adres) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $firstName, $lastName, $phoneNumber, $email, $adres]);
        }
        header('Location: profile.php');
        exit;
    } else {
        redirectToError("All fields are required.");
    }
} else {
    // Load personal info
    $sql = "SELECT email, first_name, last_name, adres, phone_number FROM shipping_info WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $personalInfo = array_merge($personalInfo, $userData);
    }
}

function getOrders($pdo, $userId, $status = 'active') {
    $condition = '';
    if ($status === 'history') {
        $condition = "AND (o.application_status = 'отклонён' OR od.status = 'Получен')";
    } else {
        $condition = "AND o.application_status != 'отклонён' AND od.status != 'Получен'";
    }

    $sql = "SELECT o.id AS order_id, oi.price, o.application_status, oi.product_id, s.size, p.manufacturer, p.name, p.image_url, oi.quantity, od.status AS delivery_status
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            JOIN sizes s ON oi.size_id = s.id
            JOIN order_delivery od ON o.id = od.order_id
            WHERE o.user_id = ? $condition
            ORDER BY o.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$activeOrders = getOrders($pdo, $userId, 'active');
$orderHistory = getOrders($pdo, $userId, 'history');
?>

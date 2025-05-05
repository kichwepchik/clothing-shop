<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/styles/styles_for_cart.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <title>Ошибка</title>
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <h1>High Score</h1>
    <div class="back-icon" onclick="window.location.href='index.php'">←</div>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>
<nav class="menu" id="mobileMenu">
    <a href="items.php">Каталог</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="container">
    <h2 class="error-title">Ошибка</h2>
    <p>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo htmlspecialchars($_SESSION['error_message']);
            unset($_SESSION['error_message']);
        } else {
            echo "Произошла неизвестная ошибка.";
        }
        ?>
    </p>
</div>
<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="static/scripts/menu.js"></script>
</body>
</html>

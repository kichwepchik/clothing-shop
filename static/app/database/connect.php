<?php
$driver = getenv('DB_DRIVER') ?: 'mysql';
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'clothing_shop';
$db_user = getenv('DB_USER') ?: 'user';
$db_pass = getenv('DB_PASS') ?: 'X531z-dahigh-24dD';
$charset = getenv('DB_CHARSET') ?: 'utf8';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO(
        "$driver:host=$host;dbname=$db_name;charset=$charset",
        $db_user,
        $db_pass,
        $options
    );
} catch (PDOException $i) {
    die("Ошибка подключения к базе данных: " . $i->getMessage());
}
?>

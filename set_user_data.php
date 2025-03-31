<?php
session_start();

function logToFile($message) {
    $logFile = 'debug.log';
    $current = file_get_contents($logFile);
    $current .= $message . "\n";
    file_put_contents($logFile, $current);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['user_name'])) {
    $userId = $_POST['user_id'];
    $userName = $_POST['user_name'];

    // Получаем токен вашего бота
    $telegramBotToken = '7129660172:AAFrvM0LleNZyOasj_SH5kDj08jrZ_64nCg'; //ПОМЕНЯТЬ!!!!!!!

    // Запрос к Telegram API для получения фотографий профиля пользователя
    $telegramApiUrl = "https://api.telegram.org/bot$telegramBotToken/getUserProfilePhotos?user_id=$userId";
    $response = file_get_contents($telegramApiUrl);
    $responseDecoded = json_decode($response, true);

    if ($responseDecoded['ok'] && count($responseDecoded['result']['photos']) > 0) {
        // Получаем URL первой фотографии
        $fileId = $responseDecoded['result']['photos'][0][0]['file_id'];

        // Запрос для получения URL файла
        $fileResponse = file_get_contents("https://api.telegram.org/bot$telegramBotToken/getFile?file_id=$fileId");
        $fileResponseDecoded = json_decode($fileResponse, true);

        if ($fileResponseDecoded['ok']) {
            $filePath = $fileResponseDecoded['result']['file_path'];
            $userPhotoUrl = "https://api.telegram.org/file/bot$telegramBotToken/$filePath";
        } else {
            $userPhotoUrl = "";
            logToFile("Failed to get file path");
        }
    } else {
        $userPhotoUrl = "";
        logToFile("No profile photos found or failed to get photos");
    }

    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
    $_SESSION['user_photo_url'] = $userPhotoUrl;

    logToFile("User ID: " . $_SESSION['user_id']);
    logToFile("User Name: " . $_SESSION['user_name']);
    logToFile("User Photo URL: " . $_SESSION['user_photo_url']);

    http_response_code(200);
    echo "User data set successfully";
} else {
    http_response_code(400);
    echo "Invalid request";
}
?>

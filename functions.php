<?php
function handleError($errorMessage) {
    // Сохраняем сообщение об ошибке в сессии
    $_SESSION['error_message'] = htmlspecialchars($errorMessage);
    // Перенаправляем на страницу ошибок
    header('Location: error.php');
    exit;
}
?>

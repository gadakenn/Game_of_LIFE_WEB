<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Или ваш пароль, если он установлен
define('DB_NAME', 'GOL_web_bd');
define('DB_PORT', '8888'); // Порт MySQL из настроек MAMP
define('DB_SOCKET', '/Applications/MAMP/tmp/mysql/mysql.sock'); // Путь к сокету из настроек MAMP

function dbConnect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_SOCKET);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    return $conn;
}
?>

<?php
function dbConnect() {
                /**
     * Возвращает переменную для подключения к рабочей базе данных на продакшене
     * 
     */
    $host = getenv('MYSQLHOST');       
    $user = getenv('MYSQLUSER');      
    $pass = getenv('MYSQL_ROOT_PASSWORD');  
    $dbname = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT');      
    
    $conn = new mysqli($host, $user, $pass, $dbname, $port);

    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    return $conn;
}

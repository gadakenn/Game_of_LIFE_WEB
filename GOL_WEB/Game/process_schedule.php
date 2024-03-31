<?php
require '../db_connect/config.php'; // Убедитесь, что путь к вашему файлу конфигурации правильный

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalEarnings = 0;
    foreach ($_POST as $time) {
        if (is_numeric($time) && $time >= 2) {
            $totalEarnings += 500; // Если ученик уделяет 2 часа или более, он получает "5"
        }
    }
    echo json_encode(['totalEarnings' => $totalEarnings]);
}
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once 'game.php';



$taskId = 1; // ID задания
$data = getTaskWithOptions($taskId);
echo json_encode($data);

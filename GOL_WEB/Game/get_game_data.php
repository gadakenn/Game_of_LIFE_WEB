<?php
// get_game_data.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once '../db_connect/config.php';
require_once 'game.php';
session_start();

function getRoundData($taskId) {
    $conn = dbConnect(); 
    $data = [];
    $data['roundId'] = $taskId;
    $taskStmt = $conn->prepare("SELECT question_text, type FROM tasks WHERE id = ?");
    $taskStmt->bind_param("i", $taskId);
    $taskStmt->execute();
    $result = $taskStmt->get_result();
    $taskResult = $result->fetch_assoc();
    $data['question'] = $taskResult['question_text'];
    $data['type'] = $taskResult['type'];
    $optionsStmt = $conn->prepare("SELECT id, option_text, type FROM options WHERE task_id = ?");
    $optionsStmt->bind_param("i", $taskId);
    $optionsStmt->execute();
    $optionsResult = $optionsStmt->get_result();
    $data['options'] = $optionsResult->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $data;
}

function getGameData() {

}

function startGameDB($game) {
    $conn = dbConnect(); 
   
    $userId = $game->getUser()->getId();
    $currentRoundId = $game->getCurrentRoundIndex();
    $gameName = $game->game_name; 

  
    $stmt = $conn->prepare("INSERT INTO games (game_name, user_id, cur_round_id, current_score) VALUES (?, ?, ?, ?)");
    
    $initialScore = $game->getUser()->getBalance();

    $stmt->bind_param("siii",  $gameName, $userId, $currentRoundId, $initialScore);
    $stmt->execute();


    if ($stmt->error) {
        echo "Ошибка при добавлении игры: " . $stmt->error;
        return false;
    }

    $stmt->close();
    
    return $conn->insert_id;
}


function updateBalanceDB($gameId, $currentRoundId) {
    $conn = dbConnect();
    $user = unserialize($_SESSION['user']);
    $balance = $user->getBalance(); 
    // Подготовка запроса на обновление
    $stmt = $conn->prepare("UPDATE games SET current_score = ?, cur_round_id = ? WHERE id = ?");
    if (!$stmt) {
        echo "Ошибка при подготовке запроса: " . $conn->error;
        return false;
    }

    // Привязка параметров к запросу
    $stmt->bind_param("iii", $balance, $currentRoundId, $gameId);

    // Выполнение запроса
    $stmt->execute();

    // Проверка на наличие ошибок при выполнении запроса
    if ($stmt->error) {
        echo "Ошибка при обновлении баланса: " . $stmt->error;
        return false;
    }

    // Закрытие statement
    $stmt->close();

    // Возвращение успеха операции
    return true;
}

$roundClasses = [   // соотношение классов раундов и индексов из бд
    'SchoolWeekRound' => '1',
    'StockBondsDeps' => '2',
    'SummerBusinessRound' => '3',
    'StartupInvestmentRound' => '4',
    'BetsRound' => '5'
];

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'roundData') {
        $game = unserialize($_SESSION['game']);
        $round = $game->getCurrentRound();
        $currentRoundIndex = $game->getCurrentRoundIndex();
    
        // Проверяем, пришел ли параметр next и равен ли он 'true'
        if (isset($_GET['next']) && $_GET['next'] == 'true') {
            $game->nextRound(); // Переходим к следующему раунду
            $currentRoundIndex = $game->getCurrentRoundIndex();
            $round = $game->getCurrentRound();
            updateBalanceDB($game->game_id, $currentRoundIndex);
            $_SESSION['currentRoundIndex'] = serialize($currentRoundIndex);
            $_SESSION['game'] = serialize($game);
        }
    
        // updateBalanceDB($game->game_id, $currentRoundIndex);
        echo json_encode(getRoundData($roundClasses[$round]));
    } elseif ($_GET['action'] == 'gamesData') {
        echo getGameData();
    }
}

?>

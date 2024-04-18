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
 
    $optionsStmt = $conn->prepare("SELECT id, option_text FROM options WHERE task_id = ?");
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
    $id = 1;
    $stmt->bind_param("siii",  $gameName, $userId, $currentRoundId, $initialScore);
    $stmt->execute();


    if ($stmt->error) {
      
        echo "Ошибка при добавлении игры: " . $stmt->error;
        return false;
    }

    $stmt->close();
    
    return $conn->insert_id;
}

// startGameDB($game);
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'roundData') {
        $taskId = unserialize($_SESSION['currentRoundIndex']);
        $game = unserialize($_SESSION['game']);
        $game->nextRound();
        $currentRoundIndex = $game->getCurrentRoundIndex();
        $_SESSION['currentRoundIndex'] = serialize($currentRoundIndex);
        echo json_encode(getRoundData($taskId));
    } elseif ($_GET['action'] == 'gamesData') {
        echo getGameData();
    }
}

?>

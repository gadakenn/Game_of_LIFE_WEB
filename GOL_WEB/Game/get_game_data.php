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

function call_chatgpt($data) {
    $url = 'http://game_of_life_web.railway.internal:8000/run_chatgpt';

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        /* Обработка ошибки */
        return null;
    }

    // Декодирование JSON-ответа
    $decoded_result = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Обработка ошибки декодирования JSON
        return null;
    }

    return $decoded_result;
}


$roundClasses = [   // соотношение классов раундов и индексов из бд
    'SchoolWeekRound' => '1',
    'StockBondsDeps' => '2',
    'SummerBusinessRound' => '3',
    'StartupInvestmentRound' => '4',
    'BetsRound' => '5',
    'HousingDecisionRound' => '6',
    'EducationRound' => '7',
    'CareerRound' => '8',
    'SelfTaughtBusinessRound' => '9',
    'CollegeClubRound' => '10',
    'QuestionsRound' => '11'
];

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'roundData') {
        $flag = false;
        $game = unserialize($_SESSION['game']);
        $user = unserialize($_SESSION['user']);
        $round = $game->getCurrentRound();
        $currentRoundIndex = $game->getCurrentRoundIndex();
        $continue_with_gpt = false;


        // Проверяем, пришел ли параметр next и равен ли он 'true'
        if (isset($_GET['next']) && $_GET['next'] == 'true') {
  
            $flag = true;
            $game->nextRound(); // Переходим к следующему раунду
            $round = $game->getCurrentRound();
            $currentRoundIndex = $game->getCurrentRoundIndex();
            $user->earnMoney(0, true);
            $game->holdAnswer(0, '', true);
            $user->salarySpending();
            updateBalanceDB($game->game_id, $currentRoundIndex);

            if ($round == 'gpt') {
                $continue_with_gpt = true;
                $roundsGPT = $game->getStory();
                $age = $game->current_age;
                $info_to_chatGPT = [
                    'age' => $age,
                    'story' => $roundsGPT,
                    'user_id' => $user->getId(),
                    'first' => $game->flag
                ];
                $roundInfo = call_chatgpt($info_to_chatGPT);
                // $game->addRoundGPT($currentRoundIndex, $roundInfo['question']);
                $game->flag = false;
            } else {
                $roundInfo = getRoundData($roundClasses[$round]);
                $game->addRoundGPT($currentRoundIndex, $roundInfo['question']);
            }

        }
            

        $_SESSION['currentRoundIndex'] = serialize($currentRoundIndex);
        $_SESSION['game'] = serialize($game);
        $_SESSION['user'] = serialize($user);

        if ($flag) {
            echo json_encode(array_merge($roundInfo, $game->getRoundGPT()));
        } else {
            echo json_encode(getRoundData($roundClasses[$round]));
        }
    
       
    } elseif ($_GET['action'] == 'gamesData') {
        echo getGameData();
    } elseif ($_GET['action'] == 'gptRoundData') {
        $game = unserialize($_SESSION['game']);
        $user = unserialize($_SESSION['user']);
        $roundsGPT = $game->getStory();
        $age = $game->current_age;
        $info_to_chatGPT = [
            'age' => $age,
            'story' => $roundsGPT,
            'user_id' => $user->getId(),
            'first' => $game->flag
        ];
        $roundInfo = call_chatgpt($info_to_chatGPT);
        $_SESSION['game'] = serialize($game);
        $_SESSION['user'] = serialize($user);
        echo json_encode(array_merge($roundInfo, $game->getRoundGPT()));

    }
}



<?php
// process_answers.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

function answerProcessing() {
    header('Content-Type: application/json');

    require_once 'game.php'; 
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);
    session_start();

    if (isset($_SESSION['user'])) { 
        $user = unserialize($_SESSION['user']);  
    }


    $roundClasses = [
        '1' => new SchoolWeekRound(),
        '2' => new StockBondsDeps(),
        '3' => new SummerBusinessRound(),
        '4' => new StartupInvestmentRound(),
        '5' => new BetsRound(),
        '6' => new HousingDecisionRound(),
        '7' => new EducationRound(),
        '8' => new CareerRound(),
        '9' => new SelfTaughtBusinessRound(),
        '10' => new CollegeClubRound(),
        'gpt' => new SendAnswerToGPT(),
        '11' => new QuestionsRound()

    ];

    // Получаем roundId из $_POST
    $roundId = $_POST['roundId'] ?? null; 


    // Проверяем, что roundId есть в массиве $roundClasses
    if (!isset($roundClasses[$roundId])) {
        echo json_encode(['error' => 'Неизвестный тип раунда', 'roundId' => $roundId]);
        exit;
    }

    $roundClass = $roundClasses[$roundId];
  
    $roundClass->play($user, $_POST);
    $answer = $roundClass->getResult();

    if (isset($_SESSION['game'])) { 
        $game = unserialize($_SESSION['game']);
        if (isset($answer['info_to_gpt'])) {
            $game->holdAnswer($game->getCurrentRoundIndex(), $answer['info_to_gpt']);
        }
    }

    $_SESSION['game'] = serialize($game);
    $_SESSION['user'] = serialize($user);
    return json_encode($answer);
}

function call_chatgpt_answer($data) {
    $url = 'http://game_of_life_web.railway.internal:8000/process_answer';

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

function balanceForPage() {
    // Тут выгружем данные по балансу для страницы каждого раунда
    session_start();
    require_once 'game.php'; 
    header('Content-Type: application/json');

    if (isset($_SESSION['user'])) {
        $user = unserialize($_SESSION['user']);
        $balance = $user->getBalance(); 
        return json_encode(['balance' => $balance]);
    } else {
        return json_encode(['error' => 'User not logged in']);
    }
}

function getSalarySpending() {

    session_start();
    require_once 'game.php';
    if (isset($_SESSION['user'])) {
        $user = unserialize($_SESSION['user']);
        $salary = $user->getSalary();
        $spending = $user->getSpending();
        return json_encode(['salary' => $salary, 'spending' => $spending]);
    } else {
        return json_encode(['error' => 'User not logged in']);
    }

}

function endGame($gameId) {
    require_once '../db_connect/config.php';
    $conn = dbConnect();
    // Обновляем статус игры на 'завершено'
    $stmt = $conn->prepare("UPDATE games SET game_status = 'finished' WHERE id = ?");
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    if ($stmt->error) {
        echo "Ошибка при завершении игры: " . $stmt->error;
        return false;
    }
    $stmt->close();
    
    return true;
}

function checkEndGame() {

    // Получаем текущее состояние игры
    if (isset($_SESSION['game'])) { 
        $game = unserialize($_SESSION['game']);
        // Проверяем, что текущий раунд - последний
        if ($game->getCurrentRoundIndex() + 1 >= count($game->getRounds())) {
            // Если да, завершаем игру
            if (endGame($game->game_id)) {
                return ['endGame' => true, 'endGameMessage' => 'Игра завершена.', 'roundID' => $game->getCurrentRoundIndex()];
            } else {
                return ['endGame' => false, 'error' => 'Не удалось завершить игру.', 'roundID' => $game->getCurrentRoundIndex()];
            }
        }
    }
    return ['endGame' => false, 'roundID' => $game->getCurrentRoundIndex(), 'roundsCount' => count($game->getRounds()), 'rounds' => $game->getRounds(), 'flag' => $game->flag];
}


if (isset($_GET['action'])) {
    if ($_GET['action'] === 'answerProcessing') {
        $result = answerProcessing();
        $endGameCheck = checkEndGame(); // Проверяем, не закончилась ли игра
        echo json_encode(array_merge(json_decode($result, true), $endGameCheck));
    } elseif (($_GET['action']) == 'getBalancePage') {
        echo balanceForPage();
    } elseif (($_GET['action']) == 'getSalarySpending') {
        echo getSalarySpending();
    }
}

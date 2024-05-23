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
    // if (isset($_SESSION['game'])) { 
    //     $game = unserialize($_SESSION['game']);
    // }

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
        '10' => new CollegeClubRound()

    ];

    // Получаем roundId из $_POST
    $roundId = $_POST['roundId'] ?? null; 


    // Проверяем, что roundId есть в массиве $roundClasses
    if (!isset($roundClasses[$roundId])) {
        echo json_encode(['error' => 'Неизвестный тип раунда', 'roundId' => $roundId]);
        exit;
    }

    $roundClass = $roundClasses[$roundId];
  
    $roundClass->play($user, $_POST); // Передаем $_POST напрямую, поскольку он содержит все данные из формы
    $_SESSION['user'] = serialize($user);
    return json_encode($roundClass->getResult());
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
                // header('Location: ../main_list/templates/main_list.php');
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
    }
}

<?php
// process_answers.php
function answerProcessing() {
    header('Content-Type: application/json');

    require_once 'game.php'; 
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);
    session_start();

    if (isset($_SESSION['user'])) { 
        $user = unserialize($_SESSION['user']);  
    }
    if (isset($_SESSION['game'])) { 
        $game = unserialize($_SESSION['game']);
    }

    $roundClasses = [
        '1' => 'SchoolWeekRound',
        '2' => 'StockBondsDeps' 
    ];

    // Получаем roundId из $_POST
    $roundId = $_POST['roundId'] ?? null; 

    // Проверяем, что roundId есть в массиве $roundClasses
    if (!isset($roundClasses[$roundId])) {
        echo json_encode(['error' => 'Неизвестный тип раунда', 'roundId' => $roundId]);
        exit;
    }

    $roundClass = $roundClasses[$roundId];
    $round = new $roundClass();
    $round->play($user, $_POST); // Передаем $_POST напрямую, поскольку он содержит все данные из формы
    $_SESSION['user'] = serialize($user);
    $_SESSION['game'] = serialize($game);
    return json_encode($round->getResult());
}

function balanceForPage() {
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



if (isset($_GET['action'])) {
    if (($_GET['action']) == 'answerProcessing') {
        echo answerProcessing();
    } elseif (($_GET['action']) == 'getBalancePage') {
        echo balanceForPage();
    }
}

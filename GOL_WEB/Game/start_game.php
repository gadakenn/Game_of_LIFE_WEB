<?php
// start_game.php
require_once 'game.php';
require_once 'get_game_data.php';

function start_game() {
    $user = unserialize($_SESSION['user']);
    $game = new Game($user, $user->getName());

    $numRounds = 3;

    $roundClasses = [
        0 => ['SummerBusinessRound', 'SchoolWeekRound'],
        1 => ['StockBondsDeps', 'BetsRound'],
        2 => ['StartupInvestmentRound']

    ];

    for ($i = 0; $i < $numRounds; $i++) {
        // Рандомно выбираем набор раундов
        $roundClass = $roundClasses[$i][array_rand($roundClasses[$i])];
        
        // $round = new $roundClass();
        
        $game->addRound($roundClass);
    }

    $_SESSION['game'] = serialize($game);

    $_SESSION['currentRoundIndex'] = serialize($game->getCurrentRoundIndex());

    $response = [
        'success' => true,
        'message' => 'Игра начата.'
    ];
    // print_r($game->getRounds());
    $game->game_id = startGameDB($game);
    $_SESSION['game'] = serialize($game);
    return json_encode($response);
}

function end_game() {
    $game = unserialize($_SESSION['game']);
    $curRound = unserialize($_SESSION['currentRoundIndex']);
    updateBalanceDB($game->game_id, $curRound);
    $user = unserialize($_SESSION['user']);
    $user->clearUserInfo();
    $_SESSION['user'] = serialize($user);
    $tempUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

    // Очищаем сессию
    // $_SESSION = [];

    // Возвращаем переменную user обратно в сессию, если она была установлена
    if ($tempUser) {
        $_SESSION['user'] = $tempUser;
    }
    $response = [
        'success' => true,
        'message' => 'Игра закончена.'
    ];
    return json_encode($response);
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'startGame') {
        echo start_game();
    } elseif ($_GET['action'] === 'endGame') {
        echo end_game();
    }
}
?>

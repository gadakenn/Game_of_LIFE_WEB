<?php
// start_game.php
require_once 'game.php';
require_once 'get_game_data.php';

function start_game() {
    $user = unserialize($_SESSION['user']);
    $user->clearUserInfo();
    $game = new Game($user, $user->getName(), 14);

    $_SESSION['game'] = serialize($game);

    $_SESSION['currentRoundIndex'] = serialize($game->getCurrentRoundIndex());

    $response = [
        'success' => true,
        'message' => 'Игра начата.'
    ];
    // print_r($game->getRounds());
    $game->game_id = startGameDB($game);
    $_SESSION['game'] = serialize($game);
    $_SESSION['user'] = serialize($user);
    return json_encode($response);
}

function summary_gpt($data) {
    $url = 'https://gptservice-production.up.railway.app/make_summary';

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json",
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

function end_game() {
    $game = unserialize($_SESSION['game']);
    $curRound = unserialize($_SESSION['currentRoundIndex']);
    updateBalanceDB($game->game_id, $curRound, true);
    $user = unserialize($_SESSION['user']);

    $summary = summary_gpt(['balance' => $user->getBalance(), 'age' => $game->current_age, 'user_id' => $user->getId()]);
    $user->clearUserInfo();
    $_SESSION['user'] = serialize($user);
    $tempUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;


    // Возвращаем переменную user обратно в сессию, если она была установлена
    if ($tempUser) {
        $_SESSION['user'] = $tempUser;
    }
    $response = [
        'success' => true,
        'message' => $summary['message']
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

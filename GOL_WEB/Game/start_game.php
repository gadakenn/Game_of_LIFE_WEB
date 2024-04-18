<?php
// start_game.php
require_once 'game.php';
require_once 'get_game_data.php';

function start_game() {
    $user = unserialize($_SESSION['user']);
    $game = new Game($user, 'Test_name1');
    $numRounds = 2;

    $roundClasses = [
        0 => ['SchoolWeekRound'],
        1 => ['StockBondsDeps'] 
    ];

    for ($i = 0; $i < $numRounds; $i++) {
    
        $roundClass = $roundClasses[$i][array_rand($roundClasses[$i])];
        
        $round = new $roundClass();
        
        $game->addRound($round);
    }

    $_SESSION['game'] = serialize($game);

    $_SESSION['currentRoundIndex'] = serialize($game->getCurrentRoundIndex());

    $response = [
        'success' => true,
        'message' => 'Игра начата.'
    ];
    // print_r($game->getUser()->getId());
    startGameDB($game);
    return json_encode($response);
}


// start_game();
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'startGame') {
        echo start_game();
    }
}
exit;
?>

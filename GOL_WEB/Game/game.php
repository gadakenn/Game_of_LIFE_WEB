<?php
class Game {
    private $playerCapital;
    private $currentRound;
    private $rounds;

    public function __construct($initialCapital) {
        $this->playerCapital = $initialCapital;
        $this->currentRound = 1;
        $this->rounds = [];
    }

    public function addRound(Round $round) {
        $this->rounds[$this->currentRound] = $round;
    }

    public function playRound($roundNumber) {
        if (!isset($this->rounds[$roundNumber])) {
            echo "Раунд $roundNumber не найден.\n";
            return;
        }

        $round = $this->rounds[$roundNumber];
        $earnings = $round->complete();
        $this->playerCapital += $earnings;
        echo "Вы заработали $earnings рублей. Текущий капитал: {$this->playerCapital} рублей.\n";
    }
}

abstract class Round {
    protected $earnings;

    public function __construct() {
        $this->earnings = 0;
    }

    abstract public function complete();
}

class SchoolWeekRound extends Round {
    public function complete() {
        // Тут будет логика выполнения заданий "Школьной недели"
        // Для упрощения примера просто вернем фиксированную сумму
        $this->earnings = 500; // Предположим, что игрок заработал 500 рублей
        return $this->earnings;
    }
}

// $initialCapital = 1000; // Начальный капитал игрока
// $game = new Game($initialCapital);

// Добавление раунда "Школьная неделя"
// $schoolWeekRound = new SchoolWeekRound();
// $game->addRound($schoolWeekRound);

// Играем первый раунд
// $game->playRound(1);


// Файл game_round.php

require_once __DIR__ . '/../db_connect/config.php';

// Функция для получения задания и вариантов ответов
function getTaskWithOptions($taskId) {
    $conn = dbConnect();
    $conn->set_charset('utf8');

    // Получение текста задания
    $taskStmt = $conn->prepare("SELECT question_text FROM tasks WHERE id = ?");
    $taskStmt->bind_param("i", $taskId);
    $taskStmt->execute();
    $taskResult = $taskStmt->get_result();
    $task = $taskResult->fetch_assoc();

    // Получение вариантов ответов
    $optionsStmt = $conn->prepare("SELECT option_text FROM options WHERE task_id = ?");
    $optionsStmt->bind_param("i", $taskId);
    $optionsStmt->execute();
    $optionsResult = $optionsStmt->get_result();
    $options = $optionsResult->fetch_all(MYSQLI_ASSOC);

    // Временный вывод для проверки
    echo "<pre>";
    print_r($task);
    print_r($options);
    echo "</pre>";

    $conn->close();

    // Возвращение данных для дальнейшего использования в JSON
    return [
        'question_text' => $task['question_text'],
        'options' => $options
    ];
}



function playRound($taskId) {
    $taskData = getTaskWithOptions($taskId);

    // Вывод сюжета
    echo $taskData['question_text'] . "\n\n";

    $totalEarnings = 0;

    // Вывод вариантов расписания для распределения времени
    foreach ($taskData['options'] as $option) {
        echo $option['option_text'] . "\n";
        // Предполагаем, что пользователь вводит число часов для каждого предмета
        $hours = readline("Введите количество часов, которое вы хотите уделить на каждый предмет (через запятую): ");
        $hoursArray = explode(',', $hours); // Разделяем введенные часы

        // Обработка введенных часов
        foreach ($hoursArray as $index => $time) {
            $time = trim($time);
            if (is_numeric($time) && $time >= 2) {
                $totalEarnings += 500; // Если ученик уделяет 2 часа или более, он получает "5"
            }
        }
    }

    echo "Вы заработали {$totalEarnings} рублей за учебную неделю.\n";
}




// Функция для вычисления заработанных денег в зависимости от распределения времени
function calculateEarnings($timeDistributions) {
    $earnings = 0;
    foreach ($timeDistributions as $subject => $time) {
        // Допустим, если ученик потратил более 2 часов на предмет, он гарантированно получит "5"
        if ($time >= 2) {
            $earnings += 500;
        }
    }
    return $earnings;
}

// Тестирование раунда
// playRound(1); // Замените 1 на ID задания, который вы хотите тестировать
?>

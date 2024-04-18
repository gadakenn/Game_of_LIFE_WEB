<?php

class Game {
    private $user;
    private $rounds = [];
    private $currentRoundIndex = 1;
    public $game_name;

    public function __construct(User $user, $game_name) {
        $this->user = $user;
        $this->game_name = $game_name;

    }

    public function addUser(User $user) {
        $this->user = $user;
    }

    public function addRound(Round $round) {
        $this->rounds[] = $round;
    }

    public function playRound($roundNumber, $data) {
        if (isset($this->rounds[$roundNumber])) {
            $this->rounds[$roundNumber]->play($this->user, $data);
            return $this->rounds[$roundNumber]->getResult();
        } else {
            throw new Exception("Round not found.");
        }
    }
    public function getCurrentRound() {
        if (isset($this->rounds[$this->currentRoundIndex])) {
            return $this->rounds[$this->currentRoundIndex];
        } else {
            throw new Exception("Current round not found.");
        }
    }

    // Метод для установки текущего раунда
    public function setCurrentRoundIndex($index) {
        if (isset($this->rounds[$index])) {
            $this->currentRoundIndex = $index;
        } else {
            throw new Exception("Round at index {$index} not found.");
        }
    }

    // Метод для получения индекса текущего раунда 
    public function getCurrentRoundIndex() {
        return $this->currentRoundIndex;
    }

    // Метод для перехода к следующему раунду 
    public function nextRound() {
        $this->currentRoundIndex++;

    }
    public function getRounds() {
        return $this->rounds;
    }

    public function getUser() {
        return $this->user;
    }

}

class User {
    private $name;
    private $balance;
    private $id;
    public function __construct($name, $id) {
        $this->name = $name;
        $this->id = $id;
        $this->balance = 12000;
    }

    public function earnMoney($amount) {
        $this->balance += $amount;
    }

    public function getBalance() {
        return $this->balance;
    }

    public function getName() {
        return $this->name;
    }
    
    public function getId() {
        return $this->id;
    }

}

abstract class Round {
    protected $questions = [];
    protected $result;

    public function addQuestion(Question $question) {
        $this->questions[] = $question;
    }

    abstract public function play(User $user, $data);

    public function getResult() {
        return $this->result;
    }
}

class Question {
    protected $questionText;
    protected $answers; // Массив ответов или другая структура, в зависимости от типа вопроса

    public function __construct($questionText) {
        $this->questionText = $questionText;
        $this->answers = [];
    }

    public function addAnswer($answer) {
        $this->answers[] = $answer;
    }

    public function getQuestionText() {
        return $this->questionText;
    }

    public function getAnswers() {
        return $this->answers;
    }
}


class SchoolWeekRound extends Round {
    private $probabilities = [
        'option_1' => [60, 70, 80, 90, 100], // Вероятности для понедельника
        'option_2' => [65, 75, 85, 95, 100], // Вероятности для вторника
        'option_3' => [70, 80, 90, 95, 100], // и т.д.
        'option_4' => [75, 85, 90, 95, 100],
        'option_5' => [80, 85, 90, 95, 100]
    ];

    private $maxHours = 20;

    public function play(User $user, $data) {
       

        $data = array_slice($data, 0, 5);
        $totalHoursSpent = array_sum($data);
       
        if ($totalHoursSpent > $this->maxHours) {
            $this->result = ['error' => 'Превышено максимальное количество часов'];
            return;
        }

        $totalEarnings = 0;
        foreach ($data as $day => $hoursSpent) {
            if (!array_key_exists($day, $this->probabilities)) {
                continue;
            }

            if ($hoursSpent < 2) {
                continue;
            }

            $randomProbabilityIndex = array_rand($this->probabilities[$day]);
            $randomProbability = $this->probabilities[$day][$randomProbabilityIndex];

            $randomNumber = mt_rand(1, 100);
            if ($randomNumber <= $randomProbability) {
                $totalEarnings += 500; 
            }
        }

       
        $user->earnMoney($totalEarnings);
        
        $this->result = ['totalEarnings' => $totalEarnings];
    }
}


class StockBondsDeps extends Round {
    private $stocksPercentage;
    private $bondsPercentage;
    private $depositsPercentage;
    private $maxPerc = 100;
    private $initialCapital;

    public function adjustAndRoundPercentages() {
        // Рассчитываем сумму, оставшуюся после вычета депозитов
        $remainingPercentage = $this->maxPerc - $this->depositsPercentage;

        // Рассчитываем новые проценты для акций и облигаций относительно оставшейся суммы
        $adjustedStocksPercentage = $this->stocksPercentage / $remainingPercentage * $this->maxPerc;
        $adjustedBondsPercentage = $this->bondsPercentage / $remainingPercentage * $this->maxPerc;

        // Округляем до ближайшего числа, кратного 10
        $roundedStocksPercentage = round($adjustedStocksPercentage / 10) * 10;
        $roundedBondsPercentage = round($adjustedBondsPercentage / 10) * 10;

        // Корректировка округления, чтобы сумма была равна 100%
        $totalRounded = $roundedStocksPercentage + $roundedBondsPercentage;
        if ($totalRounded > $this->maxPerc) {
            $roundedBondsPercentage -= 10;
        } elseif ($totalRounded < $this->maxPerc) {
            $roundedBondsPercentage += 10;
        }

        // Устанавливаем новые проценты
        $this->stocksPercentage = $roundedStocksPercentage;
        $this->bondsPercentage = $roundedBondsPercentage;
    }

    public function play(User $user, $data) {
        // $data = json_decode(file_get_contents('php://input'), true);
        $this->stocksPercentage = $data['option_6'];
        $this->bondsPercentage = $data['option_7'];
        $this->depositsPercentage = $data['option_8'];
        $this->initialCapital = $data['option_9'];

        $totalPercentage = $this->stocksPercentage + $this->bondsPercentage + $this->depositsPercentage;

        if ($this->stocksPercentage + $this->bondsPercentage !== 100) {
            $this->adjustAndRoundPercentages();
        }

        if ($totalPercentage > $this->maxPerc) {
            $this->result = ['error' => 'В сумме проценты должны давать 100!'];
            return;
        }
        if ($this->initialCapital > $user->getBalance()) {
            $this->result = ['error' => 'Использованная сумма превышает ваш нынешний капитал!'];
            return;
        }

        $stocksBondsReturn = $this->getPortfolioReturn($this->stocksPercentage, $this->bondsPercentage);
        $depositsReturn = $this->depositsPercentage;

        $futureValue = $this->initialCapital * (
            ($stocksBondsReturn / 100) * ($this->stocksPercentage / 100) +
            ($stocksBondsReturn / 100) * ($this->bondsPercentage / 100) +
            ($depositsReturn / 100) * ($this->depositsPercentage / 100)
        );
        $user->earnMoney($futureValue);
        
        $this->result = ['totalEarnings' => $futureValue];
        
    }

    private function getPortfolioReturn($stocksPercentage, $bondsPercentage) {
        $combinedReturns = [
            "0/100" => 396.3,
            "10/90" => 509.4,
            "20/80" => 631.4,
            "30/70" => 758.7,
            "40/60" => 886.7,
            "50/50" => 1009.6,
            "60/40" => 1120.5,
            "70/30" => 1211.5,
            "80/20" => 1274.1,
            "90/10" => 1299.6,
            "100/0" => 1279.2
        ];
        $key = "{$stocksPercentage}/{$bondsPercentage}";

        return $combinedReturns[$key] ?? 0;
    }
}

?>

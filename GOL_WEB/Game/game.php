<?php

class Game {
    private $user;
    private $rounds = [];
    private $currentRoundIndex = 0;
    public $game_name;
    public $game_id;

    public function __construct(User $user, $game_name) {
        $this->user = $user;
        $this->game_name = $game_name;
    }

    public function addUser(User $user) {
        $this->user = $user;
    }

    public function addRound($round) {
        $this->rounds[] = $round;
    }

    public function getRounds() {
        return $this->rounds;
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
        $this->currentRoundIndex = $this->currentRoundIndex + 1;
    }

    public function getUser() {
        return $this->user;
    }

}

class User {
    private $name;
    private $balance;
    private $id;

    private $salary = [];

    private $spending = [];

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

    public function defaultBalance() {
        $this->balance = 12000;
    }

    public function getSalary() {
        return $this->salary;
    }

    public function getSpending() {
        return $this->spending;
    }

    public function setSpending($type, $num) {
        $this->spending[$type] = $num;
    }

    public function setSalary($type, $num) {
        $this->salary[$type] = $num;
    }

    public function clearUserInfo() {
        $this->defaultBalance();
        $this->salary = [];
        $this->spending = [];
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
        
        $this->result = ['message' => $totalEarnings];
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
        
        $this->result = ['message' => $futureValue];
        
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

class SummerBusinessRound extends Round {
    private $businessOptions;
    private $weatherForecast;
    private $initialCapital;

    public function __construct() {

        $this->businessOptions = [
            'option_10' => [
                'name' => 'lemonade_stand',
                'initial_cost' => 100,
                'profit_per_good_day' => 30,
                'profit_per_bad_day' => 10,
                'weather_coefficient' => 1.2 // Более высокая прибыль в жаркие дни
            ],
            'option_11' => [
                'name' => 'bike_car_wash',
                'initial_cost' => 150,
                'profit_per_good_day' => 10, // Уменьшенная прибыль за хороший день
                'profit_per_bad_day' => 20, // Повышение прибыли в дни после дождей
                'weather_coefficient' => 0.5 // Понижающий коэффициент из-за плохой погоды
            ],
            'option_12' => [
                'name' => 'home_baking',
                'initial_cost' => 200,
                'profit_per_good_day' => 50,
                'profit_per_bad_day' => 20,
                'weather_coefficient' => 1 // Не зависит от погоды
            ]
        ];


    
        $this->weatherForecast = $this->getWeatherForecast(); // Предполагается, что это метод возвращает прогноз погоды
    }

    public function play(User $user, $data) {
        // Предполагаем, что в $data приходят данные в формате:
        // ['selected_business' => 'lemonade_stand', 'investment' => 150, 'advertising' => 50, ...]

        $selectedBusiness = $data['selected_business'] ?? null; // выбранный тип бизнеса
        $investment = $data['option_13'] ?? 0; // 
        $advertising = $data['option_14'] ?? 0;
        $extraCosts = $data['option_15'] ?? 0;

        $totalInvestment = $investment + $advertising + $extraCosts;

        if (!$selectedBusiness || !array_key_exists($selectedBusiness, $this->businessOptions)) {
            $this->result = ['error' => 'Не указан или не найден выбранный вид бизнеса.',
        'data' => json_encode($data)];
            return;
        }

        if ($totalInvestment > $user->getBalance()) {
            $this->result = ['error' => 'Недостаточно средств для начала выбранного бизнеса.'];
            return;
        }

        // Снимаем общую инвестицию с баланса пользователя
        $user->earnMoney(-$totalInvestment);

        // Расчет прибыли
        $business = $this->businessOptions[$selectedBusiness];
        $daysCount = 30; // Количество дней для ведения бизнеса
        $goodWeatherDays = $this->countGoodWeatherDays($daysCount);

        // Расчет прибыли с учетом рекламы и погоды
        $profitMultiplier = $this->calculateProfitMultiplier($advertising);
        $goodDayProfit = $business['profit_per_good_day'] * $goodWeatherDays * $business['weather_coefficient'] * 0.3;
        $badDayProfit = $business['profit_per_bad_day'] * ($daysCount - $goodWeatherDays) * $business['weather_coefficient'] * 0.3;

        $totalEarnings = $investment * ($goodDayProfit + $badDayProfit) * (1 / $profitMultiplier) - $extraCosts * ($daysCount - $goodWeatherDays);
        $user->earnMoney($totalEarnings);

        $this->result = [
            'message' => "Вы заработали {$totalEarnings} руб.\nСолнечных дней: {$goodWeatherDays}.",
            'goodWeatherDays' => $goodWeatherDays,
            'investment' => $investment,
            'advertising' => $advertising,
            'extraCosts' => $extraCosts
        ];
    }

    private function calculateProfitMultiplier($advertising) {
       
        $baseEffectiveness = 1; // Базовый уровень эффективности без рекламы
        $effectivenessCap = 2; // Максимальный множитель рекламы
        $diminishingReturn = 0.1; // Коэффициент убывающей отдачи
    
        // Рассчитываем эффективность рекламы с учетом убывающей отдачи
        $advertisingEffectiveness = $baseEffectiveness + (1 - exp(-$diminishingReturn * $advertising));
    
        // Обеспечиваем, что эффективность не превышает установленный максимум
        $advertisingMultiplier = min($advertisingEffectiveness, $effectivenessCap);
    
        return $advertisingMultiplier;
    }
    

    private function getWeatherForecast() {
        // Здесь можно интегрировать реальный API прогноза погоды или использовать заранее заданные данные
        return mt_rand(0, 20); // Количество хороших погодных дней из 30
    }

    private function countGoodWeatherDays($daysCount) {
        // Метод для расчета количества хороших погодных дней
        return $this->weatherForecast;
    }
}


class StartupInvestmentRound extends Round {
    private $startupOptions;
    private $initialCapital;

    public function __construct() {
        $this->startupOptions = [
            'option_19' => [ // умные чехлы для телефонов
                'market_volume' => 5000000000,
                'market_growth' => 0.20,
                'risk_level' => 'high',
                'ROI' => 0.35, // 35%
                'CAC' => 50, // $50
                'LTV' => 600
            ],
            'option_20' => [ // эко технологии
                'market_volume' => 3000000000,
                'market_growth' => 0.30,
                'risk_level' => 'high',
                'ROI' => 0.50, // 50%
                'CAC' => 70, // $70
                'LTV' => 500
            ],
            'option_21' => [ // онлайн-школа
                'market_volume' => 2000000000,
                'market_growth' => 0.25,
                'risk_level' => 'high',
                'ROI' => 0.20, // 20%
                'CAC' => 30, // $30
                'LTV' => 700
            ]
        ];

        $this->initialCapital = 1000000; // Предположим, что начальный капитал игрока составляет $1,000,000
    }

    public function play(User $user, $data) {
        $selectedStartup = $data['selected_business'] ?? null;
        $investmentAmount = $data['option_22'] ?? 0;
    
        if (!$selectedStartup || !array_key_exists($selectedStartup, $this->startupOptions)) {
            $this->result = ['error' => 'Не указан или не найден выбранный стартап.',
                             'data' => json_encode($data)];
            return;
        }
    
        if ($investmentAmount > $user->getBalance()) {
            $this->result = ['error' => 'Недостаточно средств для инвестиций.'];
            return;
        }
    
        $user->earnMoney(-$investmentAmount);
    
        $startup = $this->startupOptions[$selectedStartup];
        $expectedGrowth = $investmentAmount * $startup['market_growth'];
        $expectedROI = $investmentAmount * $startup['ROI'];
    
        // Рассчитываем, сколько клиентов мы можем привлечь с данной инвестицией
        $totalCustomers = $investmentAmount / $startup['CAC'];
        // Рассчитываем общий LTV от всех клиентов
        $totalLTV = $totalCustomers * $startup['LTV'];
        // Рассчитываем общую прибыль с учетом CAC и LTV
        $totalProfitFromCustomers = $totalLTV - ($totalCustomers * $startup['CAC']);
    
        // Суммируем ожидаемый рост и прибыль от клиентов
        $totalExpectedProfit = $expectedGrowth + $totalProfitFromCustomers;
    
        // Корректируем прибыль на основе риска
        $riskAdjustedProfit = $this->adjustForRisk($totalExpectedProfit, $startup['risk_level']);
    
        $user->earnMoney($riskAdjustedProfit);
    
        $this->result = [
            'expectedGrowth' => $expectedGrowth,
            'expectedROI' => $expectedROI,
            'totalCustomers' => $totalCustomers,
            'totalLTV' => $totalLTV,
            'totalProfitFromCustomers' => $totalProfitFromCustomers,
            'message' => "Ваш стартап приносит {$riskAdjustedProfit}.\nОжидаемый ROI: {$expectedROI}.\nКоличество покупателей: {$totalCustomers}.\n",
            'selectedStartup' => $selectedStartup,
            'investmentAmount' => $investmentAmount
        ];
    }
    

    private function adjustForRisk($growth, $riskLevel) {
        switch ($riskLevel) {
            case 'high':
                $fluctuation = mt_rand(-50, 50) / 100.0;
                break;
            case 'medium':
                $fluctuation = mt_rand(-30, 30) / 100.0;
                break;
            case 'low':
                $fluctuation = mt_rand(-10, 10) / 100.0;
                break;
            default:
                $fluctuation = 0;
                break;
        }
        return $growth * (1 + $fluctuation);
    }
}


class BetsRound extends Round {
    private $matchOptions;

    public function __construct() {
        // Определяем возможные спортивные матчи и их коэффициенты
        $this->matchOptions = [
            'option_26' => ['match' => 'Спартак vs Динамо', 'coefficient' => 2.0], // Победа Спартака
            'option_27' => ['match' => 'Спартак vs Динамо', 'coefficient' => 3.2], // Ничья
            'option_28' => ['match' => 'Спартак vs Динамо', 'coefficient' => 3.8], // Победа Динамо
            'option_30' => ['match' => 'Зенит vs ЦСКА', 'coefficient' => 1.8], // Победа Зенита
            'option_31' => ['match' => 'Зенит vs ЦСКА', 'coefficient' => 3.5], // Ничья
            'option_32' => ['match' => 'Зенит vs ЦСКА', 'coefficient' => 4.0]  // Победа ЦСКА
        ];
    }

    public function play(User $user, $data) {
        // Определяем выбранный исход из входных данных
        $selectedOption = $data['selected_business'] ?? null;
        $betAmount = $data['option_33'] ?? 0;

        // Проверяем корректность введенных данных
        if (!$selectedOption || !array_key_exists($selectedOption, $this->matchOptions)) {
            $this->result = ['error' => 'Неверно выбран исход или он отсутствует.',
                             'data' => json_encode($data)];
            return;
        }

        if ($betAmount > $user->getBalance()) {
            $this->result = ['error' => 'Недостаточно средств для ставки.'];
            return;
        }

        // Вычитаем сумму ставки из баланса пользователя
        $user->earnMoney(-$betAmount);

        // Получаем данные о матче и коэффициенте для выбранного исхода
        $matchInfo = $this->matchOptions[$selectedOption];
        $winningCoefficient = $matchInfo['coefficient'];
        $winningAmount = $betAmount * $winningCoefficient;

        // Случайно определяем исход игры (50% шанс выиграть/проиграть)
        $won = (mt_rand(0, 1) == 1);

        // Обновляем результат в зависимости от выигрыша или проигрыша
        if ($won) {
            $user->earnMoney($winningAmount);
            $this->result = [
                'message' => "Вы выиграли ставку на матч: {$matchInfo['match']}.\nСтавка принесла вам {$winningAmount} руб.",
                'totalEarnings' => $winningAmount
            ];
        } else {
            $this->result = [
                'message' => "Вы проиграли ставку на матч: {$matchInfo['match']}.\nУбыток {$betAmount}"
            ];
        }

        // Дополнительные детали о ставке
        $this->result['selectedOption'] = $selectedOption;
        $this->result['betAmount'] = $betAmount;
    }
}






// $user = new User("John Doe", 1);
// $user->earnMoney(500000); 

// $round = new StartupInvestmentRound();

// // Данные для тестирования
// $testData = [
//     'selected_startup' => 'smart_accessories',
//     'investment_amount' => 500000  // Пользователь инвестирует $500,000
// ];

// // Запуск раунда с тестовыми данными
// $round->play($user, $testData);

// // Вывод результатов
// echo "Results:\n";
// print_r($round->getResult());

// echo "User's final balance: " . $user->getBalance() . "\n";

?>



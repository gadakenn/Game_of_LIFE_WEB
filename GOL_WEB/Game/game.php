<?php

class Game {
    private $user;
    private $rounds = [];
    private $currentRoundIndex = 0;
    public $game_name;
    public $game_id;


    public $current_age = 12;

    private $storyToGPT = '';

    private $rounds_for_gpt = [];

    public $flag = true;
    private $numRounds;

    private $tmp_answer = [];




    private $roundClasses = [
        0 => ['QuestionsRound'],
        1 => ['SummerBusinessRound', 'SchoolWeekRound'],
        2 => ['BetsRound'],
        3 => ['EducationRound'],
        4 => ['StockBondsDeps'],
        5 => ['StartupInvestmentRound', 'SelfTaughtBusinessRound', 'CollegeClubRound'],
        6 => ['CareerRound'],
        7 => ['HousingDecisionRound'],
        8 => ['gpt'],
        9 => ['gpt'],
        10 => ['gpt'],
        11 => ['gpt'],
        12 => ['gpt'],
        13 => ['gpt'],
        
    ];
    public function __construct(User $user, $game_name, $numRounds = 7) {

        $this->user = $user;
        $this->game_name = $game_name;
        for ($i = 0; $i < $numRounds; $i++) {
            // Рандомно выбираем набор раундов
            $roundClass = $this->roundClasses[$i][array_rand($this->roundClasses[$i])];
            
            $this->addRound($roundClass);
        }
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

    public function setRounds($rounds) {
        $this->rounds = $rounds;
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
        $this->current_age = $this->current_age + 3;
    }

    public function getUser() {
        return $this->user;
    }

    public function changeRound(array $find, $replace) {
            /**
     * Подменяет раунд из массива возможных раундов для определенного события на раунд, который определился в зависимости
     * от выбора игрока
     *
     * @param array $find Массив с раундами, которые мы ищем в сформировавшейся судьбе
     * @param string $replace Раунд для подмены
     */
        $initialRounds = $this->getRounds();
    
        foreach ($initialRounds as $index => $round) {
            if (in_array($round, $find)) {
                $initialRounds[$index] = $replace;
                break; 
            }
        }
    
        // Устанавливаем обновленный массив раундов
        $this->setRounds($initialRounds);
        return $this->getRounds();
    }

    public function addRoundGPT($taskNum, $taskText, $role='assistant', $info=false, $assist_ans=false) {
        if ($info) {
            $this->rounds_for_gpt[] = [
                "role" => $role,
                "content" => "Раунд номер {$taskNum}. Инициализация игры. {$taskText}"
            ];
        } else {
            if ($role == 'assistant') {
                if ($assist_ans) {
                    $this->rounds_for_gpt[] = [
                        "role" => $role,
                        "content" => "Раунд номер {$taskNum}. Результат раунда: {$taskText}"
                    ];
                } else {
                    $this->rounds_for_gpt[] = [
                        "role" => $role,
                        "content" => "Раунд номер {$taskNum}. Текст раунда: {$taskText}"
                    ];
                }
            } else {
                $this->rounds_for_gpt[] = [
                    "role" => $role,
                    "content" => "Раунд номер {$taskNum}. Ответ игрока: {$taskText}"
                ];
            }
        }
    }

    public function holdAnswer($taskNum, $taskText, $push=false) {
        if ($push) {
            $this->makeInfoToGPT($this->tmp_answer['content']);
        } else {
            $this->tmp_answer = [
                "taskNum" => $taskNum,
                "content" => $taskText
            ];
        }
    }

    public function getRoundGPT() {
        return $this->rounds_for_gpt;
    }

    private function makeInfoToGPT($text) {
        $this->storyToGPT = $this->storyToGPT . "{$text}";
    }

    public function getStory() {
        return $this->storyToGPT;
    }

}


class User {
    private $name;

    private $balance;
    private $id;

    private $salary = [];

    private $spending = [];
    private $professions_available = [];

    private $tmp_earning = 0;

    public function __construct($name, $id) {
        $this->name = $name;
        $this->id = $id;
        $this->balance = 0;
    }

    public function earnMoney($amount=0, $execute=false) {
        if ($execute) {
            $this->balance += $this->tmp_earning;
        } else {
            $this->tmp_earning = $amount;
        }
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
        $this->balance = 0;
    }

    public function getSalary() {
        return $this->salary;
    }

    public function getSpending() {
        return $this->spending;
    }

    public function appendSpending($type, $num, $counter) {
        $this->spending[$type] = [$num, $counter];
    }

    public function appendSalary($type, $num, $counter) {
        $this->salary[$type] = [$num, $counter];
    }

    public function setSpending($updatedSpending) {
        $this->spending = $updatedSpending;
    }

    public function setSalary($updatedSalary) {
        $this->salary = $updatedSalary;
    }

    public function clearUserInfo() {
        $this->defaultBalance();
        $this->salary = [];
        $this->spending = [];
    }

    public function setProfessionsAvailable(array $professions) {
        $this->professions_available = $professions;
    }

    public function getProfessionsAvailable() {
        return $this->professions_available;
    }

    public function salarySpending() {
        /**
         * Вычитает и добавляет к счёту игры деньги в зависимости от того, в каком из массивов находится статья расхода/заработка
         */
        $totalAmountMoney = 0;

        $plusMoney = $this->getSalary(); 
        $minusMoney = $this->getSpending();
    
        // Обработка доходов
        foreach ($plusMoney as $key => $values) {
            if ($values[1] > 0) { // Проверяем, что счетчик больше 0
                $totalAmountMoney += $values[0]; // Прибавляем сумму к totalAmountMoney
                $plusMoney[$key][1]--; // Уменьшаем Уменьшаем счётчик 
            }
        }
    
        // Обработка расходов
        foreach ($minusMoney as $key => $values) {
            if ($values[1] > 0) { // Проверяем, что выплаты еще не закончились
                $totalAmountMoney -= $values[0]; // Вычитаем сумму из totalAmountMoney
                $minusMoney[$key][1]--; // Уменьшаем счётчик 
            }
        }
    
        // Обновляем информацию о доходах и расходах
        $this->setSalary($plusMoney);
        $this->setSpending($minusMoney);
    
        $this->earnMoney($totalAmountMoney);
    }

}

abstract class Round {
    protected $questions = [];
    protected $result;


    abstract public function play(User $user, $data);
                /**
     * Основная функция для каждого раунда, которая выполняет расчеты на основе выбора игрока и затем возвращает сообщение об итогах раунда
     * от выбора игрока
     *
     * @param User $user Информация о пользователе
     * @param array $data Информация о выборе пользователя
     */

    public function getResult() {
        return $this->result;
    }
}


class MortgageCalculator {
    /**
     * Рассчитывает ежемесячный платёж по ипотеке.
     *
     * @param float $propertyPrice Стоимость недвижимости
     * @param float $initialPayment Первоначальный взнос
     * @param int $numberOfRounds Количество раундов для выплаты ипотеки (максимум 6)
     * @param float $interestRate Годовая процентная ставка
     * @return float Ежемесячный платеж
     */
    public function calculateMonthlyPayment($propertyPrice, $initialPayment, $interestRate) {
        $loanAmount = $propertyPrice - $initialPayment;  // Основная сумма кредита
        $monthlyInterestRate = $interestRate / 12 / 100;  // Месячная процентная ставка
        $numberOfRounds = 12;  // Фиксированное количество раундов для выплаты

        // Формула аннуитетного платежа
        if ($monthlyInterestRate == 0) {
            $monthlyPayment = $loanAmount / $numberOfRounds;
        } else {
            $monthlyPayment = $loanAmount * 
                ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $numberOfRounds)) /
                (pow(1 + $monthlyInterestRate, $numberOfRounds) - 1);
        }

        return $monthlyPayment;
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
        $counter = 0;
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
                $counter += 1;
            }
        }

       
        $user->earnMoney($totalEarnings);
        if ($totalEarnings) {
            $this->result = ['message' => "Получено пятёрок: $counter \nВы заработали: $totalEarnings"];
        } else {
            $this->result = ['message' => "Вы не получили ни одной пятёрки и, к сожалению, ничего не заработали..."];
        }
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

        if ($totalPercentage !== 100) {
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
        
        $this->result = ['message' => "В течении года, вложенная сумма приносит Вам доходность $stocksBondsReturn %\nВаш вложенный капитал приности сверху ещё $futureValue руб."];
        
    }

    private function getPortfolioReturn($stocksPercentage, $bondsPercentage) {
        $combinedReturns = [
            "0/100" => 22.9,
            "10/90" => 25.92,
            "20/80" => 29.05,
            "30/70" => 32.05,
            "40/60" => 35.05,
            "50/50" => 38.13,
            "60/40" => 41.22,
            "70/30" => 44.36,
            "80/20" => 47.44,
            "90/10" => 50.46,
            "100/0" => 50.46
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
                'name' => 'Торговля лимонадом',
                'initial_cost' => 100,
                'profit_per_good_day' => 15,
                'profit_per_bad_day' => 10,
                'weather_coefficient' => 1.2 // Более высокая прибыль в жаркие дни
            ],
            'option_11' => [
                'name' => 'Ручная мойка машин',
                'initial_cost' => 150,
                'profit_per_good_day' => 10, // Уменьшенная прибыль за хороший день
                'profit_per_bad_day' => 10, // Повышение прибыли в дни после дождей
                'weather_coefficient' => 0.5 // Понижающий коэффициент из-за плохой погоды
            ],
            'option_12' => [
                'name' => 'Приготовление домашней выпечки',
                'initial_cost' => 200,
                'profit_per_good_day' => 50,
                'profit_per_bad_day' => 10,
                'weather_coefficient' => 1 // Не зависит от погоды
            ]
        ];


    
        $this->weatherForecast = $this->getWeatherForecast(); 
    }

    public function play(User $user, $data) {


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

        $totalEarnings = $investment * ($goodDayProfit + $badDayProfit) * (1 / $profitMultiplier) - $extraCosts * 0.5 * ($daysCount - $goodWeatherDays);
        $user->earnMoney($totalEarnings);

        $this->result = [
            'message' => "Вы заработали {$totalEarnings} руб.\nСолнечных дней в месяце: {$goodWeatherDays}.",
            'goodWeatherDays' => $goodWeatherDays,
            'investment' => $investment,
            'advertising' => $advertising,
            'extraCosts' => $extraCosts,
            'info_to_gpt' => "Открывал летний мини-бизнес {$business['name']} и заработал на нём {$totalEarnings}."
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
                'LTV' => 600,
                'name' => 'Умные чехлы для телефонов'
            ],
            'option_20' => [ // эко технологии
                'market_volume' => 3000000000,
                'market_growth' => 0.30,
                'risk_level' => 'high',
                'ROI' => 0.50, // 50%
                'CAC' => 70, // $70
                'LTV' => 500,
                'name' => 'Эко-технологии'
            ],
            'option_21' => [ // онлайн-школа
                'market_volume' => 2000000000,
                'market_growth' => 0.25,
                'risk_level' => 'high',
                'ROI' => 0.20, // 20%
                'CAC' => 30, // $30
                'LTV' => 700,
                'name' => 'Онлайн-школа'
            ]
        ];


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
            'investmentAmount' => $investmentAmount,
            'info_to_gpt' => "В унивеситете занялся стартапом вместе с одногруппниками, выбрав тему {$startup['name']}, что принесло ему {$riskAdjustedProfit} c ROI: {$expectedROI}. "
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
            'option_26' => ['match' => 'Спартак vs Динамо', 'coefficient' => 3.8], // Победа Спартака
            'option_27' => ['match' => 'Спартак vs Динамо', 'coefficient' => 3.2], // Ничья
            'option_28' => ['match' => 'Спартак vs Динамо', 'coefficient' => 1.5], // Победа Динамо
            'option_30' => ['match' => 'Зенит vs ЦСКА', 'coefficient' => 2.1], // Победа Зенита
            'option_31' => ['match' => 'Зенит vs ЦСКА', 'coefficient' => 3.5], // Ничья
            'option_32' => ['match' => 'Зенит vs ЦСКА', 'coefficient' => 2.3]  // Победа ЦСКА
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
    }
}


class EducationRound extends Round {
    private $educationOptions;
    private $availableEducationBusinesses = ['StartupInvestmentRound', 'SelfTaughtBusinessRound', 'CollegeClubRound'];

    public function __construct() {
        $this->educationOptions = [
            'option_34' => ['name' => 'Университет (очное обучение)', 'cost' => 600000, 'expected_salary' => 60000],
            'option_35' => ['name' => 'Колледж (заочное обучение)', 'cost' => 150000, 'expected_salary' => 40000],
            'option_36' => ['name' => 'Онлайн-курсы (удаленное обучение)', 'cost' => 50000, 'expected_salary' => 45000],
            'option_37' => ['name' => 'Самообразование (бесплатно)', 'expected_salary' => 'Зависит от успеха проектов']
        ];
    }

    private function determineProfessions($educationKey) {
        switch ($educationKey) {
            case 'option_34':
                return ['IT-специалист', 'Инженер', 'Менеджер', 'Техник', 'Дизайнер', 'Администратор', 'Программист', 'Веб-дизайнер', 'Маркетолог'];
            case 'option_35':
                return ['Техник', 'Дизайнер', 'Администратор'];
            case 'option_36':
                return ['Копирайтер/Контент-менеджер', 'Веб-дизайнер', 'Маркетолог'];
            case 'option_37':
                return ['Диджитал-иллюстратор', 'Фотограф'];
            default:
                return [];
        }
    }

    private function determineBusinessFuture($educationKey) {
        switch ($educationKey) {
            case 'option_34':
                return 'StartupInvestmentRound';
            case 'option_35':
                return 'CollegeClubRound';
            case 'option_36':
                return 'SelfTaughtBusinessRound';
            case 'option_37':
                return 'SelfTaughtBusinessRound';
            default:
                return 'SelfTaughtBusinessRound';
        }
    }

    public function play(User $user, $data) {
        $selectedEducation = $data['selected_business'] ?? null;

        if (!$selectedEducation || !array_key_exists($selectedEducation, $this->educationOptions)) {
            $this->result = ['error' => 'Неверно выбрано образование или оно отсутствует.', 'data' => json_encode($data)];
            return;
        }

        $education = $this->educationOptions[$selectedEducation];
        $cost = $education['cost'] ?? 0;

        if (isset($cost) && $cost > $user->getBalance()) {
            $this->result = ['error' => 'Недостаточно средств для выбранного варианта образования.'];
            return;
        }

        if ($cost > 0) {
            $user->earnMoney(-$cost);
        }

        $availableProfessions = $this->determineProfessions($selectedEducation);
        $user->setProfessionsAvailable($availableProfessions);

        // Делаем подмену раунда в зависимости от выбора игрока
        if (isset($_SESSION['game'])) {
            $game = unserialize($_SESSION['game']);
            $answer = $game->changeRound($this->availableEducationBusinesses, $this->determineBusinessFuture($selectedEducation));
            $_SESSION['game'] = serialize($game);
            $this->result = [
                'message' => "Вы выбрали {$education['name']}.\nСтоимость обучения: {$cost}.\nОжидаемая зарплата: {$education['expected_salary']}",
                'info_to_gpt' => "После школы в качестве образования выбрал: {$education['name']}. "
            ];
        } else {
            $this->result = [
                'message' => "Ошибка: сессия игры не найдена.",
                'cost' => $cost,
                'expected_salary' => $education['expected_salary']
            ];
        }
    }
}


class CareerRound extends Round {
    private $prof_idxs = [
        "option_38" => "IT-специалист",
        "option_39" => "Инженер",
        "option_40" => "Менеджер",
        "option_41" => "Техник",
        "option_42" => "Дизайнер",
        "option_43" => "Администратор",
        "option_44" => "Копирайтер/Контент-менеджер",
        "option_45" => "Веб-дизайнер",
        "option_46" => "Маркетолог",
        "option_47" => "Диджитал-иллюстратор",
        "option_48" => "Фотограф"
    ];
    
    public function play(User $user, $data) {
        $selectedProfession = $this->prof_idxs[$data['selected_business']] ?? null;
        $availableProfessions = $user->getProfessionsAvailable();

        if (!in_array($selectedProfession, $availableProfessions)) {
            $this->result = ['error' => 'Выбранная профессия недоступна. Пожалуйста, выберите одну из доступных вам профессий (на основе полученного образования).'];
            return;
        }

        $user->appendSalary($selectedProfession, $this->determineSalary($selectedProfession), 100);

        $this->result = [
            'message' => "Поздравляем! Вы начали карьеру в качестве {$selectedProfession}. Ваша начальная зарплата составляет {$this->determineSalary($selectedProfession)} рублей.",
            'profession' => $selectedProfession,
            'salary' => $this->determineSalary($selectedProfession),
            'info_to_gpt' => "Затем начал карьеру в качестве {$selectedProfession}. "
        ];
    }

    private function determineSalary($profession) {
        // Логика для определения зарплаты в зависимости от выбранной профессии
        $salaries = [
            'IT-специалист' => 100000,
            'Инженер' => 80000,
            'Менеджер' => 90000,
            'Техник' => 40000,
            'Дизайнер' => 80000,
            'Администратор' => 30000,
            'Копирайтер/Контент-менеджер' => 45000,
            'Веб-дизайнер' => 40000,
            'Маркетолог' => 38000,
            'Диджитал-иллюстратор' => 30000, 
            'Фотограф' => 20000   
        ];

        return $salaries[$profession] ?? 0;
    }
}


class HousingDecisionRound extends Round {
    private $mortgageCalculator;

    private $db_choices = [
        'option_49' => 'rent',
        'option_50' => 'mortgage'
    ];

    public function __construct() {
        $this->mortgageCalculator = new MortgageCalculator();
    }

    public function play(User $user, $data) {
        $choice = $this->db_choices[$data['selected_business']]; // 'mortgage' или 'rent'
        $initialPayment = $data['option_51'] ?? 0;
        $propertyPrice = 10000000; 
        $interestRate = 18.3; // процентная ставка
        $loanTermRounds = 6; // срок кредита в раундах

        // Проверяем, достаточно ли у пользователя средств для первоначального взноса
        if ($choice == 'mortgage' && $initialPayment > $user->getBalance()) {
            $this->result = ['error' => 'Недостаточно средств для первоначального взноса по ипотеке.'];
            return;
        }


        if ($choice == 'mortgage') {
            $user->earnMoney(-$initialPayment);
            $monthlyExpense = $this->mortgageCalculator->calculateMonthlyPayment($propertyPrice, $initialPayment, $interestRate);
            if ($monthlyExpense > 0) {
                $user->appendSpending('Ипотека', $monthlyExpense, 6);
            }
            $this->result = [
                'message' => "Вы выбрали покупку квартиры в ипотеку. Первоначальный взнос: {$initialPayment} руб.\nПлатеж каждый раунд: {$monthlyExpense}",
                'monthly_expense' => $monthlyExpense,
                'home_ownership' => true,
                'info_to_gpt' => "В качестве жилья взял квартиру в ипотеку с платой каждый раунд в $monthlyExpense."
            ];
        } else {
            $monthlyExpense = $this->currentRent($user);
            $user->appendSpending('Аренда квартиры', $monthlyExpense, 100);
            $this->result = [
                'message' => "Вы выбрали аренду жилья. Ваша арендная плата каждый раунд: $monthlyExpense руб.",
                'monthly_expense' => $monthlyExpense,
                'home_ownership' => false,
                'info_to_gpt' => "В качестве жилья на первое время выбрал аренду с платой каждый раунд в $monthlyExpense руб."
            ];
        }
    }

    private function currentRent($user) {
        // Текущая арендная плата
        return 40000; // Значение для примера
    }
}


class SelfTaughtBusinessRound extends Round {
    private $businessOptions;

    public function __construct() {
        $this->businessOptions = [
            'option_53' => [
                'name' => 'Онлайн-магазин',
                'initial_investment' => 90000,
                'expected_profit' => 450000,
                'risk_level' => 'Высокий',
                'growth_potential' => 'Высокий'
            ],
            'option_54' => [
                'name' => 'Кофейня',
                'initial_investment' => 180000,
                'expected_profit' => 360000,
                'risk_level' => 'Средний',
                'growth_potential' => 'Средний'
            ],
            'option_55' => [
                'name' => 'Студия по созданию контента для социальных сетей',
                'initial_investment' => 135000,
                'expected_profit' => 630000,
                'risk_level' => 'Средний',
                'growth_potential' => 'Высокий'
            ]
        ];
    }

    public function play(User $user, $data) {
        $selectedBusiness = $data['selected_business'] ?? null;
        $marketingInvestment = $data['option_56'] ?? 0;
        $productInvestment = $data['option_57'] ?? 0;
        $operationsInvestment = $data['option_58'] ?? 0;
        $totalInvestment = $marketingInvestment + $productInvestment + $operationsInvestment;

        if (!$selectedBusiness || !array_key_exists($selectedBusiness, $this->businessOptions)) {
            $this->result = ['error' => 'Не выбран или не найден выбранный бизнес.', 'data' => json_encode($data)];
            return;
        }

        $business = $this->businessOptions[$selectedBusiness];

        if ($totalInvestment < $business['initial_investment']) {
            $this->result = ['error' => 'Недостаточно средств для первоначальных вложений.'];
            return;
        }

        $user->earnMoney(-$totalInvestment);

        $efficiency = $this->calculateEfficiency($marketingInvestment, $productInvestment, $operationsInvestment);
        $unexpectedExpenses = mt_rand(0, 1800000);
        $unexpectedIncome = mt_rand(0, 1350000);
        $totalProfit = ($business['expected_profit'] * $efficiency) - $unexpectedExpenses + $unexpectedIncome;

        if ($totalProfit < 0) {
            $this->result = [
                'message' => "Вы выбрали {$business['name']}, но неожиданно возникли расходы в размере {$unexpectedExpenses} руб., что привело к убыткам и закрытию бизнеса.",
                'totalProfit' => $totalProfit,
                'info_to_gpt' => "Выбрал {$business['name']}, но неожиданно возникли расходы в размере {$unexpectedExpenses} руб., что привело к убыткам и закрытию бизнеса. "
            ];
        } else {
            $user->earnMoney($totalProfit);
            $this->result = [
                'message' => "Вы выбрали {$business['name']} и получили прибыль в размере {$totalProfit} руб. после учета всех расходов (непредвиденные расходы: {$unexpectedExpenses}) и доходов.",
                'totalProfit' => $totalProfit,
                'info_to_gpt' => "Выбрал {$business['name']} и получил прибыль в размере {$totalProfit} руб. после учета всех расходов (непредвиденные расходы: {$unexpectedExpenses}) и доходов. "

            ];
        }
    }

    private function calculateEfficiency($marketing, $product, $operations) {
        $total = $marketing + $product + $operations;
        if ($total == 0) return 0;

        $marketingEfficiency = $marketing / $total * 0.4;
        $productEfficiency = $product / $total * 0.4;
        $operationsEfficiency = $operations / $total * 0.2;

        return $marketingEfficiency + $productEfficiency + $operationsEfficiency;
    }
}


class CollegeClubRound extends Round {
    private $clubOptions;

    public function __construct() {
        // Определяем возможные клубы и их параметры
        $this->clubOptions = [
            'option_60' => ['name' => 'Клуб программирования', 'initial_investment' => 50000, 'expected_profit' => 150000, 'difficulty' => 'Средний'],
            'option_61' => ['name' => 'Клуб дизайна', 'initial_investment' => 30000, 'expected_profit' => 100000, 'difficulty' => 'Низкий'],
            'option_62' => ['name' => 'Клуб предпринимателей', 'initial_investment' => 70000, 'expected_profit' => 200000, 'difficulty' => 'Высокий']
        ];
    }

    private function calculateEfficiency($marketing, $equipment, $operations, $idealDistribution) {
        $total = $marketing + $equipment + $operations;
        if ($total == 0) return 0;

        $marketingRatio = $marketing / $total;
        $equipmentRatio = $equipment / $total;
        $operationsRatio = $operations / $total;

        // Идеальное распределение вложений
        $idealMarketingRatio = $idealDistribution['marketing'];
        $idealEquipmentRatio = $idealDistribution['equipment'];
        $idealOperationsRatio = $idealDistribution['operations'];

        // Рассчитываем эффективность на основе близости к идеальному распределению
        $efficiency = 1 - (abs($idealMarketingRatio - $marketingRatio) + abs($idealEquipmentRatio - $equipmentRatio) + abs($idealOperationsRatio - $operationsRatio)) / 3;
        return $efficiency;
    }

    public function play(User $user, $data) {
        $selectedClub = $data['selected_business'] ?? null;
        $marketingInvestment = $data['option_63'] ?? 0;
        $equipmentInvestment = $data['option_64'] ?? 0;
        $operationsInvestment = $data['option_65'] ?? 0;
        $totalInvestment = $marketingInvestment + $equipmentInvestment + $operationsInvestment;

        // Проверяем корректность введенных данных
        if (!$selectedClub || !array_key_exists($selectedClub, $this->clubOptions)) {
            $this->result = ['error' => 'Не выбран или не найден выбранный клуб.', 'data' => json_encode($data)];
            return;
        }

        $club = $this->clubOptions[$selectedClub];

        if ($totalInvestment < $club['initial_investment']) {
            $this->result = ['error' => 'Недостаточно средств для первоначальных вложений.'];
            return;
        }

 
        $idealDistribution = [
            'marketing' => 0.4,
            'equipment' => 0.4,
            'operations' => 0.2
        ];

   
        $user->earnMoney(-$totalInvestment);

        $efficiency = $this->calculateEfficiency($marketingInvestment, $equipmentInvestment, $operationsInvestment, $idealDistribution);
        $expectedProfit = $club['expected_profit'] * $efficiency;

        // Генерация случайных событий для создания непредсказуемости
        $unexpectedExpenses = mt_rand(0, 20000);
        $unexpectedIncome = mt_rand(0, 15000);

        $totalProfit = $expectedProfit - $unexpectedExpenses + $unexpectedIncome;

        if ($totalProfit < 0) {
            $this->result = [
                'message' => "Вы выбрали {$club['name']}, но неожиданно возникли расходы в размере {$unexpectedExpenses} руб., что привело к убыткам и закрытию клуба.",
                'totalProfit' => $totalProfit,
                'info_to_gpt' => "В колледже открыл {$club['name']}, но неожиданно возникли расходы в размере {$unexpectedExpenses} руб., что привело к убыткам и закрытию клуба. "
            ];
        } else {
            $user->earnMoney($totalProfit);
            $this->result = [
                'message' => "Вы выбрали {$club['name']} и получили прибыль в размере {$totalProfit} руб. после учета всех расходов и доходов.",
                'totalProfit' => $totalProfit,
                'info_to_gpt' => "В колледже открыл {$club['name']} и получил прибыль в размере {$totalProfit} руб. после учета всех расходов и доходов. "
            ];
        }
    }
}


class SendAnswerToGPT extends Round {

    public function play(User $user, $data) {
        $game = unserialize($_SESSION['game']);
        $data_new = $game->getRoundGPT();

        $info_to_chatGPT = [
            'ask' => $data['gpt'],
            'story' => $data_new,
            'user_id' => $user->getId()
        ];

        $answer = $this->call_chatgpt_answer($info_to_chatGPT);
        $game->addRoundGPT($game->getCurrentRoundIndex(), $data['gpt'], 'user');
        $game->addRoundGPT($game->getCurrentRoundIndex(), $answer['message']);

        // Извлечение числового значения из ответа
        $profit_or_loss = $this->extractProfitOrLoss($answer['message']);
        if ($profit_or_loss !== null) {
            $user->earnMoney($profit_or_loss);
        }

        $this->result = [
            'message' => $answer['message'],
            'profit_or_loss' => $profit_or_loss
        ];
    }

    private function call_chatgpt_answer($data) {
        $url = 'https://gptservice-production.up.railway.app/process_answer';
    
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
            return null;
        }
    
        return $decoded_result;
    }

    // Функция для извлечения числового значения прибыли или убытка
    private function extractProfitOrLoss($message) {
        // Регулярное выражение для поиска числа
        $pattern = '/Итог:\s*([+-]?[0-9.,]+)\s*(рублей|руб\.|руб)?/';
        if (preg_match($pattern, $message, $matches)) {
            // Удаляем запятые и точки, оставляем только одну точку для дробных чисел
            $value = str_replace([',', ' '], '', $matches[1]);
            $value = str_replace('.', '', substr($value, 0, strrpos($value, '.'))) . substr($value, strrpos($value, '.'));
            return (float)$value;
        }
        return null;
    }
}



class QuestionsRound extends Round {
    private $player_info;
    private $sex_info = [
        'option_67' => 'Мужской',
        'option_68' => 'Женский'
    ];
    
    private $risk_info = [
        'option_71' => 'низкий',
        'option_72' => 'средний',
        'option_73' => 'высокий'
    ];

    // Определение категорий богатства и диапазонов начального капитала в рублях
    private $wealth_categories = [
        'poor' => [100000, 500000, 'Вы родились в бедной семье, где каждый рубль на счету.'],
        'middle_class' => [500000, 2000000, 'Вы родились в семье среднего класса с устойчивым финансовым положением.'],
        'wealthy' => [5000000, 20000000, 'Вы родились в богатой семье с высоким уровнем достатка.']
    ];

    // Функция генерации начального капитала и описания семьи
    private function generateInitialCapitalAndDescription() {
        $wealth_keys = array_keys($this->wealth_categories);
        $random_category = $wealth_keys[array_rand($wealth_keys)];
        $capital_range = $this->wealth_categories[$random_category];
        $initial_capital = rand($capital_range[0], $capital_range[1]);
        $family_description = $capital_range[2];
        return [$initial_capital, $family_description];
    }

    public function play(User $user, $data) {
        $sex = $this->sex_info[$data['sex']];
        $interests = $data['option_69'];
        $country = $data['option_70'];
        $risk_level = $this->risk_info[$data['risk_level']];
        list($initial_capital, $family_description) = $this->generateInitialCapitalAndDescription();

        $info_to_gpt = "Информация об игроке. Пол: $sex. Интересы (описаны в свободной форме): '$interests'. 
        Желаемая страна для игры: $country. Уровень рискованности в финансовых решениях: $risk_level. 
        Начальный капитал: $initial_capital. Описание семьи: $family_description";

        $user->earnMoney($initial_capital);

        $this->result = [
            'message' => "Спасибо за предоставленную информацию! Хорошей игры! $family_description Ваш начальный капитал составляет $initial_capital рублей.",
            'info_to_gpt' => $info_to_gpt,
            'initial_capital' => $initial_capital
        ];
    }
}




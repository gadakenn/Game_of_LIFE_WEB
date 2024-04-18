<?php

class User {
    private $balance;

    public function __construct($balance) {
        $this->balance = $balance;
    }

    public function getBalance() {
        return $this->balance;
    }
}

class Round {
    protected $result;

    public function getResult() {
        return $this->result;
    }
}

class StockBondsDeps extends Round {
    private $stocksPercentage;
    private $bondsPercentage;
    private $depositsPercentage;
    private $maxPerc = 100;
    private $initialCapital;

    // Предполагаем, что $data - это уже декодированный JSON, полученный из другого источника
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
        $this->stocksPercentage = $data['stocks'];
        $this->bondsPercentage = $data['bonds'];
        $this->depositsPercentage = $data['deposits'];
        $this->initialCapital = $data['capital'];

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
        $depositReturn = 127.9; // Допустим, это доходность депозита

        $futureValue = $this->initialCapital * (
            ($stocksBondsReturn / 100) * ($this->stocksPercentage + $this->bondsPercentage) / $this->maxPerc +
            ($depositReturn / 100) * $this->depositsPercentage / $this->maxPerc
        );

        $this->result = ["futureValue" => $futureValue];
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

// Пример использования класса
$user = new User(10000); // Предположим, что у пользователя есть 10000 единиц капитала

$investmentData = [
    'stocks' => 40,
    'bonds' => 40,
    'deposits' => 20,
    'capital' => 1000 // Предположим, что пользователь инвестирует 1000 единиц капитала
];

$round = new StockBondsDeps();
$round->play($user, $investmentData);

// Получаем результаты
$result = $round->getResult();

// Выводим результаты
echo json_encode($result);

?>
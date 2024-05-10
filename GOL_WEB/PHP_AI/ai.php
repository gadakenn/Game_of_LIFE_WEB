<?php

require __DIR__ . '/vendor/autoload.php';

use Orhanerday\OpenAi\OpenAi;


// $open_ai_key = ;


$open_ai = new OpenAi($open_ai_key);

$prompt = $_POST['prompt'];


// Получаем токен для доступа к API

$complete = $open_ai->completion([
    'model' => 'gpt-3.5-turbo-0613',
    'prompt' => 'Напиши текст задания для' . $prompt . ', моделирующего экономическую ситуацию, в которой игрок сможет заработать или потерять деньги, не правильно их вложив или приняв неверное решение',
    'temperature'=> 0.9,
    'max_tokens' => 150,
    'frequency_penalty' => 0,
    'presence_penalty' => 0.6,
]);

var_dump($complete);
// $client = new GuzzleHttp\Client();

// $response = $client->post('https://api.openai.com/v1/engines/davinci/completions', [
//     'headers' => [
//         'Authorization' => 'Bearer ' . $open_ai_key,
//         'Content-Type' => 'application/json'
//     ],
//     'json' => [
//         'prompt' => $prompt,
//         'max_tokens' => 150
//     ]
// ]);

// $body = $response->getBody();
// $content = json_decode($body, true);

// // Ответ от API сохраняем в переменную
// $answer = $content['choices'][0]['text'] ?? 'No response';

// // Выводим результат
// echo $answer;

?>
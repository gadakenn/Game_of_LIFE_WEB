<?php
session_start();
require_once '../../Game/game.php';
if (isset($_SESSION['user'])) {
    $user = unserialize($_SESSION['user']);
    $balance = $user->getBalance(); // метод getBalance() должен быть определен в вашем классе User
} else {
    // Пользователь не авторизован, поэтому делаем редирект на страницу входа или регистрации
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Игра "Распределение времени"</title>
</head>
<body>
    <link rel="stylesheet" href="../static/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <body>
        <div class="blur-background"></div>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                
                <a class="navbar-brand" href="/">
                    <div class="neon"> 
                        <span>G</span><span>a</span><span>m</span><span>e</span>
                        <span>O</span><span>f</span> <span>L</span><span>i</span><span>f</span><span>e</span>
                    </div>
                </a>
     
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
    
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a href="#" class="nav-link active">Рейтинг</a></li>
                        <li class="nav-item"><a href="#" class="nav-link active">Inventory</a></li>
                    </ul>
    
                    <div class="dropdown text-end">
                        <a href="#" class="d-block link-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="true">
                            <img src="https://avatars.githubusercontent.com/u/112871630?s=400&u=0125f8e8e51537bc90e1e0ba307ea29bc0898b4d&v=4" width="64" height="64" class="rounded-circle">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Профиль</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Выйти</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        <div id="earnings">
            <?php if(isset($balance)): ?>
                <p>Текущий баланс: <?php echo htmlspecialchars(number_format($balance, 2, '.', ' ')); ?> руб.</p>
            <?php endif; ?>
        </div>
        
        <div class="h-10 p-5 bg-body-tertiary border rounded-3">
            <div id="game-container">
                <div id="question"></div>
                <form id="answers-form">
                    <!-- Опции будут добавлены сюда динамически -->
                </form>
            </div>
            
            <button class="btn" id="next-round">
                <span>Следующий раунд</span>
            </button>
          </div>    

<div class="theme-switcher-wrapper">
    <label class="theme-switcher-label">
        <input type="checkbox" id="theme-switcher" class="theme-switcher">
        <span class="slider round"></span>
    </label>
</div>
<script src="../static/js/game.js?v=1"></script>
<script src="../static/js/theme-switch.js?v=1"></script>
</body>
</html>
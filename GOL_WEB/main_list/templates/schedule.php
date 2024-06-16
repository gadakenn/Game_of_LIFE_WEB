<?php
session_start();
require_once '../../Game/game.php';
if (isset($_SESSION['user'])) {
    $user = unserialize($_SESSION['user']);
    $balance = $user->getBalance();
    $salary = $user->getSalary();
    $spending = $user->getSpending(); // метод getBalance() должен быть определен в вашем классе User
} else {
    // Пользователь не авторизован, поэтому делаем редирект на страницу входа или регистрации
    header('Location: Registration_page/templates/registration.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Game Of Life</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script>
  window.MathJax = {
    tex: {
      inlineMath: [['$', '$'], ['\\(', '\\)']]
    },
    svg: {
      fontCache: 'global'
    }
  };
</script>
</script>

</head>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="../static/css/style.css">
    <link rel="stylesheet" href="../static/css/inputs_styles.css">

    <body>
        <div id="particles-background" class="blur-background"></div>
        <div id="particles-foreground" class="blur-background"></div>
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container-fluid">
                
                <a class="navbar-brand" href="main_list.php">
                    <div class="neon"> 
                        <span>G</span><span>a</span><span>m</span><span>e</span>
                        <span>O</span><span>f</span> <span>L</span><span>i</span><span>f</span><span>e</span>
                    </div>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                <div class="theme-switcher-wrapper">
                    <label class="switch">
                        <input type="checkbox" id="theme-switcher">
                        <span class="theme-slider round"></span>
                    </label>
                </div>
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
        <div class="news-container">
    <div class="news-heading">Новости</div>
    <div class="news">
        <a href="#" class="news-single" data-analysis="Аналитика: фондовый рынок вскоре рухнет." onclick="showAnalysis(this)">Фондовый рынок скоро упадет</a>
        <a href="#" class="news-single" data-analysis="Аналитика: восстановление экономики идет быстрее ожидаемого." onclick="showAnalysis(this)">Экономика восстанавливается</a>
        <a href="#" class="news-single" data-analysis="Аналитика: новые технологии изменят промышленность." onclick="showAnalysis(this)">Технологические инновации</a>
    </div>
</div>
<div id="analysis-container" class="card" style="display:none;"></div>
    </div>
        <div class="main-container">
        <div id="salary-spending">
            <?php if((isset($salary) && is_array($salary) && !empty($salary)) || (isset($spending) && is_array($spending) && !empty($spending))): ?>
                <?php if(isset($salary) && is_array($salary) && !empty($salary)): ?>
                    <?php foreach($salary as $source => $amount): ?>
                        <p class="salary"><?php echo htmlspecialchars($source); ?>: <?php echo "+" . htmlspecialchars(number_format($amount[0], 2, '.', ' ')); ?> руб.</p>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if(isset($spending) && is_array($spending) && !empty($spending)): ?>
                    <?php foreach($spending as $source => $amount): ?>
                        <p class="spending"><?php echo htmlspecialchars($source); ?>: <?php echo "-" . htmlspecialchars(number_format($amount[0], 2, '.', ' ')); ?> руб.</p>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: white;"><strong>Пока тут никаких доходов и расходов</strong></p>
            <?php endif; ?>
        </div>


            <div id="earnings">
                <?php if(isset($balance)): ?>
                    <p>Текущий баланс: <?php echo htmlspecialchars(number_format($balance, 2, '.', ' ')); ?> руб.</p>
                <?php endif; ?>
            </div>
        </div>



        <div id="game-container">
        <div id="loading" class="box" style="display: none;">
        <div class="vertical-centered-box">
            <div class="content">
                <div class="loader-circle"></div>
                <div class="loader-line-mask">
                <div class="loader-line"></div>
                </div>
                <svg width="36px" height="24px" viewBox="0 0 36 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <!-- <path d="...тут ваш путь SVG..." fill="#FFFFFF"></path> -->
                </svg>
            </div>
            </div>

            </div>
		</div>
            </div>
        <div id="question">

        </div>
        <form id="answers-form">
            <!-- Опции будут добавлены сюда динамически -->
        </form>
    </div>
    <div class="button-container">
        <button class="btn" id="next-round">
            <span>Следующий раунд</span>
        </button>
        <button id="end-game-button" class="btn" style="display: none;"><span>Завершить игру</span></button>
    </div>
    <div id="customModal" class="modalbg">
    <div class="dialog">
        <a href="#close" title="Закрыть" class="close"><img src="../static/css/close-svgrepo-com.svg" alt="reload" /></a>
        <h2 id="modalTitle">Сообщение</h2>
        <p id="modalMessage"></p>
    </div>
    </div>


    
<script src="../static/js/game.js?v=1"></script>
<script src="../static/js/theme-switch.js?v=1"></script>
<script src="../static/js/news_ticker.js"></script>
<script src="../static/js/jquery.particleground.js?v=1"></script>
</body>
</html>

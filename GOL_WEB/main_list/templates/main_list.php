<?php
session_start();
require_once '../../Game/game.php';
$username = 'Гость'; 

if (isset($_SESSION['user'])) {
    $user = unserialize($_SESSION['user']);
    $username = $user->getName(); // Измените этот метод в соответствии с вашим классом User
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game of Life</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link rel="stylesheet" href="../static/css/style.css">

  
</head>
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
            <div class="theme-switcher-wrapper">
                <label class="switch">
                <input type="checkbox" id="theme-switcher">
                    <span class="theme-slider round"></span>
                </label>
            </div>
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
    <div class="news-container">
    <div class="news-heading">Новости</div>
    <div class="news">
        <a href="#" class="news-single" data-analysis="Аналитика: фондовый рынок вскоре рухнет." onclick="showAnalysis(this)">Фондовый рынок скоро упадет</a>
        <a href="#" class="news-single" data-analysis="Аналитика: восстановление экономики идет быстрее ожидаемого." onclick="showAnalysis(this)">Экономика восстанавливается</a>
        <a href="#" class="news-single" data-analysis="Аналитика: новые технологии изменят промышленность." onclick="showAnalysis(this)">Технологические инновации</a>
    </div>
</div>
<div id="analysis-container" class="card" style="display:none;"></div>



    <div class="game-container">
        <div class="title">
            <h2>Добро пожаловать в Game of Life, <?php echo htmlspecialchars($username); ?>!</h2>
        </div>
        
        <button id="start-new-game" class="btn">
            <span>Начать новую игру</span>
        </button>
        <h3>Все игры:</h3>
    </div>
    <main class="table" id="customers_table">
    <section class="table__body">
        <table>
            <thead>
                <tr>
                    <th> ID <span class="icon-arrow">&UpArrow;</span></th>
                    <th> Игрок <span class="icon-arrow">&UpArrow;</span></th>
                    <th> Счёт <span class="icon-arrow">&UpArrow;</span></th>
                    <th> Дата <span class="icon-arrow">&UpArrow;</span></th>
                </tr>
            </thead>
            <tbody>
            <?php
            require_once '../../db_connect/config.php';
            $conn = dbConnect();


            $sql = "SELECT * FROM games ORDER BY date DESC"; // или любой другой критерий сортировки, который вам нужен
            $result = $conn->query($sql);

           
            if ($result->num_rows > 0) {
                // Цикл для обработки каждой строки данных
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['game_name']) . "</td>";
                    echo "<td>" . htmlspecialchars(number_format($row['current_score'], 2, '.', ' ')) . "</td>";
                    // Преобразуйте дату из timestamp, если она хранится в таком формате, или добавьте, как есть, если она в нормальном формате
                    echo "<td>" . htmlspecialchars($row['date']) . "</td>"; // Замените 'date_column' на название вашего столбца с датой
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Игры не найдены</td></tr>";
            }

     
            $conn->close();
            ?>
            </tbody>
        </table>
    </section>
</main>


    <!-- <script src="../static/js/bootstrap.bundle.min.js"></script> -->

    <script src="../static/js/theme-switch.js"></script>
    <script src="../static/js/table_script.js"></script>
    <script src="../static/js/news_ticker.js"></script>
    <script src="../static/js/jquery.particleground.js"></script>

</body>
</html>

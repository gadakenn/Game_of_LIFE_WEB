<?php
class UserHandler {
    private $db;

    public function __construct($dbHost, $dbUser, $dbPass, $dbName) {
        $this->db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function register($nickname, $email, $password) {
        if (mb_strlen($nickname) < 5 || mb_strlen($nickname) > 30) {
            return "Недопустимая длина никнейма. Необходимо от 5 до 30 символов";
        }
        if (mb_strlen($email) < 5) {
            return "Недопустимая длина email. Необходимо от 5 символов";
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO `users` (nickname, email, password) VALUES(?, ?, ?)");
        $stmt->bind_param("sss", $nickname, $email, $passwordHash);
        $stmt->execute();
        if ($stmt->error) {
            return "Ошибка: " . $stmt->error;
        }
        return "Успешно";
    }

    public function signIn($login, $password) {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `nickname` = ? OR `email` = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return "Пользователь не найден.";
        }
        
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {

            return "Успешный вход.";
        } else {
            return "Неверный пароль.";
        }
    }
    

    public function __destruct() {
        $this->db->close();
    }
}


$userHandler = new UserHandler('localhost', 'root', 'root', 'GOL_web_bd');
$response = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'register') {
        $response = $userHandler->register($_POST['nickname'], $_POST['email'], $_POST['password']);
    } elseif ($action === 'signin') {
        $response = $userHandler->signIn($_POST['login'], $_POST['password']);
    }
}

echo $response;

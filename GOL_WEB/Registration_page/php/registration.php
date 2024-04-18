<?php
require_once '../../Game/game.php';
session_start();
class UserHandler {
    private $db;

    public function __construct($dbHost, $dbUser, $dbPass, $dbName) {
        $this->db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function register($nickname, $email, $password) {
        session_unset();
        

        session_start();
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
            return false;
        }
        $userId = $this->db->insert_id; // Получаем ID только что созданной записи
        $user = new User($nickname, $userId); // Создаем объект User с id и именем
        $_SESSION['user'] = serialize($user);
        return true;
    }

    public function signIn($login, $password) {
        session_unset();
       

        session_start();
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `nickname` = ? OR `email` = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $user_id = $user['id'];
            $username = $user['nickname'];
            $user = new User($username, $user_id);
            $_SESSION['user'] = serialize($user);
            return true;
        } else {
            return false;
        }
    }
    

    public function __destruct() {
        $this->db->close();
    }
}


$userHandler = new UserHandler('localhost', 'root', 'root', 'GOL_web_bd');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'register') {
        $success = $userHandler->register($_POST['nickname'], $_POST['email'], $_POST['password']);
        if ($success) {
            header('Location: ../../main_list/templates/main_list.php');
            exit;
        } else {
            $response = "Ошибка при регистрации";
        }
    } elseif ($action === 'signin') {
        $success = $userHandler->signIn($_POST['login'], $_POST['password']);
        if ($success) {
            header('Location: ../../main_list/templates/main_list.php');
            exit;
        } else {
            $response = "Ошибка при входе";
        }
    }
    echo $response;
}

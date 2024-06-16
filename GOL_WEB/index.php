<?php
// index.php
if (isset($_SESSION['user'])) {
    header('Location: main_list/templates/main_list.php');
} else {
    header('Location: Registration_page/templates/registration.html');
}


exit;
?>

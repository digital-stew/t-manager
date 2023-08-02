<?php
session_start();
$db = new SQLite3('../db.sqlite');

//die('logit');
//print_r($_POST);
if (isset($_POST['username']) && isset($_POST['password'])){
    $stm = $db->prepare("SELECT * FROM users WHERE user = ?");
    $stm->bindValue(1,(SQLite3::escapeString($_POST['username'])), SQLITE3_TEXT);
    $res = $stm->execute();
    $user = $res->fetchArray(SQLITE3_ASSOC);
    if (password_verify($_POST["password"], $user["password"])){
        $_SESSION['userName'] = $user['user'];
        echo "login=ok";
      
    }else {

    }

}


?>
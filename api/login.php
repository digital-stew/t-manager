<?php
session_start();
$db = new SQLite3('../db.sqlite');

if (isset($_POST['username']) && isset($_POST['password'])){
    $stm = $db->prepare("SELECT * FROM users WHERE user = ?") or die('sql error');
    $stm->bindValue(1,(SQLite3::escapeString($_POST['username'])), SQLITE3_TEXT);
    $res = $stm->execute() or die('sql error');
    $user = $res->fetchArray(SQLITE3_ASSOC);
    if (password_verify($_POST["password"], $user["password"])){
        $_SESSION['userName'] = $user['user'];
        echo "login=ok";
      
    }else {
        echo "login=false";
    }

}

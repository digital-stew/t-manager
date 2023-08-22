<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';

class Auth extends Database
{
    function login(string $userName, string $password)
    {
        $stm = $this->db->prepare("SELECT * FROM users WHERE user = ?");
        $stm->bindValue(1, $userName, SQLITE3_TEXT);
        $res = $stm->execute();
        $user = $res->fetchArray(SQLITE3_ASSOC);
        if (password_verify($password, $user['password'])) {
            $_SESSION['userName'] = $user['user'];
            $_SESSION['userLevel'] = $user['userlevel'];
            return "login=ok";
        } else {
            return "login=false";
        }
    }
    function isLoggedIn()
    {
        session_start();
        if (!isset($_SESSION['userName'])) trigger_error("Not logged in", E_USER_ERROR);
        return true;
    }
    function isAdmin()
    {
        $this->isLoggedIn();
        if ($_SESSION['userLevel'] == 'admin') return true;
        trigger_error("Not Admin", E_USER_ERROR);
        return false;
    }
}

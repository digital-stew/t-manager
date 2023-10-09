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

            //set location
            $_SESSION['location'] = "t-print";

            return "login=ok";
        } else {
            return "login=false";
        }
    }
    function isLoggedIn(): bool
    {
        if (!isset($_SESSION['userName'])) {
            die('not logged in');
            return false;
        };
        return true;
    }
    function isAdmin(): bool
    {
        $this->isLoggedIn();
        if ($_SESSION['userLevel'] == 'admin') return true;
        die('not admin');
        return false;
    }
    function getLocations(): array
    {
        return ['t-print', 'hawkins', 'fleetwood', 'cornwall'];
    }
    function setLocation($newLocation)
    {
        $this->isLoggedIn();
        $_SESSION['location'] = $newLocation;
    }
    function changePassword($oldPassword, $newPassword, $userName)
    {

        //get user
        $stm = $this->db->prepare("SELECT * FROM users WHERE user = ?");
        $stm->bindValue(1, $userName, SQLITE3_TEXT);
        $res = $stm->execute();
        $user = $res->fetchArray(SQLITE3_ASSOC);
        if (password_verify($oldPassword, $user['password'])) {

            $sql = <<<EOD
            UPDATE users
            SET password =?
            WHERE id = ?
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, password_hash($newPassword, PASSWORD_BCRYPT), SQLITE3_TEXT);
            $stm->bindValue(2, $user['id'], SQLITE3_TEXT);
            $res = $stm->execute();

            if ($res) return true;
        } else {
            return false;
        }
    }
}

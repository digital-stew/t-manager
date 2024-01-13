<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class Auth extends Database
{
    function login(string $userName, string $password)
    {
        try {
            $stm = $this->db->prepare("SELECT * FROM `t-manager`.users WHERE user = ?");
            $stm->bind_param("s", $userName);
            $stm->execute() or die('db error');
            $user = $stm->get_result()->fetch_assoc();
            $stm->close();

            if (password_verify($password, $user['password'])) {
                $_SESSION['userName'] = $user['user'];
                $_SESSION['userLevel'] = $user['userlevel'];

                //set location
                $_SESSION['location'] = "hawkins";

                return "login=ok";
            } else {
                return "login=false";
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'login()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
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
        return ['hawkins', 'fleetwood', 't-print', 'cornwall'];
    }
    function setLocation($newLocation)
    {
        $this->isLoggedIn();
        $_SESSION['location'] = $newLocation;
    }
    function changePassword($oldPassword, $newPassword, $userName)
    {
        try {
            //get user
            $stm = $this->db->prepare("SELECT * FROM `t-manager`.users WHERE user = ?");
            $stm->bind_param("s", $userName);
            $dbResponse = $stm->execute();
            $result = $stm->get_result();
            $user = $result->fetch_assoc();
            $stm->close();

            if (password_verify($oldPassword, $user['password'])) {

                $sql = <<<EOD
            UPDATE `t-manager`.users
            SET password =?
            WHERE id = ?
            EOD;
                $stm = $this->db->prepare($sql);
                $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stm->bind_param("si", $newPasswordHash, $user['id']);
                $dbResponse = $stm->execute();
                $stm->close();

                if ($dbResponse) return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'changePassword()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }
}

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
class Admin extends Database
{

    function getAllUsers()
    {
        $Auth = new Auth();
        $Auth->isAdmin();
        $sql = <<<EOD
            SELECT *
            FROM users
        EOD;

        $stm = $this->db->prepare($sql);
        $res = $stm->execute();

        $results = [];
        while ($row = $res->fetchArray()) {
            $user = array(
                'id' => $row['id'],
                'name' => $row['user'],
                'password' => $row['password'],
                'email' => $row['email'],
                'department' => $row['department'],
                'userLevel' => $row['userlevel'],
            );
            array_push($results, $user);
        }
        return $results;
    }

    function getUser($id)
    {
        $Auth = new Auth();
        $Auth->isAdmin();
        $sql = <<<EOD
            SELECT *
            FROM users
            WHERE id = ?
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $id, SQLITE3_TEXT);
        $res = $stm->execute();
        $user = $res->fetchArray();

        return array(
            'id' => $user['id'],
            'name' => $user['user'],
            'password' => $user['password'],
            'email' => $user['email'],
            'department' => $user['department'],
            'userLevel' => $user['userlevel'],
        );
    }

    function addUser($userName, $email, $password, $userLevel, $department): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
            INSERT INTO users
            (
                user,
                email,
                department,
                userlevel,
                password
            )
            VALUES
            (
                ?,?,?,?,?
            )
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $userName, SQLITE3_TEXT);
            $stm->bindValue(2, $email, SQLITE3_TEXT);
            $stm->bindValue(3, $department, SQLITE3_TEXT);
            $stm->bindValue(4, $userLevel, SQLITE3_TEXT);
            $stm->bindValue(5, password_hash($password, PASSWORD_BCRYPT), SQLITE3_TEXT);
            $res = $stm->execute();
            if ($res) return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function editUser($id, $email, $department, $userLevel)
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
        UPDATE users
        SET email =?, department = ?, userlevel = ?
        WHERE id = ?
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $email, SQLITE3_TEXT);
            $stm->bindValue(2, $department, SQLITE3_TEXT);
            $stm->bindValue(3, $userLevel, SQLITE3_TEXT);
            $stm->bindValue(4, $id, SQLITE3_TEXT);
            $res = $stm->execute();
            if ($res) return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function adminChangeUserPassword($id, $password): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
            UPDATE users
            SET password = ?
            WHERE id = ?
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, password_hash($password, PASSWORD_BCRYPT), SQLITE3_TEXT);
            $stm->bindValue(2, $id, SQLITE3_TEXT);
            $res = $stm->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function deleteUser($id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();
        try {
            $stm = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stm->bindValue(1, $id, SQLITE3_TEXT);
            $stm->execute();
            //header('Location: /admin');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

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

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, strtolower($userName), SQLITE3_TEXT);
        $stm->bindValue(2, $email, SQLITE3_TEXT);
        $stm->bindValue(3, $department, SQLITE3_TEXT);
        $stm->bindValue(4, $userLevel, SQLITE3_TEXT);
        $stm->bindValue(5, password_hash($password, PASSWORD_BCRYPT), SQLITE3_TEXT);
        $res = $stm->execute();
        if ($res) return true;
        else return false;
    }

    function editUser(string $id, string $email, string $department, string $userLevel): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
        UPDATE users
        SET email =?, department = ?, userlevel = ?
        WHERE id = ?
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $email, SQLITE3_TEXT);
        $stm->bindValue(2, $department, SQLITE3_TEXT);
        $stm->bindValue(3, $userLevel, SQLITE3_TEXT);
        $stm->bindValue(4, $id, SQLITE3_TEXT);
        $res = $stm->execute();
        if ($res) return true;
        else return false;
    }

    function adminChangeUserPassword($id, $password): bool
    {
        //working?
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
            UPDATE users
            SET password = ?
            WHERE id = ?
        EOD;


        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, password_hash($password, PASSWORD_BCRYPT), SQLITE3_TEXT);
        $stm->bindValue(2, $id, SQLITE3_TEXT);
        $res = $stm->execute();
        if ($res) return true;
        else return false;
    }

    function deleteUser($id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = "DELETE FROM users WHERE id = ?";
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $id, SQLITE3_TEXT);
        $stm->execute();
        if ($stm) return true;
        else return false;
    }

    function addStockColor(string $newCode, string $oldCode, string $color): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        if (!isset($newCode) || $newCode == '') return false;
        if (!isset($oldCode) || $oldCode == '') return false;
        if (!isset($color) || $color == '') return false;

        $sql = <<<EOD
            INSERT INTO stockCodes_color
            (
                newCode,
                oldCode,
                color
            )
            VALUES
            (
                ?,?,?
            )
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $newCode, SQLITE3_TEXT);
        $stm->bindValue(2, $oldCode, SQLITE3_TEXT);
        $stm->bindValue(3, $color, SQLITE3_TEXT);
        $res = $stm->execute();
        if ($res) return true;
        else return false;
    }

    function deleteStockColor(string $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = "DELETE FROM stockCodes_color WHERE id = ?";
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $id, SQLITE3_TEXT);
        $stm->execute();
        if ($stm) return true;
        else return false;
    }

    function addStockType(string $newCode, string $oldCode, string $type): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        if (!isset($newCode) || $newCode == '') return false;
        if (!isset($oldCode) || $oldCode == '') return false;
        if (!isset($type) || $type == '') return false;

        $sql = <<<EOD
            INSERT INTO stockCodes_type
            (
                newCode,
                oldCode,
                type
            )
            VALUES
            (
                ?,?,?
            )
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $newCode, SQLITE3_TEXT);
        $stm->bindValue(2, $oldCode, SQLITE3_TEXT);
        $stm->bindValue(3, $type, SQLITE3_TEXT);
        $res = $stm->execute();
        if ($res) return true;
        else return false;
    }

    function deleteStockType(string $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = "DELETE FROM stockCodes_type WHERE id = ?";
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $id, SQLITE3_TEXT);
        $stm->execute();
        if ($stm) return true;
        else return false;
    }

    function addStockSize(string $code, string $size): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        if (!isset($code) || $code == '') return false;
        if (!isset($size) || $size == '') return false;

        $sql = <<<EOD
            INSERT INTO stockCodes_size
            (
                code,
                size
            )
            VALUES
            (
                ?,?
            )
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $code, SQLITE3_TEXT);
        $stm->bindValue(2, $size, SQLITE3_TEXT);
        $res = $stm->execute();
        if ($res) return true;
        else return false;
    }

    function deleteStockSize(string $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = "DELETE FROM stockCodes_size WHERE id = ?";
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $id, SQLITE3_TEXT);
        $stm->execute();
        if ($stm) return true;
        else return false;
    }
}

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class Admin extends Database
{

    function getAllUsers(): array
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
            SELECT *
            FROM `t-manager`.users
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $res = $stm->get_result();

            $results = [];
            while ($row = $res->fetch_assoc()) {
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
            $stm->close();

            return $results;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getAllUsers()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getUser(int $id): array
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
            SELECT *
            FROM `t-manager`.users
            WHERE id = ?
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $stm->execute();
            $user = $stm->get_result()->fetch_assoc();
            $stm->close();

            return array(
                'id' => $user['id'],
                'name' => $user['user'],
                'password' => $user['password'],
                'email' => $user['email'],
                'department' => $user['department'],
                'userLevel' => $user['userlevel'],
            );
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getUser()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function addUser(string $userName, string $email, string $password, string $userLevel, string $department): bool
    {
        $Auth = new Auth();

        $Auth->isAdmin();

        $sql = <<<EOD
            INSERT INTO `t-manager`.users
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
            (string)$lowercaseUserName = strtolower($userName); //ensure user names are lowercase
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stm->bind_param("sssss", $lowercaseUserName, $email, $department, $userLevel, $hashedPassword);
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("NEW", "user", null, null, "new user: {$userName}");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addUser()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function editUser(string $id, string $email, string $department, string $userLevel): bool
    {

        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
            UPDATE `t-manager`.users
            SET email =?, department = ?, userlevel = ?
            WHERE id = ?
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bind_param("sssi", $email, $department, $userLevel, $id); // code-spell-checker:disable-line
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("EDIT", "user", null, $id, null);

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'editUser()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function adminChangeUserPassword(int $id, string $password): bool
    {
        //working?
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = <<<EOD
            UPDATE `t-manager`.users
            SET password = ?
            WHERE id = ?
        EOD;

        $stm = $this->db->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stm->bind_param("si", $hashedPassword, $id);
        $res = $stm->execute();
        $stm->close();

        $Log = new Log();
        $Log->add("EDIT", "user", null, $id, "admin change password");

        if ($res) return true;
        else return false;
    }

    function deleteUser(int $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        $sql = "DELETE FROM `t-manager`.users WHERE id = ?";

        try {
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("DELETE", "user", null, $id, "delete user");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'deleteUser()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function addStockColor(string $newCode, string $oldCode, string $color): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            if (!isset($newCode) || $newCode == '') new Exception('add new code error');
            if (!isset($oldCode) || $oldCode == '') new Exception('add old code error');
            if (!isset($color) || $color == '') new Exception('add color error');

            $sql = <<<EOD
                INSERT INTO `t-manager`.stockCodes_color
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
            $stm->bind_param("sss", $newCode, $oldCode, $color);
            $res = $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.stockCodes_color LIMIT 1;")->fetch_column();

            $Log = new Log();
            $Log->add("NEW", "stock color", null, $lastID, "new code: {$newCode} - old code: {$oldCode} - color: {$color}");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addStockColor()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function deleteStockColor(int $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            $sql = "DELETE FROM `t-manager`.stockCodes_color WHERE id = ?";
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("DELETE", "stock color", null, $id, "delete stock color");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'deleteStockColor()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function addStockType(string $newCode, string $oldCode, string $type): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            if (!isset($newCode) || $newCode == '') new Exception('add new code error');
            if (!isset($oldCode) || $oldCode == '') new Exception('add old code error');
            if (!isset($type) || $type == '') new Exception('add type error');

            $sql = <<<EOD
            INSERT INTO `t-manager`.stockCodes_type
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
            $stm->bind_param("sss", $newCode, $oldCode, $type);
            $res = $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.stockCodes_type LIMIT 1;")->fetch_column();

            $Log = new Log();
            $Log->add("NEW", "stock type", null, $lastID, "new code: {$newCode} - old code: {$oldCode} - type: {$type}");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addStockType()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function deleteStockType(int $id): bool
    {
        try {
            $Auth = new Auth();
            $Auth->isAdmin();

            $sql = "DELETE FROM `t-manager`.stockCodes_type WHERE id = ?";
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("DELETE", "stock type", null, $id, "delete stock type");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'deleteStockType()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function addStockSize(string $code, string $size): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            if (!isset($code) || $code == '') new Exception('add stock code error');
            if (!isset($size) || $size == '') new Exception('add size error');

            $sql = <<<EOD
            INSERT INTO `t-manager`.stockCodes_size
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
            $stm->bind_param("ss", $code, $size);
            $res = $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.stockCodes_size LIMIT 1;")->fetch_column();

            $Log = new Log();
            $Log->add("NEW", "stock size", null, $lastID, "new size: {$size} - code: {$code}");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addStockSize()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function deleteStockSize(int $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            $sql = "DELETE FROM `t-manager`.stockCodes_size WHERE id = ?";
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("DELETE", "stock size", null, $id, "delete stock size");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'deleteStockSize()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }
}

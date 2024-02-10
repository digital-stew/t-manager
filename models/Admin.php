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
            ORDER BY id DESC
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
            return false;
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
            return false;
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

    function addStockColor(string $newCode, string $oldCode, string $color, bool $trueCode): bool
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
                    color,
                    trueCode
                )
                VALUES
                (
                    ?,?,?,?
                )
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bind_param("sssi", $newCode, $oldCode, $color, $trueCode);
            $res = $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.stockCodes_color LIMIT 1;")->fetch_column();

            $Log = new Log();
            $Log->add("NEW", "stock color", null, $lastID, "new code: {$newCode} - old code: {$oldCode} - color: {$color} - true code: {$trueCode}");

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

    function addStockType(string $newCode, string $oldCode, string $type, bool $trueCode): bool
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
                type,
                trueCode
            )
            VALUES
            (
                ?,?,?,?
            )
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bind_param("sssi", $newCode, $oldCode, $type, $trueCode);
            $res = $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.stockCodes_type LIMIT 1;")->fetch_column();

            $Log = new Log();
            $Log->add("NEW", "stock type", null, $lastID, "new code: {$newCode} - old code: {$oldCode} - type: {$type} - true code: {$trueCode}");

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

    function getLocations(): array
    {
        return ['hawkins', 'fleetwood', 't-print', 'cornwall'];
    }

    function setLocation($newLocation)
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        $_SESSION['location'] = $newLocation;
    }

    function getAutoLocations(): array|bool
    {
        $sql = <<<EOD
            SELECT *
            FROM `t-manager`.autoLocations
            ORDER BY id DESC
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $res = $stm->get_result();

            $sites = [];
            while ($row = $res->fetch_assoc()) {
                array_push($sites, array(
                    'id' => $row['id'],
                    'ip' => $row['ip'],
                    'location' => $row['location'],
                ));
            }
            $stm->close();
            return $sites;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getAutoLocations()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function addAutoLocation(string $ipAddress, string $location): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            $sql = <<<EOD
            INSERT INTO `t-manager`.autoLocations
            (
                ip, location
            )
            VALUES
            (
                ?,?
            )
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bind_param("ss", $ipAddress, $location);
            $res = $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.autoLocations LIMIT 1;")->fetch_column();

            $Log = new Log();
            $Log->add("NEW", "auto location", null, $lastID, "ip: {$ipAddress} - location: {$location}");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addAutoLocation()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function deleteAutoLocation(int $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            $sql = "DELETE FROM `t-manager`.autoLocations WHERE id = ?";
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("DELETE", "auto location", null, $id, "delete auto location");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            $Log = new Log();
            $Log->add('ERROR', 'deleteRemoveStockReason()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function getRemoveStockReasons(): array | false
    {
        $sql = <<<EOD
            SELECT id, reason
            FROM `t-manager`.removeStockReasons
            ORDER BY id DESC
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $res = $stm->get_result();

            $reasons = [];
            while ($row = $res->fetch_assoc()) {
                $newReason = array(
                    'id' => $row['id'],
                    'reason' => $row['reason'],
                );
                array_push($reasons, $newReason);
            }
            $stm->close();

            return $reasons;
        } catch (Exception $e) {
            //            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getReasonsToRemoveStock()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function addRemoveStockReason(string $newReason): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            if ($newReason == '') throw new Exception('blank new reason');
            $sql = <<<EOD
            INSERT INTO `t-manager`.removeStockReasons
            (
                reason
            )
            VALUES
            (
                ?
            )
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bind_param("s", $newReason);
            $res = $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.removeStockReasons LIMIT 1;")->fetch_column();

            $Log = new Log();
            $Log->add("NEW", "remove stock reason", null, $lastID, "new reason: {$newReason}");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addRemoveStockReason()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function deleteRemoveStockReason(int $id): bool
    {
        $Auth = new Auth();
        $Auth->isAdmin();

        try {
            $sql = "DELETE FROM `t-manager`.removeStockReasons WHERE id = ?";
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $res = $stm->execute();
            $stm->close();

            $Log = new Log();
            $Log->add("DELETE", "remove reason", null, $id, "delete remove stock reason");

            if ($res) return true;
            else return false;
        } catch (Exception $e) {
            $Log = new Log();
            $Log->add('ERROR', 'deleteRemoveStockReason()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }
}

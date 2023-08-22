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

    function addUser($userName, $email, $passWd1, $passWd2, $userLevel, $department)
    {
        $Auth = new Auth();
        $Auth->isAdmin();
        if ($userName == '' || $userName == null) die('need user name');
        if ($passWd1 == '' || $passWd2 == '') die('passwords cant be blank');
        if ($passWd1 !== $passWd2) die('passwords must match');

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
        $stm->bindValue(1, $userName, SQLITE3_TEXT);
        $stm->bindValue(2, $email, SQLITE3_TEXT);
        $stm->bindValue(3, $department, SQLITE3_TEXT);
        $stm->bindValue(4, $userLevel, SQLITE3_TEXT);
        $stm->bindValue(5, password_hash($passWd1, PASSWORD_BCRYPT), SQLITE3_TEXT);
        $res = $stm->execute();


        header('Location: /admin');
    }

    function editUser()
    {
        // TODO
        $Auth = new Auth();
        $Auth->isAdmin();
    }

    function deleteUser($id)
    {
        $Auth = new Auth();
        $Auth->isAdmin();
        $stm = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stm->bindValue(1, $id, SQLITE3_TEXT);
        $stm->execute();
        header('Location: /admin');
    }
}

<?php
require_once $_SERVER['DOCUMENT_ROOT'] .'/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/models/Auth.php';
class Admin extends Database{

    function getAllUsers(){
        $Auth = new Auth();
        $Auth->isAdmin();
        $sql = <<<EOD
            SELECT *
            FROM users
        EOD;

        $stm = $this->db->prepare($sql);
        $res = $stm->execute();

        $results = [];
        while ($row = $res->fetchArray()){
            $user = array(
                'id' => $row['id'],
                'name' => $row['user'],
                'password' => $row['password'],
                'email' => $row['email'],
                'department' => $row['department'] ,
                'userLevel' => $row['userlevel'],
            );
            array_push($results, $user);
        }
        return $results;
    }

    function getUser($id){
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
                'department' => $user['department'] ,
                'userLevel' => $user['userlevel'],
            );
    }

    function editUser(){        
        $Auth = new Auth();
        $Auth->isAdmin();}
    function deleteUser(){        
        $Auth = new Auth();
        $Auth->isAdmin();}

}
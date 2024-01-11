<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
class Database
{
    public $db;
    function __construct()
    {
        include '/etc/mysql/credentials.php';
        try {
            $this->db = new mysqli($serverName, $username, $password);
        } catch (Exception $e) {
            print_r($e->getMessage());
            die(' database error');
        }
    }
    function __destruct()
    {
        $this->db->close();
    }
}

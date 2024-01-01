<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
class Database
{
    public $db;
    function __construct()
    {
        $serverName = "localhost";
        $username = "debian-sys-maint";
        $password = "nVPtaxfLbST9Ytx3";

        $this->db = new mysqli($serverName, $username, $password);
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
        //$this->db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
        //$this->db->exec("PRAGMA busy_timeout=5000");
    }
    function __destruct()
    {
        $this->db->close();
    }
}

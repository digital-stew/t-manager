<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
class Database
{
    public $db;
    function __construct()
    {
        $serverName = "127.0.0.1";
        $username = "debian-sys-maint"; // code-spell-checker:disable-line

        //$password = "nVPtaxfLbST9Ytx3"; //desktop dev // code-spell-checker:disable-line
        //$password = "nVPtaxfLbST9Ytx3"; //laptop dev// code-spell-checker:disable-line
        //$password = "I3WPz8F9tCEhArZw"; //production
        $password = "iNLBTHQBlFSoLANn"; //tux-systems.co.uk // code-spell-checker:disable-line

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

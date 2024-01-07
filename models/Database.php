<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
class Database
{
    public $db;
    function __construct()
    {
        $serverName = "127.0.0.1";
        //$serverName = "192.168.1.30";
        $username = "debian-sys-maint"; // code-spell-checker:disable-line

        if ($_SERVER['SERVER_SOFTWARE'] == 'PHP 8.2.10-2ubuntu1 Development Server') $password = "YdmtWmrw6k9L5bCK"; // code-spell-checker:disable-line
        // if (DEBUG) $password = "YdmtWmrw6k9L5bCK"; // code-spell-checker:disable-line
        else $password = "IajLPMEwoRPKEuNM"; // code-spell-checker:disable-line

        //$password = "YdmtWmrw6k9L5bCK"; //desktop dev // code-spell-checker:disable-line
        //$password = "nVPtaxfLbST9Ytx3"; //laptop dev// code-spell-checker:disable-line
        // $password = "IajLPMEwoRPKEuNM"; //production
        //$password = "iNLBTHQBlFSoLANn"; //tux-systems.co.uk // code-spell-checker:disable-line

        try {

            $this->db = new mysqli($serverName, $username, $password);
            //  if ($this->db->connect_error) {
            //      die("Connection failed: " . $this->db->connect_error);
            //   }
            //$this->db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
            //$this->db->exec("PRAGMA busy_timeout=5000");
        } catch (Exception $e) {
            die('database error');
        }
    }
    function __destruct()
    {
        $this->db->close();
    }
}

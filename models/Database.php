<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
class Database
{
    public $db;
    function __construct()
    {
        $serverName = "127.0.0.1";
        $username = "debian-sys-maint"; // code-spell-checker:disable-line

        //if ($_SERVER['SERVER_SOFTWARE'] == 'PHP 8.2.10-2ubuntu1 Development Server') $password = "V7AB6DXaNISWLEjD"; // code-spell-checker:disable-line LAPTOP
        if ($_SERVER['SERVER_SOFTWARE'] == 'PHP 8.2.10-2ubuntu1 Development Server') $password = "YdmtWmrw6k9L5bCK"; // code-spell-checker:disable-line DESKTOP
        else $password = "IajLPMEwoRPKEuNM"; // code-spell-checker:disable-line

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

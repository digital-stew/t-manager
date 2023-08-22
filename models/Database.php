<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
class Database{
    public $db;
    function __construct() {
        $this->db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
    }
    function __destruct(){
          $this->db->close();  
    }
}
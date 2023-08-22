<?php
include $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';

session_start();
if (isset($_POST['username']) && isset($_POST['password'])) {
    $Auth = new Auth();
    $res = $Auth->login($_POST['username'], $_POST['password']);
    echo $res;
}

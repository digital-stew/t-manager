<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
session_start();
$Auth = new Auth();
$Auth->isLoggedIn();

if (isset($_POST['add'])) {
    $Sample = new sample();
    $Sample->add($_POST['name'],  $_POST['number'],  $_POST['otherref'],  $_POST['front'],  $_POST['back'],  $_POST['other'],  $_POST['notes'], $_SESSION['userName'], $_FILES['files']);
    die();
}

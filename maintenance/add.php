<?php
// ========================MODAL============================
require $_SERVER['DOCUMENT_ROOT'] . '/models/Maintenance.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();
$Auth = new Auth();
$Auth->isLoggedIn();

if (isset($_POST['problem']) && isset($_POST['machine'])) {
    $Maintenance = new Maintenance();
    $res =  $Maintenance->add($_POST['problem'], $_POST['machine'], $_SESSION['userName']);
    if ($res) header('Location: /maintenance?flashUser=problem reported');
    else header('Location: /maintenance?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

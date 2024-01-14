<?php
// ========================MODAL============================
require $_SERVER['DOCUMENT_ROOT'] . '/models/Maintenance.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();
$Auth = new Auth();
$Auth->isLoggedIn();
$Maintenance = new Maintenance();

if (isset($_POST['problem']) && isset($_POST['machine'])) {
    $res =  $Maintenance->add($_POST['problem'], $_POST['machine'], $_SESSION['userName']);
    if ($res) header('Location: /maintenance?flashUser=problem reported');
    else header('Location: /maintenance?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

if (isset($_POST['completeTask']) && isset($_POST['id'])) {
    $res =  $Maintenance->complete((int)$_POST['id']);
    if ($res) header('Location: /maintenance?flashUser=Task completed');
    else header('Location: /maintenance?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

if (isset($_POST['deleteTask']) && isset($_POST['id'])) {
    $res =  $Maintenance->remove((int)$_POST['id']);
    if ($res) header('Location: /maintenance?flashUser=Task deleted');
    else header('Location: /maintenance?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

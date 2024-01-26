<?php
// ========================MODAL============================
require $_SERVER['DOCUMENT_ROOT'] . '/models/Orders.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();
$Auth = new Auth();
$Auth->isLoggedIn();
$Orders = new Orders();

if (isset($_POST['newOrder'])) {

    $files = [];
    $fileDescription = [];
    $sizes = [];
    $garment = [];

    $res =  $Orders->new(
        $_POST['orderName'],
        $_POST['printCheckbox'] ?? '',
        $_POST['embCheckbox'] ?? '',
        $_POST['transferCheckbox'] ?? '',
        $_POST['dtfCheckbox'] ?? '',
        $_POST['frontEmbellishment'] ?? '',
        $_POST['backEmbellishment'] ?? '',
        $_POST['lSleeveEmbellishment'] ?? '',
        $_POST['RSleeveEmbellishment'] ?? '',
        $_POST['neckEmbellishment'] ?? '',
        $_POST['otherEmbellishment'] ?? '',
        $_POST['otherEmbellishmentName'] ?? '',
        $_POST['packingSelect'],
        $_POST['deliverySelect'],
        $_POST['deliveryDate'],
        $_POST['sampleRequiredCheckbox'] ?? '',
        $_POST['asPreviousCheckbox'] ?? '',
        $garment,
        $files, // new table
        $fileDescription // new table
    );
    if ($res) header('Location: /orders?flashUser=order saved');
    else header('Location: /orders?flashUser=ERROR!! Contact admin if problem persists');
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

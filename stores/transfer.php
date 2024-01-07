<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
session_start();

if (isset($_POST['stockCodeInput']) && isset($_POST['transferFromSelect']) && isset($_POST['transferToSelect']) && isset($_POST['amountInput'])) {
    $Stock = new Stock();
    $res =  $Stock->transferStock($_POST['stockCodeInput'], $_POST['transferFromSelect'], $_POST['transferToSelect'], (int)$_POST['amountInput']);
    if ($res) header('Location: /stores?flashUser=stock transferred');
    else header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

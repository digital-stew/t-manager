<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
session_start();

if (isset($_POST['code']) && isset($_POST['location']) && isset($_POST['amount']) && isset($_POST['reason']) && isset($_POST['order'])) {
    //get current order id and allow "other" to remove stock with no order number
    if ($_POST['reason'] == 'other') {
        $orderId = 0;
    } else {
        $FanaticOrders = new FanaticOrders();
        $orderId = $FanaticOrders->getIdFromNumber($_POST['order']);
    }
    //remove the stock
    $Stock = new Stock();
    $res =  $Stock->removeStock($_POST['code'], $_POST['location'], $_POST['amount'], $_POST['reason'], $_POST['order'], $orderId);
    if ($res) header('Location: /stores?flashUser=stock removed');
    else header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

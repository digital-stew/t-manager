<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
session_start();

if ((isset($_POST['code']) || isset($_POST['manualRemoveStock'])) && isset($_POST['location']) && isset($_POST['amount']) && isset($_POST['reason']) && isset($_POST['order'])) {
    //get current order id and allow "other" to remove stock with no order number
    if ($_POST['reason'] == 'other') {
        $orderId = 0;
    } else {
        $FanaticOrders = new FanaticOrders();
        $orderId = $FanaticOrders->getIdFromNumber($_POST['order']);
        if (!$orderId) {
            header('Location: /stores?flashUser=ERROR with order number');
            die();
        }
    }

    if (isset($_POST['manualRemoveStock'])) {
        $stockCode = $_POST['removeStockSelectType'] . $_POST['removeStockSelectColor'] . $_POST['removeStockSelectSize'];
        $stockCode = str_pad($stockCode, 11, '0');
    } else {
        $stockCode = $_POST['code'];
    }

    //remove the stock
    $Stock = new Stock();
    $res =  $Stock->removeStock($stockCode, $_POST['location'], $_POST['amount'], $_POST['reason'], $_POST['order'], $orderId);
    if ($res) header('Location: /stores?flashUser=stock removed');
    else header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

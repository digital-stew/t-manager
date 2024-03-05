<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
session_start();

if ((isset($_POST['code']) || isset($_POST['manualRemoveStock'])) && isset($_POST['location']) && isset($_POST['amount']) && isset($_POST['reason']) && isset($_POST['order'])) {
    $Stock = new Stock();
    $Admin = new Admin();
    $Auth = new Auth();

    $Auth->isLoggedIn();

    if (isset($_POST['manualRemoveStock'])) {
        $stockCode = $_POST['removeStockSelectType'] . $_POST['removeStockSelectColor'] . $_POST['removeStockSelectSize'];
        $stockCode = str_pad($stockCode, 11, '0');
    } else {
        $stockCode = $_POST['code'];
    }

    try {
        //valid location
        $locationArray = $Admin->getLocations();
        $validFromLocation = false;
        foreach ($locationArray as $location) {
            if ($location == $_POST['location']) $validFromLocation = true;
        }
        if (!$validFromLocation) throw new Exception("invalid remove from location: {$_POST['location']}");

        //valid stock code?
        $checkCode = $Stock->searchCode($stockCode) or throw new Exception('invalid stock code: ' . $stockCode);;

        //valid amount to remove
        $validAmount = false;
        foreach ($checkCode as $check) {
            if ($check['location'] == $_POST['location'] && (int)$check['amount'] >= (int)$_POST['amount']) $validAmount = true;
        }
        if (!$validAmount) throw new Exception("not enough {$stockCode} at {$_POST['location']} to remove {$_POST['amount']}");

        //valid reason
        //get current order id and allow "other" to remove stock with no order number

        if ($_POST['reason'] == 'other' || $_POST['reason'] == 'stock adjust') {
            $orderId = 0;
        } else {
            $FanaticOrders = new FanaticOrders();
            $orderId = $FanaticOrders->getIdFromNumber($_POST['order']);
            if (!$orderId) throw new Exception('order number not in database');
        }
    } catch (Exception $e) {
        header("Location: {$_SERVER['HTTP_REFERER']}?showUser={$e->getMessage()}");
        die();
    }

    //remove the stock
    $res =  $Stock->removeStock($stockCode, $_POST['location'], $_POST['amount'], $_POST['reason'], $_POST['order'], $orderId);
    if ($res) header('Location: /stores?flashUser=stock removed');
    else header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

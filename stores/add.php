<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
session_start();

if ((isset($_POST['code']) || isset($_POST['manualAddStock'])) && isset($_POST['location']) && isset($_POST['amount'])) {
    if (isset($_POST['manualAddStock'])) {
        $stockCode = $_POST['addStockSelectType'] . $_POST['addStockSelectColor'] . $_POST['addStockSelectSize'];
        $stockCode = str_pad($stockCode, 11, '0');
    } else {
        $stockCode = $_POST['code'];
    }

    $Stock = new Stock();
    try {
        $Stock->parseCode($stockCode) or throw new Exception("invalid stock code {$stockCode}");
        $res =  $Stock->addStock($stockCode, $_POST['location'], $_POST['amount']) or throw new Exception('error updating database');
        if ($res) header('Location: /stores?flashUser=stock added');
        else header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
        die();
    } catch (Exception $e) {
        header("Location: {$_SERVER['HTTP_REFERER']}?showUser={$e->getMessage()}");
        die();
    }
}

if (isset($_POST['batchAddStock'])) {
    $Stock = new Stock();
    $sizes = $Stock->getSizes();

    $stockCode =  $_POST['batchAddModal-stockCodeInput'];

    $Stock->db->query("START TRANSACTION");

    foreach ($sizes as $size) {
        (int)$tmpAmount = $_POST[$size['size']];
        $tmpSize = $size['size'];
        if ($tmpAmount > 0) {
            $stockCode_full = str_pad($stockCode . $tmpSize, 11, '0');
            try {
                $Stock->parseCode($stockCode_full) or throw new Exception("invalid stock code {$stockCode_full}");
                $res =  $Stock->addStock($stockCode_full, $_POST['batchAddStockLocation'], $tmpAmount) or throw new Exception('error updating database');
            } catch (Exception $e) {
                $Stock->db->query("ROLLBACK");
                header("Location: {$_SERVER['HTTP_REFERER']}?showUser={$e->getMessage()}");
                die();
            }
            if ($res == true) continue;

            $Stock->db->query("ROLLBACK");
            header("Location: {$_SERVER['HTTP_REFERER']}?showUser=something went wrong");
            die();
        }
    }

    $Stock->db->query("COMMIT");
    header('Location: /stores?flashUser=stock added');
    die();
}

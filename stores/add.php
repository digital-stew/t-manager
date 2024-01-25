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
    $res =  $Stock->addStock($stockCode, $_POST['location'], $_POST['amount']);
    if ($res) header('Location: /stores?flashUser=stock added');
    else header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

if (isset($_POST['batchAddStyle']) && isset($_POST['batchAddColor']) && isset($_POST['location'])) {
    $Stock = new Stock();
    $sizes = $Stock->getSizes();

    $styleCode = $_POST['batchAddStyle'];
    $colorCode = $_POST['batchAddColor'];

    foreach ($sizes as $size) {
        (int)$tmpAmount = $_POST[$size['size']];
        $tmpSize = $size['size'];
        if ($tmpAmount > 0) {
            $stockCode = str_pad($styleCode . $colorCode . $tmpSize, 11, '0');
            try {
                $res =  $Stock->addStock($stockCode, $_POST['location'], $tmpAmount);
            } catch (Exception $e) {
                header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
            }
            if ($res == true) continue;
            header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
        }
    }

    header('Location: /stores?flashUser=stock added');
    die();
}

<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();



if (isset($_POST['transferStock']) || isset($_POST['transferStockManual'])) {
    $Stock = new Stock();
    $Auth = new Auth();
    $Admin = new Admin();

    $transferFrom = $_POST['transferFromSelect'];
    $transferTo = $_POST['transferToSelect'];
    $stockCode = $_POST['stockCodeInput'];
    $transferAmount = (int)$_POST['amountInput'];

    $Auth->isLoggedIn();

    //is everting valid?
    try {
        $Stock->db->query("START TRANSACTION");
        isValidTransfer($transferFrom, $transferTo, $stockCode, $transferAmount);
        $res =  $Stock->transferStock($stockCode, $transferFrom, $transferTo, $transferAmount) or throw new Exception('Error transferring stock');
        if ($res) {
            $Stock->db->query("COMMIT");
            header('Location: /stores?flashUser=stock transferred');
        } else {
            $Stock->db->query("ROLLBACK");
            header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
        }
        die();
    } catch (Exception $e) {
        header("Location: {$_SERVER['HTTP_REFERER']}?showUser={$e->getMessage()}");
        die();
    }
    //checks complete 

}

if (isset($_POST['batchTransferStock'])) {
    $Stock = new Stock();
    $Auth = new Auth();
    $Admin = new Admin();

    $Auth->isLoggedIn();

    $transferFrom = $_POST['batchTransferFromSelect'];
    $transferTo = $_POST['batchTransferToSelect'];
    $stockCode = $_POST['batchTransferModal-stockCodeInput'];


    $sizes = $Stock->getSizes();

    $Stock->db->query("START TRANSACTION");

    foreach ($sizes as $size) {
        (int)$tmpAmount = $_POST[$size['size']];
        $tmpSize = $size['size'];
        if ($tmpAmount > 0) {
            $fullStockCode = str_pad($stockCode . $tmpSize, 11, '0');
            try {
                $Stock->parseCode($fullStockCode) or throw new Exception("invalid stock code {$fullStockCode}");
                isValidTransfer($transferFrom, $transferTo, $fullStockCode, $tmpAmount);
                $res =  $Stock->transferStock($fullStockCode, $transferFrom, $transferTo, $tmpAmount) or throw new Exception('Error transferring stock');
            } catch (Exception $e) {
                $Stock->db->query("ROLLBACK");
                header("Location: {$_SERVER['HTTP_REFERER']}?showUser={$e->getMessage()}");
                die();
            }
            if ($res == true) continue;
            $Stock->db->query("ROLLBACK");
            header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
            die();
        }
    }
    $Stock->db->query("COMMIT");
    header('Location: /stores?flashUser=stock transferred');
    die();
}

function isValidTransfer($transferFrom, $transferTo, $stockCode, $transferAmount)
{
    $Admin = new Admin();
    $Stock = new Stock();

    $locationArray = $Admin->getLocations();

    //to and from location differ
    if ($transferFrom == $transferTo) throw new Exception('locations must differ');

    //valid from location?
    $validFromLocation = false;
    foreach ($locationArray as $location) {
        if ($location == $transferFrom) $validFromLocation = true;
    }
    if (!$validFromLocation) throw new Exception("invalid from location: {$transferFrom}");

    //valid to location?
    $validToLocation = false;
    foreach ($locationArray as $location) {
        if ($location == $transferTo) $validToLocation = true;
    }
    if (!$validToLocation) throw new Exception("invalid to location: {$transferFrom}");

    //valid code?
    $checkCode = $Stock->searchCode($stockCode) or throw new Exception('invalid stock code: ' . $stockCode . " does not exist");;

    //valid from location?
    $validLocation = false;
    foreach ($checkCode as $check) {
        if ($check['location'] == $transferFrom) $validLocation = true;
    }
    if (!$validLocation) throw new Exception("stock {$stockCode} not available from  {$transferFrom}");

    //enough available to transfer?
    $validAmount = false;
    foreach ($checkCode as $check) {
        if ($check['location'] == $transferFrom && (int)$check['amount'] >= (int)$transferAmount) $validAmount = true;
    }
    if (!$validAmount) throw new Exception("not enough {$stockCode} at {$transferFrom} to transfer {$transferAmount}");

    return true;
}

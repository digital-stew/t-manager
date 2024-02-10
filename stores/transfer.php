<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();

if (isset($_POST['stockCodeInput']) && isset($_POST['transferFromSelect']) && isset($_POST['transferToSelect']) && isset($_POST['amountInput'])) {
    $Stock = new Stock();
    $Admin = new Admin();
    $Auth = new Auth();

    $Auth->isLoggedIn();

    //is everting valid?
    try {
        $locationArray = $Admin->getLocations();

        //to and from location differ
        if ($_POST['transferFromSelect'] == $_POST['transferToSelect']) throw new Exception('locations must differ');

        //valid from location?
        $validFromLocation = false;
        foreach ($locationArray as $location) {
            if ($location == $_POST['transferFromSelect']) $validFromLocation = true;
        }
        if (!$validFromLocation) throw new Exception("invalid from location: {$_POST['transferFromSelect']}");

        //valid to location?
        $validToLocation = false;
        foreach ($locationArray as $location) {
            if ($location == $_POST['transferToSelect']) $validToLocation = true;
        }
        if (!$validToLocation) throw new Exception("invalid to location: {$_POST['transferFromSelect']}");

        //valid code?
        $checkCode = $Stock->searchCode($_POST['stockCodeInput']) or throw new Exception('invalid stock code: ' . $_POST['stockCodeInput']);;

        //valid from location?
        $validLocation = false;
        foreach ($checkCode as $check) {
            if ($check['location'] == $_POST['transferFromSelect']) $validLocation = true;
        }
        if (!$validLocation) throw new Exception("stock {$_POST['stockCodeInput']} not available from  {$_POST['transferFromSelect']}");

        //enough available to transfer?
        $validAmount = false;
        foreach ($checkCode as $check) {
            if ($check['location'] == $_POST['transferFromSelect'] && (int)$check['amount'] >= (int)$_POST['amountInput']) $validAmount = true;
        }
        if (!$validAmount) throw new Exception("not enough {$_POST['stockCodeInput']} at {$_POST['transferFromSelect']} to transfer {$_POST['amountInput']}");
    } catch (Exception $e) {
        header("Location: {$_SERVER['HTTP_REFERER']}?showUser={$e->getMessage()}");
        die();
    }
    //checks complete 

    $res =  $Stock->transferStock($_POST['stockCodeInput'], $_POST['transferFromSelect'], $_POST['transferToSelect'], (int)$_POST['amountInput']);
    if ($res) header('Location: /stores?flashUser=stock transferred');
    else header('Location: /stores?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

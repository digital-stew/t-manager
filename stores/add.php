<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
session_start();

if (isset($_GET['add']) && isset($_GET['code'])) {
    $html = <<<EOD
        <form action="/stores/add.php" method="post">
            <h4>Add stock</h4>
            <h5>{$_GET['location']}</h5>
            <label>
                code
                <input name="code" type="text" value="{$_GET['code']}">
            </label>
            <br>
            <label>
                amount
                <input name="amount" type="text">
            </label>
            <input type="hidden" name="location" value="{$_GET['location']}">
            <button type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="closeCamModal();" style="width: 80%;">Cancel</button>
        </form>
    EOD;
    die($html);
}

if (isset($_POST['code']) && isset($_POST['location']) && isset($_POST['amount'])) {
    $Stock = new Stock();
    $res =  $Stock->addStock($_POST['code'], $_POST['location'], $_POST['amount']);
    if ($res) header('Location: /stores?flashUser=stock added');
    die();
}

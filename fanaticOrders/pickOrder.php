<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
$FanaticOrders = new FanaticOrders();
//$colors = $Stock->getColors();
session_start();

if (isset($_POST['code'])) {
    $FanaticOrders = new FanaticOrders();
    $newOrderID =  $FanaticOrders->addOrder($_POST['code']);
    //if ($echoOrder) die('ok');
    //else die('error');
    die();
}

if (!isset($_GET['id'])) die('no id code');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pick order</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/qrCode.js"></script>
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/fanaticOrders/fanaticOrders.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div style="height: min-content;">

        <h1>pick order</h1>
        <hr>
    </div>
</body>

</html>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';

$FanaticOrders = new FanaticOrders();
//$colors = $Stock->getColors();
$orders = $FanaticOrders->getOrders();
//print_r($orders);
//die();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Fanatic orders</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/qrCode.js"></script>
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/fanaticOrders/fanaticOrders.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div style="height: min-content;">
        <h1>Fanatic orders</h1>
        <?php if (isset($_SESSION['userName'])) : ?>
            <button onclick="startCam();">scan order</button>
        <?php endif ?>
        <hr>

    </div>

    <table class="border" style="align-self: flex-start;">
        <thead>
            <tr>
                <th>batch</th>
                <th>code</th>
                <th>status</th>

            </tr>
        </thead>
        <tbody id="searchResults">
            <!-- placeholder -->
            <?php foreach ($orders as $order) : ?>
                <tr onclick="showModal('/fanaticOrders/orderDetails.php?id=<?= $order['id'] ?>');">
                    <td><?= $order['name'] ?></td>
                    <td><?= $order['code'] ?></td>
                    <td><?= $order['status'] ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <dialog id="scannerModal" style="text-align: center;">
        <div id="qr-reader" style="width: 200px"></div>
        <div id="qr-reader-results"></div>
        <button onclick="closeCamModal();" style="width: 80%;">cancel</button>
    </dialog>

</body>

</html>
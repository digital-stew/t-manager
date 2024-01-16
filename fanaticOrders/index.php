<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Stock = new Stock();
$FanaticOrders = new FanaticOrders();

if (isset($_GET['complete'])) {
    $orders = $FanaticOrders->getOrders('complete');
} else {
    $orders = $FanaticOrders->getOrders('pending/short');
}

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
            <button onclick="batchAddOrders();">batch add orders</button>
            <button onclick="document.getElementById('manualAddJobModal').showModal();">manual add order</button>
        <?php endif ?>
        <?php if (isset($_GET['complete'])) : ?>
            <button onclick="javascript:window.location.href = '/fanaticOrders/';">show pending/short</button>
        <?php else : ?>
            <button onclick="javascript:window.location.href = '/fanaticOrders/?complete=true';">show complete</button>
        <?php endif ?>
        <hr>

    </div>

    <table class="border" style="align-self: flex-start;">
        <thead>
            <tr>
                <th>id</th>
                <th>batch</th>
                <th>code</th>
                <th>status</th>
            </tr>
        </thead>
        <tbody id="searchResults">
            <!-- placeholder -->
            <?php foreach ($orders as $order) : ?>
                <tr onclick="showModal('/fanaticOrders/orderDetails.php?id=<?= $order['id'] ?>');">
                    <td><?= $order['id'] ?></td>
                    <td><?= $order['name'] ?></td>
                    <td><?= $order['code'] ?></td>
                    <td><?= $order['status'] ?></td>

                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <dialog id="scannerModal" style="text-align: center;">
        <div id="qr-reader" style="width: 200px;margin-inline: auto;"></div>
        <div id="qr-reader-results" style="text-align: center;"></div>
        <button onclick="closeCamModal();" style="width: 80%;">cancel</button>
    </dialog>

    <dialog id="manualAddJobModal">
        <form action="/fanaticOrders/pickOrder.php" method="post" style="text-align: center;">
            <h4>manual add job</h4>
            <input type="text" name="orderName" id="" placeholder="order name / batch"> <br>
            <select name="type" id="">
                <?php foreach ($Stock->getTypes() as $type) : ?>
                    <option value="<?= $type['oldCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>
            <select name="color" id="" style="margin-block: 2rem;">
                <?php foreach ($Stock->getColors() as $color) : ?>
                    <option value="<?= $color['oldCode'] ?> : <?= $color['color'] ?>"><?= $color['color'] ?></option>
                <?php endforeach ?>
            </select> <br>
            <?php foreach ($Stock->getSizes() as $size) : ?>
                <input type="number" name="<?= $size['size'] ?>" id="" placeholder="<?= $size['size'] ?>" style="width: 7ch;">
            <?php endforeach ?>
            <button type="submit" name="manualAddOrder" style="width: 80%;">add order</button>
            <button type="button" style="width: 80%;" onclick="document.getElementById('manualAddJobModal').close();">cancel</button>
        </form>
    </dialog>
</body>

</html>
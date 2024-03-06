<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Stock = new Stock();
$FanaticOrders = new FanaticOrders();


if (isset($_GET['complete'])) {
    $orders = $FanaticOrders->getOrders('complete');
} elseif (isset($_GET['search'])) {
    $orders = $FanaticOrders->search($_GET['search']);
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
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.png">
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
            <button onclick="document.getElementById('addAndPickOrder-modal').showModal();startCamAdd();">scan & pick order</button>
            <button onclick="document.getElementById('batchAddOrders-modal').showModal();startCamBatchAdd()">batch add orders</button>
            <button onclick="document.getElementById('manualAddJobModal').showModal();">manual add order</button>
        <?php endif ?>
        <?php if (isset($_GET['complete'])) : ?>
            <button onclick="javascript:window.location.href = '/fanaticOrders/';">show pending/short</button>
        <?php else : ?>
            <button onclick="javascript:window.location.href = '/fanaticOrders/?complete=true';">show complete</button>
        <?php endif ?>
        <hr>

    </div>
    <section style="display: flex;flex-direction: column;">
        <form action="/fanaticOrders/" method="get" autocomplete="off" style="height: min-content;text-align: center;margin-bottom: 1rem;">
            <input type="search" id="search" autocomplete="off" name="search" placeholder="search...">
        </form>
        <table class="border" style="width: 100%;">
            <thead>
                <tr>
                    <th>id</th>
                    <th>batch</th>
                    <th>garment</th>
                    <th>code</th>
                    <th>status</th>
                </tr>
            </thead>
            <tbody id="searchResults">
                <?php foreach ($orders as $order) : ?>
                    <tr onclick="showModal('/fanaticOrders/orderDetails.php?id=<?= $order['id'] ?>');">
                        <td><?= $order['id'] ?></td>
                        <td><?= $order['name'] ?></td>
                        <td><?= $order['garment'] ?></td>
                        <td><?= $order['code'] ?></td>
                        <td><?= $order['status'] ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </section>

    <dialog id="addAndPickOrder-modal">
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">example order string = 4336-2 ¦ 211M-00U2-ERS-ERS ¦ Hood Sports Grey ¦ 6 XS ¦ 6 S ¦ 8 M ¦ 8 L ¦ 8 XL ¦ 8 2XL ¦ 6 3XL ¦ 0 4XL ¦ 0 5XL</span>
        </div>
        <h4>Scan and pick fanatic order</h4>
        <div id="qr-reader-add" class="qr-reader"></div>
        <form action="/fanaticOrders/pickOrder.php" method="post" autocomplete="off">
            <input type="text" name="orderInputString" id="orderInputStringAdd" placeholder="manual order string" style="width: 100%;">
            <button id="addOrderSubmitButton" type="submit" name="addAndPickOrder" style="width: 80%;">add and pick</button>
            <button type="button" style="width: 80%;" onclick="closeAddModal();">cancel</button>
        </form>
    </dialog>

    <dialog id="batchAddOrders-modal">
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">example order string = 4336-2 ¦ 211M-00U2-ERS-ERS ¦ Hood Sports Grey ¦ 6 XS ¦ 6 S ¦ 8 M ¦ 8 L ¦ 8 XL ¦ 8 2XL ¦ 6 3XL ¦ 0 4XL ¦ 0 5XL</span>
        </div>
        <h4>Scan fanatic order</h4>
        <div id="qr-reader-batchAdd" class="qr-reader"></div>
        <form action="/fanaticOrders/pickOrder.php?batchAddOrder=true" method="post" autocomplete="off">
            <input type="text" name="orderInputString" id="orderInputStringBatchAdd" placeholder="manual order string" style="width: 100%;">
            <button id="batchAddSubmitButton" type="submit" name="batchAddOrder" style="width: 80%;">add order</button>
            <button type="button" style="width: 80%;" onclick="closeBatchAddModal();">cancel</button>
        </form>
    </dialog>

    <dialog id="manualAddJobModal">
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">This should only be utilized in the absence of a QR code</span>
        </div>
        <form action="/fanaticOrders/pickOrder.php" method="post" style="text-align: center;" autocomplete="off">
            <h4>manual add order</h4>
            <input type="text" name="orderName" placeholder="order name / batch" required> <br>
            <select name="type" required>
                <option value="">--select garment--</option>
                <?php foreach ($Stock->getTypes() as $type) : ?>
                    <option value="<?= $type['oldCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>
            <select name="color" style="margin-block: 2rem;" required>
                <option value="">--select color--</option>
                <?php foreach ($Stock->getColors() as $color) : ?>
                    <option value="<?= $color['oldCode'] ?> : <?= $color['color'] ?>"><?= $color['color'] ?></option>
                <?php endforeach ?>
            </select> <br>
            <?php foreach ($Stock->getSizes() as $size) : ?>
                <input type="number" name="<?= $size['size'] ?>" min="0" placeholder="<?= $size['size'] ?>" style="width: 7ch;">
            <?php endforeach ?>
            <button type="submit" name="manualAddOrder" style="width: 80%;">add order</button>
            <button type="button" style="width: 80%;" onclick="document.getElementById('manualAddJobModal').close();">cancel</button>
        </form>
    </dialog>
</body>

</html>
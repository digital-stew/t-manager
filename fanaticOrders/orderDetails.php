<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();
$Auth = new Auth();

$FanaticOrders = new FanaticOrders();
$order = $FanaticOrders->getOrder($_GET['id']);

$Stock = new Stock();
$returnArray = [];
$stockCode = $FanaticOrders->getStockCode($order['code']);

if (isset($_POST['deleteFanaticOrder'])) {
    $res = $FanaticOrders->deleteOrder($_GET['id']);
    if ($res) header('Location: /fanaticOrders?flashUser=order deleted');
    die();
}

?>
<section>

    <div style="display: flex;justify-content:center;gap: 1rem;">
        <h4><?= $order['name'] ?></h4>
        <h4><?= $order['code'] ?></h4>
    </div>

    <h4>status: <?= $order['status'] ?></h4>

    <table class="border" style="text-align: center;margin-inline: auto;width: 100%;">
        <thead>
            <tr>
                <th><?= $order['garment'] ?></th>
                <?php foreach (array_keys($order['sizes']) as $size) : ?>
                    <th> <?= $size ?> </th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ordered</td>
                <?php foreach (array_keys($order['sizes']) as $size) : ?>
                    <td> <?= $order['sizes'][$size] ?> </td>
                <?php endforeach ?>
            </tr>
            <tr>
                <td>picked</td>
                <?php foreach (array_keys($order['picked']) as $size) : ?>
                    <td> <?= $order['picked'][$size] ?> </td>
                <?php endforeach ?>
            </tr>
            <tr>
                <td>available</td>
                <?php
                foreach (array_keys($order['sizes']) as $sizeValue) {
                    $availableAmount = 0;
                    $search = $Stock->searchCode($stockCode . $sizeValue);
                    foreach ($search as $row) {
                        $availableAmount += (int)$row['amount'];
                    }

                    echo '<td>' . $availableAmount . '</td>';
                };

                ?>


            </tr>
        </tbody>
    </table>
    <?php if (isset($_SESSION['userName'])) : ?>
        <button type="button" onclick="window.location = '/fanaticOrders/pickOrder.php?id=<?= $order['id'] ?>';">pick order</button> <br>
    <?php endif ?>
    <button type="button" onclick="closeModal();">cancel</button>
    <?php if (isset($_SESSION['userName']) && $_SESSION['userLevel'] == 'admin') : ?>
        <hr>
        <form action="/fanaticOrders/orderDetails.php?id=<?= $order['id'] ?>" method="post">
            <button type="submit" onclick="return confirm('Permanently delete this order?')" name="deleteFanaticOrder">delete Order</button> <br>
        </form>
    <?php endif ?>
</section>
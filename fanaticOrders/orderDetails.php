<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$FanaticOrders = new FanaticOrders();
$order = $FanaticOrders->getOrder($_GET['id']);

$Stock = new Stock();
$returnArray = [];
$stockCode = $FanaticOrders->getStockCode($order['code']);

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
                <th>garment</th>
                <?php foreach (array_keys($order['sizes']) as $size) : ?>
                    <th> <?= $size ?> </th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $order['garment'] ?></td>
                <?php foreach (array_keys($order['sizes']) as $size) : ?>
                    <td> <?= $order['sizes'][$size] ?> </td>
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
    <button type="button" onclick="window.location = '/fanaticOrders/pickOrder.php?id=<?= $order['id'] ?>';">pick order</button> <br>
    <button type="button" onclick="closeModal();">cancel</button>
</section>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/FanaticOrders.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

if (isset($_POST['manualAddOrder']) && isset($_POST['orderName'])) {
    session_start();
    // code example 4336-2 ¦ 211M-00U2-ERS-ERS ¦ Hood Sports Grey ¦ 6 XS ¦ 6 S ¦ 8 M ¦ 8 L ¦ 8 XL ¦ 8 2XL ¦ 6 3XL ¦ 0 4XL ¦ 0 5XL
    $colorCode = trim(explode(':', $_POST['color'])[0]);
    $colorText = trim(explode(':', $_POST['color'])[1]);
    $manualCode = '';

    //add order name eg 4336-2
    $manualCode .= $_POST['orderName'] . ' ¦ ';

    //add order code eg 211M-00U2-ERS-ERS
    $rand1 = rand3();
    $rand2 = rand3();
    $manualCode .= "{$_POST['type']}-{$colorCode}-{$rand1}-{$rand2} ¦ ";

    //add garment name eg Hood Sports Grey
    $manualCode .= "{$colorText} ¦ ";

    //add sizes
    $Stock = new Stock();
    foreach ($Stock->getSizes() as $size) {
        if (isset($_POST[$size['size']]) && $_POST[$size['size']] > 0) {
            $manualCode .= $_POST[$size['size']] . ' ' . $size['size'] . ' ¦ ';
        } else {
            $manualCode .= '0 ' . $size['size'] . ' ¦ ';
        }
    }

    $FanaticOrders = new FanaticOrders();
    $newOrderID = $FanaticOrders->addOrder($manualCode);
    if ($newOrderID) header('location: /fanaticOrders');
    die();
}

if (isset($_POST['code'])) {
    session_start();
    $FanaticOrders = new FanaticOrders();
    $newOrderID = $FanaticOrders->addOrder($_POST['code']);
    die((string)$newOrderID);
}

if (isset($_POST['pick'])) {
    session_start();
    $FanaticOrders = new FanaticOrders();
    $res = $FanaticOrders->pickSize($_POST['orderId'], $_POST['size'], $_POST['pickedAmount'], $_POST['stockCode'], $_SESSION['location']);
    $_SESSION['pickPlace']++;
    die('ok');
};

if (!isset($_GET['id'])) die('no id code');

if (isset($_POST['skipPick'])) {
    session_start();
    $_SESSION['pickPlace']++;
    die('ok');
}

function rand3($length = 3)
{
    $random_string = "";
    while (strlen($random_string) < $length && $length > 0) {
        $randNum = mt_rand(0, 61);
        $random_string .= ($randNum < 10) ?
            chr($randNum + 48) : ($randNum < 36 ?
                chr($randNum + 55) : $randNum + 61);
    }
    return $random_string;
}

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
    <script src="/fanaticOrders/pickOrder.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
    $Auth = new Auth();
    $Auth->isLoggedIn();
    $FanaticOrders = new FanaticOrders();
    $Stock = new Stock();

    //reset pick place on first page load
    if (!isset($_GET['continue'])) {
        $_SESSION['pickPlace'] = 0;
    }

    $order = $FanaticOrders->getOrder($_GET['id']);
    $stockCodeP1 = $FanaticOrders->getStockCode($order['code']);

    //catch pickPlace overflow
    if ($_SESSION['pickPlace'] == sizeof($order['sizes'])) {
        header("Refresh:0; url=/fanaticOrders/?flashUser=Finished or no stock available");
        die();
    }

    $currentSize = array_keys($order['sizes'])[$_SESSION['pickPlace']];
    $fullCode = str_pad($stockCodeP1 . $currentSize, 11, '0');

    $availableStock = [];
    foreach (array_keys($order['sizes']) as $sizeValue) {
        $availableAmount = 0;
        $search = $Stock->searchCode($stockCodeP1 . $sizeValue);
        foreach ($search as $row) {
            //only count at user location
            if ($row['location'] == $_SESSION['location']) $availableAmount += (int)$row['amount'];
        }

        $availableStock += [$sizeValue => $availableAmount];
    };

    //catch if pick amount is zero OR available amount is zero
    if ($order["sizes"][$currentSize] - $order["picked"][$currentSize] == 0 or $availableStock[$currentSize] == 0) {
        $_SESSION['pickPlace']++;
        header("Refresh:0; url=/fanaticOrders/pickOrder.php?id=" . $_GET['id'] . "&continue=true");
        die();
    }

    ?>

    <div style="height: min-content;">

        <h1>pick order</h1>
        <hr>

        <section>

            <div style="display: flex;justify-content:center;gap: 1rem;">
                <h4><?= $order['name'] ?></h4>
                <h4><?= $order['code'] ?></h4>
            </div>

            <div id="qr-reader" style="width: 200px;margin-inline: auto;margin-top: 2rem;"></div>

            <div id="scanTarget" style="display: flex;justify-content:center;gap: 1rem;">

                <p> <?= $fullCode ?> </p>
                <p><?= $order['garment'] ?></p>
                <p><?= $currentSize ?></p>
            </div>
            <div style="margin-inline: auto;text-align: center;">
                <button onclick="skipPick();" style="width: 150px;">skip</button> <br>
                <button onclick="document.getElementById('confirm').showModal();">test</button>
            </div>

            <!--  debug info
            <?= $order['sizes'][$currentSize] ?>
            <?= $currentSize ?>
            <?= $_SESSION['pickPlace'] ?>
        -->

            <table class="border" style="text-align: center;margin-inline: auto;">
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
                        <td>to pick</td>
                        <?php foreach (array_keys($order['sizes']) as $size)
                            // <!-- print amount left to pick -->

                            echo "<td>" . $order["sizes"][$size] - $order["picked"][$size] . "</td>";
                        ?>
                    </tr>
                    <tr>
                        <td><?= $_SESSION['location'] ?></td>
                        <?php
                        foreach (array_keys($order['sizes']) as $sizeValue) {
                            echo "<td>" . $availableStock[$sizeValue] . '</td>';
                        };

                        ?>

                    </tr>
                </tbody>
            </table>
        </section>
    </div>
    <dialog id="confirm" style="text-align: center;">
        <p style="display: none;">order id: <span id="orderId"><?= $_GET['id'] ?></span></p>
        <p>code: <span id="targetCode"><?= $fullCode ?></span></p>
        <p>size: <span id="targetSize"><?= $currentSize ?></span></p>
        <!-- if available is less than required only show available stock -->
        <p>pick: <span id="pickAmount"><?= $availableStock[$currentSize] < $order['sizes'][$currentSize] - $order["picked"][$currentSize] ? $availableStock[$currentSize] : $order['sizes'][$currentSize] - $order["picked"][$currentSize] ?></span></p>
        <button onclick="uploadPick();" style="width: 90%;">done</button>
        <button onclick="skipPick();">skip</button>
        <button onclick="document.getElementById('confirm').close();">cancel</button>
    </dialog>
    <script>
        const json = '<?php echo json_encode($order) ?>';
        const stockCode = "<?= $stockCodeP1 ?>";
    </script>
</body>

</html>
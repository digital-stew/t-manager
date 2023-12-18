<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';

$Stock = new Stock();
$Auth = new Auth();
$locations = $Auth->getLocations();
$types = $Stock->getTypes();
$sizes = $Stock->getSizes();
$colors = $Stock->getColors();
$removeStockReasons = $Stock->getReasonsToRemoveStock();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Stores</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/qrCode.js"></script>
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/stores/stores.js" defer></script>
</head>

<body>

    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div style="<?= isset($_SESSION['userName']) ? 'background-image: inherit ;position: sticky;top:0px;' : '' ?>">
        <h1>Stores</h1>
        <hr>
        <?php if (isset($_SESSION['userName'])) : ?>
            <div>
                <button onclick="addStockButton()">add</button>
                <button onclick="removeStockButton()">remove</button>
            </div>
        <?php endif ?>
    </div>

    <section>
        <table class="border" style="width: 100%;">
            <thead>
                <tr>
                    <th>
                        code
                    </th>
                    <th>color <br>
                        <select name="colorSelect" id="colorSelect" onchange="searchStock();">
                            <option value="all">all</option>
                            <?php foreach ($colors as $color) : ?>
                                <option value="<?= $color['color'] ?>"><?= $color['color'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </th>
                    <th>size <br>
                        <select name="sizeSelect" id="sizeSelect" onchange="searchStock();">
                            <option value="all">all</option>
                            <?php foreach ($sizes as $size) : ?>
                                <option value="<?= $size['size'] ?>"><?= $size['size'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </th>
                    <th>type <br>
                        <select name="typeSelect" id="typeSelect" onchange="searchStock();">
                            <option value="all">all</option>
                            <?php foreach ($types as $typeArray) : ?>
                                <option value="<?= $typeArray['type'] ?>"><?= $typeArray['type'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </th>
                    <th>location <br>
                        <select name="locationSelect" id="locationSelect" onchange="searchStock();">
                            <option value="all">all</option>
                            <?php foreach ($locations as $location) : ?>
                                <option value="<?= $location ?>"><?= $location ?></option>
                            <?php endforeach ?>
                        </select>
                    </th>
                    <th>amount</th>
                </tr>
            </thead>
            <tbody id="searchResults">
                <!-- placeholder -->
            </tbody>
        </table>
    </section>

    <dialog id="scannerModal" style="text-align: center;">
        <div id="qr-reader" style="width: 200px;margin-inline: auto;"></div>
        <div id="qr-reader-results"></div>
        <form action="JavaScript:manualInput()">
            <br>
            <input type="text" name="manualInputCode" id="manualInputCode">
            <br>
            <button type="button" onclick="manualInput();" style="width: 80%;">manual input</button><br>
            <button type="button" onclick="closeCamModal();" style="width: 80%;">cancel</button>
        </form>
    </dialog>

</body>

</html>
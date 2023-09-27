<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Stock = new Stock();
$locations = $Stock->getLocations();
$types = $Stock->getTypes();
$sizes = $Stock->getSizes();
$colors = $Stock->getColors();
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
    <div>

        <h1>Stores</h1>
        <?php if (isset($_SESSION['userName'])) : ?>
            <button onclick="addStockButton()">add</button>
            <button onclick="removeStockButton()">remove</button>
            <label for="addRemoveLocationSelect">location</label>
            <select name="addRemoveLocationSelect" id="addRemoveLocationSelect">
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>
        <?php endif ?>
        <hr>
    </div>

    <section>
        <div style="margin-inline: auto;margin-bottom: 1rem;text-align: center;">
            <label>color
                <select name="colorSelect" id="colorSelect" onchange="searchStock();">
                    <option value="all">all</option>
                    <?php foreach ($colors as $color) : ?>
                        <option value="<?= $color['color'] ?>"><?= $color['color'] ?></option>
                    <?php endforeach ?>
                </select>
            </label>

            <label>size
                <select name="sizeSelect" id="sizeSelect" onchange="searchStock();">
                    <option value="all">all</option>
                    <?php foreach ($sizes as $size) : ?>
                        <option value="<?= $size['size'] ?>"><?= $size['size'] ?></option>
                    <?php endforeach ?>
                </select>
            </label>

            <label>type
                <select name="typeSelect" id="typeSelect" onchange="searchStock();">
                    <option value="all">all</option>
                    <?php foreach ($types as $typeArray) : ?>
                        <option value="<?= $typeArray['type'] ?>"><?= $typeArray['type'] ?></option>
                    <?php endforeach ?>
                </select>
            </label>

            <label>location
                <select name="locationSelect" id="locationSelect" onchange="searchStock();">
                    <option value="all">all</option>
                    <?php foreach ($locations as $location) : ?>
                        <option value="<?= $location ?>"><?= $location ?></option>
                    <?php endforeach ?>
                </select>
            </label>
        </div>

        <table class="border" style="width: 100%;">
            <thead>
                <tr>
                    <th>code</th>
                    <th>color</th>
                    <th>size</th>
                    <th>type</th>
                    <th>location</th>
                    <th>amount</th>
                </tr>
            </thead>
            <tbody id="searchResults">
                <!-- placeholder -->
            </tbody>
        </table>
    </section>

    <dialog id="scannerModal" style="text-align: center;">
        <div id="qr-reader" style="width: 200px"></div>
        <div id="qr-reader-results"></div>
        <button onclick="closeCamModal();" style="width: 80%;">cancel</button>
    </dialog>

</body>

</html>
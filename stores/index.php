<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';

$Stock = new Stock();
$Admin = new Admin();

$locations = $Admin->getLocations();
$types = $Stock->getTypes();
$sizes = $Stock->getSizes();
$colors = $Stock->getColors();
$removeStockReasons = $Admin->getRemoveStockReasons();
$options = "";
foreach ($removeStockReasons as $reason) {
    $options .= "<option value='{$reason['reason']}'>{$reason['reason']}</option>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Stores</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <!-- <link rel="stylesheet" href="/assets/light.css"> -->
    <script src="/assets/qrCode.js"></script>
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/stores/stores.js" defer></script>
</head>

<body>

    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div style="<?= isset($_SESSION['userName']) ? 'background-image: inherit ;position: sticky;top:0px;' : '' ?>">
        <h1>Stores</h1>
        <?php if (isset($_SESSION['userName'])) : ?>
            <div>
                <button onclick="addStockButton()">add</button>
                <button onclick="batchAddStockButton()">batch add</button>
                <button onclick="removeStockButton()">remove</button>
                <button onclick="transferStockButton()">transfer</button>
            </div>
        <?php endif ?>
        <hr>
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

    <dialog id="addStockModal">
        <form action="/stores/add.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>Add stock</h4>

            <h4 id="addStockModal-userLocation"><?= $_SESSION['location'] ?></h4>
            <input type="hidden" name="location" id="addStockModal-hiddenLocationInput" value="<?= $_SESSION['location'] ?>" required>

            <div id="addStockModal-qrReader" style="width: 200px;margin-inline: auto;"></div>

            <button type="button" id="removeStockModal-manualButton" onclick="addStockManualInput()">manual input</button>

            <label for="addStockModal-stockCode">stock code</label>
            <input id="addStockModal-stockCode" name="code" type="text" minlength="11" maxlength="11" required>

            <label for="addStockModal-amount" style="margin-top: 1rem;">amount</label>
            <input id="addStockModal-amount" name="amount" type="number" min="0" style="text-align: center;" required>

            <button type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="closeAddStockModal();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>

    <dialog id="addStockModal-manual">
        <form action="/stores/add.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>Add stock</h4>

            <h4 id="addStockModal-manual-userLocation"><?= $_SESSION['location'] ?></h4>
            <input type="hidden" name="location" id="addStockModal-manual-hiddenLocationInput" value="<?= $_SESSION['location'] ?>" required>

            <select name="addStockSelectType" required>
                <option value="">--- type ---</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>

            <select name="addStockSelectColor" required>
                <option value="">--- color ---</option>
                <?php foreach ($colors as $color) : ?>
                    <option value="<?= $color['newCode'] ?>"><?= $color['color'] ?></option>
                <?php endforeach ?>
            </select>

            <select name="addStockSelectSize" required>
                <option value="">--- size ---</option>
                <?php foreach ($sizes as $size) : ?>
                    <option value="<?= $size['code'] ?>"><?= $size['size'] ?></option>
                <?php endforeach ?>
            </select>

            <label for="addStockModal-manual-amount" style="margin-top: 1rem;">amount</label>
            <input id="addStockModal-manual-amount" name="amount" type="number" min="1" style="text-align: center;" required>

            <button type="submit" name="manualAddStock" style="width: 80%;margin-inline: auto;">Save</button><br>
            <button type="button" onclick="document.getElementById('addStockModal-manual').close();" style="width: 80%;margin-inline: auto;">Cancel</button>
        </form>
    </dialog>


    <dialog id="removeStockModal">
        <form action="/stores/remove.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>remove stock</h4>

            <h4 id="removeStockModal-userLocation"><?= $_SESSION['location'] ?></h4>
            <input type="hidden" name="location" id="removeStockModal-hiddenLocationInput" value="<?= $_SESSION['location'] ?>" required>

            <h4 id="removeStockModal-showUser" style="color: red;"></h4>

            <div id="removeStockModal-qrReader" style="width: 200px;margin-inline: auto;"></div>

            <button type="button" id="removeStockModal-manualButton" onclick="removeStockManualInput()">manual input</button>

            <label id="removeStockModal-stockCode-label" for="removeStockModal-stockCode">stock code</label>
            <input id="removeStockModal-stockCode" name="code" type="text" minlength="11" maxlength="11" required>

            <label for="removeStockModal-order">order</label>
            <input type="text" name="order" id="removeStockModal-order">

            <label for="removeStockModal-amount" style="margin-top: 1rem;">amount</label>
            <input id="removeStockModal-amount" name="amount" type="number" min="0" style="text-align: center;" required>

            <label for="reason-select" style="margin-top: 1rem;">reason</label>
            <select id="reason-select" name="reason" required>
                <option value="">--Please choose an option--</option>
                <?= $options ?>
            </select>

            <button type="submit" style="width: 80%;margin-inline: auto;">Save</button><br>
            <button type="button" onclick="closeRemoveStockModal();" style="width: 80%;margin-inline: auto;">Cancel</button>
        </form>
    </dialog>

    <dialog id="removeStockModal-manual">
        <form action="/stores/remove.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>remove stock</h4>

            <h4 id="removeStockModal-manual-userLocation"><?= $_SESSION['location'] ?></h4>
            <input type="hidden" name="location" id="removeStockModal-manual-hiddenLocationInput" value="<?= $_SESSION['location'] ?>" required>

            <select name="removeStockSelectType" required>
                <option value="">--- type ---</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>

            <select name="removeStockSelectColor" required>
                <option value="">--- color ---</option>
                <?php foreach ($colors as $color) : ?>
                    <option value="<?= $color['newCode'] ?>"><?= $color['color'] ?></option>
                <?php endforeach ?>
            </select>

            <select name="removeStockSelectSize" required>
                <option value="">--- size ---</option>
                <?php foreach ($sizes as $size) : ?>
                    <option value="<?= $size['code'] ?>"><?= $size['size'] ?></option>
                <?php endforeach ?>
            </select>

            <label for="removeStockModal-manual-order">order</label>
            <input type="text" name="order" id="removeStockModal-manual-order">

            <label for="removeStockModal-manual-amount" style="margin-top: 1rem;">amount</label>
            <input id="removeStockModal-manual-amount" name="amount" type="number" min="0" style="text-align: center;" required>

            <label for="reason-select" style="margin-top: 1rem;">reason</label>
            <select id="reason-select" name="reason" required>
                <option value="">--Please choose an option--</option>
                <?= $options ?>
            </select>

            <button type="submit" name="manualRemoveStock" style="width: 80%;margin-inline: auto;">Save</button><br>
            <button type="button" onclick="document.getElementById('removeStockModal-manual').close();" style="width: 80%;margin-inline: auto;">Cancel</button>
        </form>
    </dialog>

    <dialog id="transferStockModal">
        <form action="/stores/transfer.php" method="post" style="display: flex;flex-direction: column;text-align: center;">

            <div id="transferStockModal-qrReader" style="width: 200px;margin-inline: auto;"></div>

            <label for="stockCodeInput">code</label>
            <input type="text" id="stockCodeInput" name="stockCodeInput" minlength="11" maxlength="11" required>

            <label for="amountInput">amount</label>
            <input type="number" id="amountInput" name="amountInput" min="0" style="text-align: inherit;" required>

            <label for="transferFromSelect">from</label>
            <select name="transferFromSelect" id="transferFromSelect">
                <option value="none">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>

            <label for="transferToSelect">to</label>
            <select name="transferToSelect" id="transferToSelect">
                <option value="none">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>
            <button type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="closeTransferStockModal();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>

    <dialog id="batchAddStockModal">
        <h4>batch add stock</h4>
        <h4><?= $_SESSION['location'] ?></h4>
        <form action="/stores/add.php" method="post" style="text-align: center;">
            <select name="batchAddStyle" style="margin-bottom: 1rem;">
                <?php foreach ($types as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>
            <select name="batchAddColor" style="margin-bottom: 1rem;">
                <?php foreach ($colors as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['color'] ?></option>
                <?php endforeach ?>
            </select>
            <br>
            <?php foreach ($sizes as $size) : ?>
                <input type="number" name="<?= $size['size'] ?>" id="" placeholder="<?= $size['size'] ?>" min="0" style="width: 7ch;">
            <?php endforeach ?>

            <input type="hidden" name="location" value="<?= $_SESSION['location'] ?? 'none' ?>" id="">

            <button type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="closeBatchAddStockModal();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>

</body>

</html>
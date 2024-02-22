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
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.png">
    <script src="/assets/qrCode.js"></script>
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/stores/stores.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
    $sLocation = $_SESSION['location'] ?? 'location error';
    ?>
    <div style="<?= isset($_SESSION['userName']) ? 'background-image: inherit ;position: sticky;top:0px;' : '' ?>">
        <h1>Stores</h1>
        <?php if (isset($_SESSION['userName'])) : ?>
            <div>
                <button id="addStockButton" onclick="addStockButton()">add</button>
                <button id="batchAddStockButton" onclick="document.getElementById('batchAddStockModal').showModal();">batch add</button>
                <button id="removeStockButton" onclick="removeStockButton()">remove</button>
                <button id="transferStockButton" onclick="transferStockButton()">transfer</button>
                <button id="transferStockButton" onclick="document.getElementById('batchTransferModal').showModal();">batch transfer</button>
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
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">example stock code = 202M590FXS0</span>
        </div>
        <form action="/stores/add.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>Add stock</h4>

            <h4 id="addStockModal-userLocation"><?= $sLocation ?></h4>
            <input type="hidden" name="location" id="addStockModal-hiddenLocationInput" value="<?= $sLocation ?>" required>

            <div id="addStockModal-qrReader" class="qr-reader"></div>

            <button type="button" id="addStockModal-manualButton" onclick="addStockManualInput()">manual input</button>

            <label for="addStockModal-stockCode">stock code</label>
            <input id="addStockModal-stockCode" name="code" type="text" minlength="11" maxlength="11" required>

            <label for="addStockModal-amount" style="margin-top: 1rem;">amount</label>
            <input id="addStockModal-amount" name="amount" type="number" min="1" style="text-align: center;" required>

            <button id="addStockSubmitButton" type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="closeAddStockModal();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>

    <dialog id="addStockModal-manual">
        <form action="/stores/add.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>Add stock</h4>

            <h4 id="addStockModal-manual-userLocation"><?= $sLocation ?></h4>
            <input type="hidden" name="location" id="addStockModal-manual-hiddenLocationInput" value="<?= $sLocation ?>" required>

            <select id="addStockSelectType" name="addStockSelectType" required>
                <option value="">--- type ---</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>

            <select id="addStockSelectColor" name="addStockSelectColor" required>
                <option value="">--- color ---</option>
                <?php foreach ($colors as $color) : ?>
                    <option value="<?= $color['newCode'] ?>"><?= $color['color'] ?></option>
                <?php endforeach ?>
            </select>

            <select id="addStockSelectSize" name="addStockSelectSize" required>
                <option value="">--- size ---</option>
                <?php foreach ($sizes as $size) : ?>
                    <option value="<?= $size['code'] ?>"><?= $size['size'] ?></option>
                <?php endforeach ?>
            </select>

            <label for="addStockModal-manual-amount" style="margin-top: 1rem;">amount</label>
            <input id="addStockModal-manual-amount" name="amount" type="number" min="1" style="text-align: center;" required>

            <button id="addStockModal-manual-submit" type="submit" name="manualAddStock" style="width: 80%;margin-inline: auto;">Save</button><br>
            <button type="button" onclick="document.getElementById('addStockModal-manual').close();" style="width: 80%;margin-inline: auto;">Cancel</button>
        </form>
    </dialog>

    <dialog id="batchAddStockModal">
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">add multiple entries of the selected type, color and sizes</span>
        </div>
        <h4>batch add stock</h4>
        <form action="/stores/add.php" method="post" style="text-align: center;">
            <select name="batchAddStockLocation" id="batchAddStockLocation" style="text-align: center;">
                <?php foreach ($locations as $location) : ?>
                    <option <?= $_SESSION['location'] ?? '' == $location ? 'selected' : '' ?> value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>
            <br>
            <select id="batchAddStyle" name="batchAddStyle" style="margin-bottom: 1rem;" onchange="batchAddStockType = this.value;updateBatchAddStockCode();">
                <option value="">--- select type ---</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>
            <select id="batchAddColor" name="batchAddColor" style="margin-bottom: 1rem;" onchange="batchAddStockColor = this.value;updateBatchAddStockCode();">
                <option value="">--- select color ---</option>
                <?php foreach ($colors as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['color'] ?></option>
                <?php endforeach ?>
            </select>
            <br>
            <input type="text" id="batchAddModalStockCodeInput" name="batchAddModal-stockCodeInput" placeholder="Stock code" minlength="8" maxlength="8" required>
            <br>
            <?php foreach ($sizes as $size) : ?>
                <input type="number" name="<?= $size['size'] ?>" id="" placeholder="<?= $size['size'] ?>" min="1" style="width: 7ch;">
            <?php endforeach ?>

            <button id="batchAddStock-submit" name="batchAddStock" type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="document.getElementById('batchAddStockModal').close();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>

    <dialog id="removeStockModal">
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">example stock code = 202M590FXS0 <br>To remove stock, a valid order number is required, excluding "other"</span>
        </div>
        <form action="/stores/remove.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>remove stock</h4>

            <h4 id="removeStockModal-userLocation"><?= $sLocation ?></h4>
            <input type="hidden" name="location" id="removeStockModal-hiddenLocationInput" value="<?= $sLocation ?>" required>

            <h4 id="removeStockModal-showUser" style="color: red;"></h4>

            <div id="removeStockModal-qrReader" class="qr-reader"></div>

            <button type="button" id="removeStockModal-manualButton" onclick="removeStockManualInput()">manual input</button>

            <label id="removeStockModal-stockCode-label" for="removeStockModal-stockCode">stock code</label>
            <input id="removeStockModal-stockCode" name="code" type="text" minlength="11" maxlength="11" required>

            <label for="removeStockModal-order">order</label>
            <input type="text" name="order" id="removeStockModal-order">

            <label for="removeStockModal-amount" style="margin-top: 1rem;">amount</label>
            <input id="removeStockModal-amount" name="amount" type="number" min="1" style="text-align: center;" required>

            <label for="reason-select" style="margin-top: 1rem;">reason</label>
            <select id="reason-select" name="reason" required>
                <option value="">--Please choose an option--</option>
                <?= $options ?>
            </select>

            <button id="removeStock-submit" type="submit" style="width: 80%;margin-inline: auto;">Save</button><br>
            <button type="button" onclick="closeRemoveStockModal();" style="width: 80%;margin-inline: auto;">Cancel</button>
        </form>
    </dialog>

    <dialog id="removeStockModal-manual">
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">To remove stock, a valid order number is required, excluding "other"</span>
        </div>
        <form action="/stores/remove.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;text-align: center;">
            <h4>remove stock</h4>

            <h4 id="removeStockModal-manual-userLocation"><?= $sLocation ?></h4>
            <input type="hidden" name="location" id="removeStockModal-manual-hiddenLocationInput" value="<?= $sLocation ?>" required>

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
            <input id="removeStockModal-manual-amount" name="amount" type="number" min="1" style="text-align: center;" required>

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
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">example stock code = 202M590FXS0</span>
        </div>
        <h4>Transfer stock</h4>
        <form action="/stores/transfer.php" method="post" style="display: flex;flex-direction: column;text-align: center;">

            <div id="transferStockModal-qrReader" class="qr-reader"></div>

            <label for="stockCodeInput">code</label>
            <input type="text" id="stockCodeInput" name="stockCodeInput" minlength="11" maxlength="11" required>

            <label for="amountInput">amount</label>
            <input type="number" id="amountInput" name="amountInput" min="1" style="text-align: inherit;" required>

            <label for="transferFromSelect">from</label>
            <select name="transferFromSelect" id="transferFromSelect" required>
                <option value="">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>

            <label for="transferToSelect">to</label>
            <select name="transferToSelect" id="transferToSelect" required>
                <option value="">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>
            <button id="transfer-manual-button" type="button" style="width: 80%;" onclick="closeTransferStockModal();document.getElementById('transferStockManualModal').showModal();">manual</button><br>
            <button id="transfer-submit" name="transferStock" type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="closeTransferStockModal();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>

    <dialog id="transferStockManualModal">
        <h4>Transfer stock Manual</h4>
        <form action="/stores/transfer.php" method="post" style="display: flex;flex-direction: column;text-align: center;align-items: center;">

            <select id="transferManualStyle" name="transferManualStyle" style="margin-bottom: 1rem;width: 100%;" onchange="manualTransferStockCodeType = this.value;updateManualTransferStockCode();">
                <option value="">--- select type ---</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>
            <select id="transferManualColor" name="transferManualColor" style="margin-bottom: 1rem;width: 100%;" onchange="manualTransferStockCodeColor = this.value;updateManualTransferStockCode();">
                <option value="">--- select color ---</option>
                <?php foreach ($colors as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['color'] ?></option>
                <?php endforeach ?>
            </select>
            <select id="transferManualSize" name="transferManualSize" style="margin-bottom: 1rem;width: 100%;" onchange="manualTransferStockCodeSize = this.value;updateManualTransferStockCode();">
                <option value="">--- select color ---</option>
                <?php foreach ($sizes as $size) : ?>
                    <option value="<?= $size['code'] ?>"><?= $size['size'] ?></option>
                <?php endforeach ?>
            </select>

            <input type="text" id="transferStockManual-stockCode" name="stockCodeInput" minlength="11" maxlength="11" style="text-align: inherit;" placeholder="stockCode" required>

            <label for="amountInput">amount</label>
            <input type="number" id="ManualAmountInput" name="amountInput" min="1" style="text-align: inherit;" required>

            <label for="ManualTransferFromSelect">from</label>
            <select name="transferFromSelect" id="ManualTransferFromSelect" required>
                <option value="">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>

            <label for="manualTransferToSelect">to</label>
            <select name="transferToSelect" id="ManualTransferToSelect" required>
                <option value="">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>

            <button id="manual-transfer-submit" type="submit" name="transferStockManual" style="width: 80%;">Save</button><br>
            <button type="button" onclick="document.getElementById('transferStockManualModal').close();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>

    <dialog id="batchTransferModal">
        <div class="with-tooltip"><img src="/assets/images/help.png" alt="help" class="help-icon">
            <span class="tooltip-text">transfer multiple of type and color</span>
        </div>
        <h4>batch transfer stock</h4>
        <form action="/stores/transfer.php" method="post" style="text-align: center;">
            <select id="batchTransferStyle" name="batchTransferStyle" style="margin-bottom: 1rem;" onchange="batchTransferStockCodeType = this.value;updateBatchTransferStockCode();">
                <option value="none">--- select type ---</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['type'] ?></option>
                <?php endforeach ?>
            </select>
            <select id="batchTransferColor" name="batchTransferColor" style="margin-bottom: 1rem;" onchange="batchTransferStockCodeColor = this.value;updateBatchTransferStockCode();">
                <option value="none">--- select color ---</option>
                <?php foreach ($colors as $type) : ?>
                    <option value="<?= $type['newCode'] ?>"><?= $type['color'] ?></option>
                <?php endforeach ?>
            </select>
            <br>
            <select name="batchTransferFromSelect" id="batchTransferFromSelect" required>
                <option value="">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>

            <label for="batchTransferToSelect">to</label>
            <select name="batchTransferToSelect" id="batchTransferToSelect" required>
                <option value="">--- select option ---</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?= $location ?>"><?= $location ?></option>
                <?php endforeach ?>
            </select>
            <br>
            <input type="text" id="batchTransferModalStockCodeInput" name="batchTransferModal-stockCodeInput" placeholder="Stock code" minlength="8" maxlength="8" required>
            <br>
            <?php foreach ($sizes as $size) : ?>
                <input type="number" name="<?= $size['size'] ?>" id="" placeholder="<?= $size['size'] ?>" min="1" style="width: 7ch;">
            <?php endforeach ?>


            <button id="batchTransferStock-submit" name="batchTransferStock" type="submit" style="width: 80%;">Save</button><br>
            <button type="button" onclick="document.getElementById('batchTransferModal').close();" style="width: 80%;">Cancel</button>
        </form>
    </dialog>
</body>

</html>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';

$Auth = new Auth();
$Stock = new Stock();
$sizes = $Stock->getSizes();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>orders</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/styles.css">
    <!-- <link rel="stylesheet" href="/assets/light.css"> -->
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/orders/orders.js" defer></script>
</head>

<body>

    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div style="<?= isset($_SESSION['userName']) ? 'background-image: inherit ;position: sticky;top:0px;' : '' ?>">
        <h1>orders</h1>
        <?php if (isset($_SESSION['userName'])) : ?>
            <div>
                <button onclick="document.getElementById('newOrderModal').showModal()">new order</button>
            </div>
        <?php endif ?>
        <hr>
    </div>

    <section style="display: flex;flex-direction: column;">
        <input onkeyup="" type="search" id="search" placeholder="search..." class="border" style="margin-block: 1rem;" />
        <table class="border" style="width: 100%;">
            <thead>
                <tr>
                    <th>placeholder</th>
                </tr>
            </thead>
            <tbody id="searchResults">
                <!-- placeholder -->
            </tbody>
        </table>
    </section>

    <!------------------------ modal section ------------------------------------>
    <dialog id="newOrderModal">
        <form action="/orders/control.php" method="post" autocomplete="off" style="display: flex;flex-direction: column;align-items: center;">

            <h4>new order</h4>
            <hr style="width: 100%;">

            <div style="display: flex; align-items: center;gap:2rem;margin-bottom: 1rem;">
                <input name="orderName" type="text" placeholder="order name">
                <label>
                    Print
                    <input type="checkbox" name="printCheckbox" onchange="updateSelectElements(this)" data-embellishment="print">
                </label>
                <label>
                    Embroidery
                    <input type="checkbox" name="embCheckbox" onchange="updateSelectElements(this)" data-embellishment="embroidery">
                </label>
                <label>
                    Transfer
                    <input type="checkbox" name="transferCheckbox" onchange="updateSelectElements(this)" data-embellishment="transfer">
                </label>
                <label>
                    DTF
                    <input type="checkbox" name="dtfCheckbox" onchange="updateSelectElements(this)" data-embellishment="DTF">
                </label>
            </div>
            <div id="garmentInputWrap">
                <div data-input-number="0">
                    <input name="garment" type="text" placeholder="garment">
                    <input name="color" type="text" placeholder="color">
                    <?php foreach ($sizes as $size) : ?>
                        <input type="number" name="<?= $size['size'] ?>" id="" placeholder="<?= $size['size'] ?>" min="0" style="width: 7ch;">
                    <?php endforeach ?>
                    <button type="button" onclick="cloneInput(this)" style="margin: 0;">+</button>
                </div>
            </div>

            <hr style="width: 100%;">

            <h4>embellishments</h4>
            <div style="display: flex; align-items: center;gap:1rem;text-align: center;">
                <label>
                    Front
                    <select name="frontEmbellishment" data-embellishmentSelect="" style="width: 10ch;">
                        <option value="">none</option>
                    </select>
                </label>
                <label>
                    Back
                    <select name="backEmbellishment" data-embellishmentSelect="" style="width: 10ch;">
                        <option value="">none</option>
                    </select>
                </label>
                <label>
                    L-sleeve
                    <select name="lSleeveEmbellishment" data-embellishmentSelect="" style="width: 10ch;">
                        <option value="">none</option>
                    </select>
                </label>
                <label>
                    R-sleeve
                    <select name="RSleeveEmbellishment" data-embellishmentSelect="" style="width: 10ch;">
                        <option value="">none</option>
                    </select>
                </label>
                <label>
                    Neck
                    <select name="neckEmbellishment" data-embellishmentSelect="" style="width: 10ch;">
                        <option value="">none</option>
                    </select>
                </label>
                <label>
                    <input type="text" name="otherEmbellishmentName" id="" placeholder="Other" style="width: 10ch;margin-bottom: 0.5rem;">
                    <select name="otherEmbellishment" data-embellishmentSelect="" style="width: 10ch;">
                        <option value="">none</option>
                    </select>
                </label>
            </div>

            <hr style="width: 100%;">

            <section style="display: flex;gap: 2rem;">
                <label>
                    <h4>packing</h4>
                    <select name="packingSelect">
                        <option value="">none</option>
                        <option value="resealable">resealable bags</option>
                        <option value="flat">flat pack</option>
                    </select>
                </label>

                <label>
                    <h4>Delivery</h4>
                    <select name="deliverySelect">
                        <option value="">none</option>
                        <option value="dpd">DPD</option>
                        <option value="collection">Collection</option>
                    </select>
                </label>

                <label>
                    <h4>Delivered by</h4>
                    <input type="date" name="deliveryDate">
                </label>

                <label>
                    <h4>sample </h4>
                    <input type="checkbox" name="sampleCheckbox">
                </label>

                <label>
                    <h4>as previous</h4>
                    <input type="checkbox" name="asPreviousCheckbox">
                </label>
            </section>

            <hr style="width: 100%;">

            <h4>notes</h4>
            <textarea name="notes" rows="5" style="width: 80%;resize: none;text-align: center;border-radius: 10px;border: none;"></textarea>

            <hr style="width: 100%;">
            <div id=fileInputWrap>
                <h4>Files</h4>
                <div data-input-number="0">
                    <input type="file" name="fileUpload">
                    <input type="text" placeholder="fileDescription">
                    <button type="button" onclick="cloneInput(this)">+</button>
                </div>
            </div>

            <hr style="width: 100%;">

            <button type="submit" name="newOrder" style="width: 80%;">Save</button><br>
            <button type="button" onclick="document.getElementById('newOrderModal').close()" style="width: 80%;">Cancel</button>
        </form>
    </dialog>
    <!------------------------ end modal section ------------------------------------>
</body>

</html>
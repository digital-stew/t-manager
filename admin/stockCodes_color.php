<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Admin = new Admin();
$Auth = new Auth();
$Stock = new Stock();

if (isset($_POST['deleteColor']) && isset($_GET['colorId'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->deleteStockColor($_GET['colorId']);
    if ($res) header('location: /admin?flashUser=Color deleted');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
if (isset($_POST['addColor']) && isset($_POST['newCode']) && isset($_POST['oldCode']) && isset($_POST['color'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->addStockColor($_POST['newCode'], $_POST['oldCode'], $_POST['color']);
    if ($res) header('location: /admin?flashUser=New color added');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

//=============add color modal==================
if (isset($_GET['addColor'])) {
    session_start();
    $Auth->isAdmin();

    $html = <<<EOD
    <form method="post" action="/admin/stockCodes_color.php?addColor=true" class='newBox' autocomplete="off">
        <h4>Add stock color</h4>
        <label>
            new code
            <input type="text" name="newCode"  required>
        </label>
        <label>
            old code
            <input type="text" name="oldCode" required>
        </label>
        <label>
            color
            <input type="text" name="color" required>
        </label>
        <button type='submit' name='addColor'>Save</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}


//=============modal==================
if (isset($_GET['colorId'])) {
    session_start();
    $Auth->isAdmin();
    $colorTypes = $Stock->getColors();

    foreach ($colorTypes as $colorType) {
        if ($colorType['id'] == $_GET['colorId']) {
            $selectedStock = [
                'id' => $colorType['id'],
                'newCode' => $colorType['newCode'],
                'oldCode' => $colorType['oldCode'],
                'color' => $colorType['color'],
            ];
        }
    }

    $html = <<<EOD
    <form method="post" action="/admin/stockCodes_color.php?colorId={$selectedStock['id']}"  class='newBox' autocomplete="off">
        <h4>Stock color</h4>
        <p>Id: {$selectedStock['id']}</p>
        <p>new code: {$selectedStock['newCode']}</p>
        <p>old code: {$selectedStock['oldCode']}</p>
        <p>color: {$selectedStock['color']}</p>
        <button type='submit' onclick="return confirm('Permanently delete color?')" name='deleteColor'>Delete</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}
//=======================================

$Auth->isAdmin();
$stockColors = $Stock->getColors();
?>
<section id="adminView" style="width: fit-content;" class="newBox border">
    <h2>Stock colors</h2>
    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>new code</th>
                <th>old code</th>
                <th>color</th>
            </tr>
        </thead>
        <tbody id="searchResults">
            <?php foreach ($stockColors as $stock) : ?>
                <tr onclick="showModal('/admin/stockCodes_color.php?colorId=<?= $stock['id'] ?>')">
                    <td><?= $stock['id'] ?></td>
                    <td><?= $stock['newCode'] ?></td>
                    <td><?= $stock['oldCode'] ?></td>
                    <td><?= $stock['color'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button onclick="showModal('/admin/stockCodes_color.php?addColor=true')">Add new color</button>
</section>
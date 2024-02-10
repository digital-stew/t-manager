<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Admin = new Admin();
$Auth = new Auth();
$Stock = new Stock();


if (isset($_POST['deleteType']) && isset($_GET['typeId'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->deleteStockType($_GET['typeId']);
    if ($res) header('location: /admin?flashUser=Type deleted');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
if (isset($_POST['addType']) && isset($_POST['newCode']) && isset($_POST['oldCode']) && isset($_POST['type'])) {
    session_start();
    $Auth->isAdmin();
    $trueCode = isset($_POST['trueCode']) ? true : false;
    $res = $Admin->addStockType($_POST['newCode'], $_POST['oldCode'], $_POST['type'], $trueCode);
    if ($res) header('location: /admin?flashUser=New type added');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

//=============add type modal==================
if (isset($_GET['addType'])) {
    session_start();
    $Auth->isAdmin();

    $html = <<<EOD
    <form method="post" action="/admin/stockCodes_type.php?addType=true"  class='newBox' autocomplete="off">
        <h4>add stock type</h4>
        <label>
            new code
            <input type="text" name="newCode" minlength="3" required>
        </label>
        <label>
            old code
            <input type="text" name="oldCode" minlength="3" required>
        </label>
        <label>
            type
            <input type="text" name="type" required>
        </label>
        true code
        <input type="checkbox" name="trueCode" >
        </label> 
        <button type='submit' name='addType'>Save</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}
//=============modal==================
if (isset($_GET['typeId'])) {
    session_start();
    $Auth->isAdmin();
    $stockTypes = $Stock->getTypes(true);

    foreach ($stockTypes as $stockType) {
        if ($stockType['id'] == $_GET['typeId']) {
            $selectedStock = [
                'id' => $stockType['id'],
                'newCode' => $stockType['newCode'],
                'oldCode' => $stockType['oldCode'],
                'type' => $stockType['type'],
                'trueCode' => $stockType['trueCode'],
            ];
        }
    }

    if ($selectedStock['trueCode']) {
        $trueCode = "this type is a true code and \"{$selectedStock['newCode']}\" will appear in stock tables and on boxes <br> remove with caution!!";
    } else {
        $trueCode = "this is a dummy code for the purpose of dealing with code changes beyond are control";
    }

    $html = <<<EOD
    <form method="post" action="/admin/stockCodes_type.php?typeId={$selectedStock['id']}"  class='newBox' autocomplete="off">
        <h4>stock type</h4>
         <p>Id: {$selectedStock['id']}</p>
        <p>new code: {$selectedStock['newCode']}</p>
        <p>old code: {$selectedStock['oldCode']}</p>
        <p>type: {$selectedStock['type']}</p>
        <p>{$trueCode}</p>
        <button type='submit' onclick="return confirm('Permanently delete type?')" name='deleteType'>Delete</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}
//=======================================
$Auth->isAdmin();
$stockTypes = $Stock->getTypes(true);
?>
<section style="width: fit-content;" class="newBox border">
    <h2>Stock types</h2>
    <table id="stockTypes-table">
        <thead>
            <tr>
                <th>new code<br>(on boxes)</th>
                <th>old code<br>(on orders)</th>
                <th>type</th>
                <th>true code</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stockTypes as $stock) : ?>
                <tr onclick="showModal('/admin/stockCodes_type.php?typeId=<?= $stock['id'] ?>')">
                    <td><?= $stock['newCode'] ?></td>
                    <td><?= $stock['oldCode'] ?></td>
                    <td><?= $stock['type'] ?></td>
                    <td><input type="checkbox" disabled <?= $stock['trueCode'] ? 'checked' : '' ?>></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button id="stockTypesAdd-button" onclick="showModal('/admin/stockCodes_type.php?addType=true')">Add new type</button>
</section>
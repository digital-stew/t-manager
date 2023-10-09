<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Admin = new Admin();
$Auth = new Auth();
$Stock = new Stock();

if (isset($_POST['deleteSize']) && isset($_GET['sizeId'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->deleteStockSize($_GET['sizeId']);
    if ($res) header('location: /admin?flashUser=Size deleted');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
if (isset($_POST['addSize']) && isset($_POST['code']) && isset($_POST['size'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->addStockSize($_POST['code'], $_POST['size']);
    if ($res) header('location: /admin?flashUser=New size added');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

//=============add size modal==================
if (isset($_GET['addSize'])) {
    session_start();
    $Auth->isAdmin();

    $html = <<<EOD
    <form method="post" action="/admin/stockCodes_size.php?addSize=true" class='newBox' autocomplete="off">
        <h4>Add stock size</h4>
        <label>
            code
            <input type="text" name="code" required>
        </label>
        <label>
            size
            <input type="text" name="size" required>
        </label>
        <button type='submit' name='addSize'>Save</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}


//=============modal==================
if (isset($_GET['sizeId'])) {
    session_start();
    $Auth->isAdmin();
    $sizeTypes = $Stock->getSizes();

    foreach ($sizeTypes as $sizeType) {
        if ($sizeType['id'] == $_GET['sizeId']) {
            $selectedStock = [
                'id' => $sizeType['id'],
                'code' => $sizeType['code'],
                'size' => $sizeType['size']
            ];
        }
    }

    $html = <<<EOD
    <form method="post" action="/admin/stockCodes_size.php?sizeId={$selectedStock['id']}"  class='newBox' autocomplete="off">
        <h4>Stock size</h4>
        <p>Id: {$selectedStock['id']}</p>
        <p>code: {$selectedStock['code']}</p>
        <p>size: {$selectedStock['size']}</p>
        <button type='submit' onclick="return confirm('Permanently delete size?')" name='deleteSize'>Delete</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}
//=======================================

$Auth->isAdmin();
$stockSizes = $Stock->getSizes();
?>
<section id="adminView" style="width: fit-content;" class="newBox border">
    <h2>Stock sizes</h2>
    <table>
        <thead>
            <tr>
                <th>code</th>
                <th>size</th>
            </tr>
        </thead>
        <tbody id="searchResults">
            <?php foreach ($stockSizes as $stock) : ?>
                <tr onclick="showModal('/admin/stockCodes_size.php?sizeId=<?= $stock['id'] ?>')">
                    <td><?= $stock['code'] ?></td>
                    <td><?= $stock['size'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button onclick="showModal('/admin/stockCodes_size.php?addSize=true')">Add new size</button>
</section>
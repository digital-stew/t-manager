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
    $trueCode = isset($_POST['trueCode']) ? true : false;
    $res = $Admin->addStockColor($_POST['newCode'], $_POST['oldCode'], $_POST['color'], $trueCode);
    if ($res) header('location: /admin?flashUser=New color added');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

if (isset($_POST['editColor']) && isset($_POST['newCode']) && isset($_POST['oldCode'])) {
    session_start();
    $Auth->isAdmin();
    $trueCode = isset($_POST['trueCodeCheckbox']) ? true : false;

    $res = $Admin->editStockColor($_GET['colorId'], $_POST['newCode'], $_POST['oldCode'], $_POST['color'], $trueCode);
    if ($res) header('location: /admin?flashUser= color edited');
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
            <input type="text" name="newCode" minlength="3" required>
        </label>
        <label>
            old code
            <input type="text" name="oldCode" minlength="3"  required>
        </label>
        <label>
            color
            <input type="text" name="color" required>
        </label>
        <label>
        true code
        <input type="checkbox" name="trueCode" >
        </label>      
        <button type='submit' name='addColor'>Save</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}

//=============edit modal==================
if (isset($_GET['editColor'])) {
    session_start();
    $Auth->isAdmin();
    $colorTypes = $Stock->getColors(true);

    foreach ($colorTypes as $colorType) {
        if ($colorType['id'] == $_GET['colorId']) {
            $selectedStock = [
                'id' => $colorType['id'],
                'newCode' => $colorType['newCode'],
                'oldCode' => $colorType['oldCode'],
                'color' => $colorType['color'],
                'trueCode' => $colorType['trueCode']
            ];
        }
    }

    if ($selectedStock['trueCode']) {
        $trueCode = "<input type='checkbox' name='trueCodeCheckbox' id='trueCodeCheckbox' checked>";
    } else {
        $trueCode = "<input type='checkbox' name='trueCodeCheckbox' id='trueCodeCheckbox'>";
    }

    echo "
    <form method='post' action='/admin/stockCodes_color.php?colorId={$selectedStock['id']}'  class='newBox'  autocomplete='off'>
        <h4>Stock color</h4>
        <p>Id: {$selectedStock['id']}</p>
        
        <label for='newCode'>new code:</label>
        <input type='text' name='newCode' id='newCode' style='width:min-content;margin-inline:auto;margin-bottom:1rem;' value='{$selectedStock['newCode']}'>
        
        
        <label for='oldCode'>old code:</label>
        <input type='text' name='oldCode' id='oldCode' style='width:min-content;margin-inline:auto;margin-bottom:1rem;' value='{$selectedStock['oldCode']}'>
        

        <label for='color'>color:</label>
        <input type='text' name='color' id='color' style='width:min-content;margin-inline:auto;margin-bottom:1rem;' value='{$selectedStock['color']}'>
        

        <div style='margin-bottom:1rem;'>
        <label for='trueCodeCheckbox'>true code:</label>
        {$trueCode}
        </div>

        
        <button type='submit' name='editColor'>save</button>
        <button type='button' onclick='closeModal();'>Back</button>
        
    </form>
    ";

    // echo $html;
    die();
}

//=======================================

//=============modal==================
if (isset($_GET['colorId'])) {
    session_start();
    $Auth->isAdmin();
    $colorTypes = $Stock->getColors(true);

    foreach ($colorTypes as $colorType) {
        if ($colorType['id'] == $_GET['colorId']) {
            $selectedStock = [
                'id' => $colorType['id'],
                'newCode' => $colorType['newCode'],
                'oldCode' => $colorType['oldCode'],
                'color' => $colorType['color'],
                'trueCode' => $colorType['trueCode']
            ];
        }
    }

    if ($selectedStock['trueCode']) {
        $trueCode = "this color is a true code and \"{$selectedStock['newCode']}\" will appear in stock tables and on boxes <br> remove with caution!!";
    } else {
        $trueCode = "this is a dummy code for the purpose of dealing with code changes beyond are control";
    }

    $html = <<<EOD
    <form method="post" action="/admin/stockCodes_color.php?colorId={$selectedStock['id']}"  class='newBox' autocomplete="off">
        <h4>Stock color</h4>
        <p>Id: {$selectedStock['id']}</p>
        <p>new code: {$selectedStock['newCode']}</p>
        <p>old code: {$selectedStock['oldCode']}</p>
        <p>color: {$selectedStock['color']}</p>
        <p>{$trueCode}</p>
        <button type='button' onclick="showModal('/admin/stockCodes_color.php?editColor=true&colorId={$selectedStock['id']}')">Edit</button>
        <button type='submit' onclick="return confirm('Permanently delete color?')" name='deleteColor'>Delete</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}
//=======================================


$Auth->isAdmin();
$stockColors = $Stock->getColors(true);
?>



<section id="adminView" style="width: fit-content;" class="newBox border">
    <h2>Stock colors</h2>
    <table id="stockColor-table">
        <thead>
            <tr>
                <th>new code<br>(on boxes)</th>
                <th>old code<br>(on orders)</th>
                <th>color</th>
                <th>true code</th>
            </tr>
        </thead>
        <tbody id="searchResults">
            <?php foreach ($stockColors as $stock) : ?>
                <tr onclick="showModal('/admin/stockCodes_color.php?colorId=<?= $stock['id'] ?>')">
                    <td><?= $stock['newCode'] ?></td>
                    <td><?= $stock['oldCode'] ?></td>
                    <td><?= $stock['color'] ?></td>
                    <td><input type="checkbox" disabled <?= $stock['trueCode'] ? 'checked' : '' ?>></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button id="stockColorAdd-button" onclick="showModal('/admin/stockCodes_color.php?addColor=true')">Add new color</button>
</section>
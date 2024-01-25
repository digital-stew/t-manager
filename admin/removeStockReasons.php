<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Admin = new Admin();
$Auth = new Auth();
$Stock = new Stock();
$removeStockReasons = $Admin->getRemoveStockReasons();

if (isset($_POST['deleteReason']) && isset($_GET['id'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->deleteRemoveStockReason($_GET['id']);
    if ($res) header('location: /admin?flashUser=deleted');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
if (isset($_POST['addNewReason']) && isset($_POST['reason'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->addRemoveStockReason($_POST['reason']);
    if ($res) header('location: /admin?flashUser=New reason added');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}

//=============modal==================
if (isset($_GET['getId'])) {
    session_start();
    $Auth->isAdmin();
    foreach ($removeStockReasons as $reason) {
        if ($reason['id'] == $_GET['getId']) {
            $selectedReason = $reason;
            break;
        }
    }
    $html = <<<EOD
    <form method="post" action="/admin/removeStockReasons.php?id={$selectedReason['id']}"  class='newBox' autocomplete="off">
        <h4>Reason to remove stock</h4>
        <p>Id: {$selectedReason['id']}</p>
        <p>{$selectedReason['reason']}</p>
        <button type='submit' onclick="return confirm('Permanently delete?')" name='deleteReason'>Delete</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}
//=======================================



$Auth->isAdmin();
?>
<section id="adminView" style="width: fit-content;" class="newBox border">
    <h2>Reasons to remove stock</h2>
    <table>
        <thead>
            <tr>
                <th>reasons</th>
            </tr>
        </thead>
        <tbody id="searchResults">
            <?php foreach ($removeStockReasons as $reason) : ?>
                <tr onclick="showModal('/admin/removeStockReasons.php?getId=<?= $reason['id'] ?>')">
                    <td><?= $reason['reason'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button onclick="document.getElementById('addNewReason-modal').showModal();">Add new</button>
</section>

<dialog id="addNewReason-modal">
    <h4>add new stock remove reason</h4>
    <form action="/admin/removeStockReasons.php" method="post" style="display: flex;flex-direction: column;justify-content: center;">
        <input type="text" name="reason" placeholder="new reason" style="margin-bottom: 1rem;" required>
        <button type='submit' name='addNewReason'>Save</button>
        <button type='button' onclick="document.getElementById('addNewReason-modal').close();">Back</button>
    </form>
</dialog>
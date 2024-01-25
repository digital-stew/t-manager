<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Admin = new Admin();
$Auth = new Auth();
// $Stock = new Stock();
$autoLocations = $Admin->getAutoLocations();

if (isset($_POST['addNewAutoLocation']) && isset($_POST['ipAddress']) && isset($_POST['location'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->addAutoLocation($_POST['ipAddress'], $_POST['location']);
    if ($res) header('location: /admin?flashUser=New auto location added');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
if (isset($_POST['deleteAutoLocation']) && isset($_GET['id'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->deleteAutoLocation($_GET['id']);
    if ($res) header('location: /admin?flashUser=deleted');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
//=============modal==================
if (isset($_GET['getId'])) {
    session_start();
    $Auth->isAdmin();
    foreach ($autoLocations as $auto) {
        if ($auto['id'] == $_GET['getId']) {
            $selectedAuto = $auto;
            break;
        }
    }
    $html = <<<EOD
    <form method="post" action="/admin/autoLocation.php?id={$selectedAuto['id']}"  class='newBox' autocomplete="off">
        <h4>auto location</h4>
        <p>Id: {$selectedAuto['id']}</p>
        <p>ip: {$selectedAuto['ip']}</p>
        <p>location: {$selectedAuto['location']}</p>
        <button type='submit' onclick="return confirm('Permanently delete?')" name='deleteAutoLocation'>Delete</button>
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
    <h2>Auto locations</h2>
    <table>
        <thead>
            <tr>
                <th>ip</th>
                <th>location</th>
            </tr>
        </thead>
        <tbody id="searchResults">
            <?php foreach ($autoLocations as $location) : ?>
                <tr onclick="showModal('/admin/autoLocation.php?getId=<?= $location['id'] ?>')">
                    <td><?= $location['ip'] ?></td>
                    <td><?= $location['location'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button onclick="document.getElementById('addNewAutoLocation-modal').showModal()">new auto location</button>
</section>

<dialog id="addNewAutoLocation-modal">
    <h4>add new auto location</h4>
    <form action="/admin/autoLocation.php" method="post" style="display: flex;flex-direction: column;justify-content: center;">
        <input type="tel" name="ipAddress" placeholder="ip address" style="margin-bottom: 1rem;" required>
        <select name="location" id="" required>
            <option value="">--- select a location ---</option>
            <?php foreach ($Admin->getLocations() as $location) : ?>
                <option value="<?= $location ?>"><?= $location ?></option>
            <?php endforeach ?>
        </select>
        <button type='submit' name='addNewAutoLocation'>Save</button>
        <button type='button' onclick="document.getElementById('addNewAutoLocation-modal').close();">Back</button>
    </form>
</dialog>
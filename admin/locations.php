<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Admin = new Admin();
$Auth = new Auth();
// $Stock = new Stock();
$locations = $Admin->getFullLocations();

if (isset($_POST['addNewLocation']) && isset($_POST['ipAddress']) && isset($_POST['NewLocation'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->addLocation($_POST['ipAddress'], $_POST['NewLocation']);
    if ($res) header('location: /admin?flashUser=New location added');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
if (isset($_POST['deleteLocation']) && isset($_GET['id'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->deleteLocation($_GET['id']);
    if ($res) header('location: /admin?flashUser=deleted');
    else header('Location: /admin?flashUser=ERROR!! Contact admin if problem persists');
    die();
}
//=============modal==================
if (isset($_GET['getId'])) {
    session_start();
    $Auth->isAdmin();
    foreach ($locations as $location) {
        if ($location['id'] == $_GET['getId']) {
            $selectedAuto = $location;
            break;
        }
    }
    $html = <<<EOD
    <form method="post" action="/admin/locations.php?id={$selectedAuto['id']}"  class='newBox' autocomplete="off">
        <h4>auto location</h4>
        <p>Id: {$selectedAuto['id']}</p>
        <p>ip: {$selectedAuto['ip']}</p>
        <p>location: {$selectedAuto['location']}</p>
        <button type='submit' onclick="return confirm('Permanently delete?')" name='deleteLocation'>Delete</button>
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
    <h2>locations</h2>
    <table id="locations-table">
        <thead>
            <tr>
                <th>location</th>
                <th>ip</th>
            </tr>
        </thead>
        <tbody id="searchResults">
            <?php foreach ($locations as $location) : ?>
                <tr onclick="showModal('/admin/locations.php?getId=<?= $location['id'] ?>')">
                    <td><?= $location['location'] ?></td>
                    <td><?= $location['ip'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button id="addLocation-button" onclick="document.getElementById('addLocation-modal').showModal()">Add new location</button>
</section>

<dialog id="addLocation-modal">
    <h4>add new location</h4>
    <form action="/admin/locations.php" method="post" style="display: flex;flex-direction: column;justify-content: center;">
        <input type="text" name="NewLocation" placeholder="new location" style="margin-bottom: 1rem;">
        <input type="tel" name="ipAddress" placeholder="ip address" style="margin-bottom: 1rem;">
        <button type='submit' name='addNewLocation'>Save</button>
        <button type='button' onclick="document.getElementById('addLocation-modal').close();">Back</button>
    </form>
</dialog>
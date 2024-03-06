<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

$Auth = new Auth();
$Log = new Log();
if (isset($_GET['search'])) {
    $log = $Log->search($_GET['search']);
} else {
    $log = $Log->get();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Log</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/admin/log.js" defer></script>
</head>

<body>
    <?php
    require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
    $Auth->isAdmin();
    ?>
    <div>
        <h1>Log</h1>
        <hr>
    </div>

    <section>
        <form action="/admin/log.php" method="get" autocomplete="off" style="height: min-content;text-align: center;margin-bottom: 1rem;">
            <input type="search" id="search" autocomplete="off" name="search" placeholder="search...">
        </form>
        <table class="border" style="width: 100%;">
            <thead>
                <tr>
                    <th>action</th>
                    <th>subject</th>
                    <th>id</th>
                    <th>order</th>
                    <th>note</th>
                    <th>user</th>
                    <th>time</th>
                </tr>
            </thead>
            <tbody id="searchResults">
                <!-- placeholder -->
                <?php foreach ($log as $entry) : ?>
                    <tr>
                        <td><?= $entry['action'] ?></td>
                        <td><?= $entry['subject'] ?></td>
                        <td><?= $entry['logID'] ?></td>
                        <td><?= $entry['orderName'] ?></td>
                        <td><?= $entry['note'] ?></td>
                        <td><?= $entry['userName'] ?></td>
                        <td class='timestamp' data-datetime="true"><?= $entry['timestamp'] ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </section>

    <dialog id="scannerModal" style="text-align: center;">
        <div id="qr-reader" style="width: 200px"></div>
        <div id="qr-reader-results"></div>
        <button onclick="closeCamModal();" style="width: 80%;">cancel</button>
    </dialog>
</body>

</html>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/models/Maintenance.php';

$Maintenance = new Maintenance();
if (isset($_GET['complete'])) {
    $results = $Maintenance->getAll('complete');
} else {
    $results = $Maintenance->getAll('pending');
}

//print_r($results);
//die();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>maintenance log</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/maintenance/maintenance.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div>
        <h1>maintenance log </h1>
        <button onclick="javascript: document.getElementById('maintenanceModal').showModal()">report problem</button>
        <?php if (isset($_GET['complete'])) : ?>
            <button onclick="javascript: window.location.href = '/maintenance'">show pending</button>
        <?php else : ?>
            <button onclick="javascript: window.location.href = '/maintenance?complete=true'">show complete</button>
        <?php endif  ?>
        <hr>
    </div>
    <table class="border">
        <thead>
            <tr>
                <th>id</th>
                <th>problem</th>
                <th>machine</th>
                <th>reported by</th>
                <th>date</th>
                <th>status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result) : ?>
                <tr onclick="showModal('/maintenance/details.php?id=<?= $result['id'] ?>')">
                    <td><?= $result['id'] ?></td>
                    <td><?= $result['problem'] ?></td>
                    <td><?= $result['machine'] ?></td>
                    <td><?= $result['reportedBy'] ?></td>
                    <td class='timestamp'><?= $result['timestamp'] ?></td>
                    <td><?= $result['status'] ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <dialog id="maintenanceModal" style="text-align: center;">
        <h2>report problem</h2>
        <form action="/maintenance/add.php" method="post">
            <input type="text" name="problem" id="problem" placeholder="problem">
            <input type="text" name="machine" id="machine" placeholder="machine">
            <button type="submit" style="width: 80%;margin-top: 2rem;" onclick="javascript: document.getElementById('maintenanceModal').showModal()">Save</button><br>
            <button type="button" style="width: 80%;" onclick="javascript: document.getElementById('maintenanceModal').close()">Cancel</button>
        </form>
    </dialog>

</body>

</html>
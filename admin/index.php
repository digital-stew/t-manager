<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
</head>

<body>

    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
    $Auth = new Auth();
    $Auth->isAdmin();
    ?>
    <div>

        <div style="height: min-content;">
            <h1>Admin</h1>
            <button onclick="replaceElement('adminView','/admin/users.php')">Users</button>
            <hr>
        </div>
        <section id="adminView">
            <!-- placeholder -->
        </section>
    </div>

</body>

</html>
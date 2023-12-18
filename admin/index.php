<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
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
            <hr>
        </div>
        <section style="display: flex; flex-wrap: wrap;gap: 2rem;justify-content: center;">
            <p>add "open to public"</p>
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin/users.php'; ?>
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin/stockCodes_type.php'; ?>
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin/stockCodes_color.php'; ?>
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin/stockCodes_size.php'; ?>
        </section>
    </div>

</body>

</html>
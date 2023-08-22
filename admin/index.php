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

    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>

    <div class="layout">
        <h1>Admin</h1>
        <button onclick="replaceElement('show', '/admin/users.php')">Users</button>
        <hr>
        <div class="sideBySide">
            <section class="tableSection">
                <table id="show" class="border">
                    <!-- placeholder for new data -->
                </table>
            </section>
            <div class="sampleWrap" id="">
                <section id="adminLeft" class="show_sample_section">
                    <!-- placeholder for new data -->
                </section>
            </div>
        </div>
    </div>
</body>

</html>
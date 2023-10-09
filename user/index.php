<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Stores</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div>

        <h1>user settings for <?= $_SESSION['userName'] ?></h1>

        <hr>
    </div>

    <div style="display: flex; flex-wrap: wrap;gap: 2rem;justify-content: center;">

        <section id="adminView" style="width: fit-content;text-align: center;" class="newBox border">
            <h2>password change</h2>

            <form action="/api/login.php" method="post">
                <input autoComplete="new-password" type="password" name="oldPassword" placeholder="old password"> <br>
                <hr>
                <input autoComplete="new-password" type="password" name="password1" placeholder="new password"> <br>
                <input autoComplete="new-password" type="password" name="password2" placeholder="again"> <br>
                <button>change</button>
            </form>
        </section>

    </div>



</body>

</html>
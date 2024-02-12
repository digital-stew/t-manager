<?php
//$phpLines = shell_exec("find . -type f -name '*.php' | xargs wc -l | awk '{ total += $1 } END { print total }'");
//$jsLines = shell_exec("find . -type f -name '*.js' | xargs wc -l | awk '{ total += $1 } END { print total }'");
//$allLines = shell_exec("find . -type f -name '*' | xargs wc -l | awk '{ total += $1 } END { print total }'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Home</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
    <meta http-equiv="refresh" content="0; URL=/stores/" />
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
</body>

</html>
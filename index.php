<?php
//$code1 = "212M127AM00"; //  M     a/short sweat
//$code2 = "211M00U2XS0"; //  XS    core hoodie - mens

//require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
//$Stock = new Stock();

//$result = $Stock->search();
//print_r($result);

//$addResult = $Stock->addStock($code2, 'hawkins', 100);
$phpLines = shell_exec("find . -type f -name '*.php' | xargs wc -l | awk '{ total += $1 } END { print total }'");
//$jsLines = shell_exec("find . -type f -name '*.js' | xargs wc -l | awk '{ total += $1 } END { print total }'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Home</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div class="border" style="padding: 1rem;text-align: center;">
        <p>
            This program is still very much in alpha
        </p>
        <p>Any suggestions, complaints, feature requests or bugs <button onclick="location.href='mailto:stewart@tux-systems.co.uk';">send here</button> stewart@tux-systems.co.uk</p>
    </div>

    <div style="display: flex;justify-content: center;gap:3rem;margin-top: 3rem;flex-wrap: wrap;height: min-content;">

        <section class="border" style="padding: 2rem;">
            <h4>not operational ...yet</h4>
            <p>auto location (when log in?)</p>
            <p>samples - add pictures directly from app so phones don't get cluttered up with pictures</p>
            <p>full logging</p>
            <p>user options - password change ...etc</p>
            <p>clicking modal backdrop closes the modal</p>
            <p>"color" yes i know. spell check shouts at me will change when complete</p>
            <p>add download hi-res pics to samples</p>
            <p>"DO NOT USE" samples need to show red</p>
        </section>

        <section class="border" style="padding: 2rem;">
            <h4>bugs</h4>
            <p>edit user always defaults to user level to "user"</p>
        </section>
        <section class="border" style="padding: 2rem;">
            <h4>last update</h4>
            <p>pick orders</p>
        </section>
        <p>shit this is getting big: <?= $phpLines ?> lines of php code!!</p>
    </div>

</body>

</html>
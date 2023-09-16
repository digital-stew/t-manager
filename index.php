<?php
//$code1 = "212M127AM00"; //  M     a/short sweat
//$code2 = "211M00U2XS0"; //  XS    core hoodie - mens

//require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
//$Stock = new Stock();

//$result = $Stock->search();
//print_r($result);

//$addResult = $Stock->addStock($code2, 'hawkins', 100);
//$phpLines = shell_exec("find . -type f -name '*.php' | xargs wc -l | awk '{ total += $1 } END { print total }'");
//$jsLines = shell_exec("find . -type f -name '*.js' | xargs wc -l | awk '{ total += $1 } END { print total }'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>

    <div style="display: grid;place-content: center;text-align: center;">
        <div class="border" style="padding: 1rem;">
            <p>
                This program is still very much in alpha
            </p>
            <p>Any suggestions, complaints or feature requests <button onclick="location.href='mailto:stewart@tux-systems.co.uk';">send here</button> </p>
            <p>
                <!-- <?php echo $phpLines / 2; ?> lines of php code <br> -->
                <!-- <?php echo $jsLines / 2; ?> lines of javascript code <br> -->
            </p>

        </div>
        <div style="display: flex;justify-content: center;gap:3rem;margin-top: 3rem;">

            <section class="border" style="padding: 2rem;">
                <h4>not operational ...yet</h4>
                <p>stock - auto location</p>
                <p></p>
            </section>

            <section class="border" style="padding: 2rem;">
                <h4>bugs</h4>
                <p>cant click on samples on mobile</p>
                <p>port error on scanner cam - non critical</p>
            </section>

        </div>


    </div>
</body>

</html>
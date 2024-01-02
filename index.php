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
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div class="border" style="display: flex; justify-content: center;align-items: center;">
        We are currently in the testing phase and actively seeking feedback. If you encounter any errors or bugs, please report them.
        We also welcome peer reviews and assistance. <!-- <a href="/t-manager.zip">
            <span><button>source</button></span> -->
        </a> </div>

    <div style="display: flex;justify-content: center;gap:3rem;margin-top: 3rem;flex-wrap: wrap;height: min-content;">

        <section class="border" style="padding: 2rem;">
            <h4>not operational ...yet</h4>
            <p>automatic user location</p>
            <p>search fanatic orders</p>
            <p>open / close public access</p>
            <p>add/remove stock reasons in admin panel</p>
            <p>error checking on stock removal</p>
        </section>

        <!-- <section class="border" style="padding: 2rem;">
            <h4>bugs</h4>
        </section> -->

        <section class="border" style="padding: 2rem;">
            <h4>last update</h4>
            <p>move to mysql</p>
            <p>BUG FIX: total stock count error</p>
            <p>floating stock add/remove buttons</p>
            <p>moved search stores menus to table head</p>
            <p>stock list now in reverse order</p>
            <p>batch add orders</p>
            <p>more relevant log entry's</p>
            <p>BUG FIX: fanatic orders sizes now XS - 5XL</p>
            <p>reason to remove stock dropdown</p>
            <p>editable stock remove reasons (scan oder + manual input)</p>
            <p>default user location changed to hawkins</p>
            <p>reverse order for fanatic order list</p>
            <p>auto focus on remove stock</p>
            <p>combine stock color/types for all the crazy/same codes</p>
    </div>
</body>

</html>
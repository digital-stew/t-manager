<?php
//$phpLines = shell_exec("find . -type f -name '*.php' | xargs wc -l | awk '{ total += $1 } END { print total }'");
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
            <p>better print samples</p>
            <p>auto capitalize stock input strings</p>
        </section>

        <section class="border" style="padding: 2rem;">
            <h4>bugs</h4>
            <!-- <p>none known</p> -->
            <p>when add/remove stock on pc pressing enter closes modal but doesn't save</p>
        </section>

        <section class="border" style="padding: 2rem;">
            <h4>last update</h4>
            <p>BUX FIX: start images at first image every time a new sample is selected</p>
            <p>BUX FIX: browser hang if no cam and trying to pick order</p>
        </section>
    </div>
</body>

</html>
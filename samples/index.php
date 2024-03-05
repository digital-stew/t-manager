<?php

require $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
$Sample = new sample();

if (isset($_GET['search'])) {
    $searchResults = $Sample->search($_GET['search']);
} else {
    $searchResults = $Sample->search('', 10);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Samples</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/samples/samples.js" defer></script>
    <link href="/samples/printSample.css" media="print" rel="stylesheet" />

</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div>
        <h1>Samples</h1>
        <?php if (isset($_SESSION['userName'])) : ?>
            <button onclick="document.getElementById('addNewSample-modal').showModal();">add new sample</button>
        <?php endif ?>
        <hr>
    </div>

    <div>
        <section style="display: flex;flex-direction: column;">
            <input onkeyup="updateSamplesList()" type="search" id="search" placeholder="search..." class="border" style="margin-block: 1rem;" />
            <table id="show" class="border sampleTable">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>name</th>
                        <th>number</th>
                        <th>date</th>
                        <th>image</th>
                    </tr>
                </thead>
                <tbody id="searchResults">
                    <?php foreach ($searchResults as $sample) : ?>
                        <tr onclick="selectSample(<?= $sample['id'] ?>)">
                            <!-- <tr onclick="showModal('/samples/show.php?id=<?= $sample['id'] ?>');"> -->
                            <td><?= $sample['id'] ?></td>
                            <td><?= $sample['name'] ?></td>
                            <td><?= $sample['number'] ?></td>
                            <td class='timestamp'><?= $sample['date'] ?></td>
                            <td><img src="/assets/images/samples/webp/<?php if ($sample['image']) echo $sample['image'];
                                                                        else echo 'Cross_red_circle.svg' ?>" alt='' style='width:100px;height:100px;'>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </div>
    <dialog id="addNewSample-modal">
        <form id="show" enctype="multipart/form-data" action="/samples/add.php" method="POST" class="box" style="display: grid;grid-template-columns: 1fr 1fr;gap: 1rem;">
            <div>
                <h3>Job Data</h3>
                <hr">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="wide-center" required>

                    <label for="number">Number</label>
                    <input type="text" name="number" id="number" class="wide-center" required>

                    <label for="other">Other reference</label>
                    <input type="text" name="otherref" id="other" class="wide-center">
                    <br><br>
                    <div id="uploadSampleImageContainer" style="display: grid;place-items: center;gap: 1rem;">
                        <input id="uploadSampleImage" oninput="uploadAnotherImage(this);" type="file" accept="image/*" capture="camera" name="files[]">
                    </div>
            </div>
            <div>
                <h3>Print Data</h3>
                <hr>
                <label for="front">Front</label>
                <input type="text" name="front" id='front' class="wide-center">

                <label for="back">Back</label>
                <input type="text" name="back" id="back" class="wide-center">

                <label for="other">Other</label>
                <input type="text" name="other" id="other" class="wide-center">

                <label for="notes">Notes</label>
                <input type="text" name="notes" id="notes" class="wide-center"> <br />

                <button type="submit" name="add">Save</button>
                <button type="button" onclick="document.getElementById('addNewSample-modal').close();">cancel</button>
            </div>

        </form>
    </dialog>
</body>

</html>
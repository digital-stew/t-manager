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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samples</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/globalFunctions.js" defer></script>
    <script src="/samples/samples.js" defer></script>
</head>

<body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/header.php'; ?>
    <div class="layout">
        <h1>Samples</h1>
        <button onclick="replaceElement('show', '/samples/add.php')">add new sample</button>
        <hr>
        <div class="sideBySide">
            <section class="tableSection">
                <input onkeyup="updateSamplesList()" type="search" id="search" placeholder="search..." class="border" style="margin-block: 1rem;" />
                <table id="show" class="border">
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
            <div class="sampleWrap" id="sampleWrap">
                <section id="sampleData" class="show_sample_section">
                    <!-- placeholder for new data -->
                </section>
            </div>
        </div>
    </div>


</body>

</html>

<?php
$_SERVER['DOCUMENT_ROOT'] . '/footer.php';
?>
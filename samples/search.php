<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
session_start();
$sample = new sample();
$searchResults = $sample->search($_GET['search']);
?>
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
            <tr onclick="selectSample(<?= $sample['id'] ?>)" <?= str_contains($sample['name'], 'OLD SAMPLE') ? "style='background-color: red'" : '' ?>>
                <td><?= $sample['id'] ?></td>
                <td><?= $sample['name'] ?></td>
                <td><?= $sample['number'] ?></td>
                <td class='timestamp'><?= $sample['date'] ?></td>
                <td><img src='/assets/images/samples/webp/<?php if ($sample['image']) echo $sample['image'];
                                                            else echo 'Cross_red_circle.svg' ?>' alt='' style='width:100px;height:100px;'>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
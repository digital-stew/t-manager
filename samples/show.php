<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
session_start();
$Sample = new sample();
$sample = $Sample->get($_GET['id']);
$FLASH_IMAGE_LINK = "<img src='/assets/images/flash.svg' alt='flash' style='width:50px;height:50px;vertical-align:middle;' >";
?>

<section id="sampleData" style="width: 100%;" data-images='<?= json_encode($sample['images']) ?>'>

    <?php if ($sample['images'][0] != '') : ?>
        <div class="sample_show_imageWrapper newBox border" style="margin-inline: auto; margin-block: 1rem;">
            <div style="position: absolute;left: 0;top:0;color: white;">
                <span id="count">1</span> <span id="imageAmount">/<?= sizeof($sample['images']) ?></span>
            </div>
            <button style="left: 0;" class="imageButton" onclick="imageDown()">&lt;</button>
            <img id="sampleImage" src="/assets/images/samples/webp/<?= $sample['images'][0] ?>" alt="sample" style="border-radius: 10px;width: 80%;margin: auto;">
            <button style="right: 0;" class="imageButton" onclick="imageUp()">&gt;</button>
        </div>
    <?php endif ?>

    <div style="display: flex;justify-content: center;gap: 1rem;flex-wrap: wrap;">

        <div class="newBox">
            <h4>Info</h4>
            <p><?= $sample['name'] ?></p>
            <p><?= $sample['number'] ?></p>
            <p class="timestamp"><?= $sample['date'] ?></p>
            <p><?= $sample['printer'] ?></p>

            <?php if (isset($_SESSION['userName']) && $_SESSION['userName'] == $sample['printer']) : ?>
                <button onclick="showModal('/samples/edit.php?id=<?= $sample['id'] ?>');">Edit</button>
            <?php endif ?>

        </div>

        <div id="sampleData" class="newBox border">
            <h4>Print data</h4>

            <?php if (strlen($sample['frontData'])) : ?>
                <h3><?= str_contains($sample['frontData'], ':') ? explode(':', $sample['frontData'])[0] : 'Front';  ?></h3>
                <p><?= str_contains($sample['frontData'], ':') ?  str_replace("dry", $FLASH_IMAGE_LINK, explode(':', $sample['frontData'])[1]) :  str_replace("dry", $FLASH_IMAGE_LINK, $sample['frontData'])  ?></p>
            <?php endif ?>

            <?php if (strlen($sample['backData'])) : ?>
                <h3><?= str_contains($sample['backData'], ':') ? explode(':', $sample['backData'])[0] : 'Back';  ?></h3>
                <p><?= str_contains($sample['backData'], ':') ?  str_replace("dry", $FLASH_IMAGE_LINK, explode(':', $sample['backData'])[1]) :  str_replace("dry", $FLASH_IMAGE_LINK, $sample['backData'])  ?></p>
            <?php endif ?>

            <?php if (strlen($sample['otherData'])) : ?>
                <h3><?= str_contains($sample['otherData'], ':') ? explode(':', $sample['otherData'])[0] : 'Other';  ?></h3>
                <p><?= str_contains($sample['otherData'], ':') ?  str_replace("dry", $FLASH_IMAGE_LINK, explode(':', $sample['otherData'])[1]) :  str_replace("dry", $FLASH_IMAGE_LINK, $sample['otherData'])  ?></p>
            <?php endif ?>
        </div>

        <?php if (strlen($sample['notes'])) : ?>
            <div class="newBox border">
                <h4>notes</h4>
                <p><?= $sample['notes'] ?></p>
            </div>
        <?php endif ?>

    </div>

    <div>
        <button type="button" onclick="closeModal();" style="width: 90%;">Close</button>
    </div>

</section>
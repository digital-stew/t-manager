<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/models/errorHandler.php';
session_start();
$Sample = new sample();
$sample = $Sample->get($_GET['id']);
$FLASH_IMAGE_LINK = "<img src='/assets/images/flash.svg' alt='flash' style='width:50px;height:50px;vertical-align:middle;' >";
?>
<div class="sampleWrap" id="sampleWrap">
    <section id="sampleData" class="show_sample_section" data-images='<?= json_encode($sample['images']) ?>'>
        <div class="newBox">
            <h4>Info</h4>
            <p><?= $sample['name'] ?></p>
            <p><?= $sample['number'] ?></p>
            <p class="timestamp"><?= $sample['date'] ?></p>
            <p><?= $sample['printer'] ?></p>
            <button onclick="getEditSample('<?= $sample['id'] ?>')">Edit</button>

        </div>

        <div id="sampleData" class="newBox border">
            <h4>Print data</h4>
            <?php if (strlen($sample['frontData'])) : ?>
                <h3>front</h3>
                <p><?= str_replace("dry", $FLASH_IMAGE_LINK, $sample['frontData'])  ?></p>
            <?php endif ?>

            <?php if (strlen($sample['backData'])) : ?>
                <h3>back</h3>
                <p><?= str_replace("dry", $FLASH_IMAGE_LINK, $sample['backData'])  ?></p>
            <?php endif ?>

            <?php if (strlen($sample['otherData'])) : ?>
                <h3>other</h3>
                <p><?= str_replace("dry", $FLASH_IMAGE_LINK, $sample['otherData'])  ?></p>
            <?php endif ?>
        </div>

        <?php if (strlen($sample['notes'])) : ?>
            <div class="newBox border">
                <h4>notes</h4>
                <p><?= $sample['notes'] ?></p>
            </div>
        <?php endif ?>

        <?php if ($sample['images'][0] != '') : ?>
            <div class="sample_show_imageWrapper newBox border">
                <div style="position: absolute;left: 0;top:0;color: white;">
                    <span id="count">1</span> <span id="imageAmount">/<?= sizeof($sample['images']) ?></span>
                </div>
                <button style="left: 0;" class="imageButton" onclick="imageDown()">&lt;</button>
                <img id="sampleImage" src="/assets/images/samples/webp/<?= $sample['images'][0] ?>" alt="sample" style="border-radius: 10px;width: 80%;margin: auto;">
                <button style="right: 0;" class="imageButton" onclick="imageUp()">&gt;</button>
            </div>
        <?php endif ?>
    </section>
</div>
<?php
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
$FLASH_IMAGE_LINK = "<img src='/assets/images/flash.svg' alt='flash' style='width:50px;height:50px;vertical-align:middle;' >";
$id = htmlspecialchars($_GET['id']);
// SELECT all from sample table and all images from its table
$sql = <<<EOD
    SELECT samples.*, sample_images.webp_filename
    FROM samples
    LEFT JOIN sample_images
    ON samples.rowid = sample_images.sample_id
    WHERE rowid = ?;
EOD;
$stm = $db->prepare($sql);
$stm->bindValue(1, SQLite3::escapeString($id));
$res = $stm->execute();

$sample = $res->fetchArray(); // get all sample details including first image
$images = array(); // create array to hold image names
array_push($images, $sample['webp_filename']); // push the first image
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    array_push($images, $row['webp_filename']); // push the rest
}
?>
<div class="sampleWrap" id="sampleWrap">

    <section id="sampleData" class="show_sample_section" data-images='<?= json_encode($images) ?>'>
        <div class="newBox">
            <h4>Info</h4>
            <p><?= $sample['name'] ?></p>
            <p><?= $sample['number'] ?></p>
            <p class="timestamp"><?= $sample['date'] ?></p>
            <p><?= $sample['printer'] ?></p>

        </div>

        <div id="sampleData" class="newBox border">
            <h4>Print data</h4>
            <?php if (strlen($sample['printdata'])) : ?>
                <h3>front</h3>
                <p><?= str_replace("dry", $FLASH_IMAGE_LINK, $sample['printdata'])  ?></p>
            <?php endif ?>

            <?php if (strlen($sample['printdataback'])) : ?>
                <h3>back</h3>
                <p><?= str_replace("dry", $FLASH_IMAGE_LINK, $sample['printdataback'])  ?></p>
            <?php endif ?>

            <?php if (strlen($sample['printdataother'])) : ?>
                <h3>other</h3>
                <p><?= str_replace("dry", $FLASH_IMAGE_LINK, $sample['printdataother'])  ?></p>
            <?php endif ?>




        </div>
        <?php if (strlen($sample['notes'])) : ?>

            <div class="newBox border">
                <h4>notes</h4>
                <p><?= $sample['notes'] ?></p>
            </div>

        <?php endif ?>
        <?php if ($images[0] != '') : ?>
            <div class="sample_show_imageWrapper newBox border">
                <div style="position: absolute;left: 0;top:0;color: white;">
                    <span id="count">1</span> <span id="imageAmount">/<?= sizeof($images) ?></span>
                </div>
                <button style="left: 0;" class="imageButton" onclick="imageDown()">&lt;</button>
                <img id="sampleImage" src="/assets/images/samples/webp/<?= $sample['webp_filename'] ?>" alt="sample" style="border-radius: 10px;width: 80%;margin: auto;">
                <button style="right: 0;" class="imageButton" onclick="imageUp()">&gt;</button>
            </div>
        <?php endif ?>




    </section>

    <div>

        <button onclick="editSample('<?= $sample['rowid'] ?>')">Edit</button>
    </div>
</div>
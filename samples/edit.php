<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] .'/models/sample.php';
$Sample = new sample();
$sample = $Sample->get($_GET["id"]);

if ($_SESSION['userName'] != $sample['printer']) die('{"error":"you are not the printer sorry"}');

if (isset($_POST["update"]) && isset($_GET["id"])) {
    $Sample->update($_GET['id'], $_POST['front'], $_POST['back'], $_POST['other'], $_POST['notes'], $_POST['name'], $_POST['number'], $_POST['otherref'],$_FILES['files']);
}
if (isset($_POST['removeImage']) && isset($_GET["id"])) {
    $Sample->removeImage($_POST['removeImage']);
}
if (isset($_POST['delete']) && isset($_GET["id"])) {
   $Sample->remove($_GET['id']);
}

?>
<div class="sampleWrap" id="sampleWrap">
    <form enctype="multipart/form-data" action="/api/samples/edit.php?id=<?= $sample['id'] ?>" method="POST" id="sampleUpdateForm">
        <section id="sampleData" class="show_sample_section" data-images='<?= json_encode($sample['images']) ?>'>

            <div class="newBox">

                <button type="submit" name="update">Update</button>
                <button type="submit" name="delete">Delete</button>
            </div>

            <div class="newBox">
                <h4>Info</h4>
                <h3>Name</h3>
                <p><input type="text" name="name" placeholder="Empty" value="<?= $sample['name'] ?>"></p>
                <h3>Number</h3>
                <p><input type="text" name="number" placeholder="Empty" value="<?= $sample['number'] ?>"></p>
                <h3>Other Ref</h3>
                <p><input type="text" name="otherref" placeholder="Empty" value="<?= $sample['otherRef'] ?>"></p>
                <p class="timestamp"><?= $sample['date'] ?></p>
            </div>
            <div class="newBox" style="width: 500px;">
                <h4>Print Data</h4>
                <h3>front</h3>
                <input type="text" name="front" placeholder="Empty" value="<?= $sample['frontData'] ?>">

                <h3>back</h3>
                <input type="text" name="back" placeholder="Empty" value="<?= $sample['backData'] ?>">

                <h3>other</h3>
                <input type="text" name="other" placeholder="Empty" value="<?= $sample['otherData'] ?>">

                <h3>notes</h3>
                <input type="text" name="notes" placeholder="Empty" value="<?= $sample['notes'] ?>"> <br />
                <p>"dry" will be converted to flash icon</p>
                <p>text: change title to text</p>
            </div>
            <div class="sample_show_imageWrapper newBox border">
                <div style="position: absolute;left: 0;top:0;color: white;">
                    <span id="count">1</span> <span id="imageAmount">/<?= sizeof($sample['images']) ?></span>
                </div>
                <button type="button" style="left: 0;" class="imageButton" onclick="imageDown()">&lt;</button>
                <img id="sampleImage" src="/assets/images/samples/webp/<?= $sample['images'][0] ?>" alt="sample" style="border-radius: 10px;width: 80%;margin: auto;">
                <button type="button" style="right: 0;" class="imageButton" onclick="imageUp()">&gt;</button>
                <button type="button" onclick="deleteImage()">remove image</button>
            </div>
            <div class="newBox">
                <h4>Add files</h4>
                <input type="file" name="files[]" id="" multiple>
            </div>


        </section>
    </form>
</div>
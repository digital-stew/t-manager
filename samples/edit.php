<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();
$Auth = new Auth();
$Auth->isLoggedIn();
$Sample = new sample();
$sample = $Sample->get($_GET["id"]);
if ($_SESSION['userName'] != $sample['printer']) die('You are not the original printer');

if (isset($_POST["update"]) && isset($_GET["id"])) {
    $res = $Sample->update($_GET['id'], $_POST['front'], $_POST['back'], $_POST['other'], $_POST['notes'], $_POST['name'], $_POST['number'], $_POST['otherref'], $_FILES['files']);
    if ($res) header('Location: /samples?flashUser=sample updated&id=' . $_GET["id"]);
    die();
}
if (isset($_POST['removeImage']) && isset($_GET["id"])) {
    //die($_POST['removeImage']);
    $res = $Sample->removeImage($_POST['removeImage']);
    die();
}
if (isset($_POST['delete']) && isset($_GET["id"])) {
    $res = $Sample->remove($_GET['id']);
    if ($res) header('location: /samples?flashUser=Sample Deleted');
    die();
}

?>
<div class="sampleWrap" id="sampleWrap">
    <form enctype="multipart/form-data" action="/samples/edit.php?id=<?= $sample['id'] ?>" method="POST" id="sampleUpdateForm">
        <section id="sampleData" class="show_sample_section" data-images='<?= json_encode($sample['images']) ?>'>



            <div class="newBox">
                <input type="hidden" id="id" value="<?= $sample['id'] ?>">
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
                    <span id="modal_count">1</span> <span id="imageAmount">/<?= sizeof($sample['images']) ?></span>
                </div>
                <button type="button" style="left: 0;" class="imageButton" onclick="imageDown()">&lt;</button>
                <img id="modal_sampleImage" src="/assets/images/samples/webp/<?= $sample['images'][0] ?>" alt="sample" style="border-radius: 10px;width: 80%;margin: auto;">
                <button type="button" style="right: 0;" class="imageButton" onclick="imageUp()">&gt;</button>
                <button type="button" onclick="return confirm('This will permanently delete this image') && deleteImage()">remove image</button>
            </div>
            <div class="newBox">
                <h4>Add files</h4>
                <input type="file" name="files[]" id="" multiple>
                <h4></h4>
                <button type="submit" name="update">Update</button>
                <button type="button" onclick="closeModal();">Close</button>
                <h4></h4>
                <button type="submit" onclick="return confirm('This will permanently delete this sample')" name="delete">Delete</button>
            </div>

        </section>
    </form>
</div>
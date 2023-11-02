<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/sample.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
session_start();
$Auth = new Auth();
$Auth->isLoggedIn();
$Sample = new sample();
$sample = $Sample->get($_GET["id"]);

if ($_SESSION['userName'] == $sample['printer']) {
} elseif ($_SESSION['userLevel'] == 'admin') {
} else {
    die('You are not the original printer');
}

if (isset($_POST["update"]) && isset($_GET["id"])) {
    $res = $Sample->update($_GET['id'], $_POST['front'], $_POST['back'], $_POST['other'], $_POST['notes'], $_POST['name'], $_POST['number'], $_POST['otherref'], $_FILES['files']);
    if ($res) header('Location: /samples?flashUser=sample updated');
    die();
}
if (isset($_POST['removeImage']) && isset($_GET['id'])) {
    //die($_POST['removeImage']);
    $res = $Sample->removeImage($_GET['id'], $_POST['removeImage']);
    if ($res) die('image=removed');
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
        <section id="sampleData" class="show_sample_section">



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
            </div>

            <div class="newBox">
                <!-- BUG handling files -->
                <!-- <h4>Add files</h4> -->
                <!-- <input id="uploadSampleImage" type="file" accept="image/*" name="files[]"> -->

                <h4></h4>
                <button type="submit" name="update">Update</button>
                <button type="button" onclick="closeModal();">Close</button>
                <h4></h4>
                <button type="submit" onclick="return confirm('This will permanently delete this sample')" name="delete">Delete</button>
            </div>
            <?php foreach ($sample['images'] as $image) : ?>
                <div class="newBox sample_show_imageWrapper" style="display: grid;">

                    <img class="sampleEditImage" src="/assets/images/samples/webp/<?= $image ?>" alt="sample" style="border-radius: 10px;width: 80%;margin: auto;display: block;">
                    <button type="button" onclick="return confirm('This will permanently delete this image') && deleteImage('<?= $sample['id'] ?>','<?= $image ?>')">remove image</button>
                </div>
            <?php endforeach ?>


        </section>
    </form>
</div>
<?php
session_start();

$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
$id = SQLite3::escapeString($_GET["id"]);
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
$stm->bindValue(1, $id, SQLITE3_TEXT);
$res = $stm->execute() or die('sql error');

$sample = $res->fetchArray() or die('sql error');
$images = array(); // create array to hold image names
array_push($images, $sample['webp_filename']); // push the first image
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    array_push($images, $row['webp_filename']); // push the rest
}

if ($_SESSION['userName'] != $sample['printer']) die('{"error":"you are not the printer sorry"}');

if (isset($_POST["update"]) && isset($_GET["id"])) {
    die('testing');
    //BUG!! ALWAYS ADD IMAGE AFTER EDIT
    $sql = <<<EOD
    UPDATE samples
    SET printdata = ?, printdataback = ?, printdataother = ?, notes = ?, name = ?, number = ?, otherref = ?
    WHERE rowid = ?
    EOD;

    $stm = $db->prepare($sql) or die('sql error');
    $stm->bindValue(1, $_POST['front'], SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(2, $_POST['back'], SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(3, $_POST['other'], SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(4, $_POST['notes'], SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(5, $_POST['name'], SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(6, $_POST['number'], SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(7, $_POST['otherref'], SQLITE3_TEXT) or die('sql error');
    $stm->bindValue(8, $id, SQLITE3_TEXT) or die('sql error');

    $res = $stm->execute() or die('sql error');



    //handle the files
    $i = 0; //iterator
    foreach ($_FILES['files']['name'] as $originalFileName) {
        if ($originalFileName == '') continue; //BUG FIX always tries even when no files

        $fileExt = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);
        $fileUUID = uniqid();
        switch ($fileExt) {
            case 'jpg':
                $image = imagecreatefromjpeg($_FILES['files']['tmp_name'][$i]) or die('cant create image from upload');
                $webpData = imagewebp($image, $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/webp/' . $fileUUID . '.webp', 100) or die('cant convert file to webp');
                break;
            default:
                die('cant convert file ' . $originalFileName);
        }

        move_uploaded_file($_FILES['files']['tmp_name'][$i], $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/original/' . $originalFileName) or die('cant move file');

        $sql = <<<EOD
        INSERT INTO sample_images(
            webp_filename,
            sample_id,
            original_filename,
            user,
            date
            )
            VALUES (?,?,?,?,?)
        EOD;
        $stm = $db->prepare($sql) or die('sql error');
        $stm->bindValue(1, $fileUUID . '.webp', SQLITE3_TEXT) or die('sql error');
        $stm->bindValue(2, $_GET['id'], SQLITE3_TEXT) or die('sql error');
        $stm->bindValue(3, $originalFileName, SQLITE3_TEXT) or die('sql error');
        $stm->bindValue(4, $_SESSION['userName'], SQLITE3_TEXT) or die('sql error');
        $stm->bindValue(5, time(), SQLITE3_TEXT) or die('sql error');
        $res = $stm->execute() or die('sql error');

        $i++;
    }

    header('Location: /samples?id=' . $id);
    die();
}

if (isset($_POST['removeImage']) && isset($_GET["id"])) {

    $stm = $db->prepare("DELETE FROM sample_images WHERE webp_filename = ?") or die('sql error'); // BUG!! add and sample id
    $stm->bindValue(1, $_POST['removeImage'], SQLITE3_TEXT) or die('sql error bind');
    $stm->execute();
    echo "image=removed"; // client update
    die();
}
if (isset($_POST['delete']) && isset($_GET["id"])) {
    $stm = $db->prepare("DELETE FROM samples WHERE rowid = ?");
    $stm->bindValue(1, $_GET['id'], SQLITE3_TEXT);
    $res = $stm->execute() or die('error deleting sample');
    echo "sample=removed"; // client update
    header('Location: /samples');
    die();
}



$db->close();
?>
<div class="sampleWrap" id="sampleWrap">
    <form enctype="multipart/form-data" action="/api/samples/edit.php?id=<?= $id ?>" method="POST" id="sampleUpdateForm">
        <section id="sampleData" class="show_sample_section" data-images='<?= json_encode($images) ?>'>

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
                <p><input type="text" name="otherref" placeholder="Empty" value="<?= $sample['otherref'] ?>"></p>
                <p class="timestamp"><?= $sample['date'] ?></p>
            </div>
            <div class="newBox" style="width: 500px;">
                <h4>Print Data</h4>
                <h3>front</h3>
                <input type="text" name="front" placeholder="Empty" value="<?= $sample['printdata'] ?>">

                <h3>back</h3>
                <input type="text" name="back" placeholder="Empty" value="<?= $sample['printdataback'] ?>">

                <h3>other</h3>
                <input type="text" name="other" placeholder="Empty" value="<?= $sample['printdataother'] ?>">

                <h3>notes</h3>
                <input type="text" name="notes" placeholder="Empty" value="<?= $sample['notes'] ?>"> <br />
                <p>"dry" will be converted to flash icon</p>
                <p>text: change title to text</p>
            </div>
            <div class="sample_show_imageWrapper newBox border">
                <div style="position: absolute;left: 0;top:0;color: white;">
                    <span id="count">1</span> <span id="imageAmount">/<?= sizeof($images) ?></span>
                </div>
                <button type="button" style="left: 0;" class="imageButton" onclick="imageDown()">&lt;</button>
                <img id="sampleImage" src="/assets/images/samples/webp/<?= $sample['webp_filename'] ?>" alt="sample" style="border-radius: 10px;width: 80%;margin: auto;">
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
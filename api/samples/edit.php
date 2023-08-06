<?php
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
$id = SQLite3::escapeString($_GET["id"]);

$stm = $db->prepare("SELECT * FROM samples WHERE rowid = ? ") or die('sql error');
$stm->bindValue(1, $id, SQLITE3_TEXT);
$res = $stm->execute() or die('sql error');

$sample = $res->fetchArray() or die('sql error');


if (isset($_POST["update"]) && isset($_GET["id"])) {
    //BUG!! ALWAYS ADD IMAGE AFTER EDIT

    $sql = <<<EOD
    UPDATE samples
    SET printdata = ?, printdataback = ?, printdataother = ?, notes = ?
    WHERE rowid = ?
    EOD;

    $stm = $db->prepare($sql) or die('sql error');
    $stm->bindValue(1, $_POST['front'], SQLITE3_TEXT);
    $stm->bindValue(2, $_POST['back'], SQLITE3_TEXT);
    $stm->bindValue(3, $_POST['other'], SQLITE3_TEXT);
    $stm->bindValue(4, $_POST['notes'], SQLITE3_TEXT);
    $stm->bindValue(5, $id, SQLITE3_TEXT);
    $res = $stm->execute() or die('sql error');

    //handle the files
    $i = 0; //iterator
    foreach ($_FILES['files']['name'] as $originalFileName) {
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
        $stm->bindValue(1, $fileUUID . '.webp', SQLITE3_TEXT);
        $stm->bindValue(2, $lastID, SQLITE3_TEXT);
        $stm->bindValue(3, $originalFileName, SQLITE3_TEXT);
        $stm->bindValue(4, $_SESSION['userName'], SQLITE3_TEXT);
        $stm->bindValue(5, time(), SQLITE3_TEXT);
        $res = $stm->execute() or die('sql error');

        $i++;
    }

    header('Location: /samples/show.php?id=' . $id);
    die();
}
if (isset($_POST['removeImage']) && isset($_GET["id"])) {
    $stm = $db->prepare("DELETE FROM sample_images WHERE image = ?");
    $stm->bindValue(1, SQLite3::escapeString($_POST['image']), SQLITE3_TEXT);
    $res = $stm->execute() or die('error removing image');
    echo "image=removed"; // client update
    die();
}
if (isset($_POST['delete']) && isset($_GET["id"])) {
    $stm = $db->prepare("DELETE FROM samples WHERE rowid = ?");
    $stm->bindValue(1, SQLite3::escapeString($_GET['id']), SQLITE3_TEXT);
    $res = $stm->execute() or die('error deleting sample');
    echo "sample=removed"; // client update
    die();
}
$db->close();
?>
<form enctype="multipart/form-data" action="/api/samples/edit.php?id=<?= $id ?>" method="POST" id="sampleUpdateForm">

    <h3>front</h3>
    <input type="text" name="front" value="<?= htmlspecialchars($sample['printdata']) ?>">

    <h3>back</h3>
    <input type="text" name="back" value="<?= htmlspecialchars($sample['printdataback']) ?>">

    <h3>other</h3>
    <input type="text" name="other" value="<?= htmlspecialchars($sample['printdataother']) ?>">

    <h3>notes</h3>
    <input type="text" name="notes" value="<?= htmlspecialchars($sample['notes']) ?>"> <br />

    <button type="button" onclick="removeImage()">remove image</button>
    <input type="file" name="files[]" id="" multiple>
    <button type="submit" name="update">Update</button>
    <button type="submit" name="delete">Delete</button>
</form>
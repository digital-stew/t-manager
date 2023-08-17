<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
auth('user');

$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');

if (isset($_POST['add'])) {
    if ($_POST['name'] == '' or $_POST['number'] == '') die('no name or number');
    if ($_FILES['files']['tmp_name'][0] == '') die('no files');
    $sql = <<<EOD
    INSERT INTO samples (
        name,
        number,
        otherref,
        date,
        printdata,
        printdataback,
        printdataother,
        notes,
        printer
        )
        VALUES (?,?,?,?,?,?,?,?,?)
    EOD;

    $stm = $db->prepare($sql);
    $stm->bindValue(1, $_POST['name'], SQLITE3_TEXT);
    $stm->bindValue(2, $_POST['number'], SQLITE3_TEXT);
    $stm->bindValue(3, $_POST['otherref'], SQLITE3_TEXT);
    $stm->bindValue(4, time(), SQLITE3_TEXT);
    $stm->bindValue(5, $_POST['front'], SQLITE3_TEXT);
    $stm->bindValue(6, $_POST['back'], SQLITE3_TEXT);
    $stm->bindValue(7, $_POST['other'], SQLITE3_TEXT);
    $stm->bindValue(8, $_POST['notes'], SQLITE3_TEXT);
    $stm->bindValue(9, $_SESSION['userName'], SQLITE3_TEXT);
    $res = $stm->execute() or die('sql error');

    $lastID = $db->query("SELECT last_insert_rowid();")->fetchArray()['last_insert_rowid()'];

    //handle the files
    $i = 0; //iterator
    foreach ($_FILES['files']['name'] as $originalFileName) {
        $fileExt = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);
        $fileUUID = uniqid();
        switch ($fileExt) {
            case 'jpg':
                $image = imagecreatefromjpeg($_FILES['files']['tmp_name'][$i]);
                $webpData = imagewebp($image, $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/webp/' . $fileUUID . '.webp', 100);
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
        $stm = $db->prepare($sql);
        $stm->bindValue(1, $fileUUID . '.webp', SQLITE3_TEXT);
        $stm->bindValue(2, $lastID, SQLITE3_TEXT);
        $stm->bindValue(3, $originalFileName, SQLITE3_TEXT);
        $stm->bindValue(4, $_SESSION['userName'], SQLITE3_TEXT);
        $stm->bindValue(5, time(), SQLITE3_TEXT);
        $res = $stm->execute() or die('sql error');

        $i++;
    }
    header('Location: /samples?id=' . $lastID);
}
?>

<form id="show" enctype="multipart/form-data" action="/api/samples/add.php" method="POST" class="box sample__add__form">

    <div>
        <h3>Job Data</h3>
        <hr>
        <label for="name">Name</label>
        <input type="text" name="name" id="name" class="wide-center" required>

        <label for="number">Number</label>
        <input type="text" name="number" id="number" class="wide-center" required>

        <label for="other">Other reference</label>
        <input type="text" name="otherref" id="other" class="wide-center">
        <br><br>
        <input type="file" name="files[]" id="" multiple required>
    </div>
    <div>
        <h3>Print Data</h3>
        <hr>
        <label for="front">Front</label>
        <input type="text" name="front" id='front' class="wide-center">

        <label for="back">Back</label>
        <input type="text" name="back" id="back" class="wide-center">

        <label for="other">Other</label>
        <input type="text" name="other" id="other" class="wide-center">

        <label for="notes">Notes</label>
        <input type="text" name="notes" id="notes" class="wide-center"> <br />

    </div>
    <button type="submit" name="add">Save</button>

</form>
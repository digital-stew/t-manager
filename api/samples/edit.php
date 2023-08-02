<?php
 $db = new SQLite3($_SERVER['DOCUMENT_ROOT'].'/db.sqlite');
 $id = SQLite3::escapeString($_GET["id"]);

 $stm = $db->prepare("SELECT * FROM samples WHERE rowid = ? ");
 $stm->bindValue(1,$id, SQLITE3_TEXT);
 $res = $stm->execute();

$sample = $res->fetchArray();
//print_r($_POST);

if (isset($_POST["update"]) && isset($_GET["id"])){


    //print_r($_FILES);
    //print_r($_SERVER['DOCUMENT_ROOT']);
    //upload 1 file TODO multiple
    $uuid = uniqid();
    $fileName = $uuid.'.'. pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
    move_uploaded_file($_FILES['files']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/assets/images/sample-images/'.$fileName);
    
    $stm = $db->prepare("INSERT INTO sample_images (image, sample_id) VALUES (?,?);");
    $stm->bindValue(1,$fileName, SQLITE3_TEXT);
    $stm->bindValue(2,$id, SQLITE3_TEXT);
    $res = $stm->execute();

    //header('Location: /samples/show.php?id='.$id);
    die();
}
if (isset($_POST['removeImage']) && isset($_GET["id"])){
    echo 'REMOVE';
    $stm = $db->prepare("DELETE FROM sample_images WHERE image = ?");
    $stm->bindValue(1,SQLite3::escapeString($_POST['image']), SQLITE3_TEXT);
    $res = $stm->execute();

}
$db->close(); 
 ?>
<form enctype="multipart/form-data" action="/api/samples/edit.php?id=<?=$id?>" method="POST" id="sampleUpdateForm">

    <h3>front</h3>
    <input type="text" name="front" value="<?=$sample['printdata'] ?>">

    <h3>back</h3>
    <input type="text" name="back" value="<?=$sample['printdataback'] ?>">

    <h3>other</h3>
    <input type="text" name="other" value="<?=$sample['printdataother'] ?>">

    <h3>notes</h3>
    <input type="text" name="notes" value="<?=$sample['notes'] ?>"> <br />

    <button type="button" onclick="removeImage()">remove image</button>
    <input type="file" name="files" id="" multiple>
    <button type="submit" name="update">Update</button>
</form>
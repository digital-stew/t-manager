<?php
$db = new SQLite3('./old.sqlite');
$newDB = new SQLite3('./db.sqlite');
$res = $db->query("SELECT * FROM samples");
while ($sample = $res->fetchArray()) {
    $arrayOfImages = json_decode($sample['pics']);
    //print_r($arrayOfImages);
    foreach ($arrayOfImages as $imageName) {
        $linkToFile = './assets/images/sample-images/' . trim($imageName);
        if (!file_exists($linkToFile)) echo 'not exist';
        //print_r($image) . '\n';
        $fileExt = pathinfo('./assets/images/sample-images/' . $imageName, PATHINFO_EXTENSION);
        $fileUUID = uniqid();
        switch ($fileExt) {
            case 'jpg':
                echo 'convert attempt id: ' . $sample['rowid'] . ' ' . $linkToFile . '
                ';
                $image = imagecreatefromjpeg($linkToFile) or print_r('missing image \n');
                $webpData = imagewebp($image, './assets/images/samples/webp/' . $fileUUID . '.webp', 50);
                copy($linkToFile, './assets/images/samples/original/' . $imageName);
                imagedestroy($image);

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
                $stm = $newDB->prepare($sql);
                $stm->bindValue(1, $fileUUID . '.webp', SQLITE3_TEXT);
                $stm->bindValue(2, $sample['rowid'], SQLITE3_TEXT);
                $stm->bindValue(3, $imageName, SQLITE3_TEXT);
                $stm->bindValue(4, 'superUser', SQLITE3_TEXT);
                $stm->bindValue(5, time(), SQLITE3_TEXT);
                $res2 = $stm->execute();


                break;
            default:
                die('cant convert file ' . $originalFileName);
        }
    }
}

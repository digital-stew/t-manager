<?php

$db = new SQLite3('./db.sqlite');
$res = $db->query("SELECT * FROM samples");
/*
$sql = <<<EOD
    CREATE TABLE "sample_images" (
        "id"	INTEGER,
        "sample_id"	INTEGER,
        "webp_filename"	TEXT,
        "original_filename"	TEXT,
        "user"	TEXT,
        "date"	TEXT,
        FOREIGN KEY("sample_id") REFERENCES "samples"("rowid"),
        PRIMARY KEY("id" AUTOINCREMENT)
    )
EOD;
$stm = $db->prepare($sql);
$stm->execute();
*/
while ($sample = $res->fetchArray()) {
    $arrayOfImages = json_decode($sample['pics']);
    //print_r($arrayOfImages);
    if (!is_array($arrayOfImages)) print_r("{$sample['rowid']} no image array");
   

    foreach ($arrayOfImages as $imageName) {
        $linkToFile = './assets/images/sample-images/' . trim($imageName);
        if (!file_exists($linkToFile)) echo 'not exist';
        //print_r($image) . '\n';
        $fileExt = pathinfo('./assets/images/sample-images/' . $imageName, PATHINFO_EXTENSION);
        $fileUUID = uniqid();
        echo " fileExt = {$fileExt} : uuid = {$fileUUID} : ";
        switch ($fileExt) {
            case 'jpg':
                echo 'convert attempt id: ' . $sample['rowid'] . ' ' . $linkToFile;
                $image = imagecreatefromjpeg($linkToFile) or print_r('missing image');
                $webpData = imagewebp($image, './assets/images/samples/webp/' . $fileUUID . '.webp', 50) or print_r("cant convert image");
                copy($linkToFile, './assets/images/samples/original/' . $imageName) or print_r("copy file error");
                imagedestroy($image) or print_r("image destroy error");

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
                $stm = $db->prepare($sql) or print_r("prepare error");
                $stm->bindValue(1, $fileUUID . '.webp', SQLITE3_TEXT) or print_r("bind 1 error");
                $stm->bindValue(2, $sample['rowid'], SQLITE3_TEXT) or print_r("bind 2 error");
                $stm->bindValue(3, $imageName, SQLITE3_TEXT) or print_r("bind 3 error");
                $stm->bindValue(4, $sample['printer'], SQLITE3_TEXT) or print_r("bind 4 error");
                $stm->bindValue(5, $sample['date'], SQLITE3_TEXT) or print_r("bind 5 error");
                $res2 = $stm->execute() or print_r("exe error");


                break;
            default:
                die('cant convert file ' . $originalFileName);
        }
        echo "\n";
    }
}

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class sample extends Database
{
    function get(string $id): array| bool
    {
        $sql = <<<EOD
        SELECT samples.*, sample_images.webp_filename, sample_images.original_filename
        FROM samples
            LEFT JOIN sample_images
            ON samples.rowid = sample_images.sample_id
            WHERE rowid = ?;
        EOD;
        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $id);
            $res = $stm->execute();

            $sample = $res->fetchArray(SQLITE3_ASSOC); // get all sample details including first image

            $sample['images'] = []; // create array to hold image names
            $sample['originalNames'] = array(); // create array to hold image names

            array_push($sample['images'], $sample['webp_filename']); // push the first image
            array_push($sample['originalNames'], $sample['original_filename']); // push the first image

            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                array_push($sample['images'], $row['webp_filename']); // push the rest
                array_push($sample['originalNames'], $row['original_filename']); // push the rest
            }

            return array(
                'id' => $sample['rowid'],
                'name' => $sample['name'],
                'number' => $sample['number'],
                'date' => $sample['date'],
                'otherRef' => $sample['otherref'],
                'frontData' => $sample['printdata'],
                'backData' => $sample['printdataback'],
                'otherData' => $sample['printdataother'],
                'notes' => $sample['notes'],
                'printer' => $sample['printer'],
                'images' => $sample['images'],
                'originalNames' => $sample['originalNames'],
            );
        } catch (Exception $e) {
            return false;
        }
    }

    function search(string $search, int $limit = 100): array|bool
    {
        $sql = <<<EOD
            SELECT
                samples.rowid,
                samples.name,
                samples.number,
                samples.date,
                samples.otherref,
                (
                    SELECT sample_images.webp_filename
                    FROM sample_images
                    WHERE sample_images.sample_id = samples.rowid
                    LIMIT 1
                ) AS image
            FROM samples
            WHERE
                samples.name LIKE ? OR
                samples.otherref LIKE ? OR
                samples.number LIKE ?
            ORDER BY samples.rowid DESC
            LIMIT ?;
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, '%' . $search . '%', SQLITE3_TEXT);
            $stm->bindValue(2, '%' . $search . '%', SQLITE3_TEXT);
            $stm->bindValue(3, '%' . $search . '%', SQLITE3_TEXT);
            $stm->bindValue(4, $limit, SQLITE3_TEXT);
            $res = $stm->execute();

            $searchResults = [];
            while ($sample = $res->fetchArray()) {
                $sample = array(
                    'id' => $sample['rowid'],
                    'name' => $sample['name'] ?? '',
                    'number' => $sample['number'] ?? '',
                    'date' => $sample['date'],
                    'otherRef' => $sample['otherref'] ?? '',
                    'frontData' => $sample['printdata'] ?? '',
                    'backData' => $sample['printdataback'] ?? '',
                    'otherData' => $sample['printdataother'] ?? '',
                    'notes' => $sample['notes'] ?? '',
                    'printer' => $sample['printer'] ?? '',
                    'image' => $sample['image']
                );
                array_push($searchResults, $sample);
            }
            return $searchResults;
        } catch (Exception $e) {
            return $e;
        }
    }

    function update(string $id, string $frontData, string $backData, string $otherData, string $notes, string $name, string $number, string $otherRef, $files): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        $sql = <<<EOD
            UPDATE samples
            SET printdata = ?, printdataback = ?, printdataother = ?, notes = ?, name = ?, number = ?, otherref = ?
            WHERE rowid = ?
            EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $frontData, SQLITE3_TEXT);
            $stm->bindValue(2, $backData, SQLITE3_TEXT);
            $stm->bindValue(3, $otherData, SQLITE3_TEXT);
            $stm->bindValue(4, $notes, SQLITE3_TEXT);
            $stm->bindValue(5, $name, SQLITE3_TEXT);
            $stm->bindValue(6, $number, SQLITE3_TEXT);
            $stm->bindValue(7, $otherRef, SQLITE3_TEXT);
            $stm->bindValue(8, $id, SQLITE3_TEXT);

            $stm->execute();

            //TODO fix uploading image

            //handle the files
            //$i = 0; //iterator
            //foreach ($files['name'] as $originalFileName) {

            if ($files['name'] == '') {
                $Log = new Log();
                $Log->add("EDIT", "sample", $id, $number);
                return true;
            }

            $fileExt = pathinfo($files['name'], PATHINFO_EXTENSION);
            $fileUUID = uniqid();
            switch ($fileExt) {
                case 'jpg':

                    print_r($files);
                    die();
                    $image = imagecreatefromjpeg($files['tmp_name']);
                    $webpData = imagewebp($image, $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/webp/' . $fileUUID . '.webp', 50);
                    break;
                default:
                    die('cant convert file ' . $files['name']);
            }

            move_uploaded_file($files['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/original/' . $files['name']);

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
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $fileUUID . '.webp', SQLITE3_TEXT);
            $stm->bindValue(2, $_GET['id'], SQLITE3_TEXT);
            $stm->bindValue(3, $files['name'], SQLITE3_TEXT);
            $stm->bindValue(4, $_SESSION['userName'], SQLITE3_TEXT);
            $stm->bindValue(5, time(), SQLITE3_TEXT);
            $res = $stm->execute();

            //$i++;
            //}
            //header('Location: /samples?id=' . $id);
            $Log = new Log();
            $Log->add("EDIT", "sample", $id, $number);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function add(string $name, string $number, string $otherRef, string $frontData, string $backData, string $otherData, string $notes, string $userName, $files): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        if ($name == '' or $number == '') die('no name or number');
        if ($files['tmp_name'][0] == '') die('no files');
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
        try {
            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $name, SQLITE3_TEXT);
            $stm->bindValue(2, $number, SQLITE3_TEXT);
            $stm->bindValue(3, $otherRef, SQLITE3_TEXT);
            $stm->bindValue(4, time(), SQLITE3_TEXT);
            $stm->bindValue(5, $frontData, SQLITE3_TEXT);
            $stm->bindValue(6, $backData, SQLITE3_TEXT);
            $stm->bindValue(7, $otherData, SQLITE3_TEXT);
            $stm->bindValue(8, $notes, SQLITE3_TEXT);
            $stm->bindValue(9, $userName, SQLITE3_TEXT);
            $res = $stm->execute();

            $lastID = $this->db->query("SELECT last_insert_rowid();")->fetchArray()['last_insert_rowid()'];

            //handle the files
            $i = 0; //iterator
            foreach ($files['name'] as $originalFileName) {
                if ($originalFileName == '') continue;
                $fileExt = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $fileUUID = uniqid();
                switch ($fileExt) {
                    case 'jpg':
                        $image = imagecreatefromjpeg($files['tmp_name'][$i]);
                        $webpData = imagewebp($image, $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/webp/' . $fileUUID . '.webp', 50);
                        break;
                    default:
                        die('cant convert file ' . $originalFileName);
                }

                move_uploaded_file($files['tmp_name'][$i], $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/original/' . $originalFileName);

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
                $stm = $this->db->prepare($sql);
                $stm->bindValue(1, $fileUUID . '.webp', SQLITE3_TEXT);
                $stm->bindValue(2, $lastID, SQLITE3_TEXT);
                $stm->bindValue(3, $originalFileName, SQLITE3_TEXT);
                $stm->bindValue(4, $_SESSION['userName'], SQLITE3_TEXT);
                $stm->bindValue(5, time(), SQLITE3_TEXT);
                $res = $stm->execute();

                $i++;
            }
            header('Location: /samples?id=' . $lastID);
            $Log = new Log();
            $Log->add("ADD", "sample", $lastID, $number);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function remove(string $id): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        try {
            //remove images
            $stm = $this->db->prepare("DELETE FROM sample_images WHERE sample_id = ?");
            $stm->bindValue(1, $id, SQLITE3_TEXT);
            $stm->execute();
            //remove sample
            $stm = $this->db->prepare("DELETE FROM samples WHERE rowid = ?");
            $stm->bindValue(1, $id, SQLITE3_TEXT);
            $stm->execute();
            //header('Location: /samples');
            $Log = new Log();
            $Log->add("DELETE", "sample", $id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function removeImage(string $id, string $image): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        try {
            $stm = $this->db->prepare("DELETE FROM sample_images WHERE webp_filename = ?");
            $stm->bindValue(1, $image, SQLITE3_TEXT);
            $stm->execute();
            $Log = new Log();
            $Log->add("DELETE", "sample", null, 'image: ' . $image);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

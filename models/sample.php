<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class sample extends Database
{
    function get(int $id): array | false
    {
        $sql = <<<EOD
        SELECT samples.*, sample_images.webp_filename, sample_images.original_filename
        FROM `t-manager`.samples
            LEFT JOIN `t-manager`.sample_images
            ON samples.rowid = sample_images.sample_id
            WHERE rowid = ?;
        EOD;
        try {
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $stm->execute();
            $result = $stm->get_result();

            $sample = $result->fetch_assoc(); // get all sample details including first image

            $sample['images'] = []; // create array to hold image names
            $sample['originalNames'] = array(); // create array to hold image names

            array_push($sample['images'], $sample['webp_filename']); // push the first image
            array_push($sample['originalNames'], $sample['original_filename']); // push the first image

            while ($row = $result->fetch_assoc()) {
                array_push($sample['images'], $row['webp_filename']); // push the rest
                array_push($sample['originalNames'], $row['original_filename']); // push the rest
            }

            $stm->close();

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
            //print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'get()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
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
                    FROM `t-manager`.sample_images
                    WHERE sample_images.sample_id = samples.rowid
                    LIMIT 1
                ) AS image
            FROM `t-manager`.samples
            WHERE
                samples.name LIKE ? OR
                samples.otherref LIKE ? OR
                samples.number LIKE ?
            ORDER BY samples.rowid DESC
            LIMIT ?;
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $searchTerm = '%' . $search . '%';
            $stm->bind_param("sssi", $searchTerm, $searchTerm, $searchTerm, $limit); // code-spell-checker:disable-line
            $stm->execute();
            $response = $stm->get_result();

            $searchResults = [];
            while ($sample = $response->fetch_assoc()) {
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
            $stm->close();

            return $searchResults;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'search()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function update(int $id, string $frontData, string $backData, string $otherData, string $notes, string $name, string $number, string $otherRef, $files): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        $sql = <<<EOD
            UPDATE `t-manager`.samples
            SET printdata = ?, printdataback = ?, printdataother = ?, notes = ?, name = ?, number = ?, otherref = ?
            WHERE rowid = ?
            EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bind_param("sssssssi", $frontData, $backData, $otherData, $notes, $name, $number, $otherRef, $id); // code-spell-checker:disable-line
            $stm->execute();

            //if no files add log and return
            if ($files['name'] == '') {
                $Log = new Log();
                $Log->add("EDIT", "sample", $name, $id, " order number: {$number}");
                return true;
            }

            //handle new file
            $fileExt = pathinfo($files['name'], PATHINFO_EXTENSION);
            $fileUUID = uniqid();
            switch ($fileExt) {
                case 'jpg':
                    $image = imagecreatefromjpeg($files['tmp_name']);
                    $webpData = imagewebp($image, $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/webp/' . $fileUUID . '.webp', 50);
                    break;
                default:
                    throw new Exception('cant convert file ' . $files['name']);
            }

            move_uploaded_file($files['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/assets/images/samples/original/' . $files['name']);

            $sql = <<<EOD
            INSERT INTO `t-manager`.sample_images(
                webp_filename,
                sample_id,
                original_filename,
                user,
                date
                )
                VALUES (?,?,?,?,?)
            EOD;
            $stm = $this->db->prepare($sql);
            $uniqueFilename = $fileUUID . '.webp';
            $timeStamp = time();
            $stm->bind_param("sissi", $uniqueFilename, $_GET['id'], $files['name'], $_SESSION['userName'], $timeStamp); // code-spell-checker:disable-line
            $stm->execute();

            $Log = new Log();
            $Log->add("EDIT", "sample", $name, $id, "order number: {$number}");

            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'update()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function add(string $name, string $number, string $otherRef, string $frontData, string $backData, string $otherData, string $notes, string $userName, $files): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        if ($name == '' or $number == '') die('no name or number');
        if ($files['tmp_name'][0] == '') die('no files');
        $sql = <<<EOD
            INSERT INTO `t-manager`.samples (
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
            $timeStamp = time();
            $stm->bind_param("sssisssss", $name, $number, $otherRef, $timeStamp, $frontData, $backData, $otherData, $notes, $userName); // code-spell-checker:disable-line
            $res = $stm->execute();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.samples LIMIT 1;")->fetch_column();

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
            INSERT INTO `t-manager`.sample_images(
                webp_filename,
                sample_id,
                original_filename,
                user,
                date
                )
                VALUES (?,?,?,?,?)
            EOD;
                $stm = $this->db->prepare($sql);
                $uniqueFilename = $fileUUID . '.webp';
                $stm->bind_param("sissi", $uniqueFilename, $lastID, $originalFileName, $_SESSION['userName'], $timeStamp); // code-spell-checker:disable-line
                $res = $stm->execute();
                $i++;
            }

            header('Location: /samples?id=' . $lastID);

            $Log = new Log();
            $Log->add("NEW", "sample", $name, $lastID, "");

            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'add()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function remove(string $id): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        try {
            //remove images
            $stm = $this->db->prepare("DELETE FROM `t-manager`.sample_images WHERE sample_id = ?");
            $stm->bind_param("i", $id);
            $stm->execute();
            //remove sample
            $stm = $this->db->prepare("DELETE FROM `t-manager`.samples WHERE rowid = ?");
            $stm->bind_param("i", $id);
            $stm->execute();

            $Log = new Log();
            $Log->add("DELETE", "sample", null, $id, null);
            return true;
        } catch (Exception $e) {
            $Log = new Log();
            $Log->add('ERROR', 'remove()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function removeImage(string $id, string $image): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        try {
            $stm = $this->db->prepare("DELETE FROM `t-manager`.sample_images WHERE webp_filename = ?");
            $stm->bind_param("s", $image);
            $stm->execute();

            $Log = new Log();
            $Log->add("DELETE", "sample", null, $id, "delete image: {$image}");

            return true;
        } catch (Exception $e) {
            //print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'removeImage()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }
}

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

class FanaticOrders extends Database
{

    function parseCode(string $code): array
    {
        $splitCode = explode('¦', $code);
        return [
            'orderName' => trim($splitCode[0]),
            'orderCode' => trim($splitCode[1]),
            'garment' => trim($splitCode[2]),
            'XS' => (int)trim(explode(' ', trim($splitCode[3]))[0]),
            'S' => (int)trim(explode(' ', trim($splitCode[4]))[0]),
            'M' => (int)trim(explode(' ', trim($splitCode[5]))[0]),
            'L' => (int)trim(explode(' ', trim($splitCode[6]))[0]),
            'XL' => (int)trim(explode(' ', trim($splitCode[7]))[0]),
            '2XL' => (int)trim(explode(' ', trim($splitCode[8]))[0]),
            '3XL' => (int)trim(explode(' ', trim($splitCode[9]))[0]),
        ];
    }

    function addSize($forderId, $size, $amount)
    {
        $sql = <<<EOD
            INSERT INTO forders_sizes (
                forder_id,
                size,
                amount
            )
            VALUES (?,?,?)
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $forderId, SQLITE3_TEXT);
        $stm->bindValue(2, $size, SQLITE3_TEXT);
        $stm->bindValue(3, $amount, SQLITE3_TEXT);
        $stm->execute();
    }

    function getStockCode(string $code)
    {

        $splitCode = explode('-', $code);

        $type = '';
        $color = '';

        switch ($splitCode[0]) {  // type
            case '0302M':
                $type = '202M';
                break;
            case '0302W':
                $type = '202W';
                break;
            case '1108M':
                $type = '208M';
                break;
            case '1108W':
                $type = '208W';
                break;
            case '1110M':
                $type = '210M';
                break;
            case '1311M':
                $type = '211M';
                break;
            case '1311W':
                $type = '211W';
                break;
            case '2112M':
                $type = '212M';
                break;
            case '3111M':
                $type = '221M';
                break;
        }

        switch ($splitCode[1]) {  // color
            case 'AQU':
                $color = '590F';
                break;
            case 'BLK':
                $color = '127A';
                break;
            case 'BRW':
                $color = '4034';
                break;
            case 'CLT':
                $color = '2854';
                break;
            case 'DGN':
                $color = '0565';
                break;
            case 'GRD':
                $color = '008N';
                break;
            case 'GLD':
                $color = '0599';
                break;
            case 'KGN':
                $color = '008O';
                break;
            case 'KHA':
                $color = '00R9';
                break;
            case 'NVY':
                $color = 'EX53';
                break;
            case 'ORG':
                $color = '008R';
                break;
            case 'PIN':
                $color = '9117';
                break;
            case 'PPL':
                $color = '0501';
                break;
            case 'PPR':
                $color = '00E6';
                break;
            case 'URD':
                $color = '0484';
                break;
            case 'RYL':
                $color = '861G';
                break;
            case 'SKY':
                $color = '008S';
                break;
            case 'GRY':
                $color = '00U2';
                break;
            case 'TEA':
                $color = '1811';
                break;
            case 'WHT':
                $color = '0042';
                break;
        }

        return $type . $color;
    }

    function addOrder(string $code): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();

        //check if not in database done at database level CHANGE THIS!!
        $parsedCode = $this->parseCode($code);

        $sql = <<<EOD
            INSERT INTO forders (
                code,
                name,
                garment
            )
            VALUES (?,?,?)
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $parsedCode['orderCode'], SQLITE3_TEXT);
        $stm->bindValue(2, $parsedCode['orderName'], SQLITE3_TEXT);
        $stm->bindValue(3, $parsedCode['garment'], SQLITE3_TEXT);
        $stm->execute();

        $lastID = $this->db->query("SELECT last_insert_rowid();")->fetchArray()['last_insert_rowid()'];

        // die($lastID);

        if ($parsedCode['XS'] > 0) $this->addSize($lastID, 'XS', $parsedCode['XS']);
        if ($parsedCode['S'] > 0) $this->addSize($lastID, 'S', $parsedCode['S']);
        if ($parsedCode['M'] > 0) $this->addSize($lastID, 'M', $parsedCode['M']);
        if ($parsedCode['L'] > 0) $this->addSize($lastID, 'L', $parsedCode['L']);
        if ($parsedCode['XL'] > 0) $this->addSize($lastID, 'XL', $parsedCode['XL']);
        if ($parsedCode['2XL'] > 0) $this->addSize($lastID, '2XL', $parsedCode['2XL']);
        if ($parsedCode['3XL'] > 0) $this->addSize($lastID, '3XL', $parsedCode['3XL']);

        if ($parsedCode) return true;
        else return false;
    }

    function getOrder(string $id)
    {

        $sql = <<<EOD
            SELECT forders.*, forders_sizes.amount, forders_sizes.size
            FROM forders
                LEFT JOIN forders_sizes
                ON forders.id = forders_sizes.forder_id
                WHERE forders.id = ?;
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $id, SQLITE3_TEXT);
        $res = $stm->execute();
        $firstRes = $res->fetchArray();
        $order['id'] = $firstRes['id'];
        $order['code'] = $firstRes['code'];
        $order['name'] = $firstRes['name'];
        $order['garment'] = $firstRes['garment'];
        $order['status'] = $firstRes['status'];

        $order['sizes'] = [$firstRes['size'] => $firstRes['amount']];

        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {

            $order['sizes'] += [$row['size'] => $row['amount']];
        }
        return $order;
    }

    function getOrders($status = 'pending')
    {
        $sql = <<<EOD
            SELECT *
            FROM forders
            WHERE status  = ?;
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $status, SQLITE3_TEXT);
        $res = $stm->execute();

        $searchResults = [];
        while ($order = $res->fetchArray()) {
            $order = [
                'id' => $order['id'],
                'code' => $order['code'],
                'name' => $order['name'],
                'garment' => $order['garment'],
                'status' => $order['status']
            ];
            array_push($searchResults, $order);
        }
        return $searchResults;
    }

    function pickOrder()
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();
    }
}

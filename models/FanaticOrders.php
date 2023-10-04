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
        // returns 208W861G
        $splitCode = explode('-', $code);

        $type = '';
        $color = '';

        //      get type
        $sql = <<<EOD
            SELECT  newCode
            FROM stockCodes_type
            WHERE oldCode = ?
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $splitCode[0]);
        $res = $stm->execute();
        $row = $res->fetchArray();

        $type = $row['newCode'];

        //      get color
        $sql = <<<EOD
            SELECT  newCode
            FROM stockCodes_color
            WHERE oldCode = ?
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $splitCode[1]);
        $res = $stm->execute();
        $row = $res->fetchArray();

        $color = $row['newCode'];

        if ($type == '') die('cant parse code type');
        if ($color == '') die('cant parse code color');

        return $type . $color;
    }

    function addOrder(string $code)
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();

        //check if not in database done at database level CHANGE THIS!!
        $parsedCode = $this->parseCode($code);

        $sql = <<<EOD
            SELECT *
            FROM forders
            WHERE code = ?;
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $parsedCode['orderCode'], SQLITE3_TEXT);
        //$stm->bindValue(2, $parsedCode['orderName'], SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray();


        $tmp = 0;

        if ($row) return (string)$row['id'];

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

        if ($parsedCode) return $lastID;
        else return false;
    }

    function getOrder(string $id)
    {

        $sql = <<<EOD
            SELECT forders.*, forders_sizes.amount, forders_sizes.size, forders_sizes.picked
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
        $order['picked'] = [$firstRes['size'] => $firstRes['picked']];

        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {

            $order['sizes'] += [$row['size'] => $row['amount']];
            $order['picked'] += [$row['size'] => $row['picked']];
        }
        return $order;
    }

    function getOrders($status = 'pending')
    {
        $sql = <<<EOD
            SELECT *
            FROM forders
            
        EOD;

        $stm = $this->db->prepare($sql);
        //$stm->bindValue(1, $status, SQLITE3_TEXT);
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

    function pickSize($orderId, $size, $amount, $stockCode, $userLocation)
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();

        //get the entry about to be edited
        $sql = <<<EOD
            SELECT id, forder_id, size, amount, status, picked
            FROM forders_sizes
            WHERE forder_id = ? AND size = ?;
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $orderId, SQLITE3_TEXT);
        $stm->bindValue(2, $size, SQLITE3_TEXT);
        $res = $stm->execute();
        $currentEntry = $res->fetchArray();

        //check if can take that amount
        if ($currentEntry['amount'] - $amount < 0) return 'below zero';

        //set stock status
        $status = $currentEntry['status'];
        if ($currentEntry['amount'] == $currentEntry['picked'] + $amount) $status = 'complete';
        if ($currentEntry['amount'] > $currentEntry['picked'] + $amount) $status = 'short';

        //update the order
        $sql = <<<EOD
            UPDATE forders_sizes
            SET picked = ?, status = ?
            WHERE id = ?;
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $currentEntry['picked'] + $amount);
        $stm->bindValue(2, $status);
        $stm->bindValue(3, $currentEntry['id'], SQLITE3_TEXT);
        $res = $stm->execute();

        //remove the stock

        $Stock = new Stock();
        $Stock->removeStock($stockCode, $userLocation, $amount);


        $this->updateOrderStatus($currentEntry['forder_id']);

        if ($res) return 'ok';
    }

    function updateOrderStatus($id)
    {
        $status = 'pending';

        //get the order
        $order = $this->getOrder($id);

        //check for short
        foreach (array_keys($order['sizes']) as $sizeValue) {
            if ($order['sizes'][$sizeValue] - $order['picked'][$sizeValue] > 0) $status = 'short';
        };

        //check for complete
        $allComplete = true;
        foreach (array_keys($order['sizes']) as $sizeValue) {
            if ((int)$order['sizes'][$sizeValue] - (int)$order['picked'][$sizeValue] != 0) $allComplete = false;
        };
        if ($allComplete) $status = 'complete';

        $sql = <<<EOD
            UPDATE forders
            SET status = ?
            WHERE id = ?;
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $status);
        $stm->bindValue(2, $id, SQLITE3_TEXT);
        $res = $stm->execute();
    }
}

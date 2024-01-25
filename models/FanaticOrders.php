<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class FanaticOrders extends Database
{

    function parseCode(string $code): array
    {
        try {
            $splitCode = explode('¦', $code);
            $returnArray = [
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

            if (isset($splitCode[10])) $returnArray['4XL'] = (int)trim(explode(' ', trim($splitCode[10]))[0]);
            if (isset($splitCode[11])) $returnArray['5XL'] = (int)trim(explode(' ', trim($splitCode[11]))[0]);

            return $returnArray;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'parseCode()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    private function addSize($forderId, $size, $amount)
    {
        try {
            $sql = <<<EOD
            INSERT INTO `t-manager`.forders_sizes (
                forder_id,
                size,
                amount,
                status
            )
            VALUES (?,?,?,?)
            EOD;

            $status = 'pending';
            $stm = $this->db->prepare($sql);
            $stm->bind_param("isis", $forderId, $size, $amount, $status);
            $stm->execute();
            $stm->close();
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addSize()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getStockCode(string $code): string
    {
        try {
            // returns 208W861G
            $splitCode = explode('-', $code);

            $type = '';
            $color = '';

            //      get type
            $sql = <<<EOD
            SELECT newCode
            FROM `t-manager`.stockCodes_type
            WHERE oldCode = ?
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bind_param("s", $splitCode[0]);
            $stm->execute();
            $row = $stm->get_result()->fetch_assoc();
            $stm->close();
            $type = $row['newCode'] ?? '';

            //      get color
            $sql = <<<EOD
            SELECT newCode
            FROM `t-manager`.stockCodes_color
            WHERE oldCode = ?
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bind_param("s", $splitCode[1]);
            $stm->execute();
            $row = $stm->get_result()->fetch_assoc();
            $stm->close();
            $color = $row['newCode'] ?? '';

            if ($type == '') throw new Exception('cant parse code type');
            if ($color == '') throw new Exception('cant parse code color');

            return $type . $color;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getStockCode()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function addOrder(string $code): int | false
    {
        try {
            $Auth = new Auth();
            $Auth->isLoggedIn();

            $parsedCode = $this->parseCode($code);

            $sql = <<<EOD
            SELECT id, code, name, garment, status
            FROM `t-manager`.forders
            WHERE code = ? AND name = ?;
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bind_param("ss", $parsedCode['orderCode'], $parsedCode['orderName']);
            $stm->execute();
            $row = $stm->get_result()->fetch_assoc();

            if ($row) return $row['id'];

            $sql = <<<EOD
            INSERT INTO `t-manager`.forders (
                code,
                name,
                garment,
                status
            )
            VALUES (?,?,?,?)
            EOD;

            $this->db->query("START TRANSACTION");
            $stm = $this->db->prepare($sql);
            $status = 'pending';
            $stm->bind_param("ssss", $parsedCode['orderCode'], $parsedCode['orderName'], $parsedCode['garment'], $status);
            $stm->execute();
            $stm->close();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.stock LIMIT 1;")->fetch_column();

            if ($parsedCode['XS'] > 0) $this->addSize($lastID, 'XS', $parsedCode['XS']);
            if ($parsedCode['S'] > 0) $this->addSize($lastID, 'S', $parsedCode['S']);
            if ($parsedCode['M'] > 0) $this->addSize($lastID, 'M', $parsedCode['M']);
            if ($parsedCode['L'] > 0) $this->addSize($lastID, 'L', $parsedCode['L']);
            if ($parsedCode['XL'] > 0) $this->addSize($lastID, 'XL', $parsedCode['XL']);
            if ($parsedCode['2XL'] > 0) $this->addSize($lastID, '2XL', $parsedCode['2XL']);
            if ($parsedCode['3XL'] > 0) $this->addSize($lastID, '3XL', $parsedCode['3XL']);
            if (isset($parsedCode['4XL']) && $parsedCode['4XL'] > 0) $this->addSize($lastID, '4XL', $parsedCode['4XL']);
            if (isset($parsedCode['5XL']) && $parsedCode['5XL'] > 0) $this->addSize($lastID, '5XL', $parsedCode['5XL']);

            $this->db->query("COMMIT");
            $Log = new Log();
            $Log->add("NEW", "order", $parsedCode['orderName'], $lastID, $code);

            if ($lastID > 0) return $lastID;
            else return false;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addOrder()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getOrder(int $id): array
    {
        try {
            $sql = <<<EOD
            SELECT forders.*, forders_sizes.amount, forders_sizes.size, forders_sizes.picked
            FROM `t-manager`.forders
                LEFT JOIN `t-manager`.forders_sizes
                ON `t-manager`.forders.id = `t-manager`.forders_sizes.forder_id
                WHERE `t-manager`.forders.id =?;
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $stm->execute();
            $result = $stm->get_result();
            $firstRes = $result->fetch_assoc();

            $order = [];
            $order['id'] = $firstRes['id'];
            $order['code'] = $firstRes['code'];
            $order['name'] = $firstRes['name'];
            $order['garment'] = $firstRes['garment'];
            $order['status'] = $firstRes['status'];
            $order['sizes'] = [$firstRes['size'] => $firstRes['amount']];
            $order['picked'] = [$firstRes['size'] => $firstRes['picked']];

            while ($row = $result->fetch_assoc()) {

                $order['sizes'] += [$row['size'] => $row['amount']];
                $order['picked'] += [$row['size'] => $row['picked']];
            }
            $stm->close();
            return $order;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getOrder()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getOrders($status = 'pending/short'): array
    {
        try {
            if ($status == 'complete') {
                $sql = <<<EOD
                    SELECT *
                    FROM `t-manager`.forders
                    WHERE status = 'complete'
                    ORDER BY id DESC
                EOD;
            }
            if ($status == 'pending/short') {
                $sql = <<<EOD
                    SELECT *
                    FROM `t-manager`.forders
                    WHERE status = 'short' OR status = 'pending'
                    ORDER BY id DESC
                EOD;
            }

            $stm = $this->db->prepare($sql);
            $stm->execute();
            $result = $stm->get_result();

            $searchResults = [];
            while ($order = $result->fetch_assoc()) {
                $order = [
                    'id' => $order['id'],
                    'code' => $order['code'],
                    'name' => $order['name'],
                    'garment' => $order['garment'],
                    'status' => $order['status']
                ];
                array_push($searchResults, $order);
            }
            $stm->close();
            return $searchResults;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getOrders()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function pickSize(int $orderId, $size, $amount, $stockCode, $userLocation): bool
    {
        try {
            $Auth = new Auth();
            $Auth->isLoggedIn();

            //get the entry about to be edited
            $sql = <<<EOD
            SELECT id, forder_id, size, amount, status, picked
            FROM `t-manager`.forders_sizes
            WHERE forder_id = ? AND size = ?;
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bind_param("is", $orderId, $size);
            $res = $stm->execute();
            $currentEntry = $stm->get_result()->fetch_assoc();
            $stm->close();

            //check if can take that amount
            if ($currentEntry['amount'] - $amount < 0) return 'below zero';

            //set stock status
            $status = $currentEntry['status'];
            if ($currentEntry['amount'] == $currentEntry['picked'] + $amount) $status = 'complete';
            if ($currentEntry['amount'] > $currentEntry['picked'] + $amount) $status = 'short';

            //update the order
            $sql = <<<EOD
            UPDATE `t-manager`.forders_sizes
            SET picked = ?, status = ?
            WHERE id = ?;
            EOD;
            $stm = $this->db->prepare($sql);
            $amountPicked = $currentEntry['picked'] + $amount;
            $stm->bind_param("sss", $amountPicked, $status, $currentEntry['id']);
            $res = $stm->execute();
            $stm->close();

            //get current order info

            $sql = <<<EOD
            SELECT id, code, name, garment, status
            FROM `t-manager`.forders
            WHERE id = ?;
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bind_param("s", $orderId);
            $res = $stm->execute();
            $result = $stm->get_result();
            $currentOrder = $result->fetch_assoc();
            $stm->close();

            //remove the stock

            $Stock = new Stock();
            $Stock->removeStock($stockCode, $userLocation, (int)$amount, 'pick', $currentOrder['name'], $currentOrder['id']);

            $this->updateOrderStatus($currentEntry['forder_id']);

            if ($res) return true;
        } catch (Exception $e) {
            // print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'pickSize()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function updateOrderStatus(int $id): void
    {
        try {
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
            UPDATE `t-manager`.forders
            SET status = ?
            WHERE id = ?;
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bind_param("si", $status, $id);
            $stm->execute() or throw new Exception("error updating order: {$id}");
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'updateOrderStatus()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function deleteOrder(int $id): bool
    {
        try {
            $Auth = new Auth();
            $Auth->isLoggedIn();

            //get order
            $currentOrder = $this->getOrder($id);

            //remove stock
            $stm = $this->db->prepare("DELETE FROM `t-manager`.forders_sizes WHERE forder_id = ?");
            $stm->bind_param("i", $id);
            $stm->execute();
            //$stm->close();

            //remove order
            $stm2 = $this->db->prepare("DELETE FROM `t-manager`.forders WHERE id = ?");
            $stm2->bind_param("i", $id);
            $stm2->execute();
            //$stm->close();

            $Log = new Log();
            $Log->add("DELETE", "order", $currentOrder['name'], $id, "delete fanatic order");

            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'deleteOrder()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getIdFromNumber(string $orderNumber): int|false
    {
        try {
            $sql = <<<EOD
            SELECT id
            FROM `t-manager`.forders
            WHERE name = ?;
        EOD;
            $stm = $this->db->prepare($sql);
            $trimOrder = trim($orderNumber);
            $stm->bind_param("s", $trimOrder);
            $stm->execute();
            $result = $stm->get_result();
            $currentOrder = $result->fetch_assoc() or throw new Exception($orderNumber . " not in database");

            return (int)$currentOrder['id'];
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getIdFromNumber()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }
}

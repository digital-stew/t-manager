<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class Stock extends Database
{

    function parseCode(string $code): array
    {
        try {
            if (strlen($code) < 11) throw new Exception('stock code must be 11 characters');
            $code  = strtoupper($code); // auto capitalize user input
            $splitCode = str_split($code, 4);

            $type = '';
            $color = '';
            $size = '';

            //      test type
            $sql = <<<EOD
                SELECT id, newCode, oldCode, type
                FROM `t-manager`.stockCodes_type
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $result = $stm->get_result();

            while ($row = $result->fetch_assoc()) {
                if ($splitCode[0] == $row['newCode']) $type = $row['type'];
            }
            $stm->close();

            //      test color
            $sql = <<<EOD
                SELECT id, newCode, oldCode, color
                FROM `t-manager`.stockCodes_color
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $result = $stm->get_result();
            while ($row = $result->fetch_assoc()) {
                if ($splitCode[1] == $row['newCode']) $color = $row['color'];
            }
            $stm->close();

            //      test size
            $sql = <<<EOD
                SELECT id, code, size
                FROM `t-manager`.stockCodes_size
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $result = $stm->get_result();
            while ($row = $result->fetch_assoc()) {
                if ($splitCode[2] == $row['code']) $size = $row['size'];
            }
            $stm->close();

            if ($type == '') throw new Exception('parse error: type');
            if ($color == '') throw new Exception('parse error: color');
            if ($size == '') throw new Exception('parse error: size');

            return [
                'type' => $type,
                'color' => $color,
                'size' => $size,
            ];
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'parseCode()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function searchCode(string $code): array
    {
        $sql = <<<EOD
            SELECT *
            FROM `t-manager`.stock
            WHERE code LIKE ?
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $searchTerm = $code . '%';
            $stm->bind_param("s", $searchTerm);
            $stm->execute();
            $results = $stm->get_result();

            $searchResults = [];
            while ($result = $results->fetch_assoc()) {
                $result = [
                    'id' => $result['id'],
                    'code' => $result['code'],
                    'color' => $result['color'],
                    'size' => $result['size'],
                    'type' => $result['type'],
                    'location' => $result['location'],
                    'amount' => $result['amount']
                ];
                array_push($searchResults, $result);
            }
            $stm->close();
            return $searchResults;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'searchCode()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function search($color = 'all', $size = 'all', $type = 'all', $location = 'all'): array
    {
        $sql = <<< EOD
            SELECT *
            FROM `t-manager`.stock
        EOD;

        try {
            $where = [];
            if ($color !== 'all') array_push($where, "color = '$color'");
            if ($size !== 'all') array_push($where, "size = '$size'");
            if ($type !== 'all') array_push($where, "type = '$type'");
            if ($location !== 'all') array_push($where, "location = '$location'");
            if (sizeof($where) > 0) {
                $sql .= ' WHERE ';
                $sql .= implode(' AND ', $where);
            }

            $sql .= " ORDER BY id DESC";

            $stm = $this->db->prepare($sql);
            $stm->execute();
            $res = $stm->get_result();

            $searchResults = [];
            while ($result = $res->fetch_assoc()) {
                $result = [
                    'id' => $result['id'],
                    'code' => $result['code'],
                    'color' => $result['color'],
                    'size' => $result['size'],
                    'type' => $result['type'],
                    'location' => $result['location'],
                    'amount' => $result['amount']
                ];
                array_push($searchResults, $result);
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

    function addStock($code, $location, $amount): bool
    {
        try {
            $Auth = new Auth();
            $Auth->isLoggedIn();

            $code  = strtoupper($code); // auto capitalize user input

            $parsedCode = $this->parseCode($code);

            //check is code exists in database
            $sql = <<<EOD
                SELECT *
                FROM `t-manager`.stock
                WHERE code = ?
                    AND type = ?
                    AND color = ?
                    AND size = ?
                    AND location = ?
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bind_param("sssss", $code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
            $stm->execute();
            $isInDatabase = $stm->get_result()->fetch_assoc();
            $stm->close();

            if ($isInDatabase) { // if code exists in database
                $sql = <<<EOD
                    UPDATE `t-manager`.stock
                    SET amount = amount + ?
                    WHERE code = ?
                        AND type = ?
                        AND color = ?
                        AND size = ?
                        AND location = ?
                EOD;

                $stm = $this->db->prepare($sql);
                $stm->bind_param("ssssss", $amount, $code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
                $res = $stm->execute();
                $stm->close();

                $Log = new Log();
                $Log->add("ADD", "stock", null, null, "edit entry - code: {$code} location: {$location} amount: {$amount}");
                if ($res) return true;
            } else { // if code not exist in database
                $sql = <<<EOD
                    INSERT INTO `t-manager`.stock (
                        code,
                        type,
                        color,
                        size,
                        location,
                        amount
                    )
                    VALUES (?,?,?,?,?,?)
                EOD;

                $stm = $this->db->prepare($sql);
                $stm->bind_param("ssssss", $code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location, $amount);
                $res = $stm->execute();
                $stm->close();

                $Log = new Log();
                $Log->add("ADD", "stock", null, null, "new entry - code: {$code} location: {$location} amount: {$amount}");
                if ($res) return true;
            }

            return false;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'addStock()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function removeStock(string $code, string $location, int $amount, string $reason, string $orderNumber, int $orderId): bool
    {
        //TODO check stock code - check order number - just remove stock(admin only)
        try {
            $Auth = new Auth();
            $Auth->isLoggedIn();

            $code  = strtoupper($code); // auto capitalize user input

            if ($reason == 'none') throw new Exception('no remove stock reason');

            $parsedCode = $this->parseCode($code);

            // check there is enough stock to remove $amount
            (int)$currentAmount = $this->getCurrentStockAmount($code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
            if ((int)$amount > $currentAmount) throw new Exception("not enough {$code} at {$location} to remove {$amount}");

            $sql = <<<EOD
                UPDATE `t-manager`.stock
                SET amount = CASE
                    WHEN amount >= ? THEN amount - ?
                    ELSE amount
                END
                WHERE code = ?
                    AND type = ?
                    AND color = ?
                    AND size = ?
                    AND location = ?
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bind_param("iisssss", $amount, $amount, $code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location); // code-spell-checker:disable-line
            $stm->execute();
            $stm->close();

            // if stock is zero remove from database
            $currentAmount = $this->getCurrentStockAmount($code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
            if ($currentAmount === 0) {
                $sql = <<<EOD
                    DELETE FROM `t-manager`.stock
                    WHERE code = ?
                        AND type = ?
                        AND color = ?
                        AND size = ?
                        AND location = ?
                EOD;

                $stm = $this->db->prepare($sql);
                $stm->bind_param("sssss", $code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
                $stm->execute();
                $stm->close();
            }

            //get current order id
            $Log = new Log();
            $Log->add("REMOVE", "stock", $orderNumber, $orderId, "code: {$code} - size: {$parsedCode['size']} - amount: {$amount} - reason: {$reason}");
            return true;
        } catch (Exception $e) {
            //print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'removeStock()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function getCurrentStockAmount(string $code, string $type, string $color, string $size, string $location): int
    {
        $sql = <<<EOD
            SELECT amount
            FROM `t-manager`.stock
            WHERE code = ?
            AND type = ?
            AND color = ?
            AND size = ?
            AND location = ?
        EOD;

        try {
            $stm = $this->db->prepare($sql);
            $stm->bind_param("sssss", $code, $type, $color, $size, $location);
            $stm->execute();

            $row = $stm->get_result()->fetch_assoc();
            $stm->close();

            if ($row) {
                return (int)$row['amount'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getCurrentStockAmount()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getTypes($all = false): array
    {
        if ($all) {
            $sql = <<<EOD
            SELECT id, newCode, oldCode, type, trueCode
            FROM `t-manager`.stockCodes_type
            ORDER BY id
            EOD;
        } else {
            $sql = <<<EOD
            SELECT id, newCode, oldCode, type, trueCode
            FROM `t-manager`.stockCodes_type
            WHERE trueCode = true
            ORDER BY id
            EOD;
        }

        try {
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $res = $stm->get_result();

            $types = [];
            while ($row = $res->fetch_assoc()) {
                $newType = array(
                    'id' => $row['id'],
                    'newCode' => $row['newCode'],
                    'oldCode' => $row['oldCode'],
                    'type' => $row['type'],
                    'trueCode' => $row['trueCode']
                );
                array_push($types, $newType);
            }

            $stm->close();

            return $types;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getTypes()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getSizes(): array
    {
        $sql = <<<EOD
            SELECT id, code, size
            FROM `t-manager`.stockCodes_size
            ORDER BY id
        EOD;
        try {
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $res = $stm->get_result();

            $sizes = [];
            while ($row = $res->fetch_assoc()) {
                $newSize = array(
                    'id' => $row['id'],
                    'code' => $row['code'],
                    'size' => $row['size']
                );
                array_push($sizes, $newSize);
            }
            $stm->close();

            return $sizes;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getSizes()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }

    function getColors($all = false): array
    {
        if ($all) {
            $sql = <<<EOD
            SELECT id, newCode, oldCode, color, trueCode
            FROM `t-manager`.stockCodes_color
            ORDER BY id
            EOD;
        } else {
            $sql = <<<EOD
            SELECT id, newCode, oldCode, color, trueCode
            FROM `t-manager`.stockCodes_color
            WHERE trueCode = true
            ORDER BY id
            EOD;
        }

        try {
            $stm = $this->db->prepare($sql);
            $stm->execute();
            $res = $stm->get_result();

            $colors = [];
            while ($row = $res->fetch_assoc()) {
                $newColor = array(
                    'id' => $row['id'],
                    'newCode' => $row['newCode'],
                    'oldCode' => $row['oldCode'],
                    'color' => $row['color'],
                    'trueCode' => $row['trueCode']
                );
                array_push($colors, $newColor);
            }
            $stm->close();

            return $colors;
            // if ($all) return $colors;
            // else return $this->unique_array($colors, 'color');
        } catch (Exception $e) {
            //print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'getColors()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    private function unique_array($array, $key)
    {
        $rArray = array_reverse($array); // potential bug in manual add fanatic order
        $temp_array = array();
        $i = 0;
        $key_array = array();
        foreach ($rArray as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    function transferStock(string $stockCode, string $from, string $to, int $amount): bool
    {
        try {
            //check code is valid
            $stockCode = trim($stockCode);
            $parsedCode = $this->parseCode($stockCode);
            //check from and to are valid
            $Admin = new Admin();
            $locations = $Admin->getLocations();
            if (!in_array($from, $locations)) throw new Exception('invalid source');
            if (!in_array($to, $locations)) throw new Exception('invalid destination');

            $this->db->query("START TRANSACTION");
            //remove stock
            $this->removeStock($stockCode, $from, (int)$amount, "transfer from: {$from}", '', 0) or throw new Exception('transfer stock remove error ' . "from: {$from} - code: {$stockCode} - amount: {$amount}");
            //add stock
            $this->addStock($stockCode, $to, $amount) or throw new Exception('transfer stock add error');
            $this->db->query("COMMIT");

            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            //print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'transferStock()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }
}

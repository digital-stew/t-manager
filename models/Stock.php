<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class Stock extends Database
{

    function parseCode(string $code): array
    {
        if (strlen($code) < 11) die('bad stock code');
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

        if ($type == '') die('cant parse type');
        if ($color == '') die('cat parse color');
        if ($size == '') die('cant parse size');

        return [
            'type' => $type,
            'color' => $color,
            'size' => $size,
        ];
    }

    function searchCode(string $code): array
    {
        $sql = <<<EOD
        SELECT *
        FROM `t-manager`.stock
        WHERE code LIKE ?
        EOD;
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
    }

    function search($color = 'all', $size = 'all', $type = 'all', $location = 'all'): array
    {

        $sql = <<< EOD
            SELECT *
            FROM `t-manager`.stock
        EOD;

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
    }

    function addStock($code, $location, $amount): bool
    {
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
    }

    function removeStock(string $code, string $location, int $amount, string $reason, string $orderNumber, int $orderId): bool
    {
        //TODO check stock code - check order number - just remove stock(admin only)
        $Auth = new Auth();
        $Auth->isLoggedIn();

        $code  = strtoupper($code); // auto capitalize user input

        if ($reason == 'none') die('no reason');

        $parsedCode = $this->parseCode($code);

        // check there is enough stock to remove $amount
        $currentAmount = $this->getCurrentStockAmount($code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
        if ($amount > $currentAmount) return false;

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
        $Log->add("REMOVE", "stock", $orderNumber, $orderId, "size: {$parsedCode['size']} - amount: {$amount} - reason: {$reason}");
        return true;
    }

    function getCurrentStockAmount(string $code, string $type, string $color, string $size, string $location): int | false
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
    }

    function getTypes(): array
    {
        $sql = <<<EOD
            SELECT id, newCode, oldCode, type
            FROM `t-manager`.stockCodes_type
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->execute();
        $res = $stm->get_result();

        $types = [];
        while ($row = $res->fetch_assoc()) {
            $newType = array(
                'id' => $row['id'],
                'newCode' => $row['newCode'],
                'oldCode' => $row['oldCode'],
                'type' => $row['type']
            );
            array_push($types, $newType);
        }
        $stm->close();

        return $this->unique_array($types, 'type');
    }

    function getSizes(): array
    {
        $sql = <<<EOD
            SELECT id, code, size
            FROM `t-manager`.stockCodes_size
        EOD;
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
    }

    function getColors(): array
    {
        $sql = <<<EOD
            SELECT id, newCode, oldCode, color
            FROM `t-manager`.stockCodes_color
        EOD;
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
            );
            array_push($colors, $newColor);
        }
        $stm->close();

        return $this->unique_array($colors, 'color');
    }

    function getReasonsToRemoveStock(): array
    {
        $sql = <<<EOD
            SELECT id, reason
            FROM `t-manager`.removeStockReasons
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->execute();
        $res = $stm->get_result();

        $reasons = [];
        while ($row = $res->fetch_assoc()) {
            $newReason = array(
                'id' => $row['id'],
                'reason' => $row['reason'],
            );
            array_push($reasons, $newReason);
        }
        $stm->close();

        return $reasons;
    }
    private function unique_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();
        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}

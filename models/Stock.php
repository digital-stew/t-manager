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
            FROM stockCodes_type
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();
        while ($row = $res->fetchArray()) {
            if ($splitCode[0] == $row['newCode']) $type = $row['type'];
        }

        //      test color
        $sql = <<<EOD
            SELECT id, newCode, oldCode, color
            FROM stockCodes_color
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();
        while ($row = $res->fetchArray()) {
            if ($splitCode[1] == $row['newCode']) $color = $row['color'];
        }

        //      test size
        $sql = <<<EOD
            SELECT id, code, size
            FROM stockCodes_size
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();
        while ($row = $res->fetchArray()) {
            if ($splitCode[2] == $row['code']) $size = $row['size'];
        }


        if ($type == '') die('cant parse type');
        if ($color == '') die('cat parse color');
        if ($size == '') die('cant parse size');

        return [
            'type' => $type,
            'color' => $color,
            'size' => $size,
        ];
    }

    function searchCode($code)
    {
        $sql = <<<EOD
        SELECT *
        FROM stock
        WHERE code LIKE ?
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $code . '%');
        $res = $stm->execute();
        // $isInDatabase = $res->fetchArray();
        $searchResults = [];
        while ($result = $res->fetchArray()) {
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
        return $searchResults;
        // return $isInDatabase;
    }

    function search($color = 'all', $size = 'all', $type = 'all', $location = 'all'): array
    {

        // make user input safe
        $color = SQLite3::escapeString($color);
        $size = SQLite3::escapeString($size);
        $type = SQLite3::escapeString($type);
        $location = SQLite3::escapeString($location);

        //start sql string builder
        $sql = <<< EOD
            SELECT *
            FROM stock
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

        $stm = $this->db->prepare($sql);
        $res = $stm->execute();

        $searchResults = [];
        while ($result = $res->fetchArray()) {
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
        FROM stock
        WHERE code = ?
            AND type = ?
            AND color = ?
            AND size = ?
            AND location = ?
        EOD;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $code);
        $stm->bindValue(2, $parsedCode['type']);
        $stm->bindValue(3, $parsedCode['color']);
        $stm->bindValue(4, $parsedCode['size']);
        $stm->bindValue(5, $location);
        $res = $stm->execute();
        $isInDatabase = $res->fetchArray();

        if ($isInDatabase) { // if code exists in database
            $sql = <<<EOD
            UPDATE stock
            SET amount = amount + ?
            WHERE code = ?
                AND type = ?
                AND color = ?
                AND size = ?
                AND location = ?
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $amount);
            $stm->bindValue(2, $code);
            $stm->bindValue(3, $parsedCode['type']);
            $stm->bindValue(4, $parsedCode['color']);
            $stm->bindValue(5, $parsedCode['size']);
            $stm->bindValue(6, $location);
            $res = $stm->execute();
            $Log = new Log();
            $Log->add("ADD", "stock", null, "code: {$code} location: {$location} amount: {$amount}");
            if ($res) return true;
        } else { // if code not exist in database
            $sql = <<<EOD
            INSERT INTO stock (
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
            $stm->bindValue(1, $code);
            $stm->bindValue(2, $parsedCode['type']);
            $stm->bindValue(3, $parsedCode['color']);
            $stm->bindValue(4, $parsedCode['size']);
            $stm->bindValue(5, $location);
            $stm->bindValue(6, $amount);
            $res = $stm->execute();
            $Log = new Log();
            $Log->add("ADD", "stock", null, "code: {$code} location: {$location} amount: {$amount}");
            if ($res) return true;
        }

        return false;
    }

    function removeStock($code, $location, $amount): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();

        $code  = strtoupper($code); // auto capitalize user input

        $parsedCode = $this->parseCode($code);

        // check there is enough stock to remove $amount
        $currentAmount = $this->getCurrentStockAmount($code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
        if ($amount > $currentAmount) return false;

        $sql = <<<EOD
            UPDATE stock
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
        $stm->bindValue(1, $amount);
        $stm->bindValue(2, $amount);
        $stm->bindValue(3, $code);
        $stm->bindValue(4, $parsedCode['type']);
        $stm->bindValue(5, $parsedCode['color']);
        $stm->bindValue(6, $parsedCode['size']);
        $stm->bindValue(7, $location);
        $res = $stm->execute();

        // if stock is zero remove from database
        $currentAmount = $this->getCurrentStockAmount($code, $parsedCode['type'], $parsedCode['color'], $parsedCode['size'], $location);
        if ($currentAmount === 0) {
            $sql = <<<EOD
                DELETE FROM stock
                WHERE code = ?
                    AND type = ?
                    AND color = ?
                    AND size = ?
                    AND location = ?
            EOD;

            $stm = $this->db->prepare($sql);
            $stm->bindValue(1, $code);
            $stm->bindValue(2, $parsedCode['type']);
            $stm->bindValue(3, $parsedCode['color']);
            $stm->bindValue(4, $parsedCode['size']);
            $stm->bindValue(5, $location);
            $stm->execute();
        }
        $Log = new Log();
        $Log->add("REMOVE", "stock", null, "code: {$code} location: {$location} amount: {$amount}");
        if ($res) return true;
        return false;
    }

    function getCurrentStockAmount($code, $type, $color, $size, $location)
    {
        $sql = <<<EOD
            SELECT amount
            FROM stock
            WHERE code = ?
                AND type = ?
                AND color = ?
                AND size = ?
                AND location = ?
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bindValue(1, $code);
        $stm->bindValue(2, $type);
        $stm->bindValue(3, $color);
        $stm->bindValue(4, $size);
        $stm->bindValue(5, $location);
        $res =  $stm->execute();

        $row = $res->fetchArray();

        if ($row) {
            return $row['amount'];
        } else {
            return false;
        }
    }

    function getTypes(): array
    {
        $sql = <<<EOD
            SELECT id, newCode, oldCode, type
            FROM stockCodes_type
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();

        $types = [];
        while ($row = $res->fetchArray()) {
            $newType = array(
                'id' => $row['id'],
                'newCode' => $row['newCode'],
                'oldCode' => $row['oldCode'],
                'type' => $row['type']
            );
            array_push($types, $newType);
        }

        return $types;
    }

    function getSizes(): array
    {
        $sql = <<<EOD
            SELECT id, code, size
            FROM stockCodes_size
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();

        $sizes = [];
        while ($row = $res->fetchArray()) {
            $newSize = array(
                'id' => $row['id'],
                'code' => $row['code'],
                'size' => $row['size']
            );
            array_push($sizes, $newSize);
        }

        return $sizes;
    }

    function getColors(): array
    {
        $sql = <<<EOD
            SELECT id, newCode, oldCode, color
            FROM stockCodes_color
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();

        $colors = [];
        while ($row = $res->fetchArray()) {
            $newColor = array(
                'id' => $row['id'],
                'newCode' => $row['newCode'],
                'oldCode' => $row['oldCode'],
                'color' => $row['color'],
            );
            array_push($colors, $newColor);
        }

        return $colors;
    }
}

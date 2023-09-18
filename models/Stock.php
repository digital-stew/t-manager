<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';

class Stock extends Database
{

    function parseCode(string $code): array
    {
        $splitCode = str_split($code, 4);
        $type = '';
        $color = '';
        $size = '';

        switch ($splitCode[0]) {  // type

            case '202M':
                $type = 'core crew sweatshirt - mens';
                break;
            case '202W':
                $type = 'core crew sweatshirt - womans';
                break;
            case '208M':
                $type = 'core ss tee - mens';
                break;
            case '208W':
                $type = 'core ss tee (relaxed fit) - womans';
                break;
            case '210M':
                $type = 'core LS tee - mens';
                break;
            case '211M':
                $type = 'core hoodie - mens';
                break;
            case '211W':
                $type = 'core hoodie (relaxed fit) - womans';
                break;
            case '212M':
                $type = 'core sweat short - mens';
                break;
            case '221M':
                $type = 'core jogger - mens';
                break;
        }

        switch ($splitCode[1]) { // color
            case '590F':
                $color = 'aqua / new aqua';
                break;
            case '127A':
                $color = 'black / black';
                break;
            case '4034':
                $color = 'brown / classic brown';
                break;
            case '2854':
                $color = 'claret / Rhododendron';
                break;
            case '0565':
                $color = 'dark green / dark green';
                break;
            case '008N':
                $color = 'game red / samba';
                break;
            case '0599':
                $color = 'gold / yellow gold';
                break;
            case '008O': // O is NOT a zero 
                $color = 'kelly green / jolly green';
                break;
            case '00R9':
                $color = 'khaki / safari';
                break;
            case 'EX53':
                $color = 'navy / maritime navy';
                break;
            case '008R':
                $color = 'orange / orangeade';
                break;
            case '9117':
                $color = 'pink / silver pink';
                break;
            case '0501':
                $color = 'purple / dark purple';
                break;
            case '00E6':
                $color = 'purple rose / purple rose';
                break;
            case '0484':
                $color = 'uniform red / athletic red';
                break;
            case '861G':
                $color = 'royal / blue chip';
                break;
            case '008S':
                $color = 'sky / boy blue';
                break;
            case '00U2':
                $color = 'sports grey / sports grey heather';
                break;
            case '1811':
                $color = 'teal / active blue';
                break;
            case '0042':
                $color = 'white / white';
                break;
        }

        switch ($splitCode[2]) { //size
            case 'XS0':
                $size = 'XS';
                break;
            case 'S00':
                $size = 'S';
                break;
            case 'M00':
                $size = 'M';
                break;
            case 'L00':
                $size = 'L';
                break;
            case 'XL0':
                $size = 'XL';
                break;
            case '2XL':
                $size = '2XL';
                break;
            case '3XL':
                $size = '3XL';
                break;
            case '4XL':
                $size = '4XL';
                break;
            case '5XL':
                $size = '5XL';
                break;
            case '6XL':
                $size = '6XL';
                break;
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
            if ($res) return true;
        }

        return false;
    }

    function removeStock($code, $location, $amount): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();

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

    function getLocations(): array
    {
        return ['hawkins', 'fleetwood', 'cornwall'];
    }

    function getTypes(): array
    {
        return [
            'core crew sweatshirt - mens',
            'core crew sweatshirt - womans',
            'core ss tee - mens',
            'core ss tee (relaxed fit) - womans',
            'core LS tee - mens',
            'core hoodie - mens',
            'core hoodie (relaxed fit) - womans',
            'core sweat short - mens',
            'core jogger - mens'
        ];
    }

    function getSizes(): array
    {
        return ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL'];
    }

    function getColors(): array
    {
        return [
            'aqua / new aqua',
            'black / black',
            'brown / classic brown',
            'claret / Rhododendron',
            'dark green / dark green',
            'game red / samba',
            'gold / yellow gold',
            'kelly green / jolly green',
            'khaki / safari',
            'navy / maritime navy',
            'orange / orangeade',
            'pink / silver pink',
            'purple / dark purple',
            'purple rose / purple rose',
            'uniform red / athletic red',
            'royal / blue chip',
            'sky / boy blue',
            'sports grey / sports grey heather',
            'teal / active blue',
            'white / white'
        ];
    }
}

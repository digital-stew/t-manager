<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';

class Log extends Database
{
    function get(): array
    {
        $sql = <<<EOD
        SELECT *
        FROM `t-manager`.log
        ORDER BY id DESC
        LIMIT 1000
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();
        $result = $stm->get_result();
        // $result = $stm->execute()->get_result();


        $searchResults = [];
        while ($log = $result->fetch_assoc()) {
            //while ($log = $stm->get_result()->fetch_assoc()) {
            $tmp = array(
                'id' => $log['id'],
                'action' => $log['action'],
                'subject' => $log['subject'],
                'orderName' => $log['orderName'],
                'logID' => $log['logID'],
                'note' => $log['note'],
                'userName' => $log['userName'],
                'timestamp' => $log['timestamp'],
            );
            array_push($searchResults, $tmp);
        }
        return $searchResults;
    }
    function add($action, $subject, $orderName, $id, $note)
    {

        $sql = <<<EOD
            INSERT INTO `t-manager`.log
            (
                action,
                subject,
                orderName,
                logID,
                note,
                userName,
                timestamp
            )
            VALUES
            (
                ?,?,?,?,?,?,?
            )
        EOD;

        //   die($sql . ' on');
        //   $orderName = 'testing';
        $stm = $this->db->prepare($sql);
        // die('here');
        $time = time();
        $un = $_SESSION['userName'] ?? '';
        $stm->bind_param("sssissi", $action, $subject, $orderName, $id, $note, $un, $time) or die('log param');
        /*
        $stm->bindValue(1, $action, SQLITE3_TEXT) or die('this1');
        $stm->bindValue(2, $subject, SQLITE3_TEXT) or die('this2');
        $stm->bindValue(3, $orderName, SQLITE3_TEXT) or die('this3');
        $stm->bindValue(4, $id, SQLITE3_TEXT) or die('this4');
        $stm->bindValue(5, $note, SQLITE3_TEXT) or die('this5');
        $stm->bindValue(6, $_SESSION['userName'] ?? '', SQLITE3_TEXT) or die('this6');
        $stm->bindValue(7, time(), SQLITE3_INTEGER) or die('this7');
        $res = $stm->execute();
        */
        $dbResponse = $stm->execute();
        if ($dbResponse) return true;
        else return false;
    }
}

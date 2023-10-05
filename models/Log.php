<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';

class Log extends Database
{
    function get(): array
    {
        $sql = <<<EOD
        SELECT *
        FROM log
        LIMIT 100
        EOD;
        $stm = $this->db->prepare($sql);
        $res = $stm->execute();

        $searchResults = [];
        while ($log = $res->fetchArray()) {
            $tmp = array(
                'id' => $log['id'],
                'action' => $log['action'],
                'subject' => $log['subject'],
                'logID' => $log['logID'],
                'note' => $log['note'],
                'userName' => $log['userName'],
                'timestamp' => $log['timestamp'],
            );
            array_push($searchResults, $tmp);
        }
        return $searchResults;
    }
    function add(string $action, string $subject, $id = '', $note = '')
    {
        $sql = <<<EOD
            INSERT INTO log
            (
                action,
                subject,
                logID,
                note,
                userName,
                timestamp
            )
            VALUES
            (
                ?,?,?,?,?,?
            )
        EOD;

        $stm = $this->db->prepare($sql) or die('this');
        $stm->bindValue(1, $action, SQLITE3_TEXT);
        $stm->bindValue(2, $subject, SQLITE3_TEXT);
        $stm->bindValue(3, $id, SQLITE3_TEXT);
        $stm->bindValue(4, $note, SQLITE3_TEXT);
        $stm->bindValue(5, $_SESSION['userName'] ?? '', SQLITE3_TEXT);
        $stm->bindValue(6, time(), SQLITE3_INTEGER);
        $res = $stm->execute();
        if ($res) return true;
        else return false;
    }
}

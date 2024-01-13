<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Maintenance.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class Maintenance extends Database
{
    function getAll(string $search): array
    {
        if ($search == 'complete') {

            $sql =  <<<EOD
                SELECT id, problem, machine, reportedBy, timestamp, status
                FROM `t-manager`.maintenance
                WHERE status = 'complete'
                ORDER BY timestamp DESC
            EOD;
        }
        if ($search == 'pending') {
            $sql =  <<<EOD
                SELECT id, problem, machine, reportedBy, timestamp, status
                FROM `t-manager`.maintenance
                WHERE status = 'pending'
                ORDER BY timestamp DESC
            EOD;
        }

        $stm = $this->db->prepare($sql);
        $stm->execute();
        $result = $stm->get_result();

        $searchResults = [];
        while ($problem = $result->fetch_assoc()) {
            array_push($searchResults, array(
                'id' => $problem['id'],
                'problem' => $problem['problem'],
                'machine' => $problem['machine'],
                'reportedBy' => $problem['reportedBy'],
                'timestamp' => $problem['timestamp'],
                'status' => $problem['status'],
            ));
        }

        return $searchResults;
    }
    function add($problem, $machine, $reportedBy): bool
    {
        $Auth = new Auth();
        $Auth->isLoggedIn();

        $sql = <<< EOD
            INSERT INTO `t-manager`.maintenance (
                problem,
                machine,
                reportedBy,
                timestamp,
                status
            )
            VALUES (?,?,?,?,?)
        EOD;
        try {
            $stm = $this->db->prepare($sql);
            $timestamp = time();
            $status = 'pending';
            $stm->bind_param("sssis", $problem, $machine, $reportedBy, $timestamp, $status); // code-spell-checker:disable-line
            $res = $stm->execute();
            if ($res) return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'add()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            die();
        }
    }
    function get(int $id)
    {
        $sql =  <<<EOD
            SELECT id, problem, machine, reportedBy, timestamp, status
            FROM `t-manager`.maintenance
            WHERE id = ?
        EOD;

        $stm = $this->db->prepare($sql);
        $stm->bind_param("i", $id);
        $stm->execute();
        return $stm->get_result()->fetch_assoc();
    }
}

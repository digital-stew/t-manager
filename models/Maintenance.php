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
            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.maintenance LIMIT 1;")->fetch_column();
            $Log = new Log();
            $Log->add('ADD', 'maintenance', $machine, $lastID, $problem);
            if ($res) return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'add()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }
    function get(int $id): array
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

    function remove(int $id): bool
    {
        try {
            $stm = $this->db->prepare("DELETE FROM `t-manager`.maintenance WHERE id = ?");
            $stm->bind_param("i", $id);
            $stm->execute();
            $stm->close();
            $Log = new Log();
            $Log->add('REMOVE', 'maintenance', '', $id, '');
            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'remove()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function complete(int $id)
    {
        try {
            $sql = <<<EOD
                UPDATE `t-manager`.maintenance
                SET status = 'complete'
                WHERE id = ?
            EOD;
            $stm = $this->db->prepare($sql);
            $stm->bind_param("i", $id);
            $stm->execute();
            $stm->close();
            $Log = new Log();
            $Log->add('COMPLETE', 'maintenance', '', $id, '');
            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'complete()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }
}

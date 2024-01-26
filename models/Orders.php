<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

class Orders extends Database
{
    function search()
    {
    }

    function get()
    {
    }

    function new(
        $name,
        $printCheckbox,
        $embCheckbox,
        $transferCheckbox,
        $dtfCheckbox,
        $frontEmbellishment,
        $backEmbellishment,
        $lSleeveEmbellishment,
        $rSleeveEmbellishment,
        $neckEmbellishment,
        $otherEmbellishment,
        $otherEmbellishmentName,
        $packingSelect,
        $deliverySelect,
        $deliveryDate,
        $sampleRequiredCheckbox,
        $asPreviousCheckbox,
        $garment,
        // $color,
        // $sizes,
        $files,
        $fileDescription
    ) {
        $Auth = new Auth();
        $Auth->isLoggedIn();
        $sql = <<<EOD
            INSERT INTO `t-manager`.orders (
                name,
                printCheckbox,
                embCheckbox,
                transferCheckbox,
                dtfCheckbox,
                frontEmbellishment,
                backEmbellishment,
                lSleeveEmbellishment,
                rSleeveEmbellishment,
                neckEmbellishment,
                otherEmbellishment,
                otherEmbellishmentName,
                packingSelect,
                deliverySelect,
                deliveryDate,
                sampleRequiredCheckbox,
                asPreviousCheckbox
                )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            EOD;
        try {
            $stm = $this->db->prepare($sql);
            $timeStamp = time();
            $stm->bind_param(
                "ssssssssssssssiss", // code-spell-checker:disable-line
                $name,
                $printCheckbox,
                $embCheckbox,
                $transferCheckbox,
                $dtfCheckbox,
                $frontEmbellishment,
                $backEmbellishment,
                $lSleeveEmbellishment,
                $rSleeveEmbellishment,
                $neckEmbellishment,
                $otherEmbellishment,
                $otherEmbellishmentName,
                $packingSelect,
                $deliverySelect,
                $deliveryDate,
                $sampleRequiredCheckbox,
                $asPreviousCheckbox
            );
            $res = $stm->execute();

            (int)$lastID = $this->db->query("SELECT LAST_INSERT_ID() FROM `t-manager`.orders LIMIT 1;")->fetch_column();

            //handle the files
            if ($res) return true;

            //handle the garments
        } catch (\Throwable $e) {
            print_r($e->getMessage());
            $Log = new Log();
            $Log->add('ERROR', 'parseCode()', $e->getFile(), '', "{$e->getMessage()} - line: {$e->getLine()}");
            return false;
            die();
        }
    }

    function delete()
    {
    }

    function edit()
    {
    }
}

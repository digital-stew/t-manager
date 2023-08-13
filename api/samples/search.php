<?php
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
$sql = <<<EOD
        SELECT
            samples.rowid,
            samples.name,
            samples.number,
            samples.date,
            samples.otherref,
            (
                SELECT sample_images.webp_filename
                FROM sample_images
                WHERE sample_images.sample_id = samples.rowid
                LIMIT 1
            ) AS image
        FROM samples
        WHERE
            samples.name LIKE ? OR
            samples.otherref LIKE ? OR
            samples.number LIKE ?
        ORDER BY samples.date DESC;
    EOD;
$stm = $db->prepare($sql);
$stm->bindValue(1, '%' . $_GET["search"] . '%', SQLITE3_TEXT);
$stm->bindValue(2, '%' . $_GET["search"] . '%', SQLITE3_TEXT);
$stm->bindValue(3, '%' . $_GET["search"] . '%', SQLITE3_TEXT);
$res = $stm->execute();


//$db->close();
?>
<table id="show">
    <thead>
        <tr>
            <th>id</th>
            <th>name</th>
            <th>number</th>
            <th>date</th>
            <th>image</th>

        </tr>
    </thead>
    <tbody id="searchResults">
        <?php
        while ($row = $res->fetchArray()) {
            $link = '/assets/images/samples/webp/' . $row['image'];
            echo "
        <tr onclick='selectSample({$row['rowid']})'>
            <td>{$row['rowid']}</td>
            <td>{$row['name']}</td>
            <td>{$row['number']}</td>
            <td class='timestamp'>{$row['date']}</td>
            <td><img src='{$link}' alt='' style='width:100px;height:100px;'>
        </tr>";
        }
        ?>

    </tbody>
</table>
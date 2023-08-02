<?php

//TODO isset GET
//throw
 $db = new SQLite3('../../db.sqlite');
 $searchText = htmlspecialchars($_GET["search"]);

 $stm = $db->prepare("SELECT * FROM samples WHERE name LIKE ? ");
 $stm->bindValue(1, '%'.SQLite3::escapeString($searchText).'%', SQLITE3_TEXT);
 $res = $stm->execute();

 while ($row = $res->fetchArray()){ 
    echo "
        <tr onclick='selectSample({$row['rowid']})'>
            <td>{$row['rowid']}</td>
            <td>{$row['name']}</td>
            <td>{$row['number']}</td>
            <td>{$row['date']}</td>
        </tr>
    "; 
    }
$db->close(); 
 ?>
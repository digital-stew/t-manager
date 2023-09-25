<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';
$Stock = new Stock();
$searchResults = $Stock->search($_GET['color'], $_GET['size'], $_GET['type'], $_GET['location']);
//echo $searchResults;
?>

<?php foreach ($searchResults as $result) : ?>
    <tr>
        <td><?= $result['code'] ?></td>
        <td><?= $result['color'] ?></td>
        <td><?= $result['size'] ?></td>
        <td><?= $result['type'] ?></td>
        <td><?= $result['location'] ?></td>
        <td><?= $result['amount'] ?></td>
    </tr>
<?php endforeach; ?>
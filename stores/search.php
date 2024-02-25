<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Stock.php';

$Stock = new Stock();
$searchResults = $Stock->search($_GET['color'], $_GET['size'], $_GET['type'], $_GET['location']);
$totalAmount = 0;
?>

<?php foreach ($searchResults as $result) : ?>
    <tr>
        <td><?= implode(' ', str_split($result['code'], 4)) ?></td>
        <td><?= $result['color'] ?></td>
        <td><?= $result['size'] ?></td>
        <td><?= $result['type'] ?></td>
        <td><?= $result['location'] ?></td>
        <td><?= $result['amount'] ?></td>
    </tr>
    <?php $totalAmount += $result['amount'] ?>
<?php endforeach; ?>

<tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td>total:</td>
    <td><?= $totalAmount ?></td>
</tr>
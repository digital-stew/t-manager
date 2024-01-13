<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Maintenance.php';
$Maintenance = new Maintenance();


if (!isset($_GET['id'])) die('no id given');

$problem = $Maintenance->get((int)$_GET['id']);
//print_r($problem);

if (isset($_POST['delete']) && isset($_POST['id'])) {
}

?>
<section>
    <h4>problem details: <?= $problem['id'] ?> <br></h4>
    <h5><?= $problem['status'] ?> </h5>
    <span><?= $problem['reportedBy'] ?></span>
    <p>reported by</p>
    <p><?= $problem['problem'] ?></p>

</section>
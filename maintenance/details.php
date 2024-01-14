<?php
// ========================MODAL============================
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Maintenance.php';
if (!isset($_GET['id'])) die('no id given');
session_start();
$Maintenance = new Maintenance();
$problem = $Maintenance->get((int)$_GET['id']);
?>
<section>
    <form action="/maintenance/control.php" method="post">
        <h4>problem details: <?= $problem['id'] ?> <br></h4>
        <h5><?= $problem['status'] ?> </h5>
        <p>reported by:<?= $problem['reportedBy'] ?></p>
        <p>machine/equipment: <?= $problem['machine'] ?></p>
        <p><?= $problem['problem'] ?></p>
        <button type="button" style="width: 80%;" onclick="closeModal();">Close</button>
        <?php if (isset($_SESSION['userName'])) : ?>
            <button type="submit" name="completeTask" style="width: 80%;" onclick="return confirm('mark this task complete?')">Task complete</button>
            <button type="submit" name="deleteTask" style="width: 80%;" onclick="return confirm('This will permanently delete this task. Are you sure?')">Delete</button>
        <?php endif ?>
        <input type="hidden" name="id" value="<?= $problem['id'] ?>">
    </form>
</section>
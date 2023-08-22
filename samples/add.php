<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] .'/models/sample.php';

if (isset($_POST['add'])) {
    $Sample = new sample();
    $Sample->add( $_POST['name'],  $_POST['number'],  $_POST['otherref'],  $_POST['front'],  $_POST['back'],  $_POST['other'],  $_POST['notes'], $_SESSION['userName'], $_FILES['files']);
}
?>

<form id="show" enctype="multipart/form-data" action="/api/samples/add.php" method="POST" class="box sample__add__form">
    <div>
        <h3>Job Data</h3>
        <hr>
        <label for="name">Name</label>
        <input type="text" name="name" id="name" class="wide-center" required>

        <label for="number">Number</label>
        <input type="text" name="number" id="number" class="wide-center" required>

        <label for="other">Other reference</label>
        <input type="text" name="otherref" id="other" class="wide-center">
        <br><br>
        <input type="file" name="files[]" id="" multiple required>
    </div>
    <div>
        <h3>Print Data</h3>
        <hr>
        <label for="front">Front</label>
        <input type="text" name="front" id='front' class="wide-center">

        <label for="back">Back</label>
        <input type="text" name="back" id="back" class="wide-center">

        <label for="other">Other</label>
        <input type="text" name="other" id="other" class="wide-center">

        <label for="notes">Notes</label>
        <input type="text" name="notes" id="notes" class="wide-center"> <br />

    </div>
    <button type="submit" name="add">Save</button>

</form>
<?php
 $db = new SQLite3('../../db.sqlite');

if (isset($_POST["newUser"])){
    if ($_POST['password1'] == '' || $_POST['password2'] == '') die('passwords cant be blank');
    if ($_POST['password1'] !== $_POST['password2']) die('passwords must match');
    $stm = $db->prepare("INSERT INTO users (user,email,department,userlevel, password) VALUES (?,?,?,?,?)");
    $stm->bindValue(1,SQLite3::escapeString($_POST['userName']), SQLITE3_TEXT);
    $stm->bindValue(2,SQLite3::escapeString($_POST['email']), SQLITE3_TEXT);
    $stm->bindValue(3,SQLite3::escapeString($_POST['department']), SQLITE3_TEXT);
    $stm->bindValue(4,SQLite3::escapeString($_POST['userlevel']), SQLITE3_TEXT);
    $stm->bindValue(5,password_hash($_POST['password1'], PASSWORD_BCRYPT), SQLITE3_TEXT);
    $res = $stm->execute();
    $db->close(); 

    header('Location: /admin');
}
?>

<form action="/api/admin/newUser.php" method="post">
    <h3>add new user</h3>
    <input type="text" name="userName" id=""> <br>
    <input type="password" name="password1" id="">
    <input type="password" name="password2" id=""> <br>
    <button type="submit" name="newUser">add new user</button>
</form>
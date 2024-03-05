<?php
//todo add change user password or reset?
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
$Admin = new Admin();
$Auth = new Auth();

if (isset($_GET['getUser']) && isset($_GET['id'])) {
    //to do passwords
    session_start();
    $Auth->isAdmin();
    $user = $Admin->getUser($_GET['id']);
    $html = <<<EOD
    <form class='newBox' id="adminLeft" method="POST" action="/admin/users.php?id={$user['id']}" autocomplete="off">
        <h4>{$user['name']}</h4>
        <p>Id:  {$user['id']}</p>
        <p>Email: {$user['email']}</p>
        <p>Department: {$user['department']}</p>
        <p>User Level: {$user['userLevel']}</p>
        <!-- <button type="button" onClick="showModal('/admin/users.php?adminChangeUserPassword=true&id={$user['id']}')">Change password</button> -->
        <button id="editUser-button" type="button" onclick="showModal('/admin/users.php?editUser=true&id={$user['id']}')" >Edit</button>
        <button id="deleteUser-button" type='submit' name="deleteUser">Delete</button>
        <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}

if (isset($_POST['addUser'])) {
    session_start();
    $Auth->isAdmin();
    if (strlen($_POST['userName']) < 3) {
        header('Location: /admin?flashUser=Username too short. > 2');
        die();
    }
    if (strlen($_POST['userName']) > 10) {
        header('Location: /admin?flashUser=Username too long. < 10');
        die();
    }
    if ($_POST['password1'] !== $_POST['password2']) {
        header('Location: /admin?flashUser=Passwords don\'t match');
        die();
    }
    $res = $Admin->addUser($_POST['userName'], $_POST['email'], $_POST['password1'], $_POST['userLevel'], $_POST['department']);
    if ($res) header('Location: /admin?flashUser=user saved');
    else header('Location: /admin?flashUser=ERROR!! contact admin if problem persists');
    die();
}

if (isset($_POST['deleteUser']) && isset($_GET['id'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->deleteUser($_GET['id']);
    if ($res) header('Location: /admin?flashUser=user deleted');
    else header('Location: /admin?flashUser=ERROR!! contact admin if problem persists');
    die();
}

if (isset($_POST['editUser']) && isset($_POST['email']) && isset($_POST['department']) && isset($_POST['userLevel'])) {
    session_start();
    $Auth->isAdmin();
    $res = $Admin->editUser($_POST['editUser'], $_POST['email'], $_POST['department'], $_POST['userLevel']);
    if ($res) header('Location: /admin?flashUser=user saved');
    else header('Location: /admin?flashUser=ERROR!! contact admin if problem persists');
    die();
}

if (isset($_GET['adminChangeUserPassword']) && isset($_GET['id'])) {
    //to do check this
    $user = $Admin->getUser($_GET['id']);
    $html = <<<EOD
    <form action="/admin/users.php" method="post" class="newBox border">

            <h4>Change password for user</h4>
            <h4>{$user['name']}</h4>
            <label>
                New password
                <input type="password" name="password1" id="">
            </label>
            <br>
            <label>
                Again
                <input type="password" name="password2" id="">
            </label>
            <br>
            <input type="hidden" name="id" value="{$user['id']}">
            <input type="hidden" name="adminChangeUserPassword" value="true">
            <br>
            <button onClick="adminChangeUserPassword()">Change Password</button>
            <button type="button" onclick="closeModal();">cancel</button>

        <form>
    EOD;
    echo $html;
    die();
}

//============add user modal======================
if (isset($_GET['addUser'])) {
    session_start();
    $Auth->isAdmin();
    $html = <<<EOD
    <form action="/admin/users.php" method="post" class="newBox border" autocomplete="off">
        <h3>add new user</h3>
        <input type="text" name="userName" placeholder="Name" minlength="3" maxlength="10" required> <br style="margin-bottom: 1rem;">
        <input type="text" name="email" placeholder="Email"> <br style="margin-bottom: 1rem;">
        <h3>Password</h3>
        <input autoComplete="new-password"  type="password" name="password1" placeholder="Password" required> <br>
        <input autoComplete="new-password" type="password" name="password2" placeholder="again" required> <br>
        <h3>User Level</h3>
        <select name="userLevel">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
        <h3>Department</h3>
        <select name="department">
            <option value="print">Print</option>
            <option value="stores">Stores</option>
            <option value="office">Office</option>
        </select>
        <button id="addNewUser-submit" type="submit" name="addUser">add new user</button>
        <button type="button" onclick="closeModal();">cancel</button>
    </form>
    EOD;
    echo $html;
    die();
}

//============edit user modal======================
if (isset($_GET['editUser']) && isset($_GET['id'])) {
    session_start();
    $Auth->isAdmin();
    $user = $Admin->getUser($_GET['id']);
    $html = <<<EOD
    <form action="/admin/users.php" method="post" class="newBox border" autocomplete="off">
        <p>Id: {$user['id']}</p>
        <p>{$user['name']}</p>
        <label>
            E-mail
            <input type="email" name='email' placeholder='E-mail' value="{$user['email']}">
        </label>
        <br>
        <select name="department" style='margin-block:1rem;'>
            <option value="print">Print</option>
            <option value="office">Office</option>
            <option value="stores">Stores</option>
        </select>
        <br>
        <br>
        <select name="userLevel">
        <option value="{$user['userLevel']}">{$user['userLevel']}</option>
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
        <input type="hidden" name="editUser" value="{$user['id']}">
        <button id="editUser-submit" type='submit'>Save</button>
        <button type='button' onclick="closeModal();">Cancel</button>
        </form>
    EOD;
    echo $html;
    die();
}
?>

<section style="width: fit-content;" class="newBox border">
    <h2>Users</h2>
    <table id="usersTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>E-mail</th>
                <th>Department</th>
                <th>User Level</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($Admin->getAllUsers() as $user) : ?>
                <tr onclick="showModal('/admin/users.php?getUser=true&id=<?= $user['id'] ?>')">
                    <td><?= $user['name'] ?></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= $user['department'] ?></td>
                    <td><?= $user['userLevel'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button id="addNewUser-button" onclick="showModal('/admin/users.php?addUser=true')">Add new user</button>
</section>
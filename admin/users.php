<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';
$Admin = new Admin();
$Auth = new Auth();
$Auth->isAdmin();

//============modal======================
if (isset($_GET['getUser']) && isset($_GET['id'])) {
    $user = $Admin->getUser($_GET['id']);
    $html = <<<EOD
    <form class='newBox' id="adminLeft" method="POST" action="/admin/users.php?id={$user['id']}">
            <h4>{$user['name']}</h4>
            <p>Id:  {$user['id']}</p>
            <p>Email: {$user['email']}</p>
            <p>Department: {$user['department']}</p>
            <p>User Level: {$user['userLevel']}</p>
            <button type="button">Change password</button>
            <button type="button" onclick="showModal('/admin/users.php?editUser=true&id={$user['id']}')" >Edit</button>
            <button type='submit' name="deleteUser">Delete</button>
            <h4></h4>
            <button type='button' onclick="closeModal();">Back</button>
    </form>
    EOD;
    echo $html;
    die();
}

//============modal======================
if (isset($_GET['editUser']) && isset($_GET['id'])) {
    $user = $Admin->getUser($_GET['id']);
    $html = <<<EOD
    <section class='newBox' id="adminLeft">
            <h4><input type='text' name='name' placeholder='Name' value="{$user['name']}"></h4>
            <p>Id:  {$user['id']}</p>
            <p><input type="email" name='email' placeholder='E-mail' value="{$user['email']}"></p>
            <p><select name="department" id="">
                <option value="print">Print</option>
                <option value="office">Office</option>
                <option value="stores">Stores</option>
            </select></p>
            <p><select name="userLevel" id="">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select></p>
            <button>Save</button>
            <button type='button' onclick="closeModal();">Cancel</button>
    </section>
    EOD;
    echo $html;
    die();
}

//============modal======================
if (isset($_GET['addUser'])) {
    $html = <<<EOD
    <section id="adminLeft" class="show_sample_section">
        <form action="/admin/users.php" method="post" class="newBox border">
            <h3>add new user</h3>
            <input type="text" name="userName" placeholder="Name"> <br style="margin-bottom: 1rem;">
            <input type="text" name="email" placeholder="Email"> <br style="margin-bottom: 1rem;">
            <h3>Password</h3>
            <input autoComplete="new-password"  type="password" name="password1" placeholder="Password"> <br>
            <input autoComplete="new-password" type="password" name="password2" placeholder="again"> <br>
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
            <button type="submit" name="addUser">add new user</button>
            <button type="button" onclick="closeModal();">cancel</button>
        </form>
    </section>
    EOD;
    echo $html;
    die();
}

if (isset($_POST['addUser'])) {
    if (strlen($_POST['userName']) < 4) {
        header('Location: /admin?flashUser=username too short. > 4');
        die();
    }
    if (strlen($_POST['userName']) > 10) {
        header('Location: /admin?flashUser=username too long. < 10');
        die();
    }
    if ($_POST['password1'] !== $_POST['password2']) {
        header('Location: /admin?flashUser=passwords don\'t match');
        die();
    }
    $res = $Admin->addUser($_POST['userName'], $_POST['email'], $_POST['password1'], $_POST['userLevel'], $_POST['department']);
    if ($res) header('Location: /admin?flashUser=user saved');
    die();
}

if (isset($_POST['deleteUser']) && isset($_GET['id'])) {
    $res = $Admin->deleteUser($_GET['id']);
    if ($res) header('Location: /admin?flashUser=user deleted');
    die();
}

$allUsers = $Admin->getAllUsers();
?>
<section id="adminView" style="width: fit-content;">

    <div id="show" class="newBox border">
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>E-mail</th>
                    <th>Department</th>
                    <th>User Level</th>
                </tr>
            </thead>
            <tbody id="searchResults">
                <?php foreach ($allUsers as $user) : ?>
                    <tr onclick="showModal('/admin/users.php?getUser=true&id=<?= $user['id'] ?>')">
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td><?= $user['department'] ?></td>
                        <td><?= $user['userLevel'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button onclick="showModal('/admin/users.php?addUser=true')">Add new user</button>
    </div>
</section>
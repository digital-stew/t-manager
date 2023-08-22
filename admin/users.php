<?php
require_once $_SERVER['DOCUMENT_ROOT'] .'/models/Admin.php';
$Admin = new Admin();


if(isset($_GET['getUser']) && isset($_GET['id'])){
    $user = $Admin->getUser($_GET['id']);
    $html = <<<EOD
    <section class='newBox' id="adminLeft">
            <h4>{$user['name']}</h4>
            <p>Id:  {$user['id']}</p>
            <p>Email: {$user['email']}</p>
            <p>Department: {$user['department']}</p>
            <p>User Level: {$user['userLevel']}</p>
            <button>Change password</button>
            <button onclick="replaceElement('adminLeft', '/admin/users.php?editUser=true&id={$user['id']}')" >Edit</button>
            <button>Delete</button>
    </section>
    EOD;
    echo $html;
    die();
}

if(isset($_GET['editUser']) && isset($_GET['id'])){
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
            <button>Delete</button>
    </section>
    EOD;
    echo $html;
    die();
}

if(isset($_GET['newUser'])) {
    // TODO
    echo 'new user';
}
if(isset($_GET['deleteUser'])) {
    // TODO
    echo 'new user';
}
$allUsers = $Admin->getAllUsers();
?>
<table id="show" class="border">
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
        <button>test</button>
        <?php foreach ($allUsers as $user): ?>
            <tr onclick="replaceElement('adminLeft', '/admin/users.php?getUser=true&id=<?=$user['id']?>')">
                <td><?=$user['id']?></td>
                <td><?=$user['name']?></td>
                <td><?=$user['email']?></td>
                <td><?=$user['department']?></td>
                <td><?=$user['userLevel']?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

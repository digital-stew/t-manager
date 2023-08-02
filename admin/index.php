<?php
require $_SERVER['DOCUMENT_ROOT'].'/header.php';
$res = $db->query('SELECT * FROM users');
?>

<div class="box">
    <div id="addNewUser">
        <table>
            <thead>
                <tr>
                    <th>id</th>
                    <th>name</th>
                    <th>e-mail</th>
                    <th>department</th>
                    <th>level</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while ($row = $res->fetchArray()){ 
                    echo "
                    <tr>
                    <td>{$row['id']}</td>
                    <td>{$row['user']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['department']}</td>
                    <td>{$row['userlevel']}</td>
                    <td>{$row['password']}</td>
                    </tr>    
                    "; 
                } 
                ?>
            </tbody>
        </table>
        <button onclick="replaceElement('addNewUser','/api/admin/newUser.php')">add new user</button>
    </div>
</div>

<script>

</script>
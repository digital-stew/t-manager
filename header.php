<?php

session_start();
//session_destroy();
//print_r($_POST);
 $db = new SQLite3($_SERVER['DOCUMENT_ROOT'].'/db.sqlite');
//$password = '123456';
//$hash = password_hash($password, PASSWORD_BCRYPT);
//echo print_r($hash);
//echo print_r(password_verify($password, $hash));

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<style>

</style>

<nav>
    <div class="imageWrapper">
        <img src="/assets/images/logo-light.png" alt="company logo">
    </div>
    <ul class="linkList">
        <li><a href="/samples">Samples</a></li>
        <li><a href="/">Ink</a></li>
        <li><a href="/">Maintenance log</a></li>
        <li><a href="/admin">admin</a></li>
    </ul>
    <?php if (isset($_SESSION['userName'])): ?>
    <div>
        <h4>welcome <?=$_SESSION['userName'] ?></h4>
        <button onclick="logout()">logout</button>
    </div>

    <?php else: ?>
    <form id="loginForm" class="loginForm" method="post">
        <input type="text" name="username" id="" placeholder="Username"> <br />
        <input type="password" name="password" placeholder="Password"> <br />
        <button id="loginButton" type="submit" name="login" value="login">Login</button>
    </form>
    <?php endif ?>


</nav>
<script>
const loginForm = document.getElementById('loginForm');
loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    console.log('login');
    const formData = new FormData(loginForm)
    const res = await fetch('/api/login.php', {
        method: 'POST',
        body: formData
    })
    //if (res.ok) window.location.reload()

    if (await res.text() === "login=ok") {
        window.location.reload();
    } else {
        document.getElementById('loginButton').innerText = 'incorrect';
        setTimeout(() => {
            document.getElementById('loginButton').innerText = 'Login';
        }, 2000);
    }


})
async function logout() {
    const res = await fetch('/api/logout.php');
    if (res.ok) window.location.reload();
}

function login(e) {}
async function replaceElement(element, link) {
    const res = await fetch(link)
    if (res.ok) {
        document.getElementById(element).innerHTML = await res.text()
    } else {
        document.getElementById('error').innerText = 'error'
    }
}
</script>
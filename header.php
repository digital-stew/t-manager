<?php

session_start();
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

// Set the content type header for images
//header('Content-Type: image/webp'); // Adjust the content type according to your image type

// Set cache headers to cache the image for a specific period (e.g., 1 week)
$expires = 604800; // 1 week in seconds
//header('Cache-Control: public, max-age=' . $expires);
//header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

// Output the image content
//readfile('path_to_your_image.jpg'); // Replace 'path_to_your_image.jpg' with the actual path to your image

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <meta http-equiv="Cache-control" content="private">
</head>
<style>

</style>

<body>
    <nav class="navbar">
        <div class="imageWrapper">
            <img src="/assets/images/logo-light.png" alt="company logo">
        </div>
        <ul class="linkList">
            <li><a href="/samples">Samples</a></li>
            <li><a href="/">Ink</a></li>
            <li><a href="/">Maintenance log</a></li>
            <li><a href="/admin">admin</a></li>
        </ul>
        <?php if (isset($_SESSION['userName'])) : ?>
        <div>
            <h4>welcome <?= $_SESSION['userName'] ?></h4>
            <button id="logoutButton" onclick="logout()">logout</button>
        </div>

        <?php else : ?>
        <form id="loginForm" class="loginForm" method="post">
            <input type="text" name="username" id="" placeholder="Username"> <br />
            <input type="password" name="password" placeholder="Password"> <br />
            <button id="loginButton" type="submit" name="login" value="login">Login</button>
        </form>
        <?php endif ?>


    </nav>
    <script>
    <?php if (!isset($_SESSION['userName'])) : ?>

    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm)
        const res = await fetch('/api/login.php', {
            method: 'POST',
            body: formData
        })

        if (await res.text() === "login=ok") {
            window.location.reload();
        } else {
            document.getElementById('loginButton').innerText = 'incorrect';
            setTimeout(() => {
                document.getElementById('loginButton').innerText = 'Login';
            }, 2000);
        }
    })
    <?php endif ?>

    async function logout() {
        const res = await fetch('/api/logout.php');
        if (res.ok) window.location.reload();
    }

    async function replaceElement(element, link) {
        const res = await fetch(link)
        if (res.ok) {
            document.getElementById(element).innerHTML = await res.text()
        } else {
            document.getElementById('error').innerText = 'error'
        }
    }

    function HRtimestamp() {
        let timestamp = document.querySelectorAll(".timestamp");
        timestamp.forEach(element => {
            element.innerText = new Date(element.innerText * 1000).toLocaleDateString()
        });
    }
    </script>
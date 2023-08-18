<?php

session_start();
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/db.sqlite');
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

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
    <nav class="navbar-top">

        <?php if (isset($_SESSION['userName'])) : ?>
            <div>
                <h4>welcome <?= $_SESSION['userName'] ?></h4>
                <button id="logoutButton" onclick="logout()">logout</button>
            </div>

        <?php else : ?>
            <form id="loginForm" method="post" style="display: flex;flex-direction: column;">

                <input type="text" name="username" id="" placeholder="Username" style="margin-block: 0.5rem;">
                <input type="password" name="password" placeholder="Password">

                <button id="loginButton" type="submit" name="login" value="login">Login</button>
            </form>
        <?php endif ?>

        <ul class="linkList">
            <li><a href="/samples">Samples</a></li>
            <li><a href="/">Ink</a></li>
            <li><a href="/">Maintenance log</a></li>
            <li><a href="/admin">admin</a></li>
        </ul>
        <div id="error" class="error"></div>
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
            const res = await fetch(link);
            const reply = await res.text();
            try {
                const json = JSON.parse(reply)
                if (json) return setError(json.error)

            } catch (error) {
                document.getElementById(element).outerHTML = reply
                HRtimestamp()
            }
        }

        function HRtimestamp() {
            let timestamp = document.querySelectorAll(".timestamp");
            timestamp.forEach(element => {
                let formattedDate = new Date(element.innerText * 1000).toLocaleDateString();
                if (formattedDate === 'Invalid Date') return;
                element.innerText = formattedDate;
            });

        }

        function setError(string) {
            document.getElementById('error').innerText = string
            setTimeout(() => {
                document.getElementById('error').innerText = ''
            }, 2000)
        }
    </script>
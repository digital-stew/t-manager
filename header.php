<?php
session_start();
//define("ERROR_MSG", 'ERROR!! contact admin if problem persists');
//const ERROR_MSG = 'ERROR!! contact admin if problem persists';
?>
<div id="burgerMenu" class="burgerMenu" onclick="toggleNavbar();">
    <span></span>
    <span></span>
    <span></span>
</div>

<nav class="navbar" id="navbar">
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
        <li><a href="/">Home</a></li>
        <li><a href="/samples">Samples</a></li>
        <li><a href="/stores">Stores</a></li>
        <li><a href="/fanaticOrders">Fanatic orders</a></li>
        <?php if (isset($_SESSION['userName']) && $_SESSION['userLevel'] == 'admin') : ?>
            <li><a href="/admin">admin</a></li>
        <?php endif ?>
    </ul>
</nav>

<dialog id="modal" style="text-align: center;"></dialog>

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
</script>
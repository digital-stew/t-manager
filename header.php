<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';

session_start();
$Admin = new Admin();
$locations = $Admin->getLocations();
// print_r($_SERVER);
// die();
//set user location
if (isset($_POST['newLocation'])) {
    $Admin->setLocation($_POST['newLocation']);
    header("Refresh:0; url=" . $_SERVER['HTTP_REFERER']);
    die();
}

?>
<div id="burgerMenu" class="burgerMenu" onclick="toggleNavbar();">
    <span></span> <!-- burger menu horizontal line -->
    <span></span> <!-- burger menu horizontal line -->
    <span></span> <!-- burger menu horizontal line -->
</div>

<nav class="navbar" id="navbar">
    <?php if (isset($_SESSION['userName'])) : ?>
        <div style="display: flex;flex-direction: column;width: 100%;text-align: center;">
            <a href="/user/index.php">
                <h4 id="user-welcome">welcome <?= $_SESSION['userName'] ?></h4>
            </a>
            <button id="logoutButton" onclick="logout()">logout</button>

            <form action="/header.php" method="post">
                <label for="currentLocationSelect">set location</label>
                <select onchange="this.form.submit()" name="newLocation" id="currentLocationSelect" style="width: 100%;text-align: center;">
                    <!-- <option value="<?= $_SESSION['location'] ?>"><?= $_SESSION['location'] ?></option> -->
                    <?php foreach ($locations as $location) : ?>
                        <option <?= $_SESSION['location'] == $location ? 'selected' : '' ?> value="<?= $location ?>"><?= $location ?></option>
                    <?php endforeach ?>
                </select>
            </form>

        </div>

    <?php else : ?>
        <form id="loginForm" method="post" style="display: flex;flex-direction: column;">
            <input type="text" name="username" id="login_input" placeholder="Username" style="margin-block: 0.5rem;">
            <input type="password" name="password" id="password_input" placeholder="Password">
            <button id="loginButton" type="submit" name="login" value="login">Login</button>
        </form>
    <?php endif ?>

    <ul class="linkList">
        <!-- <li><a href="/">Home</a></li> -->
        <li><a href="/samples">Samples</a></li>
        <li><a href="/stores">Stores</a></li>
        <li><a href="/fanaticOrders">Fanatic orders</a></li>
        <li><a href="/maintenance">Maintenance</a></li>
        <?php if (isset($_SESSION['userName']) && $_SESSION['userLevel'] == 'admin') : ?>
            <li><a href="/admin">admin</a></li>
            <li><a href="/admin/log.php">log</a></li>
        <?php endif ?>
        <?php if (isset($_SESSION['userName'])) : ?>
            <li><a href="/user">options</a></li>
        <?php endif ?>
    </ul>

    <div style="margin-top: auto;">
        t-manager V1.0 rc1
    </div>
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
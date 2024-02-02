<?php
include $_SERVER['DOCUMENT_ROOT'] . '/models/Auth.php';
include $_SERVER['DOCUMENT_ROOT'] . '/models/Admin.php';

session_start();
if (isset($_POST['username']) && isset($_POST['password'])) {
    $Auth = new Auth();
    $res = $Auth->login(strtolower($_POST['username']), $_POST['password']);

    //set location
    $Admin = new Admin();
    $autoLocationSet = false;
    foreach ($Admin->getAutoLocations() as $auto) {
        if ($_SERVER['REMOTE_ADDR'] == $auto['ip']) {
            $Admin->setLocation($auto['location']);
            $autoLocationSet = true;
            break;
        }
    }
    if (!$autoLocationSet) $Admin->setLocation('hawkins');
    echo $res;
}

if (isset($_POST['oldPassword']) && isset($_POST['password1']) && isset($_POST['password2'])) {
    $Auth = new Auth();
    $Auth->isLoggedIn();
    if ($_POST['password1'] != $_POST['password2']) header('Location: /user?flashUser=Passwords don\'t match');
    if (empty($_POST['password1'])) header('Location: /user?flashUser=empty password') and die();
    if (empty($_POST['oldPassword'])) header('Location: /user?flashUser=empty password');

    $res = $Auth->changePassword($_POST['oldPassword'], $_POST['password1'], $_SESSION['userName']);
    if ($res) header('Location: /user?flashUser=password changed');
}

<?php
function auth($level)
{
    switch ($level) {
        case 'user':
            if (isset($_SESSION['userName'])) return true;
            break;
    }
    die('not auth');
    return false;
}

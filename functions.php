<?php
function auth($level)
{
    switch ($level) {
        case 'user':
            if (isset($_SESSION['userName'])) return true;
            break;
    }
    die('{"error":"not logged in"}');
    return false;
}

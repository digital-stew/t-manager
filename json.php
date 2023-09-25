<?php

$code1 = '1108W-DGN-STU-H2S';
$code2 = '1108W-GRY-NHL-MU3';


$splitCode = explode('-', $code1);

$type = '';
$color = '';

switch ($splitCode[0]) {  // type
    case '0302M':
        $type = '202M';
        break;
    case '0302W':
        $type = '202W';
        break;
    case '1108M':
        $type = '208M';
        break;
    case '1108W':
        $type = '208W';
        break;
    case '1110M':
        $type = '210M';
        break;
    case '1311M':
        $type = '211M';
        break;
    case '1311W':
        $type = '211W';
        break;
    case '2112M':
        $type = '212M';
        break;
    case '3111M':
        $type = '221M';
        break;
}

switch ($splitCode[1]) {  // color
    case 'AQU':
        $color = '590F';
        break;
    case 'BLK':
        $color = '127A';
        break;
    case 'BRW':
        $color = '4034';
        break;
    case 'CLT':
        $color = '2854';
        break;
    case 'DGN':
        $color = '0565';
        break;
    case 'GRD':
        $color = '008N';
        break;
    case 'GLD':
        $color = '0599';
        break;
    case 'KGN':
        $color = '008O';
        break;
    case 'KHA':
        $color = '00R9';
        break;
    case 'NVY':
        $color = 'EX53';
        break;
    case 'ORG':
        $color = '008R';
        break;
    case 'PIN':
        $color = '9117';
        break;
    case 'PPL':
        $color = '0501';
        break;
    case 'PPR':
        $color = '00E6';
        break;
    case 'URD':
        $color = '0484';
        break;
    case 'RYL':
        $color = '861G';
        break;
    case 'SKY':
        $color = '008S';
        break;
    case 'GRY':
        $color = '00U2';
        break;
    case 'TEA':
        $color = '1811';
        break;
    case 'WHT':
        $color = '0042';
        break;
}

print_r("$type$color");

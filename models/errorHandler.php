<?php
function customError($errno, $errstr, $file, $line) {
  echo "<div class='error'>
    Error: $errno <br/>
    $errstr <br/>
  <!--  $file <br/> -->
  <!-- $line <br/> -->
</div>
    ";
  die();
}

//set error handler
// trigger_error('error text', E_USER_ERROR);
set_error_handler("customError", E_ALL);
//echo($test);

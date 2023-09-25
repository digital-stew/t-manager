<?php
function customError($errno, $errstr, $file, $line)
{
  //header('Location: /?flashUser=stock added');
  //print_r($_SERVER);
  echo "<div class='error'>
   Error: $errno <br/>
   $errstr <br/>
   $file <br/>
   $line <br/>

  </div>
     ";
  die('ERROR');
}

//set error handler
//trigger_error('error text', E_USER_ERROR);
set_error_handler("customError", E_ALL);

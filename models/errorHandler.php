<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Log.php';

function customError($errno, $errstr, $file, $line)
{
  $Log = new Log();
  $Log->add("ERROR", '', '', '', "err number: {$errno} - {$errstr} - {$file} - line: {$line}");
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

function our_global_exception_handler(Throwable $exception)
{
  //this code should log the exception to disk and an error tracking system
  //  $Log = new Log();
  //$Log->add("ERROR", '', '', '', "$exception->getMessage()");
  die($exception->getMessage());
}

//set_exception_handler('our_global_exception_handler',ALL);

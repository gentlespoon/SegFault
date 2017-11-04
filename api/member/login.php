<?php
  if (
    array_key_exists("username", $_GET) && $_GET['username']!="" &&
    array_key_exists("password", $_GET) && $_GET['password']!=""
  ) {
    $result = member::login($_GET['username'], $_GET['password']);
    api_write($result);
  } else {
    api_write( ["success"=>0, "msg"=>"Insufficient arguments"] );
  }

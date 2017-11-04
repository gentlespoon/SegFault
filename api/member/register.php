<?php
  if (
    array_key_exists("username", $_GET) && $_GET['username']!="" &&
    array_key_exists("password", $_GET) && $_GET['password']!="" &&
    array_key_exists("email", $_GET) && $_GET['email']!=""
  ) {
    $result = member::register($_GET['username'], $_GET['password'], $_GET['email']);
    api_write($result);
  } else {
    api_write( ["success"=>0, "msg"=>"Insufficient arguments"] );
  }

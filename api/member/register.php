<?php
  if (
    array_key_exists("username", $_GET) &&
    array_key_exists("password", $_GET) &&
    array_key_exists("email", $_GET)
  ) {
    $result = member::register($_GET['username'], $_GET['password'], $_GET['email']);
    api_write($result);
  } else {
    api_write( ["success"=>0, "msg"=>"Insufficient arguments"] );
  }

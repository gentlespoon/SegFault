<?php
  if (
    array_key_exists("username", $_GET) &&
    array_key_exists("password", $_GET)
  ) {
    $result = member::login($_GET['username'], $_GET['password']);
    api_write($result);
  } else {
    api_write( ["success"=>0, "msg"=>"Insufficient arguments"] );
  }

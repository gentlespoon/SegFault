<?php
  if ( array_key_exists("email", $_GET) ) {
    $result = member::validEmail($_GET['email']);
    api_write($result);
  } elseif ( array_key_exists("username", $_GET) ) {
    $result = member::validUsername($_GET['username']);
    api_write($result);
  } else {
    api_write(["success"=>0, "msg"=>"Insufficient arguments"]);
  }

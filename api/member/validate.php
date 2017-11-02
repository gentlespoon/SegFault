<?php
  if ( array_key_exists("email", $_GET) ) {
    echo json_encode( member::validEmail($_GET['email']) );
  } elseif ( array_key_exists("username", $_GET) ) {
    echo json_encode( member::validUsername($_GET['username']) );
  } else {
    echo json_encode( ["success"=>0, "msg"=>"Insufficient arguments"] );
  }

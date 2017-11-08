<?php
  if ( array_key_exists("email", $_GET) ) {
    api_write( member::validEmail($_GET['email']) );
  } elseif ( array_key_exists("username", $_GET) ) {
    api_write( member::validUsername($_GET['username']) );
  } else {
    api_write( 0, "Insufficient arguments");
  }

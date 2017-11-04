<?php
  member::logout();
  api_write( ["success"=>1, "msg"=>"Logged out"] );

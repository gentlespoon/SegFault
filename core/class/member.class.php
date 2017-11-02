<?php

class member {

  public static function register($username, $password, $email) {
    // validate username;
    if ($username=="") {
      return ["success" => 0, "message" => "Empty username"];
    } else {
      $validUsername = member::validUsername($username);
      if (!$validUsername['success']) {
        return $validUsername;
      }
    }
    // validate password
    if ($password=="") {
      return ["success" => 0, "message" => "Empty password"];
    }
    // validate email
    if ($email=="") {
      return ["success" => 0, "message" => "Empty email"];
    } else {
      $validEmail = member::validEmail($email);
      if (!$validEmail['success']) {
        return $validEmail;
      }
    }
    // generate password salt and encrypt password
    $salt = randomStr(6);
    $encryptedPassword = md5($password.$salt);

    DB::query("INSERT INTO member (username, email, password, salt, regdate) VALUES ( %s , %s , %s, %s, %s)", $username, $email, $encryptedPassword, $salt, $GLOBALS['now']);

    return ["success" => 1, "message" => "User registered"];
  }



  public static function usernameCensor($string) {
    $list = str_split("!@#$%^&*{[(<>)]};'\" `~?/\\|=+");
    $restrictedNames = DB::query("SELECT word FROM member_restrictname");
    foreach ($restrictedNames as $k => $v) { array_push($list, $v['word']); }
    foreach ($list as $k => $v) {
      if ( strpos($string, $v) !== false ) { return $v; }
    }
    return false;
  }



  public static function validUsername($username) {
    // check if username contains illegal characters
    $illegalChar = member::usernameCensor($username);
    if ($illegalChar) {
      return ["success" => 0, "message" => "Illegal username containing ".$illegalChar];
    }
    // check for duplicate username
    $existedUser = DB::query("SELECT uid FROM member WHERE username=%s", $username);
    if (!empty($existedUser)) {
      return ["success" => 0, "message" => "Username registered"];
    }
    return ["success" => 1, "message" => "Username OK"];
  }



  public static function validEmail($email) {
    // check to see if this is an email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ["success" => 0, "message" => "Illegal email address"];
    }
    // check for duplicate email
    $existedUser = DB::query("SELECT uid FROM member WHERE email=%s", $email);
    if (!empty($existedUser)) {
      return ["success" => 0, "message" => "Email registered"];
    }
    return ["success" => 1, "message" => "Email OK"];
  }





























}

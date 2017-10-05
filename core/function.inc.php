<?php


// print_r wrapper
function printv($arr, $ret=false) {
  $buf = "";
  if (is_array($arr)) {
    $buf .= "<pre>";
    ob_start();
    print_r($arr);
    // var_export($arr);
    $buf .= ob_get_clean();
    $buf .= "</pre>";
    if ($ret) {
      return $buf;
    } else {
      echo $buf;
    }
  } else {
    echo $arr;
  }
}



// render the HTML template
// **** this function WILL TERMINATE the PHP EXECUTION ****
function template($file, ...$extrafiles) {
  global $_SESSION;
  global $querycount;
  global $_GET;
  global $_starttime;
  global $output;
  global $lang;
  global $settings;
  global $member;
  global $redirect;

  $_endtime = microtime(true);
  $_runtime = $_endtime - $_starttime;
  $_runtime = sprintf('%0.5f', $_runtime);

  include_once(ROOT."templates/".$settings['template']."/header_html.htm");
  include_once(ROOT."templates/".$settings['template']."/header_visual.htm");
  include_once(ROOT."templates/".$settings['template']."/".$file.".htm");
  foreach ($extrafiles as $k => $v) {
    include_once(ROOT."templates/".$settings['template']."/".$v.".htm");
  }
  include_once(ROOT."templates/".$settings['template']."/footer_visual.htm");
  include_once(ROOT."templates/".$settings['template']."/footer_html.htm");
  exit();
}


// redirect the user to another URL
// **** this function WILL TERMINATE the PHP EXECUTION ****
function redirect($url) {
  global $redirect;
  $redirect = "<meta http-equiv='refresh' content='10; URL=".$url."'>";
  template("alert");
}



// Generate a random string
// used for password salt
function randomStr($length) {
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz';
    $str = '';
    $max = strlen($keyspace) - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}



// check to see if username contains any restricted words
// returns the restricted word if the username contains any
// returns false if the username is valid
function usernameCensor($string, $sensorlist) {
  foreach ($sensorlist as $k => $v) {
    if (strpos($string, $v) !== false) {
      return $v;
    }
  }
  return false;
}




// fetch userinfo
function getUserInfo() {
  global $lang;
  if ($_SESSION['uid'] > 0) {
    // printv(DB::query("SELECT * FROM member WHERE uid=%i", $_SESSION['uid']));
    $member = DB::query("SELECT * FROM member WHERE uid=%i", $_SESSION['uid'])[0];
    return $member;
  } else {
    return ["username" => $lang['not-logged-in']];
  }
}

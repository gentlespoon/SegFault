<?php

$GLOBALS['output']['title'] = "Member";

if (!array_key_exists("redirect", $_GET)) {
  $_GET['redirect'] = "/";
}

switch ($action) {
  case "register":


    if ($_SESSION['uid'] != 0) {
      redirect(0, "/member/profile");
    }
    $GLOBALS['output']['title'] = $GLOBALS['lang']['register'];
    if (array_key_exists("username", $_POST)) {
      // when user submits a reg, username will not be empty, so we know it is submitting, instead of requesting for a reg form
      if (array_key_exists("password", $_POST)) {

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array('secret' => '6LfcUTcUAAAAAPNcLotrUqCgm2G0nqen1hNS3ACt', 'response' => $_POST['g-recaptcha-response']);
        $options = array(
          'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
          )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result = json_decode($result);
        $result = (array)$result;
        if (!$result['success']) {
          alert("Failed to verify reCAPTCHA", "alert-danger");
          redirect(60, "/member/register");
        }

        // if both field exists
        $result = member::register($_POST['username'], $_POST['password'], $_POST['email']);
        if (!$result['success']) {
          alert($result['message'], "alert-danger");
          break;
        }
        member::login($_POST['username'], $_POST['password']);
        $GLOBALS['lang']['registered-welcome'] = str_replace("[USERNAME]", $GLOBALS['curUser']['username'], $GLOBALS['lang']['registered-welcome']);
        alert($GLOBALS['lang']['registered-welcome'], "alert-success");
        // redirect user to previous page
        redirect(5, $_GET['redirect']);
      }
    }
    break;



  case "login":
    if ($_SESSION['uid'] != 0) {
      redirect(0, "/member/profile");
    }
    $title = $GLOBALS['lang']['login'];
    if (array_key_exists("username", $_POST)) {
      // when user submits a login, username will not be empty, so we know it is submitting, instead of requesting for a login form
      if (array_key_exists("password", $_POST)) {
        $result = member::login($_POST['username'], $_POST['password']);
        if ($result['success']) {
          alert($GLOBALS['lang']['logged-in'], "alert-success");
          redirect(3, $_GET['redirect']);
        } else {
          alert($result['message'], "alert-danger");
          break;
        }
      }
    }
    break;



    case "profile":
      $title = $GLOBALS['lang']['modprofile'];
      if (!array_key_exists("uid", $_GET)) {
        $_GET['uid'] = NULL;
      } else {
        if (!is_numeric($_GET['uid']) || $_GET['uid']<1) {
          $_GET['uid'] = NULL;
        }
      }
      $GLOBALS['output']['fields'] = [];
      // if submit profile change
      if (array_key_exists("submitmod", $_POST)) {
        unset($_POST['submitmod']);
        $result = member::modProfile($_POST);
        if ($result['success']) {
          alert($result['message'], "alert-success");
        } else {
          alert($result['message'], "alert-danger");
        }
      }
      // select columns that are allowed to modify
      $result = member::getFields($_GET['uid']);
      if ($result['success']) {
        $GLOBALS['output']['fields'] = $result['message'];
      } else {
        alert($result['message'], "alert-danger");
      }
      if (empty($GLOBALS['output']['fields'])) {
        alert("You do not have the permission to view other member's profile.", "alert-danger");
      }
      break;








  case "logout":
    member::logout();
    alert($GLOBALS['lang']['logged-out'], "alert-success");
    redirect(5, $_GET['redirect']);
    break;







  default:
    // display member list

    // fetch usergroup information
    $uGroupInfo = [];
    $uGroupInfoTmp = DB::query("SELECT * FROM member_groups");
    foreach ($uGroupInfoTmp as $k => $v) {
      $uGroupInfo[$v['gid']] = $v['gname'];
    }

    // fetch member list
    $GLOBALS['output']['memberlist'] = DB::query("SELECT * FROM member");
    foreach ($GLOBALS['output']['memberlist'] as $k => $v) {
      unset($GLOBALS['output']['memberlist'][$k]['password']);
      unset($GLOBALS['output']['memberlist'][$k]['salt']);
      unset($GLOBALS['output']['memberlist'][$k]['salt']);
      $GLOBALS['output']['memberlist'][$k]['regdate'] = toUserTime($GLOBALS['output']['memberlist'][$k]['regdate']);
      $GLOBALS['output']['memberlist'][$k]['lastlogin'] = toUserTime($GLOBALS['output']['memberlist'][$k]['lastlogin']);
      $GLOBALS['output']['memberlist'][$k]['usergroup'] = $uGroupInfo[$GLOBALS['output']['memberlist'][$k]['gid']];
      unset($GLOBALS['output']['memberlist'][$k]['gid']);
      unset($GLOBALS['output']['memberlist'][$k]['failcount']);
      unset($GLOBALS['output']['memberlist'][$k]['lastattempt']);
      unset($GLOBALS['output']['memberlist'][$k]['regip']);
    }
    unset($GLOBALS['output']['memberlist'][0]);
}

template("member");

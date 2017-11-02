<?php

$output['title'] = "Member";

if (!array_key_exists("redirect", $_GET)) {
  $_GET['redirect'] = "/";
}

switch ($action) {


  case "register":
    if ($_SESSION['uid'] != 0) {
      redirect(0, "/member/profile");
    }
    $output['title'] = $lang['register'];
    if (array_key_exists("username", $_POST)) {
      // when user submits a reg, username will not be empty, so we know it is submitting, instead of requesting for a reg form
      if (array_key_exists("password", $_POST)) {
        // if both field exists
        $result = member::register($_POST['username'], $_POST['password'], $_POST['email']);
        if (!$result['success']) {
          alert($result['message'], "alert-danger");
          break;
        }
        member::login($_POST['username'], $_POST['password']);
        $member = member::getUserInfo();
        alert($lang['registered-welcome'], "alert-success");
        // redirect user to previous page
        redirect(5, $_GET['redirect']);
      }
    }
    break;



  case "login":
    if ($_SESSION['uid'] != 0) {
      redirect(0, "/member/profile");
    }
    $title = $lang['login'];
    if (array_key_exists("username", $_POST)) {
      // when user submits a login, username will not be empty, so we know it is submitting, instead of requesting for a login form
      if (array_key_exists("password", $_POST)) {
        $result = member::login($_POST['username'], $_POST['password']);
        if ($result['success']) {
          $member = member::getUserInfo();
          alert($lang['logged-in'], "alert-success");
          redirect(3, $_GET['redirect']);
        } else {
          alert($result['message'], "alert-danger");
          break;
        }
      }
    }
    break;



    case "modprofile":
      $title = $lang['modprofile'];
      if (array_key_exists("username", $_POST)) {
        // fetch table keys
        $tablekeys = DB::query("SELECT * FROM member WHERE uid=%i", $_SESSION['uid']);
        foreach ($_POST as $k => $v) {
          // verify if key exists
          if(array_key_exists($k, $tablekeys[0])) {
            // if the key exists, then it is safe to update
            DB::query("UPDATE member SET ".$k."=%s WHERE uid=%i", $v, $_SESSION['uid']);
          } else {
            // $_POST contains illegal keys
            error("????????? ILLEGAL KEY ???", "alert-danger");
          }
        }

        alert($lang['modprofile-done'], "alert-success");

        // refresh userinfo
        $member = member::getUserInfo();
      }
      // select columns that are allowed to modify
      $member_fields = DB::query("SELECT * FROM member WHERE uid=%i",$_SESSION['uid']);
      $member['fields'] = $member_fields[0];
      unset($member['fields']['salt']);
      unset($member['fields']['posts']);
      unset($member['fields']['threads']);
      unset($member['fields']['gid']);
      unset($member['fields']['regdate']);
      unset($member['fields']['lastlogin']);
      unset($member['fields']['lastattempt']);
      unset($member['fields']['failcount']);
      unset($member['fields']['regip']);
      break;








  case "logout":
    $_SESSION['username'] = "";
    $_SESSION['uid'] = 0;
    alert($lang['logged-out'], "alert-success");
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
    $output['memberlist'] = DB::query("SELECT * FROM member");
    foreach ($output['memberlist'] as $k => $v) {
      unset($output['memberlist'][$k]['password']);
      unset($output['memberlist'][$k]['salt']);
      unset($output['memberlist'][$k]['salt']);
      $output['memberlist'][$k]['regdate'] = toUserTime($output['memberlist'][$k]['regdate']);
      $output['memberlist'][$k]['lastlogin'] = toUserTime($output['memberlist'][$k]['lastlogin']);
      $output['memberlist'][$k]['usergroup'] = $uGroupInfo[$output['memberlist'][$k]['gid']];
      unset($output['memberlist'][$k]['gid']);
      unset($output['memberlist'][$k]['failcount']);
      unset($output['memberlist'][$k]['lastattempt']);
      unset($output['memberlist'][$k]['regip']);
    }
    unset($output['memberlist'][0]);
}

template("member");

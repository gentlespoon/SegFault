$(document).ready(function() {

  $(".disabled").click(function() {
    alert("Still Developing");
    return false;
  });

  $("#toggleShowRegPasswordBtn").click(function() {
    if ($(this).attr("aria-pressed") == "false") {
        $("#passwordDiv").removeClass("col-md-6").addClass("col-md-11");
        $("#passwordDiv2").addClass("hidden");
        $("#regPassword").attr("type", "text");
        $("#toggleShowRegPasswordBtn").html("😲");
        $("#toggleShowRegPasswordBtn").removeClass("btn-secondary").addClass("btn-primary");
    } else {
        $("#passwordDiv").removeClass("col-md-11").addClass("col-md-6");
        $("#passwordDiv2").removeClass("hidden");
        $("#regPassword").attr("type", "password");
        $("#toggleShowRegPasswordBtn").html("😑");
        $("#toggleShowRegPasswordBtn").removeClass("btn-primary").addClass("btn-secondary");
    }
  });

  $("#regForm").submit(function() {
    if ($("#regUsername").val() == "") {
      alert("Username cannot be blank");
      return false;
    }
    if ($("#regEmail").val() == "") {
      alert("Email cannot be blank");
      return false;
    }
    if ($("#regPassword").val() == "") {
      alert("Password cannot be blank");
      return false;
    }
    if (!$("#agreeCheckbox").prop("checked")) {
      alert("You must agree the terms to register");
      return false;
    }
    if ($("#toggleShowRegPasswordBtn").attr("aria-pressed") == "false") {
      if ($("#regPassword").val() == $("#regPassword2").val()) {
      } else {
        alert("Two passwords are different!");
        return false;
      }
      $("#regPassword").val($.md5($("#regPassword").val()));
    }
  });


  $("#toggleShowLoginPasswordBtn").click(function() {
    if ($(this).attr("aria-pressed") == "false") {
        $("#loginPassword").attr("type", "text");
        $("#toggleShowLoginPasswordBtn").html("😲");
        $("#toggleShowLoginPasswordBtn").removeClass("btn-secondary").addClass("btn-primary");
    } else {
        $("#loginPassword").attr("type", "password");
        $("#toggleShowLoginPasswordBtn").html("😑");
        $("#toggleShowLoginPasswordBtn").removeClass("btn-primary").addClass("btn-secondary");
    }
  });

  $("#loginForm").submit(function() {
    if ($("#loginUsername").val() == "") {
      alert("Username cannot be blank");
      return false;
    }
    if ($("#loginPassword").val() == "") {
      alert("Password cannot be blank");
      return false;
    }
    $("#loginPassword").val($.md5($("#loginPassword").val()));
  });


  $(".registerBtn").click(function() {
    window.location.href = "/member/register";
  });

  $(".loginBtn").click(function() {
    window.location.href = "/member/login";
  });



  $(".tagBtn").click(function(){
    window.location.href = "/questions/search?tag="+$(this).attr("tagid");
  });


  $(".vote").click(function() {
    var tid=$(this).attr("tid");
    var pid=$(this).attr("pid");
    $.ajax({ url: "/api/vote.php", data: { ud: $(this).attr("ud"), tid: tid, pid: pid}, method: "get"})
      .done(function(data) {
        var score = jQuery.parseJSON(data);
        $("#upvote-"+tid+"-"+pid).text(score.upvote);
        $("#downvote-"+tid+"-"+pid).text(score.downvote);
      })
      .fail(function(data) {
        alert("Vote Failed!\n\n"+data);
      })
      .always(function(data) {
        // alert( "complete\n" + data );
      });
  });



  $("#newThread").click(function() {
    window.location.href = "/questions/ask/advice?";
  });






});

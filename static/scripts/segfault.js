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
    window.location.href = "/member/register?redirect="+redirectURI;
  });

  $(".loginBtn").click(function() {
    window.location.href = "/member/login?redirect="+redirectURI;
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





  $("#editorForm").submit(function() {
    var tags = [];
    $('#tagsList').children('span').each(function () {
      tags.push(this.getAttribute("tagid"));
    });
    $("#hiddenTags").val(tags.join(","));
    $("#hiddenEditedHTML").val($('#summernote').summernote('code'));
  });

  $('#summernote').summernote({
    height: 150,
    tabsize: 4
  });

  $("#newThreadTagSearchbox").change(function() {
      var tagName=$("#newThreadTagSearchbox").val();
      var obj=$("#tagList").find("option[value='"+tagName+"']");
      if (obj != null && obj.length>0) {
        var tagid = obj.attr('tagid');
        var tagName = obj.val();
        if ($("#newThreadTag-"+tagid).length == 0) {
          var $newTag = $("<span id='newThreadTag-" + tagid + "' tagid='" + tagid + "' class='badge badge-dark'>" + tagName +"<div class='removeTag' onclick='removeTag(" + tagid + ");'>×</div></span>");
          $("#tagsList").append($newTag);
          $("#newThreadTagSearchbox").val("");
          return true;
        }
        $("#newThreadTagSearchbox").val("");
        return false;
      }
      else {
        alert("No maching tag");
        return false;
      }
  });

});

function removeTag(tagid) {
  var tag = document.getElementById("newThreadTag-"+tagid);
  tag.outerHTML = "";
  delete tag;
}

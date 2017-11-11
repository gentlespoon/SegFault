$(document).ready(function() {

  $(".disabled").click(function() {
    alert("Still Developing");
    return false;
  });

  $("#toggleShowRegPasswordBtn").click(function() {
    if ($(this).attr("aria-pressed") === "false") {
        $("#passwordDiv").removeClass("col-md-6").addClass("col-md-11");
        $("#passwordDiv2").addClass("hidden");
        $("#regPassword").attr("type", "text");
        $("#toggleShowRegPasswordBtn").html("😲").removeClass("btn-secondary").addClass("btn-primary");
    } else {
        $("#passwordDiv").removeClass("col-md-11").addClass("col-md-6");
        $("#passwordDiv2").removeClass("hidden");
        $("#regPassword").attr("type", "password");
        $("#toggleShowRegPasswordBtn").html("😑").removeClass("btn-primary").addClass("btn-secondary");
    }
  });


  $("#regEmail").blur(function() {
    if ($("#regEmail").val()!=="") {
      $.ajax({ url: "/api/member/validate?email="+$("#regEmail").val(), method: "get"})
      .done(function(data) {
        var result = $.parseJSON(data);
        if (result.success=="1") {
          $("#regEmailHint").text("");
          $("#regEmail").addClass("is-valid").removeClass("is-invalid");
        } else {
          $("#regEmailHint").text(result.message);
          $("#regEmail").addClass("is-invalid").removeClass("is-valid");;
        }
      });
    } else {
      $("#regEmailHint").text("");
      $("#regEmail").removeClass("is-invalid");
      $("#regEmail").removeClass("is-valid");
    }
  });

  $("#regUsername").blur(function() {
    if ($("#regUsername").val()!=="") {
      $.ajax({ url: "/api/member/validate?username="+$("#regUsername").val(), method: "get"})
      .done(function(data) {
        var result = $.parseJSON(data);
        if (result.success=="1") {
          $("#regUsernameHint").text("");
          $("#regUsername").addClass("is-valid").removeClass("is-invalid");;
        } else {
          $("#regUsernameHint").text(result.message);
          $("#regUsername").addClass("is-invalid").removeClass("is-valid");;;
        }
      });
    } else {
      $("#regUsernameHint").text("");
      $("#regUsername").removeClass("is-invalid");
      $("#regUsername").removeClass("is-valid");
    }
  });

  $("#regForm").submit(function() {
    if ($("#regUsername").val() === "") {
      $("#regUsername").addClass("is-invalid");
      $("#regUsernameHint").text("Username cannot be blank");
      return false;
    } else {
      $("#regUsername").removeClass("is-invalid");
      $("#regUsernameHint").text("");
    }

    if ($("#regEmail").val() === "") {
      $("#regEmail").addClass("is-invalid");
      $("#regEmailHint").text("Email cannot be blank");
      return false;
    } else {
      $("#regEmail").removeClass("is-invalid");
      $("#regEmailHint").text("");
    }

    if ($("#regPassword").val() === "") {
      $("#regPassword").addClass("is-invalid");
      $("#regPasswordHint").text("Password cannot be blank");
      return false;
    } else {
      $("#regPassword").removeClass("is-invalid");
      $("#regPasswordHint").text("");
    }

    if (!$("#agreeCheckbox").prop("checked")) {
      $("#agreeCheckbox").addClass("is-invalid");
      $("#regAgreementHint").text("You must agree the terms to register");
      return false;
    } else {
      $("#agreecheckbox").removeClass("is-invalid");
      $("#regAgreementHint").text("");
    }

    if ($("#toggleShowRegPasswordBtn").attr("aria-pressed") === "false") {
      if ($("#regPassword").val() !== $("#regPassword2").val()) {
        $("#regPassword2").addClass("is-invalid");
        $("#regPassword2Hint").text("Two passwords are different!");
        return false;
      } else {
        $("#regPassword2").removeClass("is-invalid");
        $("#regPassword2Hint").text("");
      }
      $("#regPassword").val($.md5($("#regPassword").val()));
    }
  });


  $("#toggleShowLoginPasswordBtn").click(function() {
    if ($(this).attr("aria-pressed") === "false") {
        $("#loginPassword").attr("type", "text");
        $("#toggleShowLoginPasswordBtn").html("😲").removeClass("btn-secondary").addClass("btn-primary");
    } else {
        $("#loginPassword").attr("type", "password");
        $("#toggleShowLoginPasswordBtn").html("😑").removeClass("btn-primary").addClass("btn-secondary");
    }
  });

  $("#loginForm").submit(function() {
    if ($("#loginUsername").val() === "") {
      alert("Username cannot be blank");
      return false;
    }
    if ($("#loginPassword").val() === "") {
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



  $("#newThreadForm").submit(function() {
    var tags = [];
    $('#tagsList').children('span').each(function () {
      tags.push(this.getAttribute("tagid"));
    });
    if (tags.length == 0) {
      $("#newThreadTagSearchboxHint").text("You must add at least one tag to your question.");
      $("#newThreadTagSearchbox").addClass("is-invalid");
      return false;
    }
    $("#hiddenTags").val(tags.join(","));
    $("#hiddenEditedHTML").val($('#summernote').summernote('code'));
  });

  $("#newPostForm").submit(function() {
    $("#hiddenEditedHTML").val($('#summernote').summernote('code'));
  });

  $('#summernote').summernote({
    height: 200,
    tabsize: 2
  });


  $("#newThreadTagSearchbox").change(function() {
      var tagName=$("#newThreadTagSearchbox").val();
      var obj=$("#tagList").find("option[value='"+tagName+"']");
      if (obj !== null && obj.length>0) {
        var tagid = obj.attr('tagid');
        tagName = obj.val();
        if ($("#newThreadTag-"+tagid).length == 0) {
          var $newTag = $("<span id='newThreadTag-" + tagid + "' tagid='" + tagid + "' class='badge badge-dark'>" + tagName +"<div class='removeTag' onclick='removeTag(" + tagid + ");'>×</div></span>");
          $("#tagsList").append($newTag);
          $("#newThreadTagSearchbox").val("");
          $("#newThreadTagSearchbox").removeClass("is-invalid");
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


  $("#loadMoreAnswers").click(function() {
    if (currentAnswers < totalAnswers) {
      var url = "/api/forum/loadpost?tid="+tid+"&offset="+Number(currentAnswers);
      // alert(url);
      $.ajax({ url: url, method: "get"})
      .done(function(data) {
        // alert(data);
        var result = $.parseJSON(data);
        if (result.success=="1") {
          var newPosts = 0;
          result.message.forEach(function(item, index) {
            newPosts++;
            var obj = `
            <div class="question-content">
              <div>
                `+item.content+`
              </div>
              <div class="question-content-author">
                <div class="voteBar">
                  <a class="vote" onclick="vote('upvote', 0, `+item.pid+`)">
                    <div class="voteBtn badge badge-success">👍 <span id="upvote-0-`+item.pid+`">`+item.upvote+`</span></div>
                  </a>
                  <a class="vote" onclick="vote('downvote', 0, `+item.pid+`)">
                    <div class="voteBtn badge badge-danger">👎 <span id="downvote-0-`+item.pid+`">`+item.downvote+`</span></div>
                  </a>
                </div>
                <div class="authorBar">
                  <div class="avatar">
                    <img class="avatar-40" src="`+item.avatar+`">
                  </div>
                  <div class="author">
                    <a href="/member/profile/`+item.uid+`">
                      `+item.username+`
                    </a><br>`+item.sendtime+`
                  </div>
                </div>
              </div>
            </div>
            `;
            $("#question-answers").append(obj);
          });
          currentAnswers += newPosts;
          $("#currentAnswers").text(currentAnswers);
          if (currentAnswers >= totalAnswers) {
            $("#loadMoreAnswers").text("All answers are displayed");
          }
        } else {
          alert("Load answers failed: "+result.message);
        }
      });
    }

  });


  $("#loadMoreQuestions").click(function() {
  });




});

function removeTag(tagid) {
  var tag = document.getElementById("newThreadTag-"+tagid);
  tag.outerHTML = "";
  delete tag;
}


function vote(ud, tid, pid) {
  $.ajax({ url: "/api/forum/vote", data: { ud: ud, tid: tid, pid: pid}, method: "get"})
    .done(function(data) {
      var score = jQuery.parseJSON(data);
      $("#upvote-"+tid+"-"+pid).text(score.message.upvote);
      $("#downvote-"+tid+"-"+pid).text(score.message.downvote);
    })
    .fail(function(data) {
      alert("Vote Failed!");
    })
    .always(function(data) {
      // alert( "complete\n" + data );
    });
};

function RemovePost(pid){
    $.ajax({ url: "/api/forum/removepost", data: { pid: pid }, method: "get"})
	.done(function(data) {
	    window.location.reload();
	})
};

function RemoveThread(tid){
    $.ajax({ url: "/api/forum/removethread", data: { tid: tid }, method: "get"})
	.done(function(data) {
	    window.location = "/";
	})
};

function LockThread(tid){
    $.ajax({ url: "/api/forum/lockthread", data: { tid: tid }, method: "get"})
      .done(function(data) {
	  window.location.reload();
      })
      .fail(function(data){
      })
      .always(function(data) {
      });
};

function EditPost(pid, oldContent, newContent){
    $.ajax({ url: "/api/forum/editpost", data: { pid: pid, oldContent: oldContent, newContent: newContent}, method: "get"})
};

function edit(){
    document.getElementById('editpost').style.display = "block";
};

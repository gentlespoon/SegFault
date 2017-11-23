$(document).ready(function() {  

  $("#advSearchTags").change(function() {
    var tagName=escapeRegExp($(this).val()); //escape to convert to case insensitive regex
    tagName=RegExp('^'+tagName+'$', 'i'); //match only if value is same as whole string
    var obj=$("#advTagList > option").filter(function (index) {
      if ($(this)[0].value.match(tagName)) {
        return $(this);
      }
    });
    if (obj !== null && obj.length>0) {
      var tagid = obj.attr('tagid');
      tagName = obj.val();
      if ($("#advNewTag-"+tagid).length == 0) {
        var $newTag = $("<span id='advNewTag-" + tagid + "' tagid='" + tagid + "' class='badge badge-dark'>" + tagName +"<div class='removeTag' onclick='removeAdvSearchTag(" + tagid + ");'>&times;</div></span>");
        $("#advTagsList").append($newTag);
        $(this).val("");
        $(this).removeClass("is-invalid");
        return true;
      }
      $(this).val("");
      return false;
    }
    else {
      if (!$(this).hasClass("is-invalid")) {
        $(this).addClass("is-invalid");
      }
      return false;
    }
  });

  $("#advSearchUsernames").change(function() {
    var regex = /^([\w\u0080-\uFFFF]+)(?= |$)| ([\w\u0080-\uFFFF]+)(?= |$)/g;
    var match = ""; //for use in regex.exec() loop
    var searchUsernames = $("#advSearchUsernames").val();
    var newSearchUsernames = $("#advSearchUsernames").val().replace(regex, "");
    newSearchUsernames = newSearchUsernames.replace(/(^ *)|( *$)/g, "")
    var usernames = [];
    while ((match = regex.exec(searchUsernames)) != null) {
      if (match[1]) {
        usernames.push(match[1]);
      }
      else if (match[2]) {
        usernames.push(match[2]);
      }
      else if (match[3]) {
        usernames.push(match[3]);
      }
      else if (match[4]) {
        usernames.push(match[4]);
      }
    }
    usernames.forEach(function (item, index) {
      if (item !== null && item.length > 0 && $("#newUsername-"+item).length == 0) {
        var $newUsername = $("<span id='newUsername-" + item + "' username='" + item + "' class='badge badge-dark'>" + item +"<div class='removeTag' onclick='removeUsername(\"" + item + "\");'>&times;</div></span>");
        $("#usernamesList").append($newUsername);
      }
    });
    if (newSearchUsernames && !$(this).hasClass('is-invalid')) {
      $(this).addClass('is-invalid');
    }
    else if (!newSearchUsernames && $(this).hasClass('is-invalid')) {
      $(this).removeClass('is-invalid');
    }
    $("#advSearchUsernames").val(newSearchUsernames);
  });

  $("#advSearchKeywords").change(function() {
    var regex = /"([\w\u0080-\uFFFF ]*?)"|^([\w\u0080-\uFFFF]+)(?= |$)| ([\w\u0080-\uFFFF]+)(?= |$)/g
    var match = ""; //for use in regex.exec() loop
    var searchKeywords = $("#advSearchKeywords").val();
    var newSearchKeywords = $("#advSearchKeywords").val().replace(regex, "");
    newSearchKeywords = newSearchKeywords.replace(/(^ *)|( *$)/g, "");
    var keywords = [];  
    while ((match = regex.exec($("#advSearchKeywords").val())) != null) {
      if (match[1]) {
        keywords.push(match[1]);
      }
      if (match[2]) {
        keywords.push(match[2]);
      }
      if (match[3]) {
        keywords.push(match[3]);
      }
      if (match[4]) {
        keywords.push(match[4]);
      }
      if (match[5]) {
        keywords.push(match[5]);
      }
    }
    keywords.forEach(function (item, index) {
      if (item !== null && item.length > 0 && $("#newKeyword-"+item).length == 0) {
        var $newKeyword = $("<span id='newKeyword-" + item + "' keyword='" + item + "' class='badge badge-dark'>" + item +"<div class='removeTag' onclick='removeKeyword(\"" + item + "\");'>&times;</div></span>");
        $("#keywordsList").append($newKeyword);
      }
    });
    if (newSearchKeywords && !$(this).hasClass('is-invalid')) {
      $(this).addClass('is-invalid');
    }
    else if (!newSearchKeywords && $(this).hasClass('is-invalid')) {
      $(this).removeClass('is-invalid');
    }
    $("#advSearchKeywords").val(newSearchKeywords);
  });

  $("#advSearchForm").submit(function(event) {
    var query = {
      "all":[],
      "keywords":[],
      "usernames":[],
      "tags":[]
    };
    if ($('#advSearchAll').is(':checked')) {
      query["all"].push("all");
    }
    if ($('#advSearchKeywordsAll').is(':checked')) {
      query["all"].push("keywords");
    }
    if ($('#advSearchTagsAll').is(':checked')) {
      query["all"].push("tags");
    }
    $('#keywordsList').children('span').each(function() {
      query["keywords"].push(this.getAttribute("keyword"));
    })
    $('#usernamesList').children('span').each(function() {
      query["usernames"].push(this.getAttribute("username"));
    })
    $('#advTagsList').children('span').each(function () {
      query["tags"].push(this.getAttribute("tagid"));
    });
    var queryJSON = JSON.stringify(query);
    $("#advSearchQuery").val(queryJSON);
    return true;
  });

  $(".disabled").click(function() {
    modalalert("Still Developing");
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
      modalalert("Username cannot be blank");
      return false;
    }
    if ($("#loginPassword").val() === "") {
      modalalert("Password cannot be blank");
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



  $("#newThreadForm").submit(function(ev) { 
    var tags = [];
    $('#tagsList').children('span').each(function () {
      tags.push(this.getAttribute("tagid"));
    });
    if (tags.length == 0) {
      $("#newThreadTagSearchboxHint").text("You must add at least one tag to your question.");
      $("#newThreadTagSearchbox").addClass("is-invalid");
      return false;
    }
    var content = tinyMCE.get("tinyMCE").getContent();
    if(content == ""){
	ev.preventDefault();
    }
    $("#hiddenTags").val(tags.join(","));
  });

  $("#newPostForm").submit(function(ev) {
      var content = tinyMCE.get("tinyMCE").getContent();
      if(content == ""){
	  ev.preventDefault();
      }
      //$("#hiddenEditedHTML").val($('#summernote').summernote('code'));
  });


  $("#newThreadTagSearchbox").change(function() {
      var tagName=escapeRegExp($("#newThreadTagSearchbox").val()); //escape to convert to case insensitive regex
      tagName=RegExp('^'+tagName+'$', 'i'); //match only if value is same as whole string
      var obj=$("#tagList > option").filter(function (index) {
        if ($(this)[0].value.match(tagName)) {
          return $(this);
        }
      });
      if (obj !== null && obj.length>0) {
        var tagid = obj.attr('tagid');
        tagName = obj.val();
        if ($("#newThreadTag-"+tagid).length == 0) {
          var $newTag = $("<span id='newThreadTag-" + tagid + "' tagid='" + tagid + "' class='badge badge-dark'>" + tagName +"<div class='removeTag' onclick='removeTag(" + tagid + ");'>&times;</div></span>");
          $("#tagsList").append($newTag);
          $("#newThreadTagSearchbox").val("");
          $("#newThreadTagSearchbox").removeClass("is-invalid");
          return true;
        }
        $("#newThreadTagSearchbox").val("");
        return false;
      }
      else {
        modalalert("No matching tag");
        return false;
      }
  });

  $("#loadMoreQuestions").click(function() {
    if (currentQuestions >= totalQuestions) {
      return;
    }

    var url = "/api/forum/loadthread?offset="+Number(currentQuestions)+"&count="+Number(questionsEachLoad)+"&search="+searchJSON;
    $.ajax({ url: url, method: "get"})
    .done(function(data) {
      var result = $.parseJSON(data);

      if (result.success == "1") {
        var newThreads = 0;

        result.message.forEach(function(item, index) {
          ++newThreads;
          var obj = `
          <div class="question-summary">
            <div class="row">
              <div class="col-lg-8 col-md-7 col-sm-12">
                <a href="/questions/viewthread/`+item.tid+`"><h5>`+item.title+`</h5></a>
                <div>Tags:`;
          item.tags.forEach(function(item, index) {
            obj = obj+`<a href="/questions/search?tag=`+item+`"><span class="badge badge-dark">`+threadTags[item].tagname+`</span></a>`;
          });

          obj = obj+
                `</div>
                <p>`+item.content+`</p>
              </div>
              <div class="row col-lg-4 col-md-5 col-sm-12 questions-author">
                <div class="authorBar">
                  <div class="title">Q</div>
                  <div class="avatar"><img class="avatar-40" src="`+item.avatar+`"></div>
                  <div class="author">
                    <a href="/member/profile?uid=`+item.uid+`">`+item.username+`</a><br>`+item.sendtime+`
                  </div>
                </div>`;
          if (!$.isEmptyObject(item.lastreply)) {
            obj = obj+`
            <div class="authorBar">
              <div class="title">A</div>
              <div class="avatar"><img class="avatar-40" src="`+item.lastreply.avatar+`"></div>
              <div class="author">
                <a href="/member/profile?uid=`+item.lastreply.avatar+`">`+item.lastreply.username+`</a><br>`+item.lastreply.sendtime+`
              </div>
            </div>`;
          }
          obj = obj+`
              </div>
            </div>
          </div>`;
          $(".questions").append(obj);
        });

        currentQuestions += newThreads;
        $("#currentQuestions").text(currentQuestions);
        if (currentQuestions >= totalQuestions) {
          $("#loadMoreQuestions").text("All questions are displayed");
        }
      }
      else {
        modalalert("Load answers failed: "+result.message);
      }
    });
  });

  $("#loadMoreAnswers").click(function() {
    if (currentAnswers < totalAnswers) {
      var url = "/api/forum/loadpost?tid="+tid+"&offset="+Number(currentAnswers)+"&count="+Number(answersEachLoad);
      // modalalert(url);
      $.ajax({ url: url, method: "get"})
      .done(function(data) {
        // modalalert(data);
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
                <div class="operationBar">
                  <a class="vote" onclick="vote('upvote', 0, `+item.pid+`)">
                    <div class="voteBtn badge badge-success">👍 <span id="upvote-0-`+item.pid+`">`+item.upvote+`</span></div>
                  </a>
                  <a class="vote" onclick="vote('downvote', 0, `+item.pid+`)">
                    <div class="voteBtn badge badge-danger">👎 <span id="downvote-0-`+item.pid+`">`+item.downvote+`</span></div>
                  </a>
                  <div class="modOperationBar">`;
            if (result.isModerator) {
              obj = obj+`
                    <button class="btn btn-secondary" onclick="RemovePost(`+item.pid+`)">Remove</button>
                    <button class="btn btn-secondary" onclick="edit(`+item.pid+`)">Edit</button>`;
            }   
            obj = obj+`
                  </div>
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
            <!-- text editor here -->
            <div id="editpost`+item.pid+`" style="display:none;">
               <textarea id="textedit`+item.pid+`">`+item.content+`</textarea>
               <button class="btn btn-primary" onclick="EditPost(`+item.pid+`)">Edit</button>
               <hr />
            </div>
            `;
            $("#question-answers").append(obj);
	    initTinyMCE("textedit"+item.pid);  
          });
          currentAnswers += newPosts;
          $("#currentAnswers").text(currentAnswers);

          $('pre > code').each(function(i, block) { //Syntax highlighting
            $(this).removeClass();
            $(this).addClass($(this).parent().attr("class"));
            hljs.highlightBlock(block);
          });
          
          if (currentAnswers >= totalAnswers) {
            $("#loadMoreAnswers").text("All answers are displayed");
          }
        } else {
          modalalert("Load answers failed: "+result.message);
        }
      });
    }

  });


  $("#loadMoreQuestions").click(function() {
  });


  $("#loadMoreAnswers").click();

  initTinyMCE("tinyMCE");

  if (typeof schemeList != 'undefined') {
    $.each(schemeList, function(index, value) {
      $('#schemeList').append( $('<option/>').attr("value", value) );
    });
  }

    //give update to threads
  $.ajax({ url: "/api/forum/getnotificationcount", data: {}, method: "get"})
  .done(function (data) {
    var count = $.parseJSON(data);
    if(count.success === 1)
      $('#notification').text(count.message);
  });

  //

  $('#notification').click(function () {
    $.ajax({ url: "/api/forum/getnotification", data: {}, method: "get"})
    .done(function (data) {
      var count = $.parseJSON(data);
      if(count.success === 1){ 
        var thread ="";
        $.each(count.message, function(index, value){
          // modalalert(typeof value);
          thread = "<div>"+ thread + "<a href = '/questions/viewthread/"+ value[0] + "'>"+ value[1]+ "</a></div>";
        });
        modalalert("New updates", thread);
      }
    });
  });
});


function modalalert(title, content) {
  if (title === undefined) {title = ""; content = "";}
  if (content === undefined) {content = title; title="Alert";}
  $("#modalalert-title").html(title);
  $("#modalalert-content").html(content);
  $("#modalalert").click();
  $("#modalalert").blur();
}


function removeTag(tagid) {
  var tag = document.getElementById("newThreadTag-"+tagid);
  tag.outerHTML = "";
  delete tag;
}

function removeAdvSearchTag(tagid) {
  var tag = document.getElementById("advNewTag-"+tagid);
  tag.outerHTML = "";
  delete tag;
}

function removeUsername(user) {
  var username = document.getElementById("newUsername-"+user);
  username.outerHTML = "";
  delete username;
}


function removeKeyword(key) {
  var keyword = document.getElementById("newKeyword-"+key);
  keyword.outerHTML = "";
  delete keyword;
}

function vote(ud, tid, pid) {
  $.ajax({ url: "/api/forum/vote", data: { ud: ud, tid: tid, pid: pid}, method: "get"})
    .done(function(data) {
      var score = $.parseJSON(data);
      $("#upvote-"+tid+"-"+pid).text(score.message.upvote);
      $("#downvote-"+tid+"-"+pid).text(score.message.downvote);
    })
    .fail(function(data) {
      modalalert("Vote Failed!");
    })
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
};

function EditPost(pid){
    var content = tinyMCE.get('textedit'+pid).getContent();
    $.ajax({ url: "/api/forum/editpost", data: { pid: pid, content: content}, method: "get"})
	.done(function(data){
	    window.location.reload();
	})
};

function EditThread(tid){
    var content = tinyMCE.get("threadedit").getContent();
    $.ajax({ url: "/api/forum/editthread", data: { tid: tid, content: content}, method: "get"})
     .done(function(data){ window.location.reload(); })
};

function edit(pid){
    if(pid == 'thread'){
	document.getElementById('editthread').style.display = "block";
	initTinyMCE("threadedit");
    }
    else{
	document.getElementById('editpost'+pid).style.display = "block";
    }
};

function addfavtag(tagid){
	$.ajax({ url: "/api/tag/addfavtag", data: { tagid: tagid }, method: "get"})
	.done(function(data){ window.location.reload(); })
};

function remfavtag(tagid){
	$.ajax({ url: "/api/tag/removefavtag", data: { tagid: tagid }, method: "get"})
	.done(function(data){ window.location.reload(); })
};


function initTinyMCE(textAreaID) {
  tinymce.init({
    selector: 'textarea#' + textAreaID,
    plugins: 'codesample',
    codesample_languages: [
      {text: 'HTML/XML', value: 'html'},
      {text: 'JavaScript', value: 'javascript'},
      {text: 'CSS', value: 'css'},
      {text: 'PHP', value: 'php'},
      {text: 'Ruby', value: 'ruby'},
      {text: 'Python', value: 'python'},
      {text: 'Java', value: 'java'},
      {text: 'C', value: 'c'},
      {text: 'C#', value: 'csharp'},
      {text: 'C++', value: 'cpp'}
    ],
    toolbar: 'undo redo | styleselect | bold italic underline strikethrough superscript subscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | codesample'
  });
}

function initInlineTinyMCE(divID) {
  tinymce.init({
    selector: 'div#' + textAreaID,
    inline: true,
    plugins: 'codesample',
    codesample_languages: [
      {text: 'HTML/XML', value: 'html'},
      {text: 'JavaScript', value: 'javascript'},
      {text: 'CSS', value: 'css'},
      {text: 'PHP', value: 'php'},
      {text: 'Ruby', value: 'ruby'},
      {text: 'Python', value: 'python'},
      {text: 'Java', value: 'java'},
      {text: 'C', value: 'c'},
      {text: 'C#', value: 'csharp'},
      {text: 'C++', value: 'cpp'}
    ],
    toolbar: 'undo redo | styleselect | bold italic underline strikethrough superscript subscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | codesample'
  });
}

//From https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions
function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}

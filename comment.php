<div class="comment-type">
    <div class="d-flex">
        <form method="POST" id="readMoreComment" class="d-flex" style="width:100%;">
            <div class="user-suggetions d-none" id="users_list__">
                <ul> </ul>
            </div>
            <div class="form-control custom-form-control" id="commentDivv" onkeyup="fetchUserNames(this)"
                contentEditable="true">

            </div>
            <input type="hidden" placeholder="Enter the comments" name="ideasComment" class="form-control">
            <button onclick="comentOnthisIdea('id')" class="submit-comment" type="button">Submit</button>
        </form>
    </div>
</div>

<script>
function comentOnthisIdea(ideaId) {
    $("#loading").show();

    const taggedUserInfo = [];
    $('#commentDivv span.tagSpanInComment').each(function() {
        taggedUserInfo.push($(this).data());
        $(this).removeAttr('contentEditable');
        $(this).removeAttr('class');
        $(this).removeAttr('data-start-index');
        $(this).removeAttr('data-end-index');
        $(this).addClass('mentionedUser');
        $(this).prepend('@')
    });
    comment = $('#commentDivv').html();
    $.ajax({
        type: "POST",
        url: "comment-posting.php",
        data: {
            'idea_id_for_comment': ideaId,
            'ideasComment': comment,
            'taggedUserInfoArray': taggedUserInfo
        },
        success: function(result) {
            readMoreAboutThis(result)
            $("#loading").hide();
        }
    });
}

function fetchUserNames(t) {
    $("#users_list__").addClass('d-none');
    $("#users_list__ ul").html('');
    thisVal = $("#" + $(t).attr('id')).html();
    thisVal_text = $("#" + $(t).attr('id')).text();
    if (thisVal != null && thisVal.length > 0) {
        // console.log(thisVal);
        var start = /@/ig; // @ Match
        var word = /@(\w+)/ig; //@abc Match
        var go = thisVal.match(start); //Content Matching @
        var name = thisVal.match(word); //Content Matching @abc
        var index_ = thisVal.indexOf("@");
        var index_Text = thisVal_text.indexOf("@");
        var dataString = 'searchword=' + name + '&index_position=' + index_ + '&index_Text=' + index_Text;
        if (go != null && go.length > 0) {
            // console.log(name);
            $.ajax({
                type: "POST",
                url: "tag-user-list.php", // Database name search 
                data: dataString,
                cache: false,
                success: function(data) {
                    $("#users_list__").removeClass('d-none');
                    $("#users_list__ ul").html(data);
                }
            });
        }
        return false;
    }
    // alert("Handler for .keyup() called.");
}

function appendTagToComment(t) {
    $("#users_list__").addClass('d-none');
    $("#users_list__ ul").html('');
    startInd = $(t).data('start-index');
    text_startInd = $(t).data('text-start-index');
    endInd = $(t).data('end-index');
    Taggeduser_id = $(t).data('user_id');
    search_word_le = $(t).data('search_word_le');
    var str = $("#commentDivv").html();
    var count = startInd.length;
    let pre = str.substring(0, startInd);
    let post = str.substring(startInd + 1 + search_word_le, str.length);
    let phrase = $(t).find('span').text();
    idd_span = 'taggedUserSp_' + startInd + '_' + Taggeduser_id;
    str = pre + `<span class = 'tagSpanInComment' contentEditable='false' data-start-index= ` + text_startInd +
        `  data-end-index= ` + endInd + ` id= '` + idd_span + `'  data-user_id = ` + Taggeduser_id +
        `>${phrase}</span>` + post;
    $("#commentDivv").html(str);
    var el = document.getElementById(Taggeduser_id);
    initializeInputs(el);
}

function initializeInputs(insertedSpanId) {
    event.stopPropagation();
    var offset = getCaretCharacterOffsetWithin($(event.target).get(0));
    if (event.keyCode == 37) { //Left arrow
        if (offset == 0) {
            var lastDiv = $(event.target).prev();
            if (lastDiv.length) {
                lastDiv.addClass('selectedToken');
                $(event.target).blur();
            }
        }
    }
    if (event.keyCode == 39) { //Right arrow
        if (offset == ($(event.target).text().length)) {
            var nextDiv = $(event.target).next();
            if (nextDiv.length) {
                nextDiv.addClass('selectedToken');
                $(event.target).blur();
            }
        }
    }
    if (event.keyCode == 8 || event.keyCode == 46) { //Backspace/delete
        if (offset == 0) {
            var lastDiv = $(event.target).prev();
            if (lastDiv.length) {
                lastDiv.remove();
            }
        }
    }

    var selectedToken = $('#' + insertedSpanId);
    if (selectedToken.length) {
        if (event.keyCode == 37) { //Left arrow
            var lastSpan = selectedToken.prev();
            if (lastSpan.length) {
                placeCaretAtEnd(lastSpan.get(0));
                selectedToken.removeClass('selectedToken');
            }
            return false;
        }
        if (event.keyCode == 39) { //Right arrow
            var nextSpan = selectedToken.next();
            if (nextSpan.length) {
                nextSpan.focus();
                selectedToken.removeClass('selectedToken');
            }
            return false;
        }
        if (event.keyCode == 8 || event.keyCode == 46) { //Backspace/delete
            selectedToken.remove();
        }
    }
}


function placeCaretAtEnd(el) {
    el.focus();
    if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.collapse(false);
        textRange.select();
    }
}

function getCaretCharacterOffsetWithin(element) {
    var caretOffset = 0;
    var doc = element.ownerDocument || element.document;
    var win = doc.defaultView || doc.parentWindow;
    var sel;
    if (typeof win.getSelection != "undefined") {
        sel = win.getSelection();
        if (sel.rangeCount > 0) {
            var range = win.getSelection().getRangeAt(0);
            var preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(element);
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            caretOffset = preCaretRange.toString().length;
        }
    } else if ((sel = doc.selection) && sel.type != "Control") {
        var textRange = sel.createRange();
        var preCaretTextRange = doc.body.createTextRange();
        preCaretTextRange.moveToElementText(element);
        preCaretTextRange.setEndPoint("EndToEnd", textRange);
        caretOffset = preCaretTextRange.text.length;
    }
    return caretOffset;
}
</script>
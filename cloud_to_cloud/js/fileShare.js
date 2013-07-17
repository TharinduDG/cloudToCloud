


    $("td.filename").mouseover(function(){
        var shareSpan = "<span class='shareSpan'>share<img src='core/img/actions/share.svg'></span>";
        shareSpan = $(shareSpan);
        $(this).append(shareSpan);
    });

    $("td.filename").mouseout(function(){
        $(this).find(".shareSpan").remove();
    });



    var divHtml = '<div id="dropdown">' +
        '<fieldset>' +
        '<legend>Share with another cloud</legend>' +
        '<ol>' +
        '<li>' +
        '<label class="label" for="dropdown_username">Username</label>' +
        '<input class="textField"  type="text" id="dropdown_username"/>' +
        '</li>' +
        '<li>' +
        '<label class="label" for="dropdown_url">URL</label>' +
        '<input class="textField" type="text" id="dropdown_url" />' +
        '</li>' +
        '<li>' +
        '<label class="label" for="dropdown_email">Email</label>' +
        '<input class="textField" type="text" id="dropdown_email"/>' +
        '</li>' +
        '<li>' +
        '<input type="button" id="dropdown_share" value="Share"/>' +
        '<input  type="button" id="dropdown_cancel" value="Cancel"/>' +
        '</li>' +
        '</ol>'+
        '</fieldset>' +
        '</div>';

    $("td").on("click",'.shareSpan',function(e){
        $(this).parent();
    });

    $(".filename").bind('click',function(e){
        $(this).addClass('current');
        $("#dropdown").remove();
        var shareDiv = $(divHtml);
        shareDiv.insertAfter($(this));
        shareDiv.hide();
        shareDiv.fadeIn(300);
    });

    $("tr").on('click','#dropdown_cancel',function(){
        $("#dropdown").parent().find("td.filename").removeClass("current");
        $("#dropdown").remove();
        $(".shareSpan").remove();
    });

    $("tr").on('click','#dropdown_share',function(){
        if(!validateDropdown()){
            return false;
        }else{
            $.ajax()
        }
    });
    function validateDropdown(){

        var username = $("#dropdown_username").val();
        var url = $("#dropdown_url").val();
        var email = $("#dropdown_email").val();
        var regex_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        var regex_url = new RegExp( "^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$");
        var isValid = true;

        if(username ==""){
            $("#dropdown_username").addClass('error');
            isValid = false;
        }

        if(!(regex_url.test(url))) {
            $("#dropdown_url").addClass('error');
            isValid = false;
        }

        if(!regex_email.test(email)){
            $("#dropdown_email").addClass('error');
            isValid = false;
        }
        return isValid;
    }

    $("tr").on('click','.textField',function(){
        if($(this).hasClass('error')){
            $(this).toggleClass('error');
            $(this).val("");
        }
    });




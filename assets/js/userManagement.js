function loginAll(){
    var userName = $("#usrnm").val();
    var passWord = $("#pswd").val();
    $.ajax({
           url: 'MY_API/login?username='+userName+'&password='+passWord,
           data: {
             format: 'json'
           },
           error: function() {
             $('#info').html('<p>An error has occurred</p>');
           },
           //dataType: 'jsonp',
           success: function(data) {
            var $title = $('<p>').text(data.status);
             if(data.status=="ok"){
                $('#info').html("");
                 setCookie("wordTestLogin",data.sessionID,1);
                $("#page00_myChart01").attr('src',"");
                $("#page00_myChart02").attr('src',"");
                $("#page00_myChart03").attr('src',"");
                $("#page00_image").show();
                $("#page00_home").page();
                showMobilePage("page00_home");
                getConfig(true);
             }else{
                $('#info').html(data.message);
             }
           },
           type: 'GET'
       });
}
function changePassword(){
    var data = {
        'old':	$('#page08_old').val(),
        'new1':	$('#page08_new1').val(),
        'new2':	$('#page08_new2').val()
    };
    var sessionID = getCookie("wordTestLogin");
    var myURL = "MY_API/transaction?sessionID="+sessionID+"&trans=9003&data="+JSON.stringify(data);
    myURL=myURL.replace(/{/g,"(@(").replace(/}/g,")@)").replace(/#/g,"%23");
    $.get(myURL, function(data, status){
        if(status!='success'){
            $("#page08_info").html("<img style='width:14px' src='images/warning.ico'> Update Fail!");
        }else{
            if(data.status=='ok'){
                $("#page08_info").html("<img style='width:14px' src='images/Ok.ico'>"+data.message);
                showMobilePage('page00_home');
            }else{
                $('#page08_old').val("");
                $('#page08_new1').val("");
                $('#page08_new2').val("");
                $("#page08_info").html("<img style='width:14px' src='images/warning.ico'>"+data.message);
            }
        }
    });
    
}
function verifyCookie(){
    var cookie=getCookie("wordTestLogin");
    var responseJson = {};
    $.ajax({
           url: 'MY_API/verifySession?sessionID='+getCookie("wordTestLogin"),
           data: {
             format: 'json'
           },
           error: function() {
            //window.open('login.html', '_self');
            $.mobile.changePage("#page02_login",{ transition: "flip"});
        },
        async: true,
           success: function(data) {
            //$("#yourName").html(data.firstName+" "+data.lastName);
            if(data.status=="ok"){
                showMobilePage("page00_home");
            }else{
                //Invalide sessionID
                $.mobile.changePage("#page02_login");
            }
           },
       type: 'GET'
   });				
}
function wordTestLogout(){
    setCookie('wordTestLogin','',1);
//  $('#universal_usertype').html("");
    if($(window).width()<=768){
        showHideMobile();
    }
    $.mobile.changePage("#page02_login",{ transition: "flip"});
//  $("#previousPage").html(JSON.stringify([]));
}
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function checkCookie() {
    var user = getCookie("username");
    if (user != "") {
        alert("Welcome again " + user);
    } else {
        user = prompt("Please enter your name:", "");
        if (user != "" && user != null) {
            setCookie("username", user, 365);
        }
    }
}
var delete_cookie = function(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
};
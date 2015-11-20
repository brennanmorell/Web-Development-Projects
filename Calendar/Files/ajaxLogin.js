function loginAjax(event) {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    var button = document.getElementById("signUpButton").value;
    
    var dataString = "username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(password) + "&button=" + encodeURIComponent(button);
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "processLogin.php", true);
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlHttp.addEventListener("load", function(event){
        var jsonData = JSON.parse(event.target.responseText);
        if (jsonData.success) {
            $("#loginButton").hide();
            $("#signUpButton").hide();
            $("#username").hide();
            $("#password").hide();
            $(".errorMsg").hide();
            $(".events").show();
            $(function() {
               $("#datepicker").datepicker();
            });
            $("#loggedInUser").html(jsonData.username);
            isSignedIn = true; //to be used in calendarScript.js
            
        } else {
            $(".errorMsg").html(jsonData.message);
        }
    }, false);
    xmlHttp.send(dataString);
}

function loginTAjax(event) {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    var button = document.getElementById("loginButton").value;
    var dataString = "username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(password) + "&button=" + encodeURIComponent(button);

    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "processLogin.php", true);
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlHttp.addEventListener("load", function(event){
        var jsonData = JSON.parse(event.target.responseText);
        if (jsonData.success) {
            $("#loginButton").hide();
            $("#signUpButton").hide();
            $("#username").hide();
            $("#password").hide();
            $(".errorMsg").hide();
            $(".events").show();
            $(function() {
               $("#datepicker").datepicker();
            });
            $("#loggedInUser").html(jsonData.username);
            isSignedIn = true; //to be used in calendarScript.js
            
        } else {
            $(".errorMsg").html(jsonData.message);
        }
    }, false);
    xmlHttp.send(dataString);
}


$(document).ready(function() {
    document.getElementById("loginButton").addEventListener("click", loginTAjax, false);
    document.getElementById("signUpButton").addEventListener("click", loginAjax, false);
});
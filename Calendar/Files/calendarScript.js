(function(){ "use strict";Date.prototype.deltaDays=function(c){return new Date(this.getFullYear(),this.getMonth(),this.getDate()+c)};Date.prototype.getSunday=function(){return this.deltaDays(-1*this.getDay());}})();
function Week(c){this.sunday=c.getSunday();this.nextWeek=function(){return new Week(this.sunday.deltaDays(7))};this.prevWeek=function(){return new Week(this.sunday.deltaDays(-7))};this.contains=function(b){return this.sunday.valueOf()===b.getSunday().valueOf()};this.getDates=function(){for(var b=[],a=0;7>a;a++)b.push(this.sunday.deltaDays(a));return b}}
function Month(c,b){this.year=c;this.month=b;this.nextMonth=function(){return new Month(c+Math.floor((b+1)/12),(b+1)%12)};this.prevMonth=function(){return new Month(c+Math.floor((b-1)/12),(b+11)%12)};this.getDateObject=function(a){return new Date(this.year,this.month,a)};this.getWeeks=function(){var a=this.getDateObject(1),b=this.nextMonth().getDateObject(0),c=[],a=new Week(a);for(c.push(a);!a.contains(b);)a=a.nextWeek(),c.push(a);return c}};


isSignedIn = false;

currentDate = new Date();
month = currentDate.getMonth();
year = currentDate.getFullYear();
var currentMonth = new Month(year, month);

var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

function displayMonth() { //show the month
   "use strict";
   $("#monthDisplay").html(months[currentMonth.month] + " "+currentMonth.year);
}

function futureMonth() { // Change the month when the "next" button is pressed
   "use strict";
   currentMonth = currentMonth.nextMonth();
   $("#monthDisplay").html(months[currentMonth.month]+" "+currentMonth.year);
   updateCalendar();
}

function highlightDate() { //hihlight a day when the day's box is clicked
   "use strict";
   $(this).toggleClass("highlighted");
}

function pastMonth() { // Change the month when the "prev" button is pressed
   "use strict";
   currentMonth = currentMonth.prevMonth();
   $("#monthDisplay").html(months[currentMonth.month] + " "+currentMonth.year);
   updateCalendar();
}

$(document).ready(function() { //call one of the above functions when a button is clicked
   "use strict";
   $("#pastMonth").click(pastMonth);
   $("#futureMonth").click(futureMonth);
   $(".dateTD").mouseover(highlightDate);
   $(".dateTD").mouseout(highlightDate);
});

var currentMonthStartingID;
var nextMonthStartingID;


function updateCalendar(){ //Show the correct day number based on the current month
   "use strict";

   var cellNum = 1; //remove the notInCurrentMonth class from any cells that have it because we are changing month
   var cellIDString;
   while(cellNum <= 35) //number of cells in calendar view
   {
      cellIDString = "#day"+cellNum;
      $(cellIDString).removeClass("notCurrentMonth");

      cellNum++;
   }

   var weeks = currentMonth.getWeeks();
   var daysCounter = 0;

   currentMonthStartingID = 0; //I added this because we dont want one months starting id to carry over to the next in cases like
   nextMonthStartingID = 35; //want it to reset every month

   var w;
   for (w in weeks) {
      var days = weeks[w].getDates();
      var daysIndex = 0;

      while(daysIndex < days.length) {
         var cellIDString = "#day"+(daysCounter+1);
         $(cellIDString).html(days[daysIndex].getDate()+"<br>");
         daysIndex++;
         daysCounter++;
         if (daysIndex < days.length-1) {
            if (days[daysIndex].getDate() > days[daysIndex+1].getDate()) {
               if (daysCounter + 1 < 8) {
                  currentMonthStartingID = daysCounter+1; //this is the element ID where the current month actually starts
               }
               else
               {
                  nextMonthStartingID = daysCounter+1; //added this so we can blur out dates not in current month
               }

            }
         }
      }
   }

   var cellNum = 1;
   while(cellNum <= 35) //number of cells in calendar view
   {
      if (cellNum < currentMonthStartingID + 1) {
         var cellIDString = "#day"+cellNum;
         $(cellIDString).addClass("notCurrentMonth");
      }

      if (cellNum > nextMonthStartingID) {
         var cellIDString = "#day"+cellNum;
         $(cellIDString).addClass("notCurrentMonth");
      }
      cellNum++;
   }
}

document.addEventListener("DOMContentLoaded", displayMonth, false);
document.addEventListener("DOMContentLoaded", updateCalendar, false);




function loginAjax(event) { //SIGN UP
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
            csrfToken = jsonData.token;
            $("#loginButton").hide();
            $("#signUpButton").hide();
            $("#username").hide();
            $("#password").hide();
            $(".errorMsg").hide();
            $("#eventText").val("");
            $("#datepicker").val("");
            $("#startTime").val("");
            $("#endTime").val("");
            $("#category").val("");
            $(".events").show();
            $("#logoutButton").show();
            $("#accessShared").show();
            $(function() {
               $("#datepicker").datepicker();
            });
            $("#loggedInUser").html(jsonData.username);
            $("#sharedCalendars").show();
            isSignedIn = true; //to be used in calendarScript.js
            showEventsAjax("click");
            displayCategoryButtons();
            var accessUsers = jsonData.accessUsers; //this is where I'm trying to start doing the calendar sharing
            var accessUsers = jsonData.accessUsers;
            for (var i = 0; i < accessUsers.length; i++) {
               $("#accessShared").append("<input type=submit class=sharingButtons id=sharedFrom" + accessUsers[i] + " value=" + accessUsers[i] + " />");
               (function (getShared) {
                  document.getElementById("sharedFrom"+getShared).addEventListener("click", function() {changeToShared(getShared)}, false);
               })(accessUsers[i]);
               $("#accessShared").append("<input type=submit class=sharingButtons id=myCalendar value=" + $("#loggedInUser").html() + " />");
               document.getElementById("myCalendar").addEventListener("click", function() {changeToShared($("#loggedInUser").html())}, false);
            }
        } else {
            $(".errorMsg").html(jsonData.message);
        }
    }, false);
    xmlHttp.send(dataString);

}

function loginTAjax(event) { //SIGN IN
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
            csrfToken = jsonData.token;
            $("#loginButton").hide();
            $("#signUpButton").hide();
            $("#username").hide();
            $("#password").hide();
            $(".errorMsg").hide();
            $("#eventText").val("");
            $("#datepicker").val("");
            $("#startTime").val("");
            $("#endTime").val("");
            $("#category").val("");
            $(".events").show();
            $("#logoutButton").show();
            $("#accessShared").show();
            $(function() {
               $("#datepicker").datepicker();
            });
            $("#loggedInUser").html(jsonData.username);
             $("#sharedCalendars").show();
            isSignedIn = true;
            showEventsAjax("click");
            displayCategoryButtons();
            
            var accessUsers = jsonData.accessUsers;
            for (var i = 0; i < accessUsers.length; i++) {
               $("#accessShared").append("<input type=submit id=sharedFrom" + accessUsers[i] + " value=" + accessUsers[i] + " />");
               (function (getShared) {
                  document.getElementById("sharedFrom"+getShared).addEventListener("click", function() {changeToShared(getShared)}, false);
               })(accessUsers[i]);
            }
            $("#accessShared").append("<input type=submit id=myCalendar value=" + $("#loggedInUser").html() + " />");
            document.getElementById("myCalendar").addEventListener("click", function() {changeToShared($("#loggedInUser").html())}, false);
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

var allCatsArr = []; //store categories to check if one is ever deleted

function displayCategoryButtons() {
   var dataString = "token=" + encodeURIComponent(csrfToken);
   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "getCategories.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   xmlHttp.addEventListener("load", function(event){
      var jsonData = JSON.parse(event.target.responseText);
      var theseCats = []; //store these local categories
      if (jsonData.success) { //FILL IN APPRORPIATE SPOT IN CALENDAR
         for (i = 0; i < jsonData.categories.length; i++) { //get the categories
            var category = jsonData.categories[i];
            theseCats.push(category);
            allCatsArr.push(category);
            if (!document.getElementById("button"+category)) {
               $("#categoryButtons").append("<label><div class=catButtonClass id='button"+category+"'><input type='checkbox' id='checkbox"+category+"' value='"+category+"' checked />&nbsp;"+category+"</label></div>");
            }
            var colorNum = i;
            if (colorNum > 6) {
               colorNum = 0;
            }
            $("#button"+category).css({ //make these buttons look nice
               "border-radius": "2px",
               "margin-left": "3px",
               "margin-right": "3px",
               "padding-right": "2px",
               "background-color": pickColor(colorNum)
            });
            (function (category) {
               $("#checkbox"+category).click(function() { //on button click, set all info in dialog popup
                  categoryButtonsAction(category);
               });

            })(category); //execute with these variables

         }
         for (i = 0; i < allCatsArr.length; i++) {
            doesNotExist = true;
            for (j = 0; j < theseCats.length; j++) {
               if (allCatsArr[i] == theseCats[j]) {
                  doesNotExist = false;
               }
            }
            if (doesNotExist) {
               $("#button"+allCatsArr[i]).remove();
            }
         }
      }
   }, false);

   xmlHttp.send(dataString);
}

function pickColor(num) {
   var colors = ["#FF0000", "#00FF00", "#9494FF", "#FFFF00", "#00FFFF", "#FF00FF", "#C0C0C0" ];
   return colors[num];
}


function categoryButtonsAction(categoryName) { //hide or show events on calendar when click checkbox for category
   var dataString = "token=" + encodeURIComponent(csrfToken) + "&category=" + encodeURIComponent(categoryName);
   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "clickedCategoryButton.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   xmlHttp.addEventListener("load", function(event){
      var jsonData = JSON.parse(event.target.responseText);
      if (jsonData.success) { // show or hide events on calendar
         for (i = 0; i < jsonData.eventsArr.length; i++) { //get the categories
            var catEventID = jsonData.eventsArr[i];
            if (!document.getElementById("checkbox"+categoryName).checked) {
               $("#event"+catEventID).hide();
            } else {
               $("#event"+catEventID).show();
            }
         }
      }
   }, false);

   xmlHttp.send(dataString);
}




$(document).ready(function() { //HIDE AND SHOW HTML
   $("#editDialogs").hide();
   if (isSignedIn) { //display fields to allow for an event entry
      $(".events").show();
      $(function() {
         $("#datepicker").datepicker(); //datepicker jqueryUI functionality
      });
   } else {
      $(".events").hide(); //hide event fields if NOT signed in
      $("#logoutButton").hide();
      $("#sharedCalendars").hide();
      $("#accessShared").hide();
   }
});

$(document).ready(function() { //Call function to add event to database
   document.getElementById("submitEvent").addEventListener("click", eventAjax, false);
});

function eventAjax(event) { //Add an event to database
   var getGroup = document.getElementById("grouping").value;
   var eventDesc = document.getElementById("eventText").value;
   var eventDate = document.getElementById("datepicker").value;
   var eventStart = document.getElementById("startTime").value;
   var eventEnd = document.getElementById("endTime").value;
   var eventCat = document.getElementById("category").value;
   var currentUser = document.getElementById("loggedInUser").innerHTML; //get current user

   var dataString = "eventDesc=" + encodeURIComponent(eventDesc) + "&eventDate=" +
   encodeURIComponent(eventDate) + "&eventStart=" + encodeURIComponent(eventStart) +
   "&eventEnd=" + encodeURIComponent(eventEnd) + "&eventCat=" + encodeURIComponent(eventCat) +
   "&currentUser=" + encodeURIComponent(currentUser) + "&token=" + encodeURIComponent(csrfToken) +
   "&grouping=" + encodeURIComponent(getGroup);

   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "createEvent.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   xmlHttp.addEventListener("load", function(event){
      var jsonData = JSON.parse(event.target.responseText);

      if (!jsonData.success) { //if event could not be created, give reason why
         $("#badEventFormat").html(jsonData.message);
      } else { //clear event fields, the event has been successfully added to database
         $("#badEventFormat").html("");
         $("#eventText").val("");
         $("#datepicker").val("");
         $("#startTime").val("");
         $("#endTime").val("");
         $("#category").val("");
         $("#grouping").val("");
         displayCategoryButtons();
         showEventsAjax("click");
      }

   }, false);
   xmlHttp.send(dataString);
}

function escapeOutput(str) { //escapes output so strings are safe to view
   return str.replace(/</g, '&lt;').replace(/>/g, '&gt;');
}


function showEventsAjax(event) { //Show the events on the calendar that are associated with a user
   var currentUser = document.getElementById("loggedInUser").innerHTML; //get current user
   var dataString = "username=" + encodeURIComponent(currentUser) + "&token=" + encodeURIComponent(csrfToken);
   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "fetchEvents.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   xmlHttp.addEventListener("load", function(event){
      var jsonData = JSON.parse(event.target.responseText);

      if (jsonData.success) { //FILL IN APPRORPIATE SPOT IN CALENDAR

         for (i = 0; i < jsonData.dates.length; i++) { //get all information and show some information
            var getID = jsonData.ids[i];
            storedIDs = jsonData.ids;
            var getDesc = escapeOutput(jsonData.descriptions[i]);
            var getCat = escapeOutput(jsonData.categories[i]);
            var getStart = jsonData.starts[i];
            var getEnd = jsonData.ends[i];
            var getDate = jsonData.dates[i];
            var splitDate = getDate.split("/"); //since format is MM/DD/YYYY, split at /
            var getSplitDay = parseInt(splitDate[1]);
            var getSplitMonth = parseInt(splitDate[0]);
            var getSplitYear = parseInt(splitDate[2]); //to be used later since we arent utilizing year yet
            if ((getSplitYear) == currentMonth.year) { //make sure we are only displaying events for correct year

                  if ((getSplitMonth-1) == currentMonth.month) { //then make a button for the event at the correct day
                     var dayID = "#day" + (getSplitDay + currentMonthStartingID);

                     if (!document.getElementById("event"+getID)) { //if this id does not exist yet, append it
                        $(dayID).append("<button class=eventButtonText id=event" + getID + ">" + getDesc + "  " + getStart + "-" + getEnd + "</button>");

                        (function (getID, getDate, getDesc, getStart, getEnd, getCat) { //this will execute for each and every button
                           $("#event"+getID).unbind().click(function() { //on button click, set all info in dialog popup
                              $("#allDialogs").dialog();
                              $("#allDialogs").dialog("option", "title", getDate);
                              $("#dialogEventDesc").html(getDesc);
                              $("#dialogEventStart").html(getStart);
                              $("#dialogEventEnd").html(getEnd);
                              $("#dialogEventCat").html(getCat);
                              $("#dialogEventEdit").html("<div><input type='submit' class='btn btn-xs' id='editEvent"+getID+"' value='Edit' /></div>");
                              $("#dialogEventDelete").html("<div><input type='submit' class='btn btn-xs' id='deleteEvent"+getID+"' value='Delete' /></div>");


                              $("#editEvent"+getID).click(function() {editEventPopup(getID, getDate, getDesc, getStart, getEnd, getCat)});
                              $("#deleteEvent"+getID).click({param1: getID}, deleteEventAjax);
                           });

                        })(getID, getDate, getDesc, getStart, getEnd, getCat); //execute with these variables
                     }
                  }
            }
         }
      } else { //probably will show error message here

      }
   }, false);
   xmlHttp.send(dataString);

}

$(document).ready(function() { //show events on calendar when any of these buttons are pressed
      document.getElementById("submitEvent").addEventListener("click", showEventsAjax, false);
      document.getElementById("pastMonth").addEventListener("click", showEventsAjax, false);
      document.getElementById("futureMonth").addEventListener("click", showEventsAjax, false);
});

function deleteEventAjax(event) { //delete an event from calendar and database
   var eventID = event.data.param1;
   var dataString = "eventID=" + encodeURIComponent(eventID) + "&token=" + encodeURIComponent(csrfToken);

   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "deleteEvent.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

   xmlHttp.addEventListener("load", function(event){

      var jsonData = JSON.parse(event.target.responseText);

      if (jsonData.success) {
         $("#allDialogs").dialog("close"); //close dialog box
         $("#event"+eventID).remove(); //remove this event from calendar
         displayCategoryButtons();
      }
   }, false);
   xmlHttp.send(dataString);
}

function editEventPopup(editID, editDate, editDesc, editStart, editEnd, editCat) { //popup with editing fields
   $("#allDialogs").dialog("close");
   $("#editDialogs").dialog();
   $("#dialogEditDate").val(editDate);
   $("#dialogEditDesc").val(editDesc);
   $("#dialogEditStart").val(editStart);
   $("#dialogEditEnd").val(editEnd);
   $("#dialogEditCat").val(editCat);
   $("#submitEdit").unbind().click(function() {
      var newDate = $("#dialogEditDate").val();
      var newDesc = $("#dialogEditDesc").val();
      var newStart = $("#dialogEditStart").val();
      var newEnd = $("#dialogEditEnd").val();
      var newCat = $("#dialogEditCat").val();

      editEventAjax(editID, newDate, newDesc, newStart, newEnd, newCat);
   });
}

function editEventAjax(editID, editDate, editDesc, editStart, editEnd, editCat) {
   var currentUser = document.getElementById("loggedInUser").innerHTML; //get current user
   var dataString = "eventID=" + encodeURIComponent(editID) + "&editDate=" + encodeURIComponent(editDate) +
      "&editDesc=" + encodeURIComponent(editDesc) + "&editStart=" + encodeURIComponent(editStart) +
      "&editEnd=" + encodeURIComponent(editEnd) + "&editCat=" + encodeURIComponent(editCat) +
      "&editUser=" + encodeURIComponent(currentUser) + "&token=" + encodeURIComponent(csrfToken);

   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "editEvent.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   xmlHttp.addEventListener("load", function(event){
      var jsonData = JSON.parse(event.target.responseText);
      if (jsonData.success) { //if event could not be created, give reason why
         $("#editDialogs").dialog("close");
         $("#event"+editID).remove(); //remove from calendar
         showEventsAjax("click"); //reinsert with updated information
         displayCategoryButtons();
      } else {
         $("#badEditFormat").html(jsonData.message); //editing error
      }

   }, false);
   xmlHttp.send(dataString);
}

function logoutAjax(event) { //log out current user
   var dataString = "token=" + encodeURIComponent(csrfToken);
   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "processLogout.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   xmlHttp.addEventListener("load", function(event){
      var jsonData = JSON.parse(event.target.responseText);
      if (jsonData.success) { //if successfully logged out
         $("#loggedInUser").empty();
         for (i = 0; i < storedIDs.length; i++) {
            $("#event"+storedIDs[i]).remove(); //logged out, remove all events from calendar
         }
         $(".events").hide(); //hide event fields if NOT signed in
         $("#logoutButton").hide();
         $("#loginButton").show();
         $("#signUpButton").show();
         $("#username").val("");
         $("#username").show();
         $("#password").val("");
         $("#password").show();
         $(".errorMsg").html("");
         $(".errorMsg").show();
         $("#badShareName").html("");
         $("#categoryButtons").html("");
         $("#shareText").val("");
         $("#grouping").val("");
         $("#accessShared").empty();
         $("#accessShared").hide();
         $("#sharedCalendars").hide();

      }

   }, false);
   xmlHttp.send(dataString);
}

function shareCalendarAjax() { //submit a username to share your calendar with
   
    var userShare = document.getElementById("shareText").value;
    var dataString = "token=" + encodeURIComponent(csrfToken) + "&sharedName="+encodeURIComponent(userShare);

    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "processShare.php", true);
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlHttp.addEventListener("load", function(event){

      var jsonData = JSON.parse(event.target.responseText);
      if (jsonData.success) { //if event could not be created, give reason why
         $("#badShareName").html(""); //username error
      } else {
         $("#badShareName").html(jsonData.message); //username error
      }

    }, false);
    xmlHttp.send(dataString);
}


$(document).ready(function() { //event listener for log out button
      document.getElementById("logoutButton").addEventListener("click", logoutAjax, false);
      document.getElementById("submitShare").addEventListener("click", shareCalendarAjax, false);
});


function changeToShared(sharedUserCal) { //change to another user's calendar to view
   var userShare = document.getElementById("shareText").value;
   var dataString = "token=" + encodeURIComponent(csrfToken) + "&sharedName="+encodeURIComponent(sharedUserCal);

   var xmlHttp = new XMLHttpRequest();
   xmlHttp.open("POST", "viewSharedCalendar.php", true);
   xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   xmlHttp.addEventListener("load", function(event){
      var jsonData = JSON.parse(event.target.responseText);
      if (jsonData.actualUser == jsonData.sharedUser) {
         for (i = 0; i < storedIDs.length; i++) {
            $("#event"+storedIDs[i]).remove(); //changed calendar, remove all events from calendar
         }
         $(".events").show();
         $("#shareText").show();
         $("#submitShare").show();
         $("#dialogEventEdit").show();
         $("#dialogEventDelete").show();
         $("#categoryButtons").empty();
         showEventsAjax("click");
         displayCategoryButtons();
      } else {
         for (i = 0; i < storedIDs.length; i++) {
            $("#event"+storedIDs[i]).remove(); //changed calendar, remove all events from calendar
         }
         $(".events").hide();
         $("#shareText").hide();
         $("#submitShare").hide();
         $("#dialogEventEdit").hide();
         $("#dialogEventDelete").hide();
         $("#categoryButtons").empty();
         showEventsAjax("click");
         displayCategoryButtons();
      }
   }, false);

   xmlHttp.send(dataString);
}


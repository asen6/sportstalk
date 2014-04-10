// register.js

// Page ready handler.
function init() {
  
  // Set up event handlers.
  $("#su_button").click(su_clicked);
}
$(document).ready(init);

// Event handler for clicking the sign up button.
function su_clicked() {
  
  // Add user credentials to db
  
  var username = escape($("#st_username")[0].value);
  var password = escape($("#password")[0].value);
  var password2 = escape($("#password2")[0].value);
  
  // checks (also done on server side)
  
  if ($("#st_username")[0].value.length > 16){
  $("#error_msg").text("Username must be 16 characters or less");
  return;
  } 
  
  if ($("#password")[0].value.length <= 5){
  $("#error_msg").text("Password must be at least 6 characters.");
  return;
  } 
  
  if ($("#password")[0].value != $("#password2")[0].value){
  $("#error_msg").text("Password confirmation failed.");
  return;
  } 
  
  var teams = "No team";
  
  // passed all checks
  $.post("add_registration.php", {"username": username, "password": password, "password2" : password2}, 
  function(data, status, xhr) {
  var indicator = data[0];
  if (indicator == 0){
    window.location="index.php";
  }
  else {
      $("#error_msg").text(data[1]);
  }
  }, "json");
  

}


function verifyEmail(email)
{
var status = false;     
var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
     if (email.search(emailRegEx) == -1) {
          
     }
     else {
          status = true;
     }
     return status;
}


<?php
include './system.php';

session_name('daycare');
session_start();
if(isset($_SESSION['uid']))
{
  header('location:index.php');
}

if(isset($_POST['action']))
{
  switch($_POST['action'])
  {
    case 'login':
    $username = $_POST['username'];
    $userpassword = $_POST['userpassword'];

    $username = stripcslashes($username);
    $userpassword = stripcslashes($userpassword);
    $username = $db->real_escape_string($username);
    $userpassword = $db->real_escape_string($userpassword);

     $sql = "SELECT * FROM daycare_user
        WHERE user_username = '$username'
        AND user_password = AES_ENCRYPT('$userpassword', 'davidyap1997')
        ";

      $query = $db->query($sql);

      if($row = $query->fetch_array(MYSQLI_ASSOC))
      {
        
        $_SESSION['uid'] = $row['user_ID'];
        $_SESSION['permission'] = $row['user_permission'];
        echo json_encode(true);
      }
      else
      {
         echo json_encode(false);
      }
      exit;
    break;
  }
}

?>


<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title>Login form</title>
  <script src="resource/js/jquery-3.2.1.min.js"></script>
  <script src="resource/js/bootstrap.min.js"></script>
  <script src="resource/js/bootbox.min.js"></script>
  <link rel='stylesheet prefetch' href='https://www.google.com/fonts#UsePlace:use/Collection:Roboto:400,300,100,500'>
  <link rel='stylesheet prefetch' href='resource/css/bootstrap.min.css'>
  <link rel='stylesheet prefetch' href='https://www.google.com/fonts#UsePlace:use/Collection:Roboto+Slab:400,700,300,100'>
  <link rel="stylesheet" href="include/css/loginstyle.css">
</head>

<body>
<div id="dialog" class="dialog dialog-effect-in">
  <div class="dialog-front">
    <div class="dialog-content">
      <form id="login_form" class="dialog-form" onsubmit="return false;">
        <fieldset>
          <legend>Log in</legend>
          <div class="form-group">
            <label for="user_username" class="control-label">Username:</label>
            <input type="text" id="user_username" class="form-control" name="user_username" autofocus>
          </div>
          <div class="form-group">
            <label for="user_password" class="control-label">Password:</label>
            <input type="password" id="user_password" class="form-control" name="user_password">
          </div>
          <div class="text-center pad-top-20">
            <p>Have you forgotten your<br><a href="resetps.php" class="link"><strong>password</strong></a>?</p>
          </div>
    		  <div class="A">
    		    <a href="javascript:void(0)" onclick="login()">Login Now</a>
    		  </div>	
        </fieldset>
      </form>
    </div>
  </div>
</div>
<script>
    function login()
    {
      var username = $('#user_username').val();// documentGetElementById
      var userpassword = $('#user_password').val();

      if(username == '' || userpassword == '')
      {
        bootbox.alert("<span style='color:black'>Please enter your username and password!</span>");
        return false;
      }

       $.ajax(
        {
          url: "login.php", 
          async: true, 
          method: 'POST',
          dataType: 'json',
          data: 'action=login&username='+username+'&userpassword='+userpassword,
          success: function(result){
            if(result)
            {
              bootbox.alert("<span style='color:black'>Success, will redirect you to main page</span>");
              setTimeout(function()
              { 
                window.location = 'index.php';
              }, 2000);
            }
            else
            {
              bootbox.alert("<span style='color:black'>Invalid username or password!</span>");
            }
          }
      });
    }
  </script>
</body>
</html>



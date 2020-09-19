<?php
include './system.php';


if(isset($_POST['action']))
{
  switch($_POST['action'])
  {
    case 'sendEmail':
      echo json_encode(sendEmail());
      exit;
    break;
  }
}

function sendEmail()
{
  global $db, $mail;
  $email = $db->real_escape_string($_POST['email']);
  $username = $db->real_escape_string($_POST['username']);

  $sql = "SELECT * FROM daycare_user WHERE user_username = '$username' AND user_email = '$email'";
  $query = $db->query($sql);
  $row = $query->fetch_array(MYSQLI_ASSOC);

  $result = array(
    'status' => 1,
    'msg' => ''
  );

  if(!$row)
  {
    $result['status'] = 0;
    $result['msg'] = 'Username and Email not found';
    return $result;
  }

  $newpassword = generateRandomName(6);

  $sql = "UPDATE daycare_user SET user_password = AES_ENCRYPT('$newpassword', 'davidyap1997') 
  WHERE user_ID = '$row[user_ID]'";

  $db->query($sql);

  $emailContent =<<<HTML
    Your New Password is <strong>$newpassword</strong>
HTML;

    $emailTitle = 'Reset Password';

    
    $recipients = array(
      $email => $username
      );

    $mail->sendmailbymailgun($email, $username, $emailTitle, $emailContent);

    // $mail->setupRecipients($recipients);
    // $mail->sendEmail($emailTitle, $emailContent);

  return $result;
}

?>


<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title>Foget Password</title>
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
          <legend>Forget Password ?</legend>
          <div class="form-group">
            <label for="user_username" class="control-label">Username:</label>
            <input type="text" id="user_username" class="form-control" name="user_username" autofocus>
          </div>
          <div class="form-group">
            <label for="user_email" class="control-label">Email:</label>
            <input type="text" id="user_email" class="form-control" name="user_email" autofocus>
          </div>
    		  <div class="A">
    		    <a href="javascript:void(0)" onclick="sendEmail();">Send Now</a>
    		  </div>
          <div class="A">
            <a href="login.php" onclick="">Login</a>
          </div>

        </fieldset>
      </form>
    </div>
  </div>
</div>
<script src="resource/js/jquery-3.2.1.min.js"></script>
<script src="resource/js/bootstrap.min.js"></script>
<script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
<script src='resource/jquery-ui/jquery-ui.min.js'></script>
<script src='resource/dataTable/datatables.min.js'></script>
<script>
function sendEmail()
{
  var email = $('#user_email').val();
  var username = $('#user_username').val();
  var data = 'action=sendEmail&email='+email+'&username='+username;

  simp_ajax('POST', data, 'resetps.php').done(function(r)
  {
    if(r.status)
    {
      bootbox.alert('<span style="color:black;">'+'Success!'+'</span>');
    }
    else
    {
      bootbox.alert('<span style="color:black;">'+r.msg+'</span>');
    }
  });
}
</script>
</body>
</html>
<?php
include './system.php';

if(!isset($_SESSION['uid']))
{
  header('location:login.php');
}
else if($_SESSION['permission'] != '1')
{
  header('location:index.php');
}

if(isset($_POST['action']))
{
  $action = $_POST['action'];
  switch($action)
  {
    case 'submitregister':
      echo json_encode(submitregister());
      exit;
      break;
  }
}


function submitregister()
{
  global $db;
  $name = stripcslashes($_POST['name']);
  $verifydoc = stripcslashes($_POST['verifydoc']);

  $name = $db->real_escape_string($name);
  $verifydoc = $db->real_escape_string($verifydoc);

  $result = array(
    'status' => 1,
    'msg' => 0,
  );

  $tablename = 'daycare_child';
  $key = 'child_ic';

  if(checkunique($tablename, $key, $verifydoc))
  {
    $result['status'] = 0;
    $result['msg'] = 'The IC or Birth Certificate is exists!';
    return $result;
  }

  $sql = "INSERT INTO $tablename(child_ic, child_fullname)
  values('$verifydoc', '$name')";

  if($db->query($sql))
  {
    $result['status'] = 1;
  }
  else
  {
    $result['status'] = 0;
    $result['msg'] = 'Error when insert data';
  }

  return $result;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <!-- This file has been downloaded from Bootsnipp.com. Enjoy! -->
  <title>Student Registration</title>
    <meta name="vie wport" content="width=device-width, initial-scale=1">
    <script src="resource/js/jquery-3.2.1.min.js"></script>
    <script src="resource/js/bootstrap.min.js"></script>
    <script src="resource/js/bootbox.min.js"></script>
    <link rel='stylesheet prefetch' href='resource/css/bootstrap.min.css'>
    <link rel="stylesheet" href="include/css/adminregister.css">
</head>
<body>
<div class="container">
  <h1 class="well">Registration Form</h1>
	<div class="col-lg-12 well">
  	<div class="row">
  		<form class="form" onsubmit="return false" id="adminregisterform">
  			<div class="col-lg-12">
  				<div class="row">
  					<div class="col-lg-6 form-group">
  						<label>Child's Name</label>
  						<input type="text" id="childname" placeholder="Enter Child's Name" class="form-control">
  					</div>
  				</div>	
  				<div class="row">
  					<div class="col-lg-6 form-group">
  						<label>Child's IC or Birth Certificate</label>
  						<input type="text" id="verifydoc"  class="form-control">
  					</div>		
  				</div>	
          <button type="button" class="btn btn-lg btn-info" onclick="submitRegisterStudent()">Submit</button>
        </div>	
  		</form> 
  	</div>
  </div>
</div>
<script src="include/js/customjs.js"></script>
<script>
      function submitRegisterStudent()
      {
        bootbox.confirm('Confirm Submit?', function(r)
        {
          if(r)
          {
            var name = $('#childname').val();
            var verifydoc = $('#verifydoc').val();

            if(name == '' || verifydoc == '')
            {
              bootbox.alert("Please enter child's name and IC to proceed!");
              return false;
            }

            $.ajax(
            {
              url: "adminregister.php", 
              async: true, 
              method: 'POST',
              dataType: 'json',
              data: 'action=submitregister&name='+name+'&verifydoc='+verifydoc,
              success: function(result){
                if(result['status'])
                {
                  var msg = "<a href='registration.php?name="+name+"&verifydoc="+verifydoc+"' target='_blank'>Register Now</a>";
                  bootbox.alert('Success! '+msg);
                  $('#adminregisterform')[0].reset();
                }
                else 
                {
                  bootbox.alert(result['msg']);
                }
              }
            });
          }
        });
      }
    </script>
</body>
</html>

<?php
include './system.php';
$action = '';

if(isset($_POST['action']))
{
	$action = $_POST['action'];
}

switch($action)
{
	case 'submitRegistration':
		echo json_encode(submitRegistration());
	exit;
	break;
}

function submitRegistration()
{
	global $db;
	$result = array(
		'status' => 1,
		'msg' => ''
	);

	$checkSubmitData = checkSubmitRegistration();

	if(!$checkSubmitData['status'])
	{
		return $checkSubmitData;
	}

	beginTransaction();

	$tableuser = 'daycare_user';
	$primarykeyuser = 'user_id';

	$user_password = $_POST['parent_userpassword'];

	$sql = "SELECT AES_ENCRYPT('$user_password', 'davidyap1997') AS ps";
	$query = $db->query($sql);
	$ps = $query->fetch_array(MYSQLI_ASSOC)['ps'];

	$arruser = array(
		'user_username' => $_POST['parent_username'],
		'user_password' => $ps,
		'user_email' => $_POST['parent_email'],
		'user_permission' => 0,
	);

	$result = insertRecord($tableuser, $arruser);

	if(!$result['status'])
	{
		$db->rollback();
		return $result;
	}

	$tableparent = 'daycare_parent';
	$primarykeyparent = 'parent_id';
	$user_id = $db->insert_id;

	$arrparent = array(
		'parent_name' => $_POST['parent_name'],
		'parent_phnum' => $_POST['parent_phnum'],
		'parent_email' => $_POST['parent_email'],
		'user_id' => $user_id,
	);

	$result = insertRecord($tableparent, $arrparent);

	if(!$result['status'])
	{
		$db->rollback();
		return $result;
	}

	$tablechild = 'daycare_child';
	$primarykeychild = 'child_id';
	$parent_id = $db->insert_id;

	$sql = "SELECT child_id FROM daycare_child 
	WHERE child_fullname = '$_POST[child_fullname]' 
	AND child_ic = '$_POST[child_ic]'";
	$query = $db->query($sql);

	$child_id = $query->fetch_array(MYSQLI_ASSOC)['child_id'];
	$child_pic = $_FILES['child_pic'];
	$child_picuri = convertImgToUri($child_pic);


	$arrchild = array(
		'parent_id' => $parent_id,
		'child_nickname' => $_POST['child_nickname'],
		'child_fullname' => $_POST['child_fullname'],
		'child_dob' => $_POST['child_dob'],
		'child_hobby' => $_POST['child_hobby'],
		'child_favfood' => $_POST['child_favfood'],
		'child_allergy' => $_POST['child_allergy'],
		'child_emerph' => $_POST['child_emerph'],
		'child_emername' => $_POST['child_emername'],
		'child_address' => $_POST['child_address'],
		'child_pic' => $child_picuri,
	);

	$result = updateRecord($tablechild, $arrchild, $primarykeychild, $child_id);

	if(!$result['status'])
	{
		$db->rollback();
		return $result;
	}
	commit();
	return $result;

}

function checkSubmitRegistration()
{

	$result = array(
		'status' => 1,
		'msg' => '',
	);

	return $result;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <!-- This file has been downloaded from Bootsnipp.com. Enjoy! -->
  <title>Registration</title>
    <meta name="vie wport" content="width=device-width, initial-scale=1">
    <script src="resource/js/jquery-3.2.1.min.js"></script>
    <script src="resource/js/bootstrap.min.js"></script>
    <script src="resource/js/bootbox.min.js"></script>
    <script src="include/js/customjs.js"></script>
    <link rel='stylesheet prefetch' href='resource/css/bootstrap.min.css'>
    <link rel="stylesheet" href="include/css/adminregister.css">
</head>
<body>
<form id="registrationform" onsubmit="return false">
	<div class="container">
	  <h1 class="well">Registration Form</h1>
		<div class="col-lg-12 well">
			<div class="row">
				<div class="col-lg-12">
					<div class="row">
						<div class="col-lg-12 text-center"><h3>Parent Information</h3></div>
						<div class="col-lg-6 form-group">
							<label>Parents Name</label>
							<input type="text" name="parent_name" placeholder="Enter Parent Name" class="form-control">
							<span></span>
						</div>
						<div class="form-group col-lg-6">
			        <label>Phone Number</label>
			        <input type="text" name="parent_phnum" placeholder="Enter Phone Number Here.." class="form-control">
			        <span></span>
			    </div>	
					</div>	
					<div class="row">
			    	<div class="form-group col-lg-6">
					    <label>Username</label>
					    <input type="email" name="parent_username" placeholder="Enter System Username Here.." class="form-control">
					    <span></span>
			    	</div>	
				    <div class="form-group col-lg-6">
					    <label>Email Address</label>
					    <input type="email" name="parent_email" placeholder="Enter Email Address Here.." class="form-control">
					    <span></span>
			    	</div>	
		    	</div>
					<div class="row">
						<div class="col-lg-6 form-group">
							<label>Password</label>
							<input type="password" name="parent_userpassword" placeholder="Enter Password" class="form-control" onchange="verifyPs(this);">
							<span></span>
						</div>		
						<div class="col-lg-6 form-group">
							<label>Confirm Password</label>
							<input type="password" name="parent_confirmuserpassword" placeholder="Confirm Password" class="form-control" onchange="verifyConfirmPs(this);">
							<span></span>
						</div>	
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-12 well">
			<div class="row">
				<div class="col-lg-12 text-center"><h3>Children Information</h3></div>
				<div class="col-lg-6">
					<div class="form-group">
						<label>Children Name</label>
						<input type="text" name="child_fullname" placeholder="Enter Children Name" class="form-control">
						<span></span>
					</div>
					<div class="form-group">
						<label>Children Nick Name</label>
						<input type="text" name="child_nickname" placeholder="Enter Children Nick Name" class="form-control">
						<span></span>
					</div>
					<div class="form-group">
						<label>Children IC or Birth Certificate</label>
					  <input type="text" name="child_ic" placeholder="Enter IC or Birth Certificate Here.." class="form-control">
					  <span></span>
				  </div>
				  <div class="form-group">
							<label>Emergency Contact Name</label>
							<input type="text" name="child_emername" placeholder="Enter Contact Name Here.." class="form-control">
							<span></span>
					</div>	
					<div class="form-group">
						<label>Emergency Contact Number</label>
						<input type="text" name="child_emerph" placeholder="Enter Phone Number Here.." class="form-control">
						<span></span>
					</div>	
				</div>
				<div class="col-lg-6">
					<img src="" id="child_picture" height="320px" width="320px" onclick="$('input[name=\'child_pic\']').trigger('click');">
					<input type="file" name="child_pic" class="form-control" accept="image/*" onchange="updatePicture(this);">
					<span></span>
				</div>
				<div class="col-lg-12">
				  <div class="form-group">
						<label>Address</label>
						<textarea placeholder="Enter Address Here.." name="child_address" rows="3" class="form-control"></textarea>
						<span></span>
					</div>			
					<div class="row">
						<div class="col-lg-4 form-group">
							<label>Hobi</label>
							<input type="text" name="child_hobby" placeholder="Enter Hobi Here.." class="form-control">
							<span></span>
						</div>	
						<div class="col-lg-4 form-group">
							<label>Favourite food</label>
							<input type="text" name="child_favfood" placeholder="Enter Favourite Food Here.." class="form-control">
							<span></span>
						</div>	
						<div class="col-lg-4 form-group">
							<label>Allergy</label>
							<input type="text" name="child_allergy" placeholder="Enter Child Allergy Here.." class="form-control">
							<span></span>
						</div>		
					</div>			
				  <button type="button" class="btn btn-lg btn-info" onclick="checkBeforeSubmit();">Submit</button>					
				</div> 
			</div>
		</div>
	</div>
</form>
<script>
	var requiredfieldarr = {
		'parent_name' : 'Parent Name',
	 	'parent_phnum' : 'Phone Number',
	 	'parent_username' : 'Username',
		'parent_userpassword' : 'User Password',
	 	'parent_confirmuserpassword' : 'Confirm Password',
	 	'child_fullname' : 'Children Name',
		'child_ic' : 'Children IC or Birth Certificate',
		'child_emername' : 'Emergency Contact Name',
		'child_emerph' : 'Emergency Contact Number',
		'child_address' : 'Address',
		'child_pic': 'Children Picture',
	} ;
  $(function()
  {
  	$.each(requiredfieldarr, function(column, name)
  	{
  		$('input[name="'+column+'"], textarea[name="'+column+'"]').prev().html(function()
  		{
  			return $(this).html()+'<span style="padding-left:5px; color:red">*</span>';
  		});
  	});
  });
  function updatePicture(data)
  {
  	var value = $(data).val();
  	if(value == '')
  	{
  		$('#child_picture').attr({'src': ''});
  		return;
  	}

  	convertImageToBase64(data, function(r)
		{
			if(r != 'false')
			{
				$('#child_picture').attr({'src': r});
			}
			else
			{
				$(data).empty();
			}
		});
  }

  function verifyPs(data)
  {
  	var value = $(data).val();
  	if(!checkPasswordValid(value))
  	{
  		$(data).next().html('Password must contain 8 characters, at least one letter and one number!');
  		$(data).css('border-color', 'red');
  	}
  	else
  	{
  		$(data).next().empty();
  		$(data).attr({'style': ''});
  	}
  	$(data).focus();
  }

  function verifyConfirmPs(data)
  {
  	var confirmvalue = $(data).val();
  	var value = $('input[name="parent_userpassword"]').val();

  	if(confirmvalue != value)
  	{
  		$(data).next().html('Confirm Password must same with the Password');
  		$(data).css({'border-color':'red'});
  	}
  	else
  	{
  		$(data).next().empty();
  		$(data).attr({'style': ''});
  	}
  	$(data).focus();
  }

  function checkBeforeSubmit()
  {
  	var check = 1;
  	var i = 0;

  	$.each(requiredfieldarr, function(column, name)
  	{
  		var field = 'input[name="'+column+'"], textarea[name="'+column+'"]';
  		var value = $(field).val();

  		if(value == '')
  		{
  			check = 0;
  			$(field).next().html(name+' cannot empty!');
  			$(field).css({'border-color':'red'});
  			if(i == '0')
  			{
  				$(field).focus();
  			}
  			i++;
  		}
  		else
  		{
  			$(field).next().html('');
  			$(field).attr({'style': ''});
  		}
  	});

  	// if(check)
  	{
  		submitRegistration();
  	}
  }

  function submitRegistration()
  {
  	var formdata = new FormData($('#registrationform')[0]);
  	formdata.append('action', 'submitRegistration');

  	data_ajax('POST', formdata, 'registration.php').done(function(r)
  	{
  		if(r.status)
  		{
  			bootbox.alert("Success Register, will redirect you to login page");
  			setTimeout(function()
		    { 
		      window.location = 'login.php';
		    }, 2000);
  		}
  		else
  		{
  			bootbox.alert(r.msg);
  		}
  	});
  }
</script>
</body>
</html>

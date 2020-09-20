<?php
include './system.php';

if(!isset($_SESSION['uid']))
{
  header('location:login.php');
}

if(isset($_POST['action']))
{
  switch($_POST['action'])
  {
    case 'updateParentProfile':
      echo json_encode(updateParentProfile());
      exit;
    break;

    case 'updateChildProfile':
      echo json_encode(updateChildProfile());
      exit;
    break;
  }
}

function updateParentProfile()
{
  $parent_id = $_POST['parent_id'];
  $parent_name = $_POST['parent_name'];
  $parent_phnum = $_POST['parent_phnum'];

  $arr = array(
    'parent_name' => $parent_name,
    'parent_phnum' => $parent_phnum,
  );

  global $db;

  updateRecord('daycare_parent', $arr, 'parent_id', $parent_id);

  return true;
}

function updateChildProfile()
{
  global $db;
  $childarr = $_POST;
  $childimg = $_FILES['child_pic'];
  $child_id = $_POST['child_id'];
  $parent_id = $_POST['parent_id'];

  unset($childarr['action']);
  unset($childarr['child_id']);
  unset($childarr['parent_id']);
  $tablename = 'daycare_child';
  $primarykey = 'child_id';
  
  $result = array(
    'status' => 1,
    'msg' => ''
  );

  if($child_id != 'new')
  {

    if($childimg['size'] > 0)
    {
      $childarr['child_pic'] =  convertImgToUri($childimg);
    }
    $result = updateRecord($tablename, $childarr, $primarykey, $child_id);

    return $result;
  }
  else
  {
    $child_ic = $childarr['child_ic'];
    $childarr['parent_id'] = $parent_id;

    $sql = "SELECT * FROM daycare_child WHERE parent_id = '0' AND child_ic = '$child_ic'";

    $query = $db->query($sql);

    if(!$row = $query->fetch_array(MYSQLI_ASSOC))
    {
      $result['msg'] = 'Child is Not Found';
      $result['status'] = 0;

      return $result;
    }

    $child_id = $row['child_id'];

    if($childimg['size'] > 0)
    {
      $childarr['child_pic'] =  convertImgToUri($childimg);
    }
    $result = updateRecord($tablename, $childarr, $primarykey, $child_id);

    $result['html'] = ChildProfileHTML($child_id);

    return $result;
  }
}

function ChildProfileHTML($child_id)
{
  global $db;
  $sql = "SELECT * FROM daycare_child WHERE child_id = '$child_id'";
    $querychild = $db->query($sql);

    $childrow = $querychild->fetch_array(MYSQLI_ASSOC);
    
      $child_id = $childrow['child_id'];
      $childimg = $childrow['child_pic'];
      $child_nickname = $childrow['child_nickname'];
      $child_fullname = $childrow['child_fullname'];
      $child_ic = $childrow['child_ic'];
      $child_dob = $childrow['child_dob'];
      $child_hobby = $childrow['child_hobby'];
      $child_favfood = $childrow['child_favfood'];
      $child_allergy = $childrow['child_allergy'];
      $child_emerph = $childrow['child_emerph'];
      $child_emername = $childrow['child_emername'];
      $child_address = $childrow['child_address'];
      $parent_id = $childrow['parent_id'];
      return <<<HTML
      

<h4>$child_fullname</h4>
<form id="childform_{$child_id}" onsubmit="return false">

  <div class="col-lg-4 form-group">
    <img id="image_{$child_id}" style="max-width:250px; max-height:250px;border-radius: 50%" src="$childimg"/>
    <input type="file" class="form-control" name="child_pic" onchange="updatePicture(this, '{$child_id}')">
    <br/>
    <button class="btn btn-primary" onclick="updateChildProfile('$child_id', '$parent_id')">Update Child Profile</button>
  </div>
  <div class="col-lg-8">
    <div class="row">
      <div class="col-lg-6 form-group">
        <label>Name</label>
        <input class="form-control" name="child_fullname" value="$child_fullname">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Nick Name</label>
        <input class="form-control" name="child_nickname" value="$child_nickname">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>IC or Birth Certificate</label>
        <input class="form-control" name="child_ic" value="$child_ic">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Date of birth</label>
        <input type="date" class="form-control" name="child_dob" value="$child_dob">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Emergency Contact Name</label>
        <input class="form-control" name="child_emername" value="$child_emername">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Emergency Contact Number</label>
        <input class="form-control" name="child_emerph" value="$child_emerph">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Hobby</label>
        <input class="form-control" name="child_hobby" value="$child_hobby">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Favourite Food</label>
        <input class="form-control" name="child_favfood" value="$child_favfood">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Allergy</label>
        <input class="form-control" name="child_allergy" value="$child_allergy">
        <span></span>
      </div>
      <div class="col-lg-6 form-group">
        <label>Address</label>
        <textarea class="form-control" name="child_address" >$child_address</textarea>
        <span></span>
      </div>
    </div>
  </div>
</form>
<br/>

HTML;


}
  $user_id = $_SESSION['uid'];

  $sql = "SELECT * FROM daycare_parent WHERE user_id = '$user_id'";

  $query = $db->query($sql);

  $parentArr = $query->fetch_array(MYSQLI_ASSOC);

  $parent_name = $parentArr['parent_name'];
  $parent_phnum = $parentArr['parent_phnum'];
  $parent_email = $parentArr['parent_email'];
  $parent_id = $parentArr['parent_id'];

?>

<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title>Profile</title>
  
  

<link href="resource/css/bootstrap.min.css" rel="stylesheet" />
<link href="resource/css/default.css" rel="stylesheet" type="text/css" media="all" />
<link href="http://fonts.googleapis.com/css?family=Chivo:400,900" rel="stylesheet" />
<link href="resource/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link href='resource/jquery-ui/jquery-ui.min.css' rel="stylesheet" type="text/css"/>
<link href='resource/dataTable/datatables.min.css' rel="stylesheet" type="text'css"/>


  
</head>

<div id="menu-wrapper">
    <div id="menu" class="container">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="Payment.php">Payments</a></li>
        <li><a href="foodschedule.php">Food</a></li>
        <li><a href="timetable.php">Timetable</a></li>
        <li><a href="result.php">Gallery</a></li>
        <?php 

        if($_SESSION['permission'] == '1')
        {
          echo '<li><a href="adminregister.php">Register</a></li>';
        }
        ?>        
        <!-- <li><a href="javascript:void(0)" onclick="changePermission();">Permission </a></li> -->
        <li class="current_page_item"><a href="newprofile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>

<body>
<div id="wrapper">
  <div id="page">
    <div class="container">
      <h1>Profile</h1>
      <br/>

      <div class="col-lg-3">
        <ul class="nav nav-pills nav-stacked">
          <li class="active"><a data-toggle="tab" href="#home"><i class="glyphicon glyphicon-user" style="padding-right: 5px"></i>Profile</a></li>
          <li><a data-toggle="tab" href="#menu1"><i class="glyphicon glyphicon-heart" style="padding-right: 5px"></i>Child</a></li>
          <li><a data-toggle="tab" href="#menu2"><i class="glyphicon glyphicon-plus" style="padding-right: 5px"></i>Add Child</a></li>
        </ul>
      </div>

      <div class="col-lg-9">
        <div class="tab-content">
          <div id="home" class="tab-pane fade in active">
            <h3>Parent Profile</h3>
            <br/>
            <div class="col-lg-4 form-group">
              <label>Parent Name</label>
              <input type="text" name="parent_name" placeholder="Enter Parent Name" class="form-control" value="<?php echo $parent_name;?>">
              <span></span>
            </div>
            <div class="col-lg-4 form-group">
              <label>Phone Number</label>
              <input type="text" name="parent_phnum" placeholder="Enter Phone Number" class="form-control" value="<?php echo $parent_phnum;?>">
              <span></span>
            </div>
            <div class="col-lg-12">
              <div class='row'>
                <div class="col-lg-2">
                  <button class="btn btn-primary" onclick="updateParentProfile('<?php echo $parent_id;?>')">Update Parent Profile</button>
                </div>
              </div>
            </div>
          </div>

          <div id="menu1" class="tab-pane fade">
            <h3>Child(s) Profile</h3>
            <br/>

<?php

$sql = "SELECT * FROM daycare_child WHERE parent_id = '$parent_id'";
$querychild = $db->query($sql);

while($childrow = $querychild->fetch_array(MYSQLI_ASSOC))
{
  echo ChildProfileHTML($childrow['child_id']);
}

?>
            <div id="beforebox"></div>
          </div>
          <div id="menu2" class="tab-pane fade">
            <h3>Add Child</h3>
            <br/>
            <form id="childform_new" onsubmit="return false">
              <div class="col-lg-4 form-group">
                <img id="image_new" style="max-width:250px; max-height:250px;border-radius: 50%"/>
                <input type="file" class="form-control" name="child_pic" onchange="updatePicture(this, 'new')">
                <br/>
                <button class="btn btn-primary" onclick="updateChildProfile('new', '<?php echo $parent_id ?>')">Add Child Profile</button>
              </div>
              <div class="col-lg-8">
                <div class="row">
                  <div class="col-lg-6 form-group">
                    <label>Name</label>
                    <input class="form-control" name="child_fullname" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Nick Name</label>
                    <input class="form-control" name="child_nickname" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>IC or Birth Certificate</label>
                    <input class="form-control" name="child_ic" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Date of birth</label>
                    <input type="date" class="form-control" name="child_dob" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Emergency Contact Name</label>
                    <input class="form-control" name="child_emername" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Emergency Contact Number</label>
                    <input class="form-control" name="child_emerph" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Hobby</label>
                    <input class="form-control" name="child_hobby" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Favourite Food</label>
                    <input class="form-control" name="child_favfood" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Allergy</label>
                    <input class="form-control" name="child_allergy" value="">
                    <span></span>
                  </div>
                  <div class="col-lg-6 form-group">
                    <label>Address</label>
                    <textarea class="form-control" name="child_address" ></textarea>
                    <span></span>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>


    </div>
  </div>
</div>
<script src="resource/js/jquery-3.2.1.min.js"></script>
<script src="resource/js/bootstrap.min.js"></script>
<script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
<script src='resource/jquery-ui/jquery-ui.min.js'></script>
<script>
function updatePicture(data, child_id)
  {
    var value = $(data).val();
    if(value == '')
    {
      $('#image_'+child_id).attr({'src': ''});
      return;
    }

    convertImageToBase64(data, function(r)
    {
      if(r != 'false')
      {
        $('#image_'+child_id).attr({'src': r});
      }
      else
      {
        $(data).empty();
      }
    });
  }

function updateParentProfile(parent_id)
{
  var parent_name = $('input[name="parent_name"]').val();
  var parent_phnum = $('input[name="parent_phnum"]').val();

  var data = 'action=updateParentProfile&parent_id='+parent_id+'&parent_name='+parent_name
  +'&parent_phnum='+parent_phnum;

  simp_ajax('POST', data , 'newprofile.php').done(function(r)
  {
    if(r)
    {
      bootbox.alert('Success!');
    }
  });
}

function updateChildProfile(child_id, parent_id)
{
  var formdata = new FormData($('#childform_'+child_id)[0]);
  formdata.append('action', 'updateChildProfile');
  formdata.append('child_id', child_id);
  formdata.append('parent_id', parent_id);

  data_ajax('POST', formdata, 'newprofile.php').done(function(r)
  {
    if(r.status)
    {
      if(child_id == 'new')
      {
        $('#beforebox').append(r.html);
        $('#childform_new')[0].reset();
        $('#childform_new').find('input[name="child_pic"]').val('');
        $('#image_new').attr('src', '');
      }
      
      bootbox.alert('Success!');

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

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
  $parent_email = $_POST['parent_email'];

  $arr = array(
    'parent_name' => $parent_name,
    'parent_phnum' => $parent_phnum,
    'parent_email' => $parent_email
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
      return <<<HTML
      <div class="additional-block">
    <form id="childform_{$child_id}" onsubmit="return false">
  <div class="box-container">

      <div class="image-container">

        <img id="image_$child_id" src="$childimg" style="max-width:250px; max-height:300px;"/>

        <div class="item">
        <div class="label">
          &nbsp
        </div>
        <div class="value">
          <input type="file" name="child_pic" onchange="updatePicture(this, '$child_id')">
        </div>
      </div> 
      </div>  

  </div>


    <h2>
      Children Profile
    </h2>
    
    <div class="address-details">
      <div class="item">
        <div class="label">
          Name
        </div>
        <div class="value">
          <input class="form-control" name="child_fullname" value="$child_fullname">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Nick Name
        </div>
        <div class="value">
          <input class="form-control" name="child_nickname" value="$child_nickname">
        </div>
      </div>

      <div class="item">
        <div class="label">
          IC or Birth Certificate
        </div>
        <div class="value">
          <input class="form-control" name="child_ic" value="$child_ic">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Date of birth
        </div>
        <div class="value">
          <input type="date" class="form-control" name="child_dob" value="$child_dob">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Emergency Contact Name
        </div>
        <div class="value">
          <input class="form-control" name="child_emername" value="$child_emername">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Emergency Contact Number
        </div>
        <div class="value">
          <input class="form-control" name="child_emerph" value="$child_emerph">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Address
        </div>
        <div class="value">
          <textarea class="form-control" name="child_address" >$child_address</textarea>
        </div>
      </div>

      <div class="item">
        <div class="label">
         Hobby
        </div>
        <div class="value">
          <input class="form-control" name="child_hobby" value="$child_hobby">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Favourite Food
        </div>
        <div class="value">
          <input class="form-control" name="child_favfood" value="$child_favfood">
        </div>
      </div>
      
      <div class="item">
        <div class="label">
         Allergy
        </div>
        <div class="value">
          <input class="form-control" name="child_allergy" value="$child_allergy">
        </div>
      </div>

      <div class="item">
        <div class="label">
          &nbsp
        </div>
        <div class="value">
          <button onclick="updateChildProfile('$child_id', '$parent_id')">Update Child Profile</button>
        </div>
      </div>
   </div>
   </form>
  </div>
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
  <title>Profiles</title>
  
  

<link href="resource/css/default.css" rel="stylesheet" type="text/css" media="all" />
<link href="http://fonts.googleapis.com/css?family=Chivo:400,900" rel="stylesheet" />
<link href="resource/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link rel="stylesheet" href="resource/css/profile.css">



  
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
        <li class="current_page_item"><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>

<body>
  <div class="wrapper">
  <div class="additional-block estateinfo-block">

  
  <div class="additional-block">
    <h2>
      My Profile
    </h2>
    <div class="address-details">
      <div class="item">
        <div class="label">
          Name
        </div>
        <div class="value">
          <input class="form-control" name="parent_name" value="
<?php
  echo $parent_name;

?>
">
        </div>
      </div>
      <div class="item">
        <div class="label">
          Number
        </div>
        <div class="value">
          <input class="form-control" name="parent_phnum" value="
<?php
  echo $parent_phnum;

?>
">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Email Address
        </div>
        <div class="value">
                    <input class="form-control" name="parent_email" value="
<?php
  echo $parent_email;

?>
">
        </div>
      </div>
      <div class="item">
        <div class="label">
          &nbsp
        </div>
        <div class="value">
          <button onclick="updateParentProfile('<?php echo $parent_id;?>')">Update Parent Profile</button>
        </div>
      </div>
  </div>
    </div>
  </div>

  <?php
    $sql = "SELECT * FROM daycare_child WHERE parent_id = '$parent_id'";
    $querychild = $db->query($sql);

    while($childrow = $querychild->fetch_array(MYSQLI_ASSOC))
    {
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
      echo <<<HTML
      <div class="additional-block">
    <form id="childform_{$child_id}" onsubmit="return false">
  <div class="box-container">

      <div class="image-container">

        <img id="image_$child_id" src="$childimg" style="max-width:250px; max-height:300px;"/>

        <div class="item">
        <div class="label">
          &nbsp
        </div>
        <div class="value">
          <input type="file" name="child_pic" onchange="updatePicture(this, '$child_id')">
        </div>
      </div> 
      </div>  

  </div>


    <h2>
      Children Profile
    </h2>
    
    <div class="address-details">
      <div class="item">
        <div class="label">
          Name
        </div>
        <div class="value">
          <input class="form-control" name="child_fullname" value="$child_fullname">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Nick Name
        </div>
        <div class="value">
          <input class="form-control" name="child_nickname" value="$child_nickname">
        </div>
      </div>

      <div class="item">
        <div class="label">
          IC or Birth Certificate
        </div>
        <div class="value">
          <input class="form-control" name="child_ic" value="$child_ic">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Date of birth
        </div>
        <div class="value">
          <input type="date" class="form-control" name="child_dob" value="$child_dob">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Emergency Contact Name
        </div>
        <div class="value">
          <input class="form-control" name="child_emername" value="$child_emername">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Emergency Contact Number
        </div>
        <div class="value">
          <input class="form-control" name="child_emerph" value="$child_emerph">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Address
        </div>
        <div class="value">
          <textarea class="form-control" name="child_address" >$child_address</textarea>
        </div>
      </div>

      <div class="item">
        <div class="label">
         Hobby
        </div>
        <div class="value">
          <input class="form-control" name="child_hobby" value="$child_hobby">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Favourite Food
        </div>
        <div class="value">
          <input class="form-control" name="child_favfood" value="$child_favfood">
        </div>
      </div>
      
      <div class="item">
        <div class="label">
         Allergy
        </div>
        <div class="value">
          <input class="form-control" name="child_allergy" value="$child_allergy">
        </div>
      </div>

      <div class="item">
        <div class="label">
          &nbsp
        </div>
        <div class="value">
          <button onclick="updateChildProfile('$child_id', '$parent_id')">Update Child Profile</button>
        </div>
      </div>
   </div>
   </form>
  </div>
HTML;
    }


  ?>
  <div id="beforebox">
  </div>
  <div class="additional-block" style="background-color: #80ffaa">
    <form id="childform_new" onsubmit="return false">
  <div class="box-container">

      <div class="image-container">
        <img id="image_new" src="" style="max-width:250px; max-height:300px;"/>

        <div class="item">
        <div class="label">
          &nbsp
        </div>
        <div class="value">
          <input type="file" name="child_pic" onchange="updatePicture(this, 'new')">
        </div>
      </div> 
      </div>  

  </div>


    <h2>
      Children Profile
    </h2>
    
    <div class="address-details">
      <div class="item">
        <div class="label">
          Name
        </div>
        <div class="value">
          <input class="form-control" name="child_fullname" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Nick Name
        </div>
        <div class="value">
          <input class="form-control" name="child_nickname" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
          IC or Birth Certificate
        </div>
        <div class="value">
          <input class="form-control" name="child_ic" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Date of birth
        </div>
        <div class="value">
          <input type="date" class="form-control" name="child_dob" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
          Emergency Contact Name
        </div>
        <div class="value">
          <input class="form-control" name="child_emername" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Emergency Contact Number
        </div>
        <div class="value">
          <input class="form-control" name="child_emerph" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Address
        </div>
        <div class="value">
          <textarea class="form-control" name="child_address" ></textarea>
        </div>
      </div>

      <div class="item">
        <div class="label">
         Hobby
        </div>
        <div class="value">
          <input class="form-control" name="child_hobby" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
         Favourite Food
        </div>
        <div class="value">
          <input class="form-control" name="child_favfood" value="">
        </div>
      </div>
      
      <div class="item">
        <div class="label">
         Allergy
        </div>
        <div class="value">
          <input class="form-control" name="child_allergy" value="">
        </div>
      </div>

      <div class="item">
        <div class="label">
          &nbsp
        </div>
        <div class="value">
          <button onclick="updateChildProfile('new', '<?php echo $parent_id ?>')">Add Child Profile</button>
        </div>
      </div>
   </div>
  </form>
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
  var parent_email = $('input[name="parent_email"]').val();

  var data = 'action=updateParentProfile&parent_id='+parent_id+'&parent_name='+parent_name
  +'&parent_phnum='+parent_phnum+'&parent_email='+parent_email;

  simp_ajax('POST', data , 'profile.php').done(function(r)
  {
    if(r)
    {
      alert('Success!');
    }
  });
}

function updateChildProfile(child_id, parent_id)
{
  var formdata = new FormData($('#childform_'+child_id)[0]);
  formdata.append('action', 'updateChildProfile');
  formdata.append('child_id', child_id);
  formdata.append('parent_id', parent_id);

  data_ajax('POST', formdata, 'profile.php').done(function(r)
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
      
      alert('Success!');

    }
    else
    {
      alert(r.msg);
    }
  });

}
</script>


</body>
</html>

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
    case 'addNewGallery':
      echo json_encode(addNewGallery());
      exit;
    break;

    case 'searchGallery':
      echo json_encode(searchGallery());
      exit;
    break;

    case 'deleteImage':
      echo json_encode(deleteImage());
      exit;
    break;

    case 'submitImages':
      echo json_encode(submitImages());
      exit;
    break;
  }
}

function addNewGallery()
{
  global $db;

  $gallery_title = $db->real_escape_string($_POST['gallery_title']);

  $sql = "SELECT * FROM daycare_gallery WHERE gallery_title = '$gallery_title'";

  $query = $db->query($sql);

  $row = $query->fetch_array(MYSQLI_ASSOC);
  
  $result = array(
    'status' => 1,
    'msg' => '',
  );

  if(!empty($row))
  {
    $result['status'] = 0;
    $result['msg'] = 'This title has been used';
    return $result;
  }

  $arr = array(
    'gallery_title' => $gallery_title,
    'gallery_date' => date('Y-m-d'),
  );

  $tablename = 'daycare_gallery';
  $primarykey = 'gallery_id';

  insertRecord($tablename, $arr);

  $pk = $db->insert_id;

  $result['value'] = $pk;
  $result['html'] = "<option value='$pk'>$gallery_title</option>";

  return $result;
}

function searchGallery()
{
  global $db;

  $gallery_id = $_POST['gallery_id'];

  $sql = "SELECT * FROM daycare_image WHERE gallery_id = '$gallery_id'";

  $query = $db->query($sql);
  
  $html = "<br/>";

  $i = 0;
  while($arr = $query->fetch_array(MYSQLI_ASSOC))
  {
    $html .=<<<HTML
    <div class="imageWrapper col-lg-3" style="position: relative; ">
     <img src="{$arr[image_uri]} " style="position: relative; z-index: 1; width: 100%; height:300px;" />
HTML;
     if($_SESSION['permission'] == '1')
     {
        $html .=<<<HTML
        <h2 class="glyphicon glyphicon-remove text text-danger" style="position: absolute;left:85%; top: 5%;z-index: 10;" title="Delete Image" onmouseover="$(this).css('cursor', 'pointer')" onclick="deleteImage('$arr[image_id]')"></h2>
HTML;
     }
     $html .=<<<HTML
         </div>
HTML;
    $i ++;

    $html .= ($i % 4 == 0)?'<div class="col-lg-12"><br/></div>':'';
  }

  return $html;
}

function deleteImage()
{
  global $db;
  $image_id = $_POST['image_id'];

  $sql = "SELECT image_uri FROM daycare_image WHERE image_id = '$image_id'";

  $query = $db->query($sql);

  $row = $query->fetch_array(MYSQLI_ASSOC);

  $imagepath = urldecode(str_replace('http://localhost/fyp-daycare/', '', $row['image_uri']));

  unlink($imagepath);

  $sql = "DELETE FROM daycare_image WHERE image_id = '$image_id'";

  $query = $db->query($sql);

  return true;
}

function submitImages()
{
  global $db;
  $files = $_FILES['image']['name'];
  $gallery_id = $_POST['gallery_id'];
  $tablename = 'daycare_image';

  $sql = "SELECT gallery_title FROM daycare_gallery WHERE gallery_id = '$gallery_id'";

  $query = $db->query($sql);

  $gallery_title = $query->fetch_array(MYSQLI_ASSOC)['gallery_title'];

  foreach($files AS $i => $name)
  {
    $arr = array(
      'name' => $name,
      'type' => $_FILES['image']['type'][$i],
      'tmp_name' => $_FILES['image']['tmp_name'][$i],
      'size' => $_FILES['image']['size'][$i],
    );

    $move = moveUploadFile($arr, $gallery_title);

    if($move['status'])
    {
      $arrinsert = array(
        'image_uri' => $move['urlpath'],
        'gallery_id' => $gallery_id,
      );
      insertRecord($tablename, $arrinsert);
    }
  }

  return true;
}

?>


<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="resource/css/bootstrap.min.css" rel="stylesheet" />
<link href="resource/css/default.css" rel="stylesheet" type="text/css" media="all" />
<link href="http://fonts.googleapis.com/css?family=Chivo:400,900" rel="stylesheet" />
<link href="resource/css/fonts.css" rel="stylesheet" type="text/css" media="all" />


</head>
<body>
<div id="wrapper">
    <div id="menu-wrapper">
    <div id="menu" class="container">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="Payment.php">Payments</a></li>
        <li><a href="foodschedule.php">Food</a></li>
        <li><a href="timetable.php">Timetable</a></li>
        <li class="current_page_item"><a href="gallery.php">Gallery</a></li>
        <?php 

        if($_SESSION['permission'] == '1')
        {
          echo '<li><a href="adminregister.php">Register</a></li>';
        }
        ?>        
        <!-- <li><a href="javascript:void(0)" onclick="changePermission();">Permission </a></li> -->
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
  <div id="page" style="min-height: 700px;">
    <div class="form-group col-lg-4">
      <label>Gallery Title</label>
      <select id="gallery_title" class="form-control" >
<?php
  $sql = "SELECT * FROM daycare_gallery ORDER BY gallery_id DESC";

  $query = $db->query($sql);
  $selected = 'selected';

  while($row = $query->fetch_array(MYSQLI_ASSOC))
  {
    echo "<option value='$row[gallery_id]' $selected>$row[gallery_title]</option>";
    $selected = '';
  }

  echo "<option value='addnew' id='addNewGallery'>- Add New -</option>";
?>
      </select>
    </div>
<?php

if($_SESSION['permission'] == '1')
{
  echo <<<HTML
    <div class="form-group col-lg-4">
        <label>Images</label>
        <input type="file" multiple class="form-control" accept="Image/*" id="gallery_image">
      </div>
      <div class="col-lg-2 form-group">
        <label class="col-lg-12">&nbsp</label>
        <button class="btn btn-info" onclick="submitImages()">Add Images</button>
    </div>
HTML;
}
?>
    <div  class="col-lg-12" id="galleryBox">

    </div>
  </div>
</div>

<script src="resource/js/jquery-3.2.1.min.js"></script>
<script src="resource/js/bootstrap.min.js"></script>
<script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
<script>
$(function()
{
  searchGallery();
});
var previous = '';
$(document).on('change', '#gallery_title', function()
{
  if($(this).val() == 'addnew')
  {
    addNewGallery();
  }
  else
  {
    searchGallery();
  }
});

$(document).on('focus', '#gallery_title', function()
{
  previous = $(this).val();

});


function searchGallery()
{
  var gallery_id = $('#gallery_title').val();
  var data = 'action=searchGallery&gallery_id='+gallery_id;

  simp_ajax('POST', data, 'gallery.php').done(function(r)
  {
    $('#galleryBox').html(r);
  });
}
</script>

<?php

if($_SESSION['permission'] == '1')
{
  echo <<<HTML
<script>
function addNewGallery()
{
  $('#gallery_title').val(previous);

  bootbox.prompt('Unique Gallery Name', function(title, test)
  { 
    if(title != '' && title != null)
    {
      bootbox.confirm('Confirm add new gallery? It cannot be change once added', function(rs)
      {
        if(rs)
        {
          var data = 'action=addNewGallery&gallery_title='+title;

          simp_ajax('POST', data, 'gallery.php').done(function(r)
          {
            if(r.status)
            {
              $('#gallery_title').prepend(r.html);
              $('#gallery_title').val(r.value);
            }
            else
            {
              bootbox.alert(r.msg);
            }
          });
        }
      });
    }
  });

}

function deleteImage(image_id)
{
  bootbox.confirm('Confirm delete?', function(rs)
  {
    if(rs)
    {
      var data = 'action=deleteImage&image_id='+image_id;

      simp_ajax('POST', data, 'gallery.php').done(function(r)
      {
        searchGallery();
      });
    }
  })
  
}

function submitImages()
{
  var formdata = new FormData;
  var j = 0;

  $.each($('#gallery_image')[0].files, function(i, obj)
  {
    formdata.append('image['+i+']', obj);
    ++j;
  });

  if(j == 0)
  {
    return;
  }

  formdata.append('gallery_id', $('#gallery_title').val());
  formdata.append('action', 'submitImages');
  data_ajax('POST', formdata, 'gallery.php').done(function(r)
  {
    $('#gallery_image').val('');
    bootbox.alert('Success');
    searchGallery();
  });
}

</script>

HTML;
}

?>
</body>
</html>

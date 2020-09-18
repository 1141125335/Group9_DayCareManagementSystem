
<?php
include './system.php';

if(!isset($_SESSION['uid']))
{
  header('location:login.php');
}

if(!isset($_POST['action']))
{
  getFoodSchedule();
}

switch($_POST['action'])
{
  case 'submitFoodSchedule':
    echo json_encode(submitFoodSchedule());
    exit;
  break;

  case 'removeFoodSchedule':
    echo json_encode(removeFoodSchedule());
    exit;
  break;
}

function submitFoodSchedule()
{
  global $db;

  $tablename = 'daycare_foodschedule';
  $primarykey = 'foodschedule_id';


  $arr = array(
    'foodschedule_title' => $_POST['foodschedule_title'],
    'foodschedule_desc' => $_POST['foodschedule_desc'],
    'foodschedule_day' => $_POST['foodschedule_day'],
    'foodtitle_id' => $_POST['foodtitle_id'],
    'isactive' => 1,
  );

  if($_POST['foodschedule_id'] != '0' && $_POST['foodschedule_id'] != '')
  {
    $arr['foodschedule_id'] = $_POST['foodschedule_id'];
    $result = updateRecord($tablename, $arr, $primarykey, $_POST['foodschedule_id']);
  }
  else
  {
    $result = insertRecord($tablename, $arr);
    $arr['foodschedule_id'] = $db->insert_id;
  }

  $result['data'] = $arr;

  return $result;}

function removeFoodSchedule()
{

}

function getFoodSchedule()
{
  global $db;

  $sql = "SELECT fc.*, ftt.foodtitle_title 
  FROM daycare_foodschedule fc 
  LEFT JOIN daycare_foodtitle ftt ON ftt.foodtitle_id = fc.foodtitle_id 
  WHERE isactive = '1'";

  $query = $db->query($sql);

  $result = array();
  
  while($row = $query->fetch_array(MYSQLI_ASSOC))
  {
    $result[$row['foodschedule_id']]['foodschedule_id'] = $row['foodschedule_id'];
    $result[$row['foodschedule_id']]['foodschedule_title'] = $row['foodschedule_title'];
    $result[$row['foodschedule_id']]['foodschedule_desc'] = $row['foodschedule_desc'];
    $result[$row['foodschedule_id']]['foodschedule_day'] = $row['foodschedule_day'];
    $result[$row['foodschedule_id']]['foodtitle_id'] = $row['foodtitle_id'];
    $result[$row['foodschedule_id']]['foodtitle_title'] = $row['foodtitle_title'];
  }

  $result = json_encode($result);

  echo <<<HTML
    <script>
      var foodScheduleArr = {$result};
    </script>
HTML;
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
				<li class="current_page_item"><a href="index.php">Home</a></li>
				<li><a href="Payment.php">Payments</a></li>
				<li><a href="foodschedule.php">Food</a></li>
				<li><a href="timetable.php">Timetable</a></li>
				<li><a href="gallery.php">Gallery</a></li>
				<?php 

				if($_SESSION['permission'] == '1')
				{
					echo '<li><a href="adminregister.php">Register</a></li>';
				}
				?>				
				<li><a href="javascript:void(0)" onclick="changePermission();">Permission </a></li>
				<li><a href="logout.php">Logout</a></li>
			</ul>
		</div>
	</div>
    
	
	<div id="page">
<br/>
<div id="foodscheduleBox" class="container">
  <?php
    $dayArr = array(
      'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'
    );

    $sql = "SELECT * FROM daycare_foodtitle
    ORDER BY created ASC";

    $query = $db->query($sql);

    $i = 0;
    while($row = $query->fetch_array(MYSQLI_ASSOC))
    {
      $foodtitlearr[$i]['foodtitle_title'] = $row['foodtitle_title'];
      $foodtitlearr[$i]['foodtitle_id'] = $row['foodtitle_id'];
      ++$i;
    }

    $html = <<<HTML
    <h1>Foodschedule</h1>
    <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th> Food/ Day</th>
HTML;
    
    foreach($dayArr AS $index => $day)
    {
      $html .=<<<HTML
          <th class="text-center"> {$day}</th>
HTML;
    }

    $html .=<<<HTML
        </tr>
      </thead>
      <tbody>
HTML;

    foreach($foodtitlearr AS $index => $arr)
    {
      $foodtitle = $arr['foodtitle_title'];
      $foodtitle_id = $arr['foodtitle_id'];
      $html .= <<<HTML
        <tr>
          <td><strong>{$foodtitle}</strong></td>
HTML;

      $dbclick = "editfoodschedule(this)";

      if($_SESSION['permission'] != '1')
      {
        $dbclick = '';
      }
      foreach($dayArr AS $index => $day)
      {
        $html .= <<<HTML
          <td class="foodscheduletd text-center" style="vertical-align: middle; " day="{$day}" foodschedule_id="" foodtitle="{$foodtitle}" foodtitle_id="{$foodtitle_id}"  title="" ondblclick="$dbclick" titlename=""></td>
HTML;
      }
      $html .=<<<HTML
        </tr>
HTML;
    }
    $html .=<<<HTML
      </tbody>
    </table>
    </div>
HTML;

    echo $html;
  ?>
</div>

<div class="modal fade" id="editFoodScheduleModal" >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="editFoodScheduleTitle"></h4>
      </div>
      <div class="modal-body row" id="editFoodScheduleContent">
        <form role="form" id="editFoodScheduleForm" onsubmit="return false;"> 
          <div class="form-group col-lg-12">
            <label for="foodschedule_title" >Food</label><span style="padding-left:5px; color:red">*</span>
            <input type="text" value="" class="form-control" name="foodschedule_title">
            <span></span>
          </div>
          <div class="form-group col-lg-12">
            <label for="foodschedule_desc" >Description</label>
            <textarea value="" class="form-control" name="foodschedule_desc" value=""></textarea>
            <span></span>
          </div>
          <input type="hidden" value="" name="foodschedule_id">
          <input type="hidden" value="" name="foodtitle_id">
          <input type="hidden" value="" name="foodschedule_day">
        </form>

      </div>
      <div class="modal-footer ">
        <!-- <button type="button" class="btn btn-info" onclick="removefoodSchedule();">Remove</button> -->
        <button type="button" class="btn btn-info" onclick="checkbeforeSave();">Save</button>
      </div>
    </div>
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
    $.each(foodScheduleArr, function(id, arr)
    {
      setfoodschedule(arr);
  
    });
  });

  function setfoodschedule(arr)
  {
    var obj = $('.foodscheduletd[day="'+arr.foodschedule_day+'"][foodtitle_id="'+arr.foodtitle_id+'"]');
    obj.addClass('bg bg-success');
    obj.html(arr.foodschedule_title);
    obj.attr({'title': arr.foodschedule_desc, 'foodschedule_id': arr.foodschedule_id});
  }

</script>

<?php
  if($_SESSION['permission'] == '1')
  {
    echo <<<HTML
    <script>
    function editfoodschedule(data)
  {
    var foodschedule_id = $(data).attr('foodschedule_id');
    var title = $(data).attr('foodtitle');
    var day = $(data).attr('day');
    var foodtitle_id = $(data).attr('foodtitle_id');
    $('input[name="foodschedule_day"]').val(day);
    $('input[name="foodtitle_id"]').val(foodtitle_id);

    if(foodschedule_id == '')
    {
      var modaltitle = '<strong>'+title+'</strong>';
      $('#editFoodScheduleTitle').html(modaltitle);
      $('#editFoodScheduleModal').modal('show');
      return;
    }


    var foodschedule_title = foodScheduleArr[foodschedule_id]['foodschedule_title'];
    var foodschedule_desc = foodScheduleArr[foodschedule_id]['foodschedule_desc'];
    var foodschedule_id = (typeof foodScheduleArr[foodschedule_id]['foodschedule_id']!='undefined'?foodScheduleArr[foodschedule_id]['foodschedule_id']:'');
    

    var modaltitle = '<strong>'+title+'</strong>'+' ('+foodschedule_title+')';
    $('#editFoodScheduleTitle').html(modaltitle);
    $('input[name="foodschedule_title"]').val(foodschedule_title);
    $('textarea[name="foodschedule_desc"]').val(foodschedule_desc);
    $('input[name="foodschedule_id"]').val(foodschedule_id);
    

    $('#editFoodScheduleModal').modal('show');
  }

  function checkbeforeSave()
  {
    var check = 1;
    var i = 0;
    var field = 'input[name="foodschedule_title"]';

    if($(field).val() == '')
    {
      check = 0;
      $(field).next().html('Food cannot empty!');
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

    if(check)
    {
      bootbox.confirm('Confirm Save?', function(r)
      {
        if(r)
        {
          submitFoodSchedule();
        }
      });
      
    }

    function submitFoodSchedule()
    {
      var data= 'action=submitFoodSchedule&'+$('#editFoodScheduleForm').serialize();

      simp_ajax('POST', data, 'foodschedule.php').done(function(r)
      {
        if(r.status)
        {
          setfoodschedule(r.data);
          $('#editFoodScheduleForm')[0].reset();
          $('#editFoodScheduleModal').modal('hide');

        }
        else
        {
          bootbox.alert(r.msg);
        }
      });
    }
  }
  
</script>
HTML;
  }
?>
  
</body>
</html>
	
		
	</div>
</div>
<script src="resource/js/jquery-3.2.1.min.js"></script>
  <script src="resource/js/bootstrap.min.js"></script>
  <script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
</body>
</html>

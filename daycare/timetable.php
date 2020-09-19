<?php
include './system.php';

if(!isset($_SESSION['uid']))
{
  header('location:login.php');
}

if(!isset($_POST['action']))
{
  getTimeTable();
}

if(isset($_POST['action']))
{
  switch($_POST['action'])
  {
    case 'submitTimeTable':
      echo json_encode(submitTimeTable());
      exit;
    break;

    case 'removeTimeTable':
      echo json_encode(removeTimeTable());
      exit;
    break;
  }
}

function submitTimeTable()
{
  global $db;

  $tablename = 'daycare_timetable';
  $primarykey = 'timetable_id';

  $timetable_fromtime = (strlen($_POST['timetable_fromtime']) == 5)?$_POST['timetable_fromtime'].':00':$_POST['timetable_fromtime'];
  $timetable_totime = (strlen($_POST['timetable_totime']) == 5)?$_POST['timetable_totime'].':00':$_POST['timetable_totime'];

  $arr = array(
    'timetable_title' => $_POST['timetable_title'],
    'timetable_fromtime' => $timetable_fromtime,
    'timetable_totime' => $timetable_totime,
    'timetable_desc' => $_POST['timetable_desc'],
    'timetable_day' => $_POST['timetable_day'],
    'isactive' => 1,
  );

  if($_POST['timetable_id'] != '0' && $_POST['timetable_id'] != '')
  {
    $arr['timetable_id'] = $_POST['timetable_id'];
    $result = updateRecord($tablename, $arr, $primarykey, $_POST['timetable_id']);
  }
  else
  {
    $result = insertRecord($tablename, $arr);
    $arr['timetable_id'] = $db->insert_id;
  }

  $result['data'] = $arr;

  return $result;
}

function getTimeTable()
{
  global $db;
  $sql = "SELECT * FROM daycare_timetable WHERE isactive = '1'";

  $query = $db->query($sql);

  $result = array();
  
  while($row = $query->fetch_array(MYSQLI_ASSOC))
  {
    $result[$row['timetable_id']]['timetable_id'] = $row['timetable_id'];
    $result[$row['timetable_id']]['timetable_title'] = $row['timetable_title'];
    $result[$row['timetable_id']]['timetable_desc'] = $row['timetable_desc'];
    $result[$row['timetable_id']]['timetable_day'] = $row['timetable_day'];
    $result[$row['timetable_id']]['timetable_fromtime'] = $row['timetable_fromtime'];
    $result[$row['timetable_id']]['timetable_totime'] = $row['timetable_totime'];
  }

  $result = json_encode($result);

  echo <<<HTML
    <script>
      var timetableArr = {$result};
    </script>
HTML;
}

?>

<!DOCTYPE html>

<html >
<head>
  <meta charset="UTF-8">
  <title>Time Table</title>
    <link rel='stylesheet prefetch' href='resource/css/bootstrap.min.css'>
    <link href="resource/css/default.css" rel="stylesheet" type="text/css" media="all" />

</head>

<body>

<div id="wrapper">
<div id="menu-wrapper">
    <div id="menu" class="container">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="Payment.php">Payments</a></li>
        <li><a href="foodschedule.php">Food</a></li>
        <li class="current_page_item"><a href="timetable.php">Timetable</a></li>
        <li><a href="gallery.php">Gallery</a></li>
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
<div id="page">

<div id="timetableBox" class="container">
  <?php
    $dayArr = array(
      'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'
    );

    $timeArr = array(
      '08:00:00' => '08:00AM',
      '08:30:00' => '08:30AM',
      '09:00:00' => '09:00AM',
      '09:30:00' => '09:30AM',
      '10:00:00' => '10:00AM',
      '10:30:00' => '10:30AM',
      '11:00:00' => '11:00AM',
      '11:30:00' => '11:30AM',
      '12:00:00' => '12:00AM',
      '12:30:00' => '12:30AM',
      '13:00:00' => '01:00PM',
      '13:30:00' => '01:30PM',
      '14:00:00' => '02:00PM',
      '14:30:00' => '02:30PM',
      '15:00:00' => '03:00PM',
      '15:30:00' => '03:30PM',
      '16:00:00' => '04:00PM',
      '16:30:00' => '04:30PM',
      '17:00:00' => '05:00PM',
      '17:30:00' => '05:30PM',
      '18:00:00' => '06.00PM'
    );

    $html = <<<HTML
    <h1>Timetable</h1>
    <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th> Time/ Day</th>
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

    foreach($timeArr AS $value => $displaytime)
    {
      $html .= <<<HTML
        <tr>
          <td><strong>{$displaytime}</strong></td>
HTML;
      $dbclick = "editTimeTable(this)";

      if($_SESSION['permission'] != '1')
      {
        $dbclick = '';
      }
      foreach($dayArr AS $index => $day)
      {
        
        $html .= <<<HTML
          <td class="timetabletd text-center" style="vertical-align: middle; " day="{$day}" time="{$value}" timetableid="" totime="{$value}" title="" ondblclick="{$dbclick}" titlename=""></td>
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

<div class="modal fade" id="editTimeTableModal" >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="editTimeTableTitle"></h4>
      </div>
      <div class="modal-body row" id="editTimeTableContent">
        <form role="form" id="editTimeTableForm" onsubmit="return false;"> 
          <div class="form-group col-lg-12">
            <label for="timetable_title" >Title</label>
            <input type="text" value="" class="form-control" name="timetable_title">
            <span></span>
          </div>
          <div class="form-group col-lg-12">
            <label for="timetable_day" >Day</label>
            <select class="form-control" name="timetable_day">
              <option value="Monday">Monday</option>
              <option value="Tuesday">Tuesday</option>
              <option value="Wednesday">Wednesday</option>
              <option value="Thursday">Thursday</option>
              <option value="Friday">Friday</option>
            </select>
            <span></span>
          </div>
          <div class="form-group col-lg-6">
            <label for="timetable_fromtime" >From</label>
            <input type="time" value="" class="form-control" name="timetable_fromtime" min="08:00" max="18:00" step="1800">
            <span></span>
          </div>
          <div class="form-group col-lg-6">
            <label for="timetable_totime" >To</label>
            <input type="time" value="" class="form-control" name="timetable_totime" min="08:00" max="18:00" step="1800">
            <span></span>
          </div>
          <div class="form-group col-lg-12">
            <label for="timetable_desc" >Description</label>
            <textarea value="" class="form-control" name="timetable_desc" value=""></textarea>
            <span></span>
          </div>
          <input type="hidden" value="" name="timetable_id">
        </form>

      </div>
      <div class="modal-footer ">
        <!-- <button type="button" class="btn btn-info" onclick="removeTimeTable();">Remove</button> -->
        <button type="button" class="btn btn-info" onclick="checkbeforeSave();">Save</button>
      </div>
    </div>
  </div>
</div>
</div>
</div>
<link href="resource/css/bootstrap.min.css" rel="stylesheet" />
<link href="resource/css/default.css" rel="stylesheet" type="text/css" media="all" />
<link href="http://fonts.googleapis.com/css?family=Chivo:400,900" rel="stylesheet" />
<link href="resource/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<script src="resource/js/jquery-3.2.1.min.js"></script>
<script src="resource/js/bootstrap.min.js"></script>
<script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
<script>
  var requiredfieldarr = {
    'timetable_title' : 'Title',
    'timetable_day' : 'Day',
    'timetable_fromtime' : 'From Time',
    'timetable_totime' : 'To Time',
  } ;
  $(function()
  {
    $.each(timetableArr, function(id, arr)
    {
      setTimeTable(arr);
  
    });
    $.each(requiredfieldarr, function(column, name)
      {
        $('input[name="'+column+'"], textarea[name="'+column+'"], select[name="'+column+'"]').prev().html(function()
        {
          return $(this).html()+'<span style="padding-left:5px; color:red">*</span>';
        });
      });
    
  });

  function setTimeTable(arr)
  {
    var start = new Date('01-01-2017 '+arr.timetable_fromtime);
    var end = new Date('01-01-2017 '+arr.timetable_totime);
    var different = (end - start);
    different = Math.round(different  / 60000);
    
    var columnuse = (different / 30);


    for(var i = 0; i < columnuse; ++ i)
    {

      var time = (start.getHours()<=9?"0":"")+start.getHours()+':'+(start.getMinutes()<=9?"0":"")+start.getMinutes()+':00';

      var obj = $('.timetabletd[day="'+arr.timetable_day+'"][time="'+time+'"]');
      
      if(i == '0')
      {
        obj.addClass('bg bg-success');
        obj.attr({'rowspan': columnuse, 'timetableid' : arr.timetable_id, 'title': arr.timetable_desc,
        'totime': arr.timetable_totime, 'titlename': arr.timetable_title});
        obj.html(arr.timetable_title + '<br/>' + arr.timetable_fromtime.slice(0, -3) + ' - ' + arr.timetable_totime.slice(0, -3));
      }
      else
      {
        obj.hide();
      }

      start = new Date(start.getTime() + 30*60000);
    } 
  }
</script>
<?php

if($_SESSION['permission'] == '1')
{
  echo <<<HTML
  <script>
  function editTimeTable(data)
  {
    var day = $(data).attr('day');
    var fromtime = $(data).attr('time');
    var totime = $(data).attr('totime');
    var title = $(data).attr('titlename');
    var desc = $(data).attr('title');
    var id= $(data).attr('timetableid');

    var modaltitle = '<strong>'+title+'</strong>'+' ('+day+' From: '+fromtime+' '+(totime!=''?' To ':'')+totime+')';
    $('#editTimeTableTitle').html(modaltitle);
    $('input[name="timetable_title"]').val(title);
    $('select[name="timetable_day"]').val(day);
    $('input[name="timetable_fromtime"]').val(fromtime);
    $('input[name="timetable_totime"]').val(totime);
    $('textarea[name="timetable_desc"]').val(desc);
    $('input[name="timetable_id"').val(id);
    $('#editTimeTableModal').modal('show');
  }

  function checkbeforeSave()
  {
    var check = 1;
    var i = 0;

    $.each(requiredfieldarr, function(column, name)
    {
      var field = 'input[name="'+column+'"], textarea[name="'+column+'"], select[name="'+column+'"]';
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

    if(check)
    {
      submitTimeTable();
    }
  }

  function submitTimeTable()
  {
    var data= 'action=submitTimeTable&'+$('#editTimeTableForm').serialize();

    simp_ajax('POST', data, 'timetable.php').done(function(r)
    {
      if(r.status)
      {
        var timetableid = $('input[name="timetable_id"]').val();
        var day = $('.timetabletd[timetableid="'+timetableid+'"]').attr('day');
        var fromtime = $('.timetabletd[timetableid="'+timetableid+'"]').attr('time');
        var totime = $('.timetabletd[timetableid="'+timetableid+'"]').attr('totime');
        resetTd(day, fromtime, totime);
        setTimeTable(r.data);
        $('#editTimeTableModal').modal('hide');
      }
      else
      {
        bootbox.alert(r.msg);
      }
    });
  }

  function removeTimeTable()
  {
    if($('input[name="timetable_id"]').val() == '' || $('input[name="timetable_id"]').val() == '0')
    {
      return;
    }
    var data = 'action=removeTimeTable&timetable_id='+$('input[name="timetable_id"]').val();

    simp_ajax('POST', data, 'timetable.php').done(function(r)
    {
      if(r.status)
      {
        var timetableid = $('input[name="timetable_id"]').val();
        var day = $('.timetabletd[timetableid="'+timetableid+'"]').attr('day');
        var fromtime = $('.timetabletd[timetableid="'+timetableid+'"]').attr('time');
        var totime = $('.timetabletd[timetableid="'+timetableid+'"]').attr('totime');
        resetTd(day, fromtime, totime);
        $('#editTimeTableModal').modal('hide');
      }
      else
      {
        bootbox.alert(r.msg);
      }
    });
  }

  function resetTd(day, fromtime, totime)
  {
    var start = new Date('01-01-2017 '+fromtime);
    var end = new Date('01-01-2017 '+totime);
    var different = (end - start);
    different = Math.round(different  / 60000);
    
    var columnuse = (different / 30);


    for(var i = 0; i < columnuse; ++ i)
    {

      var time = (start.getHours()<=9?"0":"")+start.getHours()+':'+(start.getMinutes()<=9?"0":"")+start.getMinutes()+':00';

      var obj = $('.timetabletd[day="'+day+'"][time="'+time+'"]');
      
      obj.removeClass('bg bg-success');
      obj.attr({'rowspan': '', 'timetableid' : '', 'title': '',
      'totime': '', 'titlename': ''});
      obj.empty();
      obj.show();

      start = new Date(start.getTime() + 30*60000);
    } 
  }
  </script>
HTML;
}
?>

</body>
</html>

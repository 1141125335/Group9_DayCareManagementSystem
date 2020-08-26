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
		case 'searchBoard':
			echo json_encode(searchBoard());
			exit;
		break;

		case 'saveBoard':
			echo json_encode(saveBoard());
			exit;
		break;

		case 'deleteBoard':
			echo json_encode(deleteBoard());
			exit;
		break;
	}
}

function searchBoard()
{
	global $db;

	$fromdate = $db->real_escape_string(date('Y-m-d', strtotime($_POST['fromdate'])));
	$todate = $db->real_escape_string(date('Y-m-d', strtotime($_POST['todate'])));

	$sql = "SELECT * FROM daycare_board WHERE board_date BETWEEN '$fromdate' AND '$todate'";

	$query = $db->query($sql);

	$html = '';

	while($arr = $query->fetch_array(MYSQLI_ASSOC))
	{
		$html .= displayUserBoard($arr);
	}

	if($_SESSION['permission'] == '1')
	{
		$html .=<<<HTML
		<br/>
		<div class="col-lg-12" style="background-color:#bfbfbf; border-radius: 10px;">
			<div class="row" >
				<h2 class="col-lg-12 text text-info">Add/ Edit Board</h2>
				<br/><br/>
				<form class="form" id="boardform" onsubmit="return false">
					<div class="form-group col-lg-6">
						<label>Title</label>
						<input type="text" name="board_title" class="form-control">
					</div>
					<div class="form-group col-lg-6">
						<label>Date</label>
						<input type="text" name="board_date" class="form-control">
					</div>
					<div class="form-group col-lg-12">
						<label>Description</label>
						<textarea type="text" name="board_desc" class="form-control"></textarea>
					</div>
					<input type="hidden" name="board_id" value="">
				</form>
				<br/>
				<div class="col-lg-12">
					<button class="btn btn-info" onclick="resetBoard()">New</button>
					<button class="btn btn-info" onclick="saveBoard()">Save</button>
					<button class="btn btn-info" id="deleteButton" onclick="deleteBoard()" style="display:none;">Delete</button>
				</div>
			</div>
			<br/>
		</div>
HTML;
	}

	return $html;
}

function displayUserBoard($arr)
{
	$js = '';
	$board_id = $arr['board_id'];
	$board_title = $arr['board_title'];
	$board_date = date('d-m-Y', strtotime($arr['board_date']));
	$board_desc = $arr['board_desc'];

	if($_SESSION['permission'] == '1')
	{
		$js = "onclick='editUserBoard(\"{$board_id}\")'; ";
		$js .= "onmouseover='$(this).css({\"cursor\": \"pointer\"}); $(this).addClass(\"clickhover\")'";
		$js .= " title='Click To Edit'";
	}	

	$html =<<<HTML
	<div class="title" id="board_{$board_id}" >
		<h2 {$js} id="boardtitle_{$board_id}">{$board_title}</h2>
		<span id="boarddate_{$board_id}" class="byline">{$board_date}</span> 
	</div>
	<p id="boarddesc_{$board_id}">{$board_desc}</p> 
HTML;
	return $html;
}

function saveBoard()
{
	$tablename = 'daycare_board';
	$primarykey = 'board_id';

	$arr = array(
		'board_title' => $_POST['board_title'],
		'board_desc' => $_POST['board_desc'],
		'board_date' => date('Y-m-d', strtotime($_POST['board_date'])),
	);

	if($_POST['board_id'] != '')
	{
		updateRecord($tablename, $arr, $primarykey, $_POST['board_id']);
	}
	else
	{
		insertRecord($tablename, $arr);
	}
	return true;
}

function deleteBoard()
{
	global $db;

	$board_id = $_POST['board_id'];
	$tablename = 'daycare_board';
	$primarykey = 'board_id';

	$sql = "DELETE FROM $tablename WHERE $primarykey = '$board_id'";

	$query = $db->query($sql);

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
<link href='resource/jquery-ui/jquery-ui.min.css' rel="stylesheet" type="text/css">
<style>
	.clickhover:hover
	{
		color:blue;
	}
</style>


</head>
<body>
<div id="wrapper">
	<div id="header-wrapper">
		<div id="header" class="container">
			<div id="logo">
				<h1><a href="#">Daycare Management</a></h1>
				
			</div>
		</div>
	</div>
	<div id="menu-wrapper">
		<div id="menu" class="container">
			<ul>
				<li class="current_page_item"><a href="index.php">Home</a></li>
				<li><a href="Payment.php">Payments</a></li>
				<li><a href="foodschedule.php">Food</a></li>
				<li><a href="timetable.php">Timetable</a></li>
				<li><a href="gallery.php">Result</a></li>
				<?php 

				if($_SESSION['permission'] == '1')
				{
					echo '<li><a href="adminregister.php">Register</a></li>';
				}

				if($_SESSION['uid'] != '14')
				{
					echo '<li><a href="javascript:void(0)" onclick="changePermission();">Permission </a></li>';
				}
				else
				{
					echo '<li><a href="profile.php" >Profile </a></li>';
				}
				?>				
				
				<li><a href="logout.php">Logout</a></li>
			</ul>
		</div>
	</div>
    <div class="text-center">
       <img src="picture/slider-2.jpg"></img>
	</div>
	
	<div id="page" class="container">
		<div id="content">
			{ board content }
			
		</div>
		<div id="sidebar">
			<div class="box2">
				<div class="title">
					<h1>Bulletin board</h1>
				</div>
			</div>
			<input type="text" class="form-control" id="fromdate">

			<h4>To</h4>
			
			<input type="text" class="form-control" id="todate">
			<br/>
			<button class="btn btn-info" id="searchboardbtn" onclick="searchBoard()">Search</button>
		</div>
	</div>
</div>
<script src="resource/js/jquery-3.2.1.min.js"></script>
<script src="resource/js/bootstrap.min.js"></script>
<script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
<script src='resource/jquery-ui/jquery-ui.min.js'></script>
<script>
$(function()
{
	setDefaultDate();
	searchBoard()
});

function setDefaultDate()
{
	var curr = new Date;
	var first = curr.getDate() - curr.getDay(); 
	var last = first + 6; 

	var firstday = new Date(curr.setDate(first));
	var lastday = new Date(curr.setDate(last));

	$('#fromdate').datepicker({dateFormat:'dd-mm-yy'}).datepicker("setDate", firstday);
	$('#todate').datepicker({dateFormat:'dd-mm-yy'}).datepicker("setDate", lastday);
	
}

function searchBoard()
{
	var fromdate = $('#fromdate').val();
	var todate = $('#todate').val();
	
	var data = 'action=searchBoard&fromdate='+fromdate+'&todate='+todate;

	simp_ajax('POST', data, 'index.php').done(function(r)
	{
		$('#content').html(r);
		$('input[name="board_date"]').datepicker({dateFormat:'dd-mm-yy'}).datepicker("setDate", new Date());
	});
}
</script>

<?php

if($_SESSION['permission'] == '1')
{
	echo <<<HTML
<script>
function editUserBoard(board_id)
{
	var boardtitle = $('#boardtitle_'+board_id).html(); 
	var boarddate = $('#boarddate_'+board_id).html(); 
	var boarddesc = $('#boarddesc_'+board_id).html();

	$('input[name="board_title"]').val(boardtitle);
	$('input[name="board_date"]').val(boarddate);
	$('textarea[name="board_desc"]').val(boarddesc);
	$('input[name="board_id"]').val(board_id);
	$('#deleteButton').show();
}

function saveBoard()
{
	if(!checkBoard())
	{
		return;
	}
	var formdata = new FormData($('#boardform')[0]);
	formdata.append('action', 'saveBoard');

	data_ajax('POST', formdata, 'index.php').done(function(r)
	{
		if(r)
		{
			searchBoard();
		}
		
	});

}

function checkBoard()
{
	if($('input[name="board_title"]').val() == '')
	{
		bootbox.alert('Board Title cannot be empty!');
		return false;
	}

	if($('input[name="board_title"]').val() == '')
	{
		bootbox.alert('Board Date cannot be empty!');
		return false;
	}

	return true;
}

function resetBoard()
{
	$('#boardform')[0].reset(); 
	$('input[name="board_id"]').val(''); 
	$('#deleteButton').hide()
}

function deleteBoard()
{
	var board_id = $('input[name="board_id"]').val(); 

	if(board_id == '')
	{
		return;
	}

	bootbox.alert('Confirm Delete?', function(rs)
	{
		if(rs)
		{
			var data = 'action=deleteBoard&board_id='+board_id;

			simp_ajax('POST', data, 'index.php').done(function(r)
			{
				if(r)
				{
					searchBoard();
				}
			})
		}
	});


}
</script>
HTML;
}
?>



</body>
</html>

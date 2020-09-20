<?php
include './system.php';

if(!isset($_SESSION['uid']))
{
  header('location:login.php');
}

switch($_POST['action'])
{
	case 'searchPayment':
		echo json_encode(searchPayment());
		exit;
	break;

	case 'submitPayment':
		echo json_encode(submitPayment());
		exit;
	break;

	case 'getPaymentLine':
		echo json_encode(getPaymentLine());
		exit;
	break;

	case 'deletePayment':
		echo json_encode(deletePayment());
		exit;
	break;

	case 'deletePaymentLine':
		echo json_encode(deletePaymentLine());
		exit;
	break;
}

function searchPayment()
{
	global $db;
	$parent = $db->real_escape_string($_POST['parent']);
	$paymentfrom = $db->real_escape_string($_POST['paymentfrom']);
	$paymentto = $db->real_escape_string($_POST['paymentto']);

	$parentwherestr = ($parent != 'All')?"AND dp.parent_id = '$parent'":'';
	$paymentfrom = ($paymentfrom != '')?date('Y-m-d', strtotime($paymentfrom)):'0000-00-00';
	$paymentto = ($paymentto != '')?date('Y-m-d', strtotime($paymentto)):'9999-12-31';

	$sql = "
	SELECT a.*, COALESCE(SUM(a.paymentline_total),0) AS total_amt 
	FROM 
	(
		SELECT dp.*, p.parent_name, dpl.paymentline_total
		FROM daycare_payment dp 
		LEFT JOIN daycare_parent p ON p.parent_id = dp.parent_id
		LEFT JOIN daycare_paymentline dpl ON dpl.payment_id = dp.payment_id 
		WHERE dp.payment_date BETWEEN '$paymentfrom' AND '$paymentto' 
		$parentwherestr 
		ORDER BY dp.payment_date DESC 
	)a GROUP BY a.payment_id";

	$query = $db->query($sql);

	$result = array();

	while($row = $query->fetch_array(MYSQLI_ASSOC))
	{
		$result[$row['payment_id']] = $row;
	}

	return $result;
}

function submitPayment()
{
	global $db;

	beginTransaction();
	$paymentObj = json_decode($_POST['paymentObj'], true);

	$parent_id = $_POST['parent_id'];
	$payment_id = $_POST['payment_id'];

	$arrpayment = array(
		'payment_date' => date('Y-m-d'),
		'parent_id' => $parent_id
	);

	$primarytable = 'daycare_payment';
	$primarykey = 'payment_id';
	$secondarytable = 'daycare_paymentline';
	$secondarykey = 'paymentline_id';

	if($payment_id == '0' || $payment_id == '')
	{
		$result = insertRecord($primarytable, $arrpayment);
		$payment_id = $db->insert_id;
	}
	else
	{
		$result = updateRecord($primarytable, $arrpayment, $payment_id);
	}

	if(!$result['status'])
	{
		$db->rollback();
		return $result;
	}

	foreach($paymentObj AS $index => $arr)
	{
		$paymentline_id = $arr['paymentline_id'];
		unset($arr['paymentline_id']);
		$arr['payment_id'] = $payment_id;

		if($paymentline_id == '0' || $paymentline_id == '')
		{
			$result = insertRecord($secondarytable, $arr);
		}
		else
		{
			$result = updateRecord($secondarytable, $arr, $secondarykey, $paymentline_id);
		}

		if(!$result['status'])
		{
			$db->rollback();
			return $result;
		}
	}

	commit();

	return $result;
}

function getPaymentLine()
{
	global $db;
	$payment_id = $_POST['payment_id'];

	$sql = "SELECT * FROM daycare_paymentline WHERE payment_id = '$payment_id'";

	$result = array();

	$query = $db->query($sql);

	while($arr = $query->fetch_array(MYSQLI_ASSOC))
	{
		$result[$arr['paymentline_id']] = $arr;
	}

	return $result;
}

function deletePayment()
{
	global $db;
	$payment_id = $_POST['payment_id'];

	$sql = "DELETE FROM daycare_payment WHERE payment_id = '$payment_id'
	";
	$db->query($sql);

	$sql = "DELETE FROM daycare_paymentline WHERE payment_id = '$payment_id'";

	$db->query($sql);

	return true;
}
function deletePaymentLine()
{
	global $db;
	$paymentline_id = $_POST['paymentline_id'];

	$sql = "DELETE FROM daycare_paymentline WHERE paymentline_id = '$paymentline_id'";
	$db->query($sql);

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
<link href='resource/jquery-ui/jquery-ui.min.css' rel="stylesheet" type="text/css"/>
<link href='resource/dataTable/datatables.min.css' rel="stylesheet" type="text'css"/>
</head>
<body>
<div id="wrapper">
	<div id="menu-wrapper">
		<div id="menu" class="container">
			<ul>
				<li ><a href="index.php">Home</a></li>
				<li class="current_page_item"><a href="Payment.php">Payments</a></li>
				<li><a href="foodschedule.php">Food</a></li>
				<li><a href="timetable.php">Timetable</a></li>
				<li><a href="gallery.php">Gallery</a></li>
				<?php 

				if($_SESSION['permission'] == '1')
				{
					echo '<li><a href="adminregister.php">Register</a></li>';
				}
				?>				
				<!-- <li><a href="javascript:void(0)" onclick="changePermission();">Permission </a></li> -->
				<li><a href="newprofile.php">Profile</a></li>
				<li><a href="logout.php">Logout</a></li>
			</ul>
		</div>
	</div>
	<div id="page">
		<form id="paymentForm" onsubmit="return false">
<?php
	if($_SESSION['permission'] == '1')
	{
		echo <<<HTML
		
			<div class="form-group col-lg-3	">
				<label>Parent</label>
				<select name="parent" class="form-control">
				<option value="All">All</option>
HTML;
		
		$sql = "SELECT * FROM daycare_parent";
		$query = $db->query($sql);
		while($row = $query->fetch_array(MYSQLI_ASSOC))
		{
			echo "<option value='$row[parent_id]'>$row[parent_name]</option>";
		}

			echo <<<HTML
				</select>
			</div>
HTML;
	}
	else if($_SESSION['permission'] == '0')
	{
		$sql = "SELECT parent_id FROM daycare_parent WHERE user_id = '$_SESSION[uid]'";
		$query = $db->query($sql);
		$parent_id = $query->fetch_array(MYSQLI_ASSOC)['parent_id'];
		echo <<<HTML
		<input name="parent" type="hidden" value="{$parent_id}">
HTML;
	}
?>
		<div class="form-group col-lg-3">
			<label>Payment Date</label>
			<input type="text" name="paymentfrom" class="form-control">
		</div>
		<div class="form-group col-lg-3">
			<label>To</label>
			<input type="text" name="paymentto" class="form-control">
		</div>
		<div class="col-lg-2 form-group">
	    <label class="col-lg-12">&nbsp</label>
	    <button class="btn btn-info" onclick="">Search</button>
	  </div>`
	</form>
	<br/>
	<div class="col-lg-12" id="paymentTableBox">
	</div>
<?php
	if($_SESSION['permission'] == '1')
	{
		echo <<<HTML
	<div>
		<div class="form-group col-lg-3	">
			<label>Parent</label>
			<select name="parent_id" class="form-control">

HTML;
		
		$sql = "SELECT p.*, COUNT(c.child_id) AS count_children, GROUP_CONCAT(c.child_nickname, ' ') AS allchild_name FROM daycare_parent p LEFT JOIN daycare_child c ON c.parent_id = p.parent_id GROUP BY p.parent_id";
		$query = $db->query($sql);
		while($row = $query->fetch_array(MYSQLI_ASSOC))
		{
			echo "<option value='$row[parent_id]'>$row[parent_name] ({$row[count_children]} Childs) - {$row[allchild_name]}</option>";
		}

			echo <<<HTML
			</select>
		</div>
		<div class="form-group col-lg-3">
			<label>Total</label>
			<input type="text" name="total_amt" disabled class="form-control">
			<input type="hidden" id="payment_id" value="">
		</div>
	</div>
	<div class="col-lg-2 form-group">
	    <label class="col-lg-12">&nbsp</label>
	    <button class="btn btn-info" onclick="resetPaymentLine()">New</button>
	    <label class="col-lg-12">&nbsp</label>
	    <button class="btn btn-info" style="margin-left:15px;" onclick="submitPayment()">Submit Payment</button>
	 </div>

	<div class="col-lg-12">
		<table id="addEditPaymentTable" class="table table-bordered" rownum="1">
			<thead>
				<tr>
					<th style="min-width: 100px;">Item</th>
					<th style="min-width: 300px;">Description</th>
					<th style="min-width: 100px;">Unit Cost</th>
					<th style="min-width: 100px;">Quantity</th>
					<th style="min-width: 100px;">Price</th>
					<th class="text-center">Add New/ Delete</th>
				</tr>
			</thead>
			<tbody>
				<tr >
					<td class="bg bg-success"><input type="text" id="paymentline_item" class="form-control"></td>
					<td class="bg bg-success"><input type="text" id="paymentline_desc" class="form-control"></td>
					<td class="bg bg-success"><input type="number" id="paymentline_unitprice" onchange="updateLineTotal(this, 0)" class="form-control" value="0"></td>
					<td class="bg bg-success"><input type="number" id="paymentline_qty" onchange="updateLineTotal(this, 0)" class="form-control" value="0"></td>
					<td class="bg bg-success paymentline_total"><input type="number" id="paymentline_total"  disabled class="form-control" value="0"></td>
					<td class="text-center bg bg-success"><button class="btn btn-info" onclick="addNewRow(1);"><i class="glyphicon glyphicon-plus"></i></button></td>
				</tr>
				
				<tr id="addNewTr">
				</tr>
			</tbody>
		</table>
	</div>
HTML;
	}
	
?>
	</div>
	
</div>
<script src="resource/js/jquery-3.2.1.min.js"></script>
<script src="resource/js/bootstrap.min.js"></script>
<script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
<script src='resource/jquery-ui/jquery-ui.min.js'></script>
<script src='resource/dataTable/datatables.min.js'></script>

<script>
$(function()
{
	setDefaultDate();
	searchPayment();
});

function setDefaultDate()
{
	var date = new Date();
	var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
	var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

	$('input[name="paymentfrom"]').datepicker({dateFormat:'dd-mm-yy'}).datepicker("setDate", firstDay);
	$('input[name="paymentto"]').datepicker({dateFormat:'dd-mm-yy'}).datepicker("setDate", lastDay);
}

function searchPayment()
{
	var formdata = new FormData($('#paymentForm')[0]);
	formdata.append('action', 'searchPayment');

	data_ajax('POST', formdata, 'payment.php').done(function(r)
	{
		 drawPaymentTable(r);
	});
}

function drawPaymentTable(r)
{
	var table = '<table class="table table-bordered" id="paymentTable">';
    table += '<thead>';
    table += '<tr>';
    table += '<th style="width:50px;">No.</th>';
    table += '<th>Date</th>';
    table += '<th>Parent Name</th>';
    table += '<th>Total Amount</th>';
    table += '<th class="text-center" style="width:125px;">Print</th>';
<?php
	echo ($_SESSION['permission'] == '1')?"table += '<th style=\"width:75px;\">Delete</th>';":"";
?>
    table += '</tr>';
    table += '</thead>';
    table += '<tbody>';

    var i = 1;
    $.each(r, function(pk, arr)
    {
    	<?php
	echo ($_SESSION['permission'] == '1')?"table += '<tr class=\"paymenttr\" payment_id=\"'+pk+'\"onclick=\"editPayment(this, event)\" onmouseover=\"$(this).css(\'cursor\', \'pointer\')\">'":"table += '<tr class=\"paymenttr\" payment_id=\"'+pk+'\">'";
?>
        ;
        table += '<td>'+i+'</td>';
        table += '<td class="payment_date">'+arr.payment_date+'</td>';
        table += '<td class="parent_name" parent_id="'+arr.parent_id+'">'+arr.parent_name+'</td>';
        table += '<td class="total_amt">'+arr.total_amt+'</td>';
        table += '<td class="print text-center"><a target="_blank" href="printpayment.php?payment_id='+pk+'">Print</a></td>';
        <?php
	echo ($_SESSION['permission'] == '1')?
				"table += '<td class=\"delete_payment text-center text text-danger\">';
				table += '<i class=\"glyphicon glyphicon-remove ml-2\" onmouseover=\"$(this).css(\'cursor\', \'pointer\')\" onclick=\"deletePayment(this)\"></i></td>';"
				:"";
?>
        table += '</tr>';
        ++i;
    });

    table += '</tbody>';
    table += '</table>';

    $('#paymentTableBox').html(table);

    $('#paymentTable').DataTable();
}


</script>

<?php

if($_SESSION['permission'] == '1')
{
echo <<<HTML
<script>
var deleteArr = [];
var paymentObj = {};
function addNewRow(addnew = 0, r={'paymentline_item': '', 'paymentline_desc': '', 'paymentline_unitprice': '', 'paymentline_qty': 1, 'paymentline_total': 0, 'paymentline_id': ''})
{
	var rownum = $('#addEditPaymentTable').attr('rownum');
	var tr = "";

	tr += '<tr class="paymentlinetr" paymentline_id="'+r.paymentline_id+'" row="'+rownum+'">';

	tr += '<td class="paymentline_item"><input type="text" class="form-control" value="'+((addnew)?$('#paymentline_item').val():r.paymentline_item)+'"></td>';

	tr += '<td class="paymentline_desc"><input type="text"  class="form-control" value="'+((addnew)?$('#paymentline_desc').val():r.paymentline_desc)+'"></td>';

	tr += '<td class="paymentline_unitprice"><input onchange="updateLineTotal(this, 1, \'unitprice\')" type="number"  class="form-control" value="'+((addnew)?$('#paymentline_unitprice').val():r.paymentline_unitprice)+'"></td>';

	tr += '<td class="paymentline_qty"><input onchange="updateLineTotal(this, 1, \'qty\')" type="number"  class="form-control" value="'+((addnew)?$('#paymentline_qty').val():r.paymentline_qty)+'"></td>';

	tr += '<td class="paymentline_total"><input type="number" disabled class="form-control" value="'+((addnew)?$('#paymentline_total').val():r.paymentline_total)+'"></td>';

	tr += '<td class="text-center "><button class="btn btn-danger" onclick="deletePaymentLine(this);"><i class="glyphicon glyphicon-remove"></i></button></td>';
	tr += '</tr>';
				
	$('#addEditPaymentTable').attr('rownum', rownum++);
	$('#addNewTr').before(tr);


	$('#paymentline_item').val('');
	$('#paymentline_desc').val('');
	$('#paymentline_unitprice').val(0);
	$('#paymentline_qty').val(0);
	$('#paymentline_total').val(0);

	updateGrandTotal();
}

function updateLineTotal(selector, isnew, which='')
{
	var qty = 0;
	var unitprice = 0;

	if(!isnew)
	{
		qty = $('#paymentline_qty').val();
		unitprice = $('#paymentline_unitprice').val();
	}
	else
	{

		if(which == 'qty')
		{
			qty = $(selector).val();
			unitprice = $(selector).parent().parent().find('.paymentline_unitprice').children().val();
		}
		else
		{
			unitprice = $(selector).val();
			qty = $(selector).parent().parent().find('.paymentline_qty').children().val();
		}
	}

	var total = (qty == ''?0:qty) * (unitprice == ''?0:unitprice);

	$(selector).parent().parent().find('.paymentline_total').children().val(total);
	updateGrandTotal();
}

function updateGrandTotal()
{
	var total = 0;
	$('.paymentline_total').each(function()
	{
		total += parseFloat($(this).children().val());
	});

	$('input[name="total_amt"]').val(total);
}

function submitPayment()
{
	setPaymentObj();

	if($.isEmptyObject(paymentObj))
	{
		return;
	}

	var data = 'action=submitPayment&paymentObj='+JSON.stringify(paymentObj)+'&parent_id='+$('select[name="parent_id"]').val()+'&payment_id='+$('#payment_id').val();

	simp_ajax('POST', data, 'payment.php').done(function(r)
	{
		if(r.status)
		{
			resetPaymentLine();
			bootbox.alert('Success!');
			searchPayment();

		}
		else
		{
			bootbox.alert(r.msg);
		}
	});
}

function resetPaymentLine()
{
	$('.paymentlinetr').remove();
	$('input[name="total_amt"]').val(0);
	$('#payment_id').val('');
	paymentObj = {};
}

function setPaymentObj()
{
	var i = 0;
	$('.paymentlinetr').each(function()
	{
		paymentObj[i] = {};

		paymentObj[i]['paymentline_id'] = $(this).attr('paymentline_id');
		paymentObj[i]['paymentline_item'] = $(this).find('.paymentline_item').children().val();
		paymentObj[i]['paymentline_desc'] = $(this).find('.paymentline_desc').children().val();
		paymentObj[i]['paymentline_unitprice'] = $(this).find('.paymentline_unitprice').children().val();
		paymentObj[i]['paymentline_qty'] = $(this).find('.paymentline_qty').children().val();
		paymentObj[i]['paymentline_total'] = $(this).find('.paymentline_total').children().val();

		++i;
	});
}

function editPayment(selector, event)
{
	if(event.target.tagName == 'I')
	{
		return;
	}
	resetPaymentLine();
	var payment_id = $(selector).attr('payment_id');
	var parent_id = $(selector).find('.parent_name').attr('parent_id');


	var data = 'action=getPaymentLine&payment_id='+payment_id;

	simp_ajax('POST', data, 'payment.php').done(function(r)
	{
		$.each(r, function(paymentline_id, arr)
		{
			addNewRow(0, arr);
		});
		$('select[name="parent_id"]').val(parent_id);
		$('#payment_id').val(payment_id);
	});

}

function deletePayment(selector)
{
	bootbox.confirm('Confirm Delete?', function(rs)
	{
		if(rs)
		{
			var payment_id = $(selector).parent().parent().attr('payment_id');
			var data = 'action=deletePayment&payment_id='+payment_id;

			simp_ajax('POST', data, 'payment.php').done(function(r)
			{
				resetPaymentLine();
				searchPayment();
			});
		}
	});

}

function deletePaymentLine(selector)
{
	bootbox.confirm('Confirm Delete?', function(rs)
	{
		if(rs)
		{
			var paymentline_id = $(selector).parent().parent().attr('paymentline_id');
			if(paymentline_id == '' || paymentline_id == '0')
			{
				return;
			}

			var data = 'action=deletePaymentLine&paymentline_id='+paymentline_id;

			simp_ajax('POST', data, 'payment.php').done(function(r)
			{
				$(selector).parent().parent().remove();
				updateGrandTotal();
				searchPayment();
			});
		}
	});
}

</script>
HTML;
}

?>
</body>
</html>

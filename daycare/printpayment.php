<?php
include './system.php';

if(!isset($_SESSION['uid']))
{
  header('location:login.php');
}
?>


<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />

<link href="http://fonts.googleapis.com/css?family=Chivo:400,900" rel="stylesheet" />
<link href="resource/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link rel='stylesheet' type='text/css' href='resource/css/style.css' />

</head>
<body>

	<div id="page">

	<div id="page-wrap">

		<textarea id="header">OFFICIAL RECEIPT</textarea>
		
		<div id="identity">
		
            <div id="address">
            	<b>Multimedia University,</b> 
            	<br/>
            	 Jalan Multimedia, <br/>
            	 63000 Cyberjaya, <br/> 
            	 Selangor, Malaysia

            </div>

           <!--  <div id="logo">

              <div id="logoctr">
                <a href="javascript:;" id="change-logo" title="Change logo">Change Logo</a>
                <a href="javascript:;" id="save-logo" title="Save changes">Save</a>
                |
                <a href="javascript:;" id="delete-logo" title="Delete logo">Delete Logo</a>
                <a href="javascript:;" id="cancel-logo" title="Cancel changes">Cancel</a>
              </div>

              <div id="logohelp">
                <input id="imageloc" type="text" size="50" value="" /><br />
                (max width: 540px, max height: 100px)
              </div>
              <img id="image" src="images/logo.png" alt="logo" />
            </div> -->
		
		</div>
		
		<div style="clear:both"></div>
		
		<div id="customer">

            <div id="customer-title">
<?php
	$sql = "
	SELECT a.*, COALESCE(SUM(a.paymentline_total),0) AS total_amt 
	FROM 
	(
		SELECT dp.*, p.parent_name, dpl.paymentline_total
		FROM daycare_payment dp 
		LEFT JOIN daycare_parent p ON p.parent_id = dp.parent_id
		LEFT JOIN daycare_paymentline dpl ON dpl.payment_id = dp.payment_id 
		WHERE dp.payment_id = '$_REQUEST[payment_id]' 
		ORDER BY dp.payment_date DESC 
	)a GROUP BY a.payment_id";

	$query = $db->query($sql);

	$row = $query->fetch_array(MYSQLI_ASSOC);

	$parent_name = $row['parent_name'];

	echo $parent_name;
?>
          </div>

            <table id="meta">
<!--                 <tr>
                    <td class="meta-head">Invoice #</td>
                    <td><textarea>000123</textarea></td>
                </tr> -->
                <tr>

                    <td class="meta-head">Date</td>
                    <td><div id="date">
<?php
	$date = $row['payment_date'];
	echo $date;
?>
                    </div></div>
                </tr>
                <tr>
                    <td class="meta-head">Total Amount</td>
                    <td><div class="due">RM 
<?php
	$total_amt = $row['total_amt'];
	echo $total_amt;
?>                   	
                    </div></td>
                </tr>

            </table>
		
		</div>
		
		<table id="items">
		
		  <tr>
		      <th>Item</th>
		      <th>Description</th>
		      <th>Unit Cost</th>
		      <th>Quantity</th>
		      <th>Price</th>
		  </tr>
<?php
	$sql = "SELECT * FROM daycare_paymentline WHERE payment_id = '$_REQUEST[payment_id]'";

	$query = $db->query($sql);

	while($row = $query->fetch_array(MYSQLI_ASSOC))
	{
		echo <<<HTML
		<tr class="item-row">
	      <td class="item-name"><div>{$row['paymentline_item']}</div></td>
	      <td class="description"><div>{$row['paymentline_desc']}</div></td>
	      <td><div class="cost">RM {$row['paymentline_unitprice']}</div></td>
	      <td><div class="qty">{$row['paymentline_qty']}</div></td>
	      <td><span class="price">RM {$row['paymentline_total']}</span></td>
	  </tr>
HTML;
	}
?>
		  


		  <tr>

		      <td colspan="2" class="blank"> </td>
		      <td colspan="2" class="total-line">Total</td>
		      <td class="total-value"><div id="total">
		      	RM 
<?php
	echo $total_amt;
?>           
		      </div></td>
		  </tr>

		</table>
		
		
	
	</div>
	</div>

<script src="resource/js/jquery-3.2.1.min.js"></script>
  <script src="resource/js/bootstrap.min.js"></script>
  <script src="resource/js/bootbox.min.js"></script>
<script src="include/js/customjs.js"></script>
</body>
</html>

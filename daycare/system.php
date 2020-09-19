<?php
error_reporting(E_PARSE | E_ERROR);
$db = new mysqli('localhost', 'root', 'admin', 'daycaresystem');

session_name('daycare');
session_start();

$exceptionpage = array(
	'login.php', 'registration.php', 'resetps.php'
);

if(!isset($_SESSION['uid']) && !in_array(basename($_SERVER['PHP_SELF']), $exceptionpage))
{
	header('location:login.php');
}

if($_POST['action'] == 'changePermission')
{
	if($_SESSION['permission'] == '1')
  {
    $_SESSION['permission'] = '0';
  }
  else if($_SESSION['permission'] == '0')
  {
    $_SESSION['permission'] = '1';
  }
  echo json_encode(true);
  exit;

}

include 'class/NewMail.inc.php';

$mail = new Mail;


function checkunique($tablename, $primarykey, $value)
{
	global $db;
	$sql = "SELECT * FROM $tablename 
	WHERE $primarykey = '$value'";

	$query = $db->query($sql);
	if($row = $query->fetch_array(MYSQLI_ASSOC))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function beginTransaction($mode = MYSQLI_TRANS_START_READ_WRITE)
{
	global $db;
	$db->autocommit(0);
	$db->begin_transaction($mode);
}

function commit()
{
	global $db;
	$db->commit();
	$db->autocommit(1);
}

function real_escape_string2($string)
{
	global $db;
  return "'" . str_replace("\\\"", '"', str_replace("\\&quot;", '&quot;', $db->real_escape_string($string))) . "'";
}

function insertRecord($tablename, $arr, $uniquekey='')
{
	global $db;
	$columnfield = "";
	$value = "";

	foreach($arr AS $columnname => $columvalue)
	{
		$columnfield .= $columnname.", ";
		$columvalue = ""?"'$columvalue'":$columvalue;
		$value .= real_escape_string2($columvalue).", ";
	}

	$columnfield = substr($columnfield, 0, -2);
	$value = substr($value, 0, -2);

	$sql = "INSERT INTO $tablename ($columnfield) VALUES($value);";

	$result = array(
		'status' => 1,
		'msg' => '',
		);

	// if($uniquekey != '')
	// {
	// 	if($this->db->checkExistData($tablename, $uniquekey, $arr[$uniquekey]))
	// 	{
	// 		$result['status'] = 0;
	// 		$result['msg'] = lang('The data is exists!');
	// 	}
	// }

	if(!$db->query($sql))
	{
		$result['status'] = 0;
		$result['msg'] = $db->error;
	}

	return $result;
}

function updateRecord($tablename, $arr, $primarykey, $pk_value, $uniquekey='')
{
	global $db;
	$result = array(
		'status' => 1,
		'msg' => '',
	);
	$updatestr = "";

	foreach($arr AS $columnname => $columvalue)
	{
		$updatestr .= "$columnname = ".real_escape_string2($columvalue).", ";
	}
	$updatestr = substr($updatestr, 0, -2);
	
	$sql = "UPDATE $tablename SET $updatestr WHERE $primarykey = '$pk_value'";

	// if($uniquekey != '')
	// {
	// 	if($this->db->checkExistData($tablename, $uniquekey, $arr[$uniquekey]))
	// 	{
	// 		$result['status'] = 0;
	// 		$result['msg'] = lang('The data is exists!');
	// 	}
	// }



	if(!$db->query($sql))
	{
		$result['status'] = 0;
		$result['msg'] = $db->error;
	}

	return $result;
}

function convertImgToUri($image)
{
	$type = $image['type'];
	$data = file_get_contents($image['tmp_name']);

	if($data == '')
	{
		return '';
	}
	return 'data:'.$type.';base64,'.base64_encode($data);
}

function moveUploadFile($file, $gallery_name)
{
	global $uid;

	$ext = pathinfo(basename($file['name']), PATHINFO_EXTENSION);

	$filename = generateRandomName().'.'.$ext;
	$path = "upload/gallery/$gallery_name";
	$filepath = "$path/$filename";

	$url = substr($_SERVER['HTTP_REFERER'], 0, strrpos($_SERVER['HTTP_REFERER'], '/'));

	$urlpath = $url."/upload/gallery/$gallery_name/$filename";

	if(!is_dir($path))
	{
	 		mkdir($path, 0777, true);
	}

	$move = move_uploaded_file($file['tmp_name'], $filepath);

	$result = array(
		'status' => $move,
		'urlpath' => $urlpath,
		'filepath' => $filepath,
		'filename' => $filename,
	);

	return $result;
}

function generateRandomName($length=30)
{
	$keyspace = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

  $str = '';
  $max = mb_strlen($keyspace, '8bit') - 1;
  for ($i = 0; $i < $length; ++$i) {
  	if(((($i+1) % 30) % 15) == 0)
  	{
  		$str .= '_';
  	}
      $str .= $keyspace[random_int(0, $max)];
  }
  return $str;
}
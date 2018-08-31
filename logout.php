<?
	include 'inc/function.php';
	session_start();
	$sGUID = $_SESSION['sessid'];
	$sql = "Update t_user set guid ='' where guid ='$sGUID'";
	mysql_query($sql);
	//$_SESSION['user']="";
	unset($_SESSION['sessid']);
	session_unregister('sessid');
	header('Location: admin.php?msg='.urlencode("Anda telah berhasil Logout!")); 
?>
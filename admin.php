<?
	include "inc/smarty/Smarty.class.php";
	include "inc/function.php";
	$smarty = new Smarty;
	session_start();
	// Check for a cookie, if none got to login page 
	if(!isset($_SESSION['sessid'])) { 
		$log = false;
	} else{
	
	// Try to find a match in the database 
	$sGUID = $_SESSION['sessid']; 
	$sQuery = "Select id_user From t_user Where guid='$sGUID'"; 
	$hResult = mysql_query($sQuery); 
	
	if(!mysql_num_rows($hResult)) { 
	//No match for guid 
		$log = false;
	} else{
		$log = true;
	}
	}
	if ($log){
		$kontrol = getData("kontrolisasi","t_user","guid='$sGUID'");
		if($kontrol){
			$usr = "<a href=\"admin_user.php\" class=\"link\">
					<img src=\"images/user.png\" width=\"48\" height=\"48\" border=\"0\" align=\"middle\"><br>User</a>";
			$style = "style=\"border:1px solid black \"";
		}
		$topnavigasi = "Control Panel";
		$bottomnavigasi = "<a href='logout.php' class='link'><strong>Logout</strong></a>";
		$title = "Administrator Bank Data";
		$content = "admin.htm";
		$smarty -> assign ("user",$usr);
		$smarty -> assign ("style",$style);
		$jumlah_bag = getCount("t_bagian","id_bagian");
		$jumlah_data = getCount("t_data","id_data");
		$bytes = number_format(getSum("ukuran_file","t_data","id_data"))." KB";
		$smarty -> assign("jumlah_bag",$jumlah_bag);
		$smarty -> assign("jumlah_data",$jumlah_data);
		$smarty -> assign("bytes",$bytes);
	}else{
		$topnavigasi = "Login";
		$title = "Login";
		$content = "loginadmin.htm";
		$smarty ->assign ("uname",$uname);
	}
	$header = "header.htm";
	$headsearch = "none.htm";
	$right = "none.htm";
	$smarty -> assign("base","http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);	
	$smarty -> assign("title",$title);
	$smarty -> assign("tanggal",tanggal());
	$smarty -> assign("topnavigasi",$topnavigasi);
	$smarty -> assign("bottomnavigasi",$bottomnavigasi);
	$smarty -> assign ("header",$header);
	$smarty -> assign ("headsearch",$headsearch);
	$smarty -> assign ("msg",$msg);
	$smarty -> assign ("content",$content);
	$smarty -> assign ("right",$right);
	$smarty -> display ("template.htm");
	
?>
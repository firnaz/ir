<?
	include "inc/smarty/Smarty.class.php";
	include "inc/function.php";
	include "inc/incSession.php";
	$smarty = new Smarty;
	$kontrol = getData("kontrolisasi","t_user","guid='$sGUID'");
	if(!$kontrol){
		header('Location: admin.php?msg='.urlencode("Anda tidak memiliki access <br> 
			untuk memasuki halaman tersebut!!"));
	}
	if ($action== "add"){
		if(!uname || !$passwd || !$retpasswd){
			$msg = "Username dan password harus diisi!!"; 
			$state="add";
		}else{
			if($passwd != $retpasswd){
				$msg = "Password tidak sama!!"; 
				$state="add";				
			}else{
				$sql = "insert into t_user values ('','$uname',Password('$passwd'),'','$kontrolisasi')";
				if(mysql_query($sql)){
					$msg = "User berhasil di add!!";
					header('Location: admin_user.php?msg='.urlencode($msg));
				} else {
					$msg = "Query Error!!";
					$state = "add";
				}
			}
		}
	} elseif($action =="edit"){
		if(!uname){
			$msg = "Username harus diisi!!"; 
			$state="edit";
		}else{
			if($passwod || $retpasswd){
				if ($passwd != $retpasswd){
					$msg = "Password tidak sama!!"; 
					$state="edit";				
				}else{
					$sqlplus = "password=Password('$passwd'),";
				}
			}
			$sql = "update t_user set uname='$uname', $sqlplus kontrolisasi='$kontrolisasi'
					where id_user='$id_user'";
			echo $sql;
			if(mysql_query($sql)){
				$msg = "User berhasil di edit!!";
				header('Location: admin_user.php?msg='.urlencode($msg)); 
			} else {
				$msg = "Query Error!!";
				$state = "edit";
			}
		}
	} elseif($action == "delete"){
		$sql = "delete from t_user where id_user='$id_user'";
		if(mysql_query($sql)){
			$msg = "User berhasil di edit!!";
			header('Location: admin_user.php?msg='.urlencode($msg)); 
		} 
	}

	if ($state == "add"){
		$topnavigasi = "Add User";
		$content = "admin_user_update.htm";
		$smarty -> assign ("action",$state);
		$smarty -> assign ("uname",$uname);
		if($kontrolisasi){
			$smarty -> assign("kontrolisasi","checked");
		}
	} elseif($state == "edit"){
		$sql = "select id_user,uname,kontrolisasi from t_user where id_user='$id_user'";
		$rs = mysql_query($sql);
		while ($row= mysql_fetch_array($rs)){
			$id_user = $row[0];
			$uname = $row[1];
			$kontrolisasi = $row[2];
		}
		$topnavigasi = "Edit User";
		$content = "admin_user_update.htm";
		$smarty -> assign ("action",$state);
		$smarty -> assign ("id_user",$id_user);
		$smarty -> assign ("uname",$uname);
		if($kontrolisasi){
			$smarty -> assign("kontrolisasi","checked");
		}
		$smarty -> assign ("pesan", ":: Jika field password tidak di isi maka password tidak berubah.");
		$smarty -> assign ("cancel","<input type=\"button\" name=\"cancel\" value=\"cancel\" onClick=\"history.back()\" class=\"button\">");
	}else{
		$sql = "select id_user,uname,kontrolisasi from t_user order by id_user ASC"; 
		$rs = mysql_query($sql);
		$myid = getData("id_user","t_user","guid='$sGUID'");
		while ($row = mysql_fetch_array($rs)){
			$id_user[] = $row[0];
			$uname[] = $row[1];
			if ($row[2]){
				$kontrolisasi[] = "Ya";
			}else{
				$kontrolisasi[] = "Tidak";
			}
			if($row[0] >1){
				if($row[0] == $myid){
					$delete[] = " ";
					$edit[] = " ";
				}else{
					$delete[] = "<img src=\"images/button_drop.png\" alt=\"delete\" width=\"11\" height=\"13\" border=\"0\">";
					$edit[] = "<img src=\"images/button_edit.png\" alt=\"edit\" width=\"12\" height=\"13\" border=\"0\">";
				}
			} else{
				$edit[] = " ";
				$delete[] = " ";
			}
		}
		$smarty -> assign ("id_user",$id_user);
		$smarty -> assign ("uname",$uname);
		$smarty -> assign ("kontrolisasi",$kontrolisasi);
		$smarty -> assign ("edit",$edit);
		$smarty -> assign ("delete",$delete);
		$topnavigasi = "Kontrol User";
		$content = "admin_user.htm";
	}
	$title = "Administrator Bank Data - Kontrol User";
	$bottomnavigasi = "<a href='logout.php' class='link'><strong>Logout</strong></a>";
	$header = "header.htm";
	$headsearch = "none.htm";
	if ($kontrol){
		$smarty ->assign ("user",showUserControl());
	}
	$right = "rightadmin.htm";
	$jumlah_bag = getCount("t_bagian","id_bagian");
	$jumlah_data = getCount("t_data","id_data");
	$bytes = number_format(getSum("ukuran_file","t_data","id_data"))." KB";
	$smarty -> assign("jumlah_bag",$jumlah_bag);
	$smarty -> assign("jumlah_data",$jumlah_data);
	$smarty -> assign("bytes",$bytes);
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
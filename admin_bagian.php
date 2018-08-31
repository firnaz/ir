<?
	include "inc/smarty/Smarty.class.php";
	include "inc/nanaz.class.php";
	include "inc/function.php";
	include "inc/incSession.php";
	if (($page=$HTTP_GET_VARS['page'])==""){
		$page=0;
	}
	if((($halaman=$HTTP_GET_VARS['halaman'])=="") || (($halaman=$HTTP_GET_VARS['halaman'])==0)){
		$halaman=1;
	}
	$pagelist = new nanaz;
	$smarty = new Smarty;
	if ($_GET['action']){
		$action = $_GET['action'];
	}else {
		$action = $_POST['action'];		
	}
	$state = $_GET['state'];
	if($_GET['id_bagian']){
		$id_bagian = $_GET['id_bagian'];
	}else{
		$id_bagian = $_POST['id_bagian'];
	}
	$msg = $_GET['msg'];
	if ($action== "add"){
		$bagian =$_POST['bagian'];
		if ($bagian==""){
			$msg = "Error : Nama Bagian harus diisi !! <br>";
		} else{
			$sql = "select * from t_bagian where nama_bagian='$bagian'";
			if (@mysql_num_rows(mysql_query($sql))){
				$msg = "Error : Nama Bagian Sudah ada!! <br>";
			}else {
				$bag = preg_replace("/\W+/i","",$bagian);
				if (!$bag|| strlen($bag) <=1){
					$msg = "Nama Bagian salah!!<br>"; 
				} else{
					$folder = preg_replace("/\W+/i","",$bagian);
					$folder = strtolower($folder);
					$folder = ereg_replace(" ", "_",$folder);
					$dir= "data/";
					$sql = "Insert into t_bagian values('','$bagian','$folder')";
					if (mysql_query($sql)){
						mkdir($dir.$folder);
						header('Location: admin_bagian.php?msg='.urlencode(":: Bagian Berhasil di tambahkan!!"));
					}else{
						$msg = "Query Error!! <br>";
						$id_bagian="";
					}
				}
			}
		}
	} elseif($action =="edit"){
		$bagian_baru = $_GET['bagian_baru'];
		if ($bagian_baru==""){
			$msg = "Error : Nama Bagian Baru harus diisi !! <br>";
		} else{
			$sql = "select * from t_bagian where nama_bagian='$bagian_baru'";
			if (@mysql_num_rows(mysql_query($sql))){
				$msg = "Error : Nama Bagian Sudah ada!! <br>";
			}else {
				$bag = preg_replace("/\W+/i","",$bagian_baru);
				if (!$bag|| strlen($bag) <=1){
					$msg = "Nama Bagian Baru salah!!<br>"; 
				} else{
					$folder = preg_replace("/\W+/i","",$bagian_baru);
					$folder = strtolower($folder);
					$folder = ereg_replace(" ", "_",$folder);
					$dir= "data/";
					$sql = "Update t_bagian set nama_bagian='$bagian_baru', folder='$folder' where id_bagian='$id_bagian'";
					if (mysql_query($sql)){
						rename($dir.$folder_lama,$dir.$folder);
						header('Location: admin_bagian.php?msg='.urlencode(":: Bagian Berhasil di Update!!"));
					}else{
						$msg = "Query Error!! <br>";
						$id_bagian="";
					}
				}
			}
		}		
	} elseif($action == "empty"){
		$sql = "select letak,temp_file from t_data where id_bagian='$id_bagian'";
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)){
			unlink($row[0]);
			@unlink($row[1]);
		}
		$sql = "DELETE from t_data where id_bagian='$id_bagian'";
		if (mysql_query($sql)){
			header('Location: admin_bagian.php?msg='.urlencode(":: Bagian Berhasil di Kosongkan!!"));
		}else{
			$msg = "Query Error!! <br>";
			$id_bagian="";
		}
	} elseif($action == "delete"){
		$dir = "data/"; 
		$sql = "select letak,temp_file from t_data where id_bagian='$id_bagian'";
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)){
			unlink($row[0]);
			@unlink($row[1]);			
		}
		$sql = "select folder from t_bagian where id_bagian='$id_bagian'";
		$folder = mysql_fetch_row(mysql_query($sql));
		$sql = "DELETE from t_data where id_bagian='$id_bagian'";
		mysql_query($sql);
		$sql = "Delete from t_bagian where id_bagian='$id_bagian'";
		if (mysql_query($sql)){
			rmdir($dir.$folder[0]);
			header('Location: admin_bagian.php?msg='.urlencode(":: Bagian Berhasil di Hapus!!"));
		}else{
			$msg = "Query Error!! <br>";
			$id_bagian="";
		}
		
	}
	if($state == "edit"){
		$topnavigasi = "Edit Bagian";
		$content = "admin_bagian_Update.htm";
		$sql = "select id_bagian,nama_bagian,folder from t_bagian where id_bagian='$id_bagian'";
		$data = mysql_fetch_array(mysql_query($sql));
		$smarty -> assign ("id_bagian",$data[0]);
		$smarty -> assign ("nama_bagian_lama",$data[1]);
		$smarty -> assign ("folder_lama",$data[2]);
		$smarty -> assign ("nama_bagian_baru",$bagian_baru);
	}else{
		$sql = "SELECT COUNT(*) FROM t_bagian";
		$jumlah = mysql_num_rows(mysql_query($sql));
		$pagelist -> listNavigate($page,10,$sql,$konek);
		$pagenavigation = $pagelist -> showPage($halaman,"","link");
		$sql = "SELECT id_bagian, nama_bagian FROM t_bagian 
				order by nama_bagian limit ".$pagelist ->showList().", ".$pagelist->showItem();
		//echo $pagelist->showJumlah();
		if ($pagelist->showJumlah()){
			$rs = mysql_query($sql);		
			while ($row = mysql_fetch_array($rs)){
				$id_bagian[]= $row[0];
				$nama_bagian[]= $row[1];
				$jumlah_file[] = getCount("t_data","id_bagian='$row[0]'");
				$total_byte[] = number_format(getSum("ukuran_file","t_data","id_bagian='$row[0]'")/1024)." KB";
			}
			$smarty -> assign ("id_bagian",$id_bagian);
			$smarty -> assign ("nama_bagian",$nama_bagian);
			$smarty -> assign ("jumlah_file",$jumlah_file);
			$smarty -> assign ("total_bytes",$total_byte);
			
		} else{
			$msg.= " :: List Bagian masih kosong";
		}
		$topnavigasi = "Kontrol Bagian";
		$content = "admin_bagian.htm";
	}
	$title = "Administrator Bank Data - Kontrol Bagian";
	$bottomnavigasi = "<a href='logout.php' class='link'><strong>Logout</strong></a>";
	$header = "header.htm";
	$headsearch = "none.htm";
	$kontrol = getData("kontrolisasi","t_user","guid='$sGUID'");
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
	$smarty -> assign("pagenavigation",$pagenavigation);
	$smarty -> assign ("header",$header);
	$smarty -> assign ("headsearch",$headsearch);
	$smarty -> assign ("msg",$msg);
	$smarty -> assign ("content",$content);
	$smarty -> assign ("right",$right);
	$smarty -> display ("template.htm");
	
?>
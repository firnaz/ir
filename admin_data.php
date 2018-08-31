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
	$smarty = new Smarty;
	$pagelist = new nanaz;
	if ($_GET['action']){
		$action = $_GET['action'];
	}else {
		$action = $_POST['action'];		
	}
	if($_GET['state']){
		$state = $_GET['state'];
	} else{
		$state = $_POST['state'];	
	}
	if ($_POST['id_bagian']){
		$id_bagian=$_POST['id_bagian'];
	} else{
		$id_bagian=$_GET['id_bagian'];		
	}
	$penulis = stripslashes($_POST['penulis']);
	$nama_data = stripslashes($_POST['nama_data']);
	$upload_file = $_FILES['upload_file'];
	//print_r($upload_file);
	if($_GET['id_data']){
		$id_data = $_GET['id_data'];
	}else{
		$id_data = $_POST['id_data'];
	}
	$msg = $_GET['msg'];
	if ($action== "add"){
		if(!$nama_data || !$upload_file || !$id_bagian){
			$msg = "Yang bertanda <strong>*</strong> tidak boleh kosong!!";
			$state = "add";
		} else{
			$ren = date("YmdHis");
			$date = date ("Y-m-d");
			$dir = "data/";
			$nama_file = $upload_file['name'];
			$nama_file_baru =$ren.strtolower(ereg_replace(" ","_",$nama_file)); 
			$folder = getData("folder","t_bagian","id_bagian='".$id_bagian."'")."/";
			$letak = $dir.$folder.$nama_file_baru;
			$type_file = $upload_file['type'];
			$ukuran_file = $upload_file['size'];
			if ($ukuran_file > 0){
				@copy($upload_file['tmp_name'],$letak);	
				$desc = getDesc($letak,$type_file);
				$sql = "insert into t_data values('','$id_bagian','$date','$nama_data','$letak',
						'$nama_file','$type_file','$ukuran_file','$penulis','".addslashes($desc[0])."','".$desc[1]."')";
				//echo $sql;
				if(mysql_query($sql)){
					$msg = ":: File berhasil di add!!";
					header('Location: admin_data.php?id_bagian='.$id_bagian.'&msg='.urlencode($msg));
				}else{
					$msg = "Query Error!!";
					$state= "add";					
				}
			}else{
				$msg = "File gagal di Upload!!";
				$state= "add";
			}
		}
	} elseif($action =="edit"){
		if(!$nama_data || !$id_bagian){
			$msg = "Yang bertanda <strong>*</strong> tidak boleh kosong!!";
			$state = "edit";
		} else{
			$date = date ("Y-m-d");
			if ($upload_file){
				$filelama = getData("letak","t_data","id_data='$id_data'");
				$temp_filelama = getData("temp_file","t_data","id_data='$id_data'");
				unlink($filelama);
				@unlink($temp_filelama);
				$ren = date("YmdHis");
				$dir = "data/";
				$nama_file = $upload_file['name'];
				$nama_file_baru =$ren.strtolower(ereg_replace(" ","_",$nama_file)); 
				$folder = getData("folder","t_bagian","id_bagian='".$id_bagian."'")."/";
				$letak = $dir.$folder.$nama_file_baru;
				$type_file = $upload_file['type'];
				$ukuran_file = $upload_file['size'];
				if (copy($upload_file['tmp_name'],$letak)){
					$desc = getDesc($letak,$type_file);	
					$sqlplus = "letak='$letak', nama_file='$nama_file',type_file='$type_file',
								ukuran_file='$ukuran_file',deskripsi='".$desc[0]."',temp_file='".$desc[1]."',";				
					
				}
			}
				
			$sql = "Update t_data set id_bagian='$id_bagian',nama_data='$nama_data', tanggal='$date',
					$sqlplus penulis='$penulis' 
					where id_data='$id_data'";
			if(mysql_query($sql)){
				$msg = ":: File berhasil di edit!";
				header('Location: admin_data.php?id_bagian='.$id_bagian.'&msg='.urlencode($msg));
			}else{
				$msg = "Query Error!!";
				$state= "edit";					
			}

		}
	} elseif($action == "delete"){
		$filelama = getData("letak","t_data","id_data='$id_data'");
		$temp_filelama = getData("temp_file","t_data","id_data='$id_data'");
		unlink($filelama); 
		@unlink($temp_filelama);
		$sql = "delete from t_data where id_data='$id_data'";
		if(mysql_query($sql)){
			$msg ='Data berhasil di Hapus !!';
			$sql = "delete from t_index where id_data='$id_data'";
			header('Location: admin_data.php?id_bagian='.$id_bagian.'&msg='.urlencode($msg));
		}
	}
	$sql = "select id_bagian,nama_bagian from t_bagian order by nama_bagian";
	$rs = mysql_query($sql);
	if (!@mysql_num_rows($rs)){
		header('Location: admin_bagian.php?msg='.urlencode("Add Bagian terlebih dahulu<br>")); 
	}
	$ids_bagian[] = "";
	$nama_bagian[] = "";
	while ($row = mysql_fetch_array($rs)){
		$ids_bagian[] = $row[0];
		$nama_bagian[] = $row[1];
	}
	$smarty -> assign ("ids_bagian",$ids_bagian);
	$smarty -> assign ("nama_bagian",$nama_bagian);
	
	if ($state == "add"){
		$topnavigasi = "Add Data";
		$content = "admin_data_update.htm";
		$smarty -> assign ("action",$state);
		$smarty -> assign ("id_bagian",$id_bagian);
		$smarty -> assign ("nama_data",$nama_data);
		$smarty -> assign ("penulis",$penulis);
	} elseif($state == "edit"){
		$sql = "select id_data,id_bagian,nama_data,nama_file,penulis from t_data where id_data='$id_data'";
		$rs = mysql_query($sql);
		while ($row= mysql_fetch_array($rs)){
			$id_data = $row[0];
			$id_bagian = $row[1];
			$nama_data = $row[2];
			$nama_file = "(".$row[3].")";
			$penulis = $row[4];
		}
		$topnavigasi = "Edit Data";
		$content = "admin_data_update.htm";
		$smarty -> assign ("action",$state);
		$smarty -> assign ("id_data",$id_data);
		$smarty -> assign ("id_bagian",$id_bagian);
		$smarty -> assign ("nama_data",$nama_data);
		$smarty -> assign ("nama_file",$nama_file);
		$smarty -> assign ("penulis",$penulis);
		$smarty -> assign ("cancel","<input type=\"button\" name=\"cancel\" 
						value=\"cancel\" onClick=\"history.back()\" class=\"button\">");
	}else{
		if($id_bagian==""){
			$msg = "Pilih Bagian Terlebih dahulu";
		} else{
			$smarty ->assign ("id_bagian",$id_bagian);
			$sql = "SELECT COUNT(*) FROM t_data where id_bagian='$id_bagian'";
			$pagelist -> listNavigate ($page,10,$sql,$konek);
 			$pagenavigation = $pagelist ->showPage($halaman,"&id_bagian=$id_bagian","link");
			 
 			$sql = "select id_data,nama_data,nama_file,ukuran_file from t_data 
					where id_bagian='$id_bagian' order by id_data desc LIMIT ".$pagelist->showList().",".
					$pagelist->showItem();
			$rs = mysql_query($sql);
			if (@mysql_num_rows($rs)){
				while ($row = mysql_fetch_array($rs)){
					$id_data[] = $row[0];
					$nama_data[] = $row[1];
					$nama_file[] = $row[2];
					$ukuran_file[] = number_format($row[3]/1024)." KB";
				}
				$smarty -> assign ("id_data",$id_data);
				$smarty -> assign ("nama_data",$nama_data);
				$smarty -> assign ("nama_file",$nama_file);
				$smarty -> assign ("ukuran_file",$ukuran_file);
			} else{
				$msg.= " :: Data masih kosong";
			}
		}
		$topnavigasi = "Kontrol Data";
		$content = "admin_data.htm";
	}
	$title = "Administrator Bank Data - Kontrol Data";
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
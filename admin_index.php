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
	$state = $_GET['state'];
	$success = $_GET['success'];
	$msg = $_GET['msg'];
	$totaltime = $_GET['totaltime'];
	$mode = $_GET['mode'];
	$id_bagian = $_GET['id_bagian'];
	$id_data = $_GET['id_data'];
	if($state == "1"){
		$content = "admin_index_success.htm";
		if ($success){
			if ($mode == "all"){
				$sql = "select id_data,nama_data,nama_file,ukuran_file from t_data";
				$plussql = "id_data";
			}elseif($mode == "bagian"){
				$sql = "select id_data,nama_data,nama_file,ukuran_file 
						from t_data where id_bagian='$id_bagian'";
				$plussql = "id_bagian='$id_bagian'";
			}else if ($mode == "dokumen"){
				$OrSql = true;
				if (count($id_data)){
					for ($i =0 ; $i <= count($id_data)- 1 ; $i++){
						if ($OrSql){
							$plussql = "id_data=".$id_data[$i]." ";
							$OrSql = false;
						} else{
							$plussql = "OR id_data=".$id_data[$i]." ";
						}
						$QStr.= "id_data[]=".$id_data[$i]."&";
					}
					$sql = "select id_data,nama_data,nama_file,ukuran_file 
							from t_data where $plussql";
				}
			}
			$jumlah_halaman = getCount("t_data",$plussql);			
			$rs = mysql_query($sql);
			$i=1;
			while ($row = mysql_fetch_row($rs)){
				$no[] = $i++.".";
				$nama_data[] = $row[1];
				$nama_file[] = $row[2];
				$ukuran_file[] =  number_format($row[3]/1024)." KB";
				$total_kata[] = getCount("t_index","id_data=".$row[0]);
			}
/* 			$jam = floor($totaltime/3600);
			$menit = floor(($totaltime%3600)/60);
			$detik = $totaltime%60;
 */			$totaltime = gmdate("H:i:s",$totaltime);
			$smarty ->assign ("totaltime",$totaltime);
			$smarty ->assign ("jumlah_halaman",$jumlah_halaman);
			$smarty ->assign ("no",$no);
			$smarty ->assign ("nama_data",$nama_data);
			$smarty ->assign ("nama_file",$nama_file);
			$smarty ->assign ("ukuran_file",$ukuran_file);
			$smarty ->assign ("total_kata",$total_kata);
		}else{
			$state = 0;
		}
	}
	if (!$state){
		$jumlah_data = getCount("t_data","id_data");
		if ($jumlah_data){
			$sql = "select id_bagian,nama_bagian from t_bagian order by nama_bagian";
			$rs = mysql_query($sql);
			$ids_bagian[] = "";
			$nama_bagian[] = "";
			while ($row = mysql_fetch_array($rs)){
				$ids_bagian[] = $row[0];
				$nama_bagian[] = $row[1];
			}
			$smarty -> assign ("ids_bagian",$ids_bagian);
			$smarty -> assign ("nama_bagian",$nama_bagian);
			
			$sql = "select count(*) from t_data";
			$pagelist-> listNavigate($page,10,$sql,$konek);
 			$pagenavigation = $pagelist ->showPage($halaman,"","link");			
			$sql = "select t_data.id_data,t_data.nama_data,t_bagian.nama_bagian,t_data.nama_file,
					t_data.ukuran_file from t_data,t_bagian 
					where t_data.id_bagian=t_bagian.id_bagian order by nama_data
					LIMIT ".$pagelist ->showList().",".$pagelist->showItem();
			$rs = mysql_query($sql);
			while ($row = mysql_fetch_array($rs)){
				$id_data[] =$row[0];
				$nama_data[] = $row[1];
				$bagian[] = $row[2];
				$nama_file[] = $row[3];
				$ukuran_file[] = number_format($row[4]/1024)." KB";
				
				$smarty->assign("id_data",$id_data);
				$smarty->assign("nama_data",$nama_data);
				$smarty->assign("bagian",$bagian);
				$smarty->assign("nama_file",$nama_file);
				$smarty->assign("ukuran_file",$ukuran_file);
			}
		} else{
			$msg.= " :: Data masih kosong silahkan isi data terlebih dahulu!!";
		}
		$content = "admin_index_form.htm";
	}
	$topnavigasi = "Indeks Dokumen";	
	$title = "Administrator Bank Data - Indeks Dokumen";
	$bottomnavigasi = "<a href='logout.php' class='link'><strong>Logout</strong></a>";
	$header = "header.htm";
	$headsearch = "none.htm";
	$kontrol = getData("kontrolisasi","t_user","guid='$sGUID'");
	if ($kontrol){
		$smarty ->assign ("user",showUserControl());
	}
	$script = "OnLoad=\"setVariables();checkLocation()\"";
	//rightmain
	$right = "rightadmin.htm";
	$jumlah_bag = getCount("t_bagian","id_bagian");
	$jumlah_data = getCount("t_data","id_data");
	$bytes = number_format(getSum("ukuran_file","t_data","id_data"))." KB";
	$smarty -> assign("jumlah_bag",$jumlah_bag);
	$smarty -> assign("jumlah_data",$jumlah_data);
	$smarty -> assign("bytes",$bytes);
	//
	$smarty -> assign("script",$script);
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
	mysql_close($konek);
	
?>
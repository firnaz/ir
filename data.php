<?
	$id_bagian = $_GET['id_bagian'];
	include "inc/smarty/Smarty.class.php";
	include "inc/nanaz.class.php";
	include "inc/function.php";
	if (($page=$_GET['page'])==""){
		$page=0;
	}
	if((($halaman=$_GET['halaman'])=="") || (($halaman=$_GET['halaman'])==0)){
		$halaman=1;
	}
	$pagelist = new nanaz; 
	$sql = "SELECT COUNT(*) FROM t_data where id_bagian='$id_bagian'";
	$pagelist -> listNavigate($page,5,$sql,$konek);
	//echo $pagelist -> showList();
	$info_page ="Halaman ke $halaman dari ".$pagelist->showPageNum()." halaman";
 	$pagenavigation = $pagelist -> showPage($halaman,"&id_bagian=$id_bagian","link");
	
	$nama_bagian = getData("nama_bagian","t_bagian","id_bagian='$id_bagian'");
	$sql = "select id_data,tanggal,nama_data,nama_file,type_file,ukuran_file,penulis,deskripsi 
			from t_data where id_bagian='$id_bagian' order by nama_data ASC LIMIT ".
			$pagelist->showList().",". $pagelist->showItem();
	$rs = mysql_query($sql);
	while ($row = mysql_fetch_array($rs)){
		$id_data[] = $row[0];
		$last_update[] = convertTgl($row[1]);
		$nama_data[] = $row[2];
		$nama_file[] = wordwrap($row[3],50,"<br>",1);
		if ($row[4] == "application/vnd.ms-excel"){
			$file_image[]= "excel.png";
		} elseif($row[4] == "application/msword"){
			$file_image[] = "word.png";
		} elseif($row[4] == "application/pdf"){
			$file_image[] = "pdf.png";
		}else{
			$file_image[] = "other.png";
		}
		$ukuran_file[] = number_format($row[5])." KB";
		$penulis[] = $row[6];
		$deskripsi[] = wordwrap($row[7],45,"<br>");
	}
	
	$title = "Bank Data - Daftar Dokumen";
	$header = "header.htm";
	$headsearch = "headsearch.htm";
	$content = "data.htm";
	$right = "rightmain.htm";
	$topnavigasi = "Daftar Dokumen";
	$bottomnavigasi = "";
	$smarty = new Smarty;
	
	//rightmain
	$sql = "select nama_data,nama_file,ukuran_file from t_data order by tanggal DESC";
	$row = mysql_fetch_array(mysql_query($sql));
	$n_data = $row[0];
	$file = wordwrap($row[1],33,"<br>",1);
	$bytes = number_format($row[2])." KB";
	$smarty -> assign("n_data",$n_data);
	$smarty -> assign("file",$file);
	$smarty -> assign("bytes",$bytes);
	$smarty -> assign("info_page",$info_page);
	$smarty -> assign("pagenavigation",$pagenavigation);
	$smarty -> assign("nama_bagian",$nama_bagian);
	$smarty -> assign("id_data",$id_data);
	$smarty -> assign("nama_data",$nama_data);
	$smarty -> assign("last_update",$last_update);	
	$smarty -> assign("nama_file",$nama_file);
	$smarty -> assign("file_image",$file_image);
	$smarty -> assign("ukuran_file",$ukuran_file);
	$smarty -> assign("penulis",$penulis);
	$smarty -> assign("deskripsi",$deskripsi);
	$smarty -> assign("title",$title);
	$smarty -> assign("base","http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);	
	$smarty -> assign ("header",$header);
	$smarty -> assign ("headsearch",$headsearch);
	$smarty -> assign ("topnavigasi",$topnavigasi);
	$smarty -> assign ("tanggal",tanggal());
	$smarty -> assign ("content",$content);
	$smarty -> assign ("right",$right);
	$smarty -> display ("template.htm");
?>
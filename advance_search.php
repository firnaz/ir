<?
	include "inc/smarty/Smarty.class.php";
	include "inc/nanaz.class.php";
	include "inc/function.php";
	$action = $_POST['action'];
	$kata_tepat = $_POST['kata_tepat'];
	$kata_kunci = $_POST['kata_kunci'];
	$pilih_bagian = $_POST['pilih_bagian'];
	$type_file = $_POST['type_file'];
	$mode = $_POST['mode'];
	if ($action == "Cari"){
		if (trim($kata_tepat)){
			$q1 = '"'.$kata_tepat.'" ';
		}
		if (trim($kata_kunci)){
			$q2 = $kata_kunci;
		}
		$q = $q1.$q2;
		header('Location: search.php?q='.urlencode($q).'&mode='.$mode.'&pilih_bagian='.$pilih_bagian.
				'&type_file='.$type_file);
	}
	
	$content = "advance_search.htm";
	$title = "Bank Data - Pencarian Canggih";
	$header = "header.htm";
	$headsearch = "none.htm";
	$right = "rightmain.htm";
	$topnavigasi = "Pencarian Canggih";
	$bottomnavigasi = "";
	$smarty = new Smarty;
	
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

	//rightmain
	$sql = "select nama_data,nama_file,ukuran_file from t_data order by tanggal DESC";
	$row = mysql_fetch_array(mysql_query($sql));
	$n_data = $row[0];
	$file = wordwrap($row[1],33,"<br>",1);
	$bytes = number_format($row[2])." KB";
	$smarty -> assign("n_data",$n_data);
	$smarty -> assign("file",$file);
	$smarty -> assign("bytes",$bytes);
	//
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
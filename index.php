<?
	include "inc/smarty/Smarty.class.php";
	include "inc/function.php";
	
	$sql = "select id_bagian,nama_bagian from t_bagian order by nama_bagian ASC";
	$rs = mysql_query($sql);
	while ($row=mysql_fetch_array($rs)){
		$id_bagian[] = $row[0];
		$nama_bagian[] = $row[1];
		$total_data[] = getCount("t_data","id_bagian='$row[0]'");
	} 
	$title = "Bank Data";
	$header = "header.htm";
	$headsearch = "headsearch.htm";
	$content = "home.htm";
	$right = "rightmain.htm";
	$topnavigasi = "Daftar Bagian Penyimpanan";
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
	
	$smarty -> assign("id_bagian",$id_bagian);
	$smarty -> assign("nama_bagian",$nama_bagian);
	$smarty -> assign("total_data",$total_data);
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
<? 	
	$id_data=$_GET['id_data'];
	include "inc/function.php";
	$sql = "select letak,nama_file,type_file from t_data where id_data='$id_data'";
	$row = mysql_fetch_array(mysql_query($sql));
	$files = $row[0];
	ob_start();
	echo file_get_contents($files);
	$nama_files = $row[1];
	$type = $row[2];
	$size = filesize($files);
	header('Content-Type: '.$type);
	header('Content-Length: '.$size);
	header('Content-Disposition: attachment; filename="'.$nama_files.'"');
	ob_end_flush();
?>
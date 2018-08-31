<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Tersimpan</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<?
$id_data=$_GET['id_data'];
$q=$_GET['q'];
include "inc/ir.function.php";
$temp_file = getData("temp_file","t_data","id_data=$id_data");
$string = ereg_replace("\n","<br>",getFile($temp_file));
if ($q){
	$q = urldecode($q);
	$phrase = getPhraseArray($q,array("."));
	for($i=0 ; $i <= count($phrase)-1 ; $i++){
		//eregi($phrase[$i],$string,$regs);
		//for ($j=0 ; $j <=count($regs)-1 ;$j++){
		if (trim($phrase[$i])){
			$string = preg_replace('/\b'.$phrase[$i].'\b/i',
						"<span style=\"background-color:#FFFF00\">\\0</span>",$string);
		}
		//}
	}
}
echo $string;
?>
</body>
</html>

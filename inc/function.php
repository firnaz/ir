<?php
require_once 'db.php';


function getFile($filename){
	if (file_exists($filename)){
		$fp=fopen($filename,"r");
		$fd=fread($fp,filesize($filename));
		fclose($fp);
	}
	return $fd;
}
function getCount($table, $where){
	global $konek;
	$sql=mysql_query("SELECT COUNT(*) FROM $table WHERE $where",$konek);
	$data=@mysql_fetch_row($sql);
	$count=$data[0];
	return number_format($count);
}
function getSum($field,$table, $where){
	global $konek;	
	$sql=mysql_query("SELECT SUM($field) FROM $table WHERE $where",$konek);
	$data=@mysql_fetch_row($sql);
	$sum=$data[0];
	return $sum;
}
function getData($field,$table,$where){
	global $konek;
	$sql=mysql_query("SELECT $field FROM $table WHERE $where",$konek);
	$data=@mysql_fetch_row($sql);
	$content=$data[0];
	return $content;
}
Function tanggal() { //fungsi untuk menampilkan tanggal
         $namaHari = array('Minggu', 'Senin','Selasa','Rabu','Kamis','Jum\'at','Sabtu');
         $namaBulan = array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
         $strtanggal.= $namaHari[date('w')];
         $strtanggal.= ", ";
         $strtanggal.= date('d');
         $strtanggal.= " ";
         $strtanggal.= $namaBulan[date('n')-1];
         $strtanggal.= " ";
         $strtanggal.= date('Y');
return $strtanggal;
}

Function convertTgl($tgl){
	$tanggal = getdate(strtotime($tgl));
	$namaHari = array('Minggu', 'Senin','Selasa','Rabu','Kamis','Jum\'at','Sabtu');
	$namaBulan = array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
	$strtanggal.= $namaHari[$tanggal['wday']];
	$strtanggal.= ", ";
	$strtanggal.= $tanggal['mday'];
	$strtanggal.= " ";
	$strtanggal.= $namaBulan[$tanggal['mon']-1];
	$strtanggal.= " ";
	$strtanggal.= $tanggal['year'];
	return $strtanggal;
} 
function showUserControl(){
	$str = "  <tr>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#F4FCF8\">&nbsp;</td>
			  </tr>
			  <tr>
				<td align=\"center\" valign=\"middle\" bgcolor=\"#F4FCF8\"><table width=\"80\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\" style=\"border:1px solid black \">
				  <tr>
					<td align=\"center\" valign=\"middle\"><a href=\"admin_user.php\" class=\"link\"><img src=\"images/user.png\" width=\"48\" height=\"48\" border=\"0\"><br>
					  User</a></td>
				  </tr>
				</table></td>
			  </tr>";
	return $str;
}
/* function getContentFromFile($temp_file){
	unset($arrdesc);
	if (file_exists($temp_file)){
		$arrdesc = file_get_contents($temp_file);
	} 
	return $arrdesc; 
}
 */
function getDesc($file,$type_file){
	if ($type_file == "application/pdf"){
		$bin = "bin/xpdf/pdftotext";
		$type = "pdf";
	}elseif($type_file == "application/vnd.ms-excel"){
		$bin = "bin/catdoc/bin/xls2csv";
		$type = "xls";
	}elseif($type_file == "application/msword"){
		$bin = "bin/catdoc/bin/catdoc";
		$type = "doc";
	} else{
		$bin = "";
		$desc = "Tidak terdapat deskripsi untuk file ini";
	}
	if ($bin){
		$ren = date("YmdHis_");
		for ($i=1; $i<=8; $i++) {
		  $temp_filename .= rand(1,9);
		}
		$temp_file = "tmp/".$temp_filename.".tmp";
		$temp_file_save = "tmp/".$ren.$temp_filename.".tmp";
		$dir = dirname($_SERVER['SCRIPT_FILENAME'])."/";
		if (copy($file,$temp_file)){
			$execute = $dir.$bin." ".$dir.$temp_file;
			if ($type == "doc" || $type == "xls"){
				exec($execute,$output);
				for($i=0;$i <= count($output)-1 ;$i++){
					$arrdesc.=$output[$i]."\n"; 
				}
				if ($type =="xls"){
					$arrdesc = ereg_replace(";"," ",$arrdesc);
					$arrdesc = ereg_replace("\""," ",$arrdesc);
				}
			  $berkas=fopen($temp_file_save,"w");
			  fputs($berkas,$arrdesc);
			  fclose($berkas);
			} elseif ($type == "pdf"){
				exec($execute);
				$temp_file2 = $temp_file.".txt";
				if (file_exists($temp_file2)){
					$arrdesc = getFile($temp_file2);
					copy($temp_file2,$temp_file_save);
					unlink($temp_file2);
				} else{
					$desc = "Deskripsi file tidak ada.";
				}
			}
			if ($arrdesc){
				$desc = preg_replace("/\n/"," ",$arrdesc);
				$desc = preg_replace("/\s+/"," ",$desc);
				$desc = preg_replace("/\s+[\d+\W*]*\s+/"," ",$desc);
				//$desc = preg_replace("/\s+\S\S\S\s+/","",$desc);
				$desc = substr($desc,0,150)."...";				
			}
		}
		unlink($temp_file);
	}
	return array($desc,$temp_file_save);
}
?>
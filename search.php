<?
	$q= stripslashes($_GET['q']);	
	$type_file = $_GET['type_file'];
	$mode = $_GET['mode'];
	$pilih_bagian = $_GET['pilih_bagian'];
	include "inc/smarty/Smarty.class.php";
	include "inc/ir.function.php";
	//$q = "kuliah kerja nyata";
	if (($page=$HTTP_GET_VARS['page'])==""){
		$page=0;
	}
	if((($halaman=$HTTP_GET_VARS['halaman'])=="") || (($halaman=$HTTP_GET_VARS['halaman'])==0)){
		$halaman=1;
	}
	$stopwords = file("./bin/stopwords.txt");
	for ($i =0 ; $i <= count($stopwords) -1; $i++){
		$stopwords[$i]= trim($stopwords[$i]);
	}		
	if (!$found){
		if ($mode == "advance_search"){
			if ($pilih_bagian){
				$addSqlBagian = " and t_data.id_bagian = '$pilih_bagian'";
			}
			if($type_file){
				$addSqlType = " and t_data.type_file = '$type_file'";
			}
		}
		if (trim($q)){
			$mtime=microtime();
			$mtime=explode(" ",$mtime);
			$mtime=$mtime[1] + $mtime[0];
			$tstart=$mtime;
			$phrase = getPhraseArray($q,$stopwords);			
			$TermArray = getTermArray($q,$stopwords);
			$plussql = $TermArray[0];
			//$array_user_query = $TermArray[1];
			//$Qvektor = $TermArray[2];
			$sql = "select distinct t_data.id_data 
					from t_index,t_term,t_data
					where t_index.id_term=t_term.id_term and t_data.id_data=t_index.id_data 
					and ($plussql) $addSqlBagian $addSqlType";
			$rs = mysql_query($sql,$konek);
/* 			print_r($array_user_query);
			echo "<br>";
			print_r($phrase);
			echo "<br>";									
 */			if (@mysql_num_rows($rs)){
				while ($row = mysql_fetch_row($rs)){
					unset($Dvektor);
					unset($tf);
					unset($idf);
					for ($i = 0 ; $i <= count($phrase)-1;$i++){
						if($wij = getTruePhrase($phrase[$i],$row[0],$stopwords)){
/* 							for ($i=0 ; $i <= count($array_user_query) -1 ; $i++){
								$sql = "SELECT t_index.tf, t_term.idf
										FROM t_index, t_term
										WHERE t_index.id_data = '".$row[0]."'
										AND t_term.term = '".$array_user_query[$i]."'
										AND t_index.id_term = t_term.id_term";
								$result = mysql_query($sql,$konek);
								if ($rows = @mysql_fetch_row($result)){
									
									$tf = $rows[0];
									$idf = $rows[1];
								} else {
									$tf = 0;
									$idf = 0;					
								}
								$tfidf= $tf*$idf;
								$Avektor[] = $tfidf; 
								$Bvektor[] = $tfidf*$tfidf;
								//echo $row[0]." | ".$rows[0]." | ".$rows[1]." | ".($tf*$idf)." | ";				
							}
							unset($atas);
							unset($bawah);
							unset($idf);				
							$atas = array_sum($Avektor);
							$bawah = sqrt(array_sum($Bvektor));
/* 							$sql = "select t_index.tf,t_term.idf from t_index,t_term 
									where t_term.id_term=t_index.id_term 
									AND t_index.id_data=".$row[0];
							$result = mysql_query($sql,$konek);
							while ($rows = mysql_fetch_row($result)){
								$tfidf = $rows[0]*$rows[1];
								$bawah += $tfidf*$tfidf; 
							}		 
 							//echo $atas." | ".$bawah." | ";
							$sim[$row[0]] = $atas/sqrt($bawah); 
 */							//echo $sim[$row[0]]."<br>";
							//$sim[$row[0]]=$wij;
							$numQterm++;
							$atas += ($wij[0]); 
							$bawah += ($wij[1]); 
							$qvektor += ($wij[2]);
							$found = true;
							$search = true;
						}
							
					}
					if ($numQterm){
						//if ($numQterm > 1){
							$sim[$row[0]] = ($atas / sqrt($bawah*$qvektor))*$numQterm;
						//} else{
						//	$sim[$row[0]] = $atas;
						//}
					}
					unset($numQterm);
					unset($bawah);
					unset($atas);
				}
				$mtime=microtime();
				$mtime=explode(" ",$mtime);
				$mtime=$mtime[1] + $mtime[0];
				$tend=$mtime;
				$totaltime=($tend-$tstart);
				$totaltime=number_format($totaltime,4);										
			} else{
				$messages= ":: Data tidak ditemukan!!";
				$found = false;
			}
		}else {
			$messages = ":: Kata apa yang mau anda cari?";
			$found = false;
		}
	}
	if ($found){
/*  		print_r($sim);
		echo "<br>";
*/		
		if ($search){
			arsort($sim);
			//print_r($sim);
			foreach($sim as $key => $val){
					$id_temp[] = $key;
					$sim_array .= "$key".urlencode(",");
			}
			$time = "Pencarian '<strong>$q</strong>' dilakukan dalam $totaltime detik<br>";										
		}else{
			$id_temp = explode(",",substr($sim_array,0,-1));
			$time = "Pencarian '<strong>$q</strong>' dilakukan dalam $totaltime detik<br>";													
		}
/*  			print_r($sim);
		echo "<br>";			
 */			
		//print_r($id_temp);
		$item=5;
		$jumlah=count($id_temp);
		$pageNum=ceil($jumlah/$item);
		$list = $page*$item;
		if (($list + $item) <= $jumlah){
			$downlist = ($list+$item)-1;
		} else{
			$downlist = $jumlah-1;
		}
		$info_page ="Halaman ke $halaman dari $pageNum halaman";			
		$pagenavigation = "Halaman: ";
		for ($i=1 ;$i <=$pageNum ; $i++){
			if ($halaman == $i){
				$pagenavigation .= $i." ";
			} else {
				$pagenavigation .= "<a href='"."http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].
									"?id_bagian=$id_bagian&page=".($i-1)."&halaman=".($i)."&q="
									.urlencode($q)."&sim_array=$sim_array&found=1&search=0&
									totaltime=".urlencode($totaltime)."&mode=$mode&
									pilih_bagian=$pilih_bagian&type_file=$type_file' class=link>$i									   									</a> "; 
			}
		}
		
		for ($i=$list ; $i <= $downlist; $i++){
			$sql = "select t_data.id_data,t_bagian.nama_bagian,t_data.tanggal,t_data.nama_data,
					t_data.nama_file,t_data.type_file,t_data.ukuran_file,t_data.penulis,t_data.deskripsi 
					from t_data,t_bagian where id_data='".$id_temp[$i]."' 
					and t_data.id_bagian=t_bagian.id_bagian";
			$row = mysql_fetch_row(mysql_query($sql));
			$id_data[] = $row[0];
			$nama_bagian[] = $row[1];
			$last_update[] = convertTgl($row[2]);
			$nama_data[] = $row[3];
			$nama_file[] = wordwrap($row[4],50,"<br>",1);
			if ($row[5] == "application/vnd.ms-excel"){
				$file_image[]= "excel.png";
			} elseif($row[5] == "application/msword"){
				$file_image[] = "word.png";
			} elseif($row[5] == "application/pdf"){
				$file_image[] = "pdf.png";
			}else{
				$file_image[] = "other.png";
			}
			$ukuran_file[] = number_format($row[6])." KB";
			$penulis[] = $row[7];
			$deskripsi[] = wordwrap($row[8],45,"<br>");
		}
		$messages = ":: Ditemukan sebanyak <strong>$jumlah</strong> Dokumen";		
	} else{
		$messages= ":: Data tidak ditemukan!!";
	}
	 
	$title = "Bank Data - Pencarian data";
	$header = "header.htm";
	$headsearch = "headsearch.htm";
	$content = "searching.htm";
	$right = "rightmain.htm";
	$topnavigasi = "Hasil Pencarian data";
	$bottomnavigasi = "";
	$smarty = new Smarty;
	//$q = addcslashes($q,"");
	//rightmain
	$sql = "select nama_data,nama_file,ukuran_file from t_data order by tanggal DESC";
	$row = mysql_fetch_array(mysql_query($sql));
	$n_data = $row[0];
	$file = wordwrap($row[1],33,"<br>",1);
	$bytes = number_format($row[2])." KB";
	$smarty -> assign("n_data",$n_data);
	$smarty -> assign("file",$file);
	$smarty -> assign("bytes",$bytes);
	// end rightmain
	$query  =urlencode($q);
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
	$smarty -> assign("time",$time);
	$smarty -> assign("messages",$messages);
	$smarty -> assign("q",$q);
	$smarty -> assign("query",$query);
	$smarty -> assign("title",$title);
	$smarty -> assign("base","http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);	
	$smarty -> assign ("header",$header);
	$smarty -> assign ("headsearch",$headsearch);
	$smarty -> assign ("topnavigasi",$topnavigasi);
	$smarty -> assign ("tanggal",tanggal());
	$smarty -> assign ("content",$content);
	$smarty -> assign ("right",$right);
	$smarty -> display ("template.htm");	
	mysql_close($konek);
?>
<? 
include "inc/ir.function.php";
	set_time_limit(86400); // 1 full day
	$stopwords = file("./bin/stopwords.txt");
	for ($i =0 ; $i <= count($stopwords) -1; $i++){
		$stopwords[$i]= trim($stopwords[$i]);
	}
	$mode = $_POST['mode'];
	$id_data = $_POST['id_data'];
	$pilih_bagian = $_POST['pilih_bagian'];
	if ($mode == "all"){
		$sql = "select id_data,temp_file from t_data";
	}elseif($mode == "bagian"){
		$sql = "select id_data,temp_file from t_data where id_bagian='$pilih_bagian'";
	}else if ($mode == "dokumen"){
		$OrSql = true;
		if (count($id_data)){
			for ($i =0 ; $i <= count($id_data)- 1 ; $i++){
				if (trim($id_data[$i])){
					if ($OrSql){
						$plussql = "id_data=".$id_data[$i]." ";
						$OrSql = false;
					} else{
						$plussql .= "OR id_data=".$id_data[$i]." ";
					}
					$QStr.= "id_data[]=".$id_data[$i]."&";
				}
			}
			$sql = "select id_data, temp_file from t_data where $plussql";
		}
	}
	$rs = mysql_query($sql,$konek);
	if ($jum_row = @mysql_num_rows($rs)){
 		$tstart = time();
		while ($row = mysql_fetch_row($rs)){
			unset($sqltoken,$j);
			unset($temp_tokens);
			$no++;
			if(file_exists($row[1])){
				//$string = getFile($row[1]);				
				$string = file_get_contents($row[1]);
				$temp_tokens = indexText($string,$stopwords);				
				$maxText = max($temp_tokens[1]);
 				$totaltxt = count($temp_tokens[0]);
				$j = 0;
				$i=0;
				$OrSql = true;
				foreach($temp_tokens[0] as $token => $property){
					usleep(50000);
					$tf=number_format($property[0]/$maxText,4);
					$position = substr($property[1],0,-1);
					$i++;
					if (!getCount("t_term","term='$token'")){
						$sqltoken= "insert into t_term values('','$token','')";
						mysql_query($sqltoken);
						$id_term= mysql_insert_id();
					}else {
 						$id_term= getData("id_term","t_term","term='".$token."'");
 					}
					$array_sql[$j][0] = $row[0];
					$array_sql[$j][1] = $id_term;
					$array_sql[$j][2] = $tf;
					$array_sql[$j][3] = $position;
					$j++;
					if ($j==200 || $i == $totaltxt){
						usleep(50000);
						for ($k=0; $k <=count($array_sql);$k++){
							if ($k % 15 == 0){
								usleep(50000);
							}
							$sqlindex = "insert into t_index 
										values('".$array_sql[$k][0]."',
										'".$array_sql[$k][1]."',
										'".$array_sql[$k][2]."',
										'".$array_sql[$k][3]."')";
							mysql_query($sqlindex,$konek);
						}
						$j=0;
						unset($array_sql);
					}
					
				}
			}
		}
		CreateIDF();
		$tend = time();
 		$totaltime=($tend-$tstart);
		//$totaltime=number_format($totaltime,4);
		$success = 1;
		$msg = urlencode("Pengindeksan dokumen selesai dilakukan!!");
	} else{
		$success = 0;	
		$msg = urlencode("Error: tidak ada dokumen yang di indeks!!");
	}
	mysql_close($konek);
	header('Location: admin_index.php?state=1&'.$QStr.
			'mode='.$mode.'&success='.$success.'&msg='.$msg.'&totaltime='.$totaltime.
			'&id_bagian='.$pilih_bagian);
 ?>
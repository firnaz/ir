<? 
include "function.php";
	$buangkarakter = array('/\s*\W+\s+/','/\s+\W+\s*/','/\s+[\d+\W*]*\s+/','/\-\-+/','/\)+/','/\(+/',
					'/"+/','/\'+/','/\*+/',"/\'+/",'/=+/','/\]+/','/\++/','/\[+/','/\,+/','/\;+/',
					'/\:+/','/\\+/','/\n+/','/\t+/','/\r+/');

function indexText($string,$stopwords){
	global $buangkarakter;
	$str2 = strtolower($string);
	$str2 = strip_tags($str2);
	$str2 = preg_replace($buangkarakter," ",$str2);
	$arraystr = explode (" ",$str2);
	$j=0;
	for ($i = 0 ; $i <= count($arraystr)-1 ; $i++){
		$tokens =trim($arraystr[$i]);
		if ($tokens){
			if (strlen($tokens) >2 && !in_array($tokens,$stopwords)){
				$maxtoken[$tokens]++;
				$token[$tokens][0]++;
				$token[$tokens][1].= $j.",";
				$j++;
			}
		}
	}
	return array($token,$maxtoken);
}
function CreateIDF(){
	global $konek;
	$OrSQL = true;
	 
	$sql = "select id_term from t_term";
	$rs = mysql_query($sql,$konek);
	//echo $sql;
	$totalDocument = getCount("t_data","id_data");
	//$totalterm = @mysql_num_rows($rs);
	//$i =0;
	$j=0;
	//$OrSql = true;
	while ($row = mysql_fetch_row($rs)){
		usleep(50000);
		$totalUsedTerm = getCount("t_index","id_term='".$row[0]."'");
		$idf = number_format(log($totalDocument/$totalUsedTerm),4);
		$term[$j][0] = $row[0];
		$term[$j][1] = $idf;
		$j++;
 	}
	usleep(100000);	
	for ($k=0 ; $k<=count($term)-1;$k++){
		if ($k % 15 == 0){
			usleep(50000);
		}
		$sqlidf = "update t_term set idf='".$term[$k][1]."' where id_term='".$term[$k][0]."'";
		mysql_query($sqlidf);
	}
 }
function getTermArray($q,$stopwords){
	global $konek;
	$user_query = trim(strtolower($q));
	$user_query = ereg_replace('"',"",$user_query);
	$array_user_query = explode(" ",$user_query);
	$orSql = true;
	for ($i=0 ; $i <= count($array_user_query) -1 ; $i++){
		if (!in_array($array_user_query[$i],$stopwords)){
			if ($orSql){
				$plussql = "t_term.term = '".$array_user_query[$i]."'";
				$orSql = false;
			}else{
				$plussql .= " or t_term.term = '".$array_user_query[$i]."'"; 
			}
		}
	}
	$orSql = true;
	$sql = "select distinct id_term
			from t_term 
			where $plussql";
	$rs = mysql_query($sql,$konek);
	if (@mysql_num_rows($rs)){
		while ($row= mysql_fetch_row($rs)){
			if ($orSql){
				$plussql = "t_term.id_term='".$row[0]."'";				
				$orSql = false;
			}else {
				$plussql .= " or t_term.id_term='".$row[0]."'";							
			}
			//$Qvektor[] = (0.5 + ((0.5*1)/count($array_user_query)))*$row[1];
			$Qvektor[] =1;
		}
	}
	return array($plussql,$array_user_query,$Qvektor);
}
function getPhraseArray($q,$stopwords){
	$quoted = explode('"', $q);
	for($i = 0; $i <= count($quoted)-1 ; $i++) {
		if($i == 0 && !$quoted[$i]) {
			//kutip pada awal kata kunci
			$begin = True;
			$i++;
		}
		if($begin) { $phrase[] = $quoted[$i]; }
		elseif($quoted[$i]) {
			$temp_phrase = explode(" ", $quoted[$i]); // bikin array dari kalimat dengan pemisahnya spasi (" ")
			for($n = 0; $n < count($temp_phrase); $n++) {
				$str = trim($temp_phrase[$n]);
				if(trim($str) && !in_array($str,$stopwords)) { 
					$phrase[] = $str; 
				}
			}
		}
		$begin = !$begin;
	}
	return $phrase;
}
function getTruePhrase($phrase,$id_data,$stopwords){
	global $konek;
	global $buangkarakter;
	$temp = explode(" ",$phrase);
	for ($i=0 ; $i <= count($temp) -1 ; $i++){
		if (!in_array($temp[$i],$stopwords)){
			$array_kunci[] = preg_replace($buangkarakter,"",$temp[$i]);
		}
	}
	for ($i = 0 ; $i <= count($array_kunci) -1 ; $i++){
		$sql = "SELECT t_index.position,t_index.tf,t_term.idf
				FROM t_index, t_term
				WHERE t_index.id_data = '".$id_data."'
				AND t_term.term = '".$array_kunci[$i]."'
				AND t_index.id_term = t_term.id_term";
		$result = mysql_query($sql,$konek);
		if ($row = @mysql_fetch_row($result)){
			$array_phrase[$i]= explode(",",$row[0]);
			$diwq += $row[1]*$row[2]*$row[2];
			$di += ($row[1]*$row[2])*($row[1]*$row[2]);
			$wq += $row[2]*$row[2];
			//$atas += $tfidf;
			//$bawah += ($tfidf*$tfidf);
		}else{
			return false;
		}
	}
	//print_r($array_phrase);
	if (count($array_kunci) > 1){
		$match = false;
		$banyak_letak_kata1 = count($array_phrase[0]);
		for ($i = 0 ; $i <= $banyak_letak_kata1-1 ;$i++){
			$j = 1;
			$letak = $array_phrase[0][$i]; 
			while ($j <= count($array_phrase)-1){
				if (in_array($letak+1,$array_phrase[$j])){
					if($j == count($array_phrase)-1){
						$match = true;
						$j++;
					}else{
						$letak++;
						$j++;
					}
				}else{
					break;
				}
			}
			if ($match){
				break;
			}
		} 
		if ($match){
			//$wij = $atas / sqrt($bawah);	
			return array($diwq,$di,$wq);
		}else{
			return false;
		}
	}else{
		//$wij = $atas / sqrt($bawah);	
		return array($diwq,$di,$wq);
	}
}
?>
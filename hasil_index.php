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
	$huruf_depan = $_GET['huruf_depan'];
	$totalindex = getCount("t_term","id_term");
	$mode = $_GET['mode'];
	
	if ($mode=="list"){
		$smarty ->assign ("judul",strtoupper($huruf_depan)."-".$halaman);
		$sql = "select count(*) from t_term where term LIKE '".$huruf_depan."%'";
		$pagelist-> listNavigate($page,20,$sql,$konek);
		$pagenavigation = $pagelist ->showPage($halaman,"&mode=$mode&huruf_depan=$huruf_depan","link");	
		$sql = "select id_term, term from t_term where term LIKE '".$huruf_depan."%' order by
				term LIMIT ".$pagelist ->showList().",".$pagelist->showItem();
		$rs = mysql_query($sql);
		while ($row = mysql_fetch_array($rs)){
			$totaldoc = getCount("t_index","id_term='$row[0]'");
			$indeks[] = "<a href='search.php?q=$row[1]' class=link target=_blank>$row[1] ($totaldoc 														dokumen)</a>";;
		}
		$smarty->assign ("indeks",$indeks);
		$smarty->assign ("pagenavigation",$pagenavigation);
	}
	
	$content = "hasil_index.htm";
	$topnavigasi = "Indeks Dokumen";	
	$title = "Administrator Bank Data - Indeks Dokumen";
	$bottomnavigasi = "<a href='logout.php' class='link'><strong>Logout</strong></a>";
	$header = "header.htm";
	$headsearch = "none.htm";
	$kontrol = getData("kontrolisasi","t_user","guid='$sGUID'");
	if ($kontrol){
		$smarty ->assign ("user",showUserControl());
	}
	//rightmain
	$right = "rightadmin.htm";
	$jumlah_bag = getCount("t_bagian","id_bagian");
	$jumlah_data = getCount("t_data","id_data");
	$bytes = number_format(getSum("ukuran_file","t_data","id_data"))." KB";
	$smarty -> assign("jumlah_bag",$jumlah_bag);
	$smarty -> assign("jumlah_data",$jumlah_data);
	$smarty -> assign("bytes",$bytes);
	//
	$smarty -> assign("base","http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
	$smarty -> assign("title",$title);
	$smarty -> assign($huruf_depan,"selected");
	$smarty -> assign("totalindex",$totalindex);
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
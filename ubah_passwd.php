<?
	include "inc/smarty/Smarty.class.php";
	include "inc/function.php";
	include "inc/incSession.php";
	$smarty = new Smarty;
	$myid = getData("id_user","t_user","guid='$sGUID'");
	$myuname = getData("uname","t_user","guid='$sGUID'");
	$ubahpasswd = $_POST['ubahpasswd'];	
	if ($ubahpasswd == "OK"){
		$passwdlama = $_POST['passwdlama'];
		$passwdbaru = $_POST['passwdbaru'];
		$retpasswdbaru = $_POST['retpasswdbaru'];		
		if(!$passwdlama || !$passwdbaru || !$retpasswdbaru){
			$msg = "Yang bertanda <strong>*</strong> tidak boleh kosong!!";
		} else{
			$getid = getData("id_user","t_user","password=Password('$passwdlama')");
			if($getid != $myid){
				$msg = "Password lama salah!!";
			}else{
				if($passwdbaru != $retpasswdbaru){
					$msg = "Password baru tidak sama!!";
				} else{
					$sql = "update t_user set password=Password('$passwdbaru') where id_user='$myid'";
					if(mysql_query($sql)){
						$msg = "Password berhasil di ubah!!";
						header('Location: ubah_passwd.php?state=success&msg='.urlencode($msg));
					}
				}
			} 
		}
	}
	$state = $_POST['state'];
	if ($state == "success"){
		$topnavigasi = "Ubah Password User $myuname";
		$content = "none.htm";
	} else{
		$smarty -> assign ("id_user",$myid);
		$topnavigasi = "Ubah Password User <u>$myuname</u>";
		$content = "ubah_passwd.htm";
	}
	$title = "Administrator Bank Data - Ubah Password";
	$bottomnavigasi = "<a href='logout.php' class='link'><strong>Logout</strong></a>";
	$header = "header.htm";
	$headsearch = "none.htm";
	$kontrol = getData("kontrolisasi","t_user","guid='$sGUID'");
	if ($kontrol){
		$smarty ->assign ("user",showUserControl());
	}
	$right = "rightadmin.htm";
	$jumlah_bag = getCount("t_bagian","id_bagian");
	$jumlah_data = getCount("t_data","id_data");
	$bytes = number_format(getSum("ukuran_file","t_data","id_data"))." KB";
	$smarty -> assign("jumlah_bag",$jumlah_bag);
	$smarty -> assign("jumlah_data",$jumlah_data);
	$smarty -> assign("bytes",$bytes);
	$smarty -> assign("base","http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
	$smarty -> assign("title",$title);
	$smarty -> assign("tanggal",tanggal());
	$smarty -> assign("topnavigasi",$topnavigasi);
	$smarty -> assign("bottomnavigasi",$bottomnavigasi);
	$smarty -> assign ("header",$header);
	$smarty -> assign ("headsearch",$headsearch);
	$smarty -> assign ("msg",$msg);
	$smarty -> assign ("content",$content);
	$smarty -> assign ("right",$right);
	$smarty -> display ("template.htm");
	
?>
<?
include ('inc/function.php');
$uname = $_POST['uname'];
$passwd = $_POST['passwd'];
if($uname == '' || $passwd == '') {

// No login information
header('Location: admin.php?msg='.urlencode(":: User dan Password tidak boleh kosong!!!"));

} else {

// Authenticate user
$sQuery = "Select id_user, MD5(UNIX_TIMESTAMP() + id_user + RAND(UNIX_TIMESTAMP())) guid
From t_user
Where uname = '$uname'
And password = password('$passwd')";

$hResult = mysql_num_rows(mysql_query($sQuery));
if($hResult==1) {

$aResult = mysql_fetch_row(mysql_query($sQuery));

// Update the user record
$sQuery = "
Update t_user
Set guid = '$aResult[1]'
Where id_user = $aResult[0]";

mysql_query($sQuery);

// Set the cookie and redirect
session_start();
$_SESSION['sessid']= $aResult[1];
/* $sql = "select * from t_pesan where untuk='$aResult[0]' and baca='0'";
$row = mysql_num_rows(mysql_query($sql));
if ($row){
	$msg = "Anda memiliki $row pesan yang belum di baca.";
}else{
	$msg = "Anda tidak memiliki pesan baru.";
}  
 */
 if(!$psrefer) $psrefer = 'admin.php?msg='.urlencode("Login Success!!");
header('Location: '.$psrefer);

} else {

// Not authenticated
header('Location: admin.php?uname='.$uname.'&msg='.urlencode("Username/password salah!!!"));

}
}
?>
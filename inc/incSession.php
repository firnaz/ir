<?
// Check for a cookie, if none got to login page 
session_start();
if(!isset($_SESSION['sessid'])) { 
header('Location: admin.php?msg='.urlencode("Anda harus Login terlebih dahulu!!!")); 

} 

// Try to find a match in the database 
$sGUID = $_SESSION['sessid']; 
$sQuery = "Select id_user From t_user Where guid='$sGUID'"; 
$hResult = mysql_query($sQuery); 


if(!@mysql_num_rows($hResult)) { 
//No match for guid 
header('Location: admin.php?msg='.urlencode("Anda harus Login terlebih dahulu!!!")); 
}
?>
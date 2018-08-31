<?php
$host = 'localhost';	// nama host atau ip host
$db = 'w181070_ir';		// nama database
$user= 'w181070_firnas';		// username mysql
$password = 'k4mpr3t';		// password mysql

$konek = mysql_connect($host,$user,$password); if (!$konek) die("Koneksi ke database gagal...");
$database=mysql_select_db($db,$konek) or die ("Data base tidak dapat di buka...".mysql_error() );
?>
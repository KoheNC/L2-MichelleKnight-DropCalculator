<?php 
error_reporting(E_ERROR | E_WARNING | E_PARSE);

/*
Michelle Knight's Drop Calc - Version 5.0.0
Author - Michelle Knight
Copyright 2006/2007
Contact - dropcalc@msknight.com

GNU General Licence
Use and distribute freely, but leave headers intact and make no charge.
Code distributed without warantee or liability as to merchantability as
no charge is made for its use.  Use is at users risk.
*/


// --------------------------------------------------------
// -----              Common Code Block               -----
// --------------------------------------------------------


//Logonserverdb
$logsvr_location = "localhost";
$dblog_l2jdb = "l2jdblog";
$dblog_user = "username";
$dblog_psswd = "password";

$con2 = mysql_connect($log_location,$dblog_user,$dblog_psswd);
if (!$con2)
{
	echo "Could Not Connect";
	die('Wrap_start could not connect to logserver database: ' . mysql_error());
}		
if (!mysql_select_db("$dblog_l2jdb",$con2))
{	die('Wrap_start could not change to logserver database: ' . mysql_error());	}

$result = mysql_query("insert ignore into knightdrop (name, access_level) select login, accessLevel from accounts;",$con2);

?>
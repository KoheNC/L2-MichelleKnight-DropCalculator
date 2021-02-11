<?php 
error_reporting(E_ERROR | E_WARNING | E_PARSE);

/*
Michelle Knight's Drop Calc - Version 3
Author - Michelle Knight
Copyright 2006
Contact - dropcalc@msknight.com

GNU General Licence
Use and distribute freely, but leave headers intact and make no charge.
Change HTML code as necessary to fit your own site.
Code distributed without warantee or liability as to merchantability as
no charge is made for its use.  Use is at users risk.
*/

/* SERVER NOTES
The system will only operate assuming that telnet is active to the server.
Put the telnet configuration in to the config.php file.
*/

include('config.php');
include('config-read.php');
include('skin.php');
include('common.php');

// Retrieve environment variables
$username = input_check($_REQUEST['username'],1);
$token = input_check($_REQUEST['token'],0);
$langval = input_check($_REQUEST['langval'],2);
$ipaddr = $_SERVER["REMOTE_ADDR"];
$passwd = input_check($_REQUEST['passwd'],0);
$go = input_check($_REQUEST['go'],0);

$langfile = $language_array[$langval][1];
include($langfile);		// Import language variables.

echo "<html class=\"popup\">
<head>
<title>Michelle's Generic Drop Calc</title>
<LINK rel=\"stylesheet\" type=\"text/css\" href=\"$skin_dir/style.css\">
</head>
<body topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" class=\"popup\">
<center>";


$evaluser = evalUser($username, $token, $ipaddr, $db_location, $db_user, $db_psswd, $db_l2jdb, $dblog_location, $dblog_user, $dblog_psswd, $dblog_l2jdb, $db_tokenexp, $guest_allow, $all_users_maps, $all_users_recipe, $sec_inc_admin, $sec_inc_gmlevel, $guest_user_maps);
if ($evaluser == 2)
{	$username = "guest";	}
if ($evaluser == 0)
{	$username = "";	}

if ($evaluser)
{
	$con = mysql_connect($db_location,$db_user,$db_psswd);
	mysql_query("SET NAMES 'utf8'", $con);
	mysql_query("SET character_set_results='utf8'", $con);
	if (!$con)
	{
		echo "<p class=\"popup\">Could Not Connect</p>";
		die('Could not connect: ' . mysql_error());
	}		
	if (!mysql_select_db("$db_l2jdb",$con))
	{
		die('Could not change to L2J database: ' . mysql_error());
	}

	

	if ($user_access_lvl < $sec_inc_admin)
	{
		echo "<p class=\"popup\">You don't have sufficient access.</p>";
		echo "</center></body></html>";
		return 0;
	}
	else
	{
		// If we have entered with an item, then attempt to give it.
		if ($go)
		{

			$usetelnet = fsockopen($log_telnet_host, $log_telnet_port, $errno, $errstr, $log_telnet_timeout);
			if($usetelnet)
			{
				$give_string = 'shutdown' ;
//				fputs($usetelnet, $username);
//				fputs($usetelnet, "\r\n");
				fputs($usetelnet, $passwd);
				fputs($usetelnet, "\r\n");
				fputs($usetelnet, $give_string);
				fputs($usetelnet, "\r\n");
				while (!feof($usetelnet))
				{
					$line = fgets($usetelnet, 128);
					echo "<p>$line</p>";
				}
				fclose($usetelnet);
				echo "<p class=\"popup\">Shutdown command sent</p>";
			}
			else
			{	echo "<p class=\"popup\">Couldn't connect to telnet</p>";	}

			echo "</center></body></html>";
			return 0;
		}


		// Echo out the giving form.
		echo "<p class=\"popup\">Please enter your game password...</p>
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"popup\">
		<tr>
		<td class=\"popuptrans\"><form action=\"logons.php\">
		<input name=\"username\" type=\"hidden\" value=\"$username\">
		<input name=\"token\" type=\"hidden\" value=\"$token\">
		<input name=\"langval\" type=\"hidden\" value=\"$langval\"><input name=\"server_id\" value=\"$server_id\" type=\"hidden\"><input name=\"skin_id\" value=\"$skin_id\" type=\"hidden\">
		<input name=\"passwd\" type=\"password\" value=\"\">
		<input name=\"go\" type=\"hidden\" value=\"go\">
		<input value=\"Go\" onclick=\"submit\" height=\"9\" type=\"submit\" class=\"popupcentre\">
		</form></td>
		</tr></table>

		";
	}
}

echo "</center></body></html>";

?>
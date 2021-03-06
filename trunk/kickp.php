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
$touser = input_check($_REQUEST['touser'],1);
$kick = input_check($_REQUEST['kick'],0);

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

	echo "<p class=\"popup\">About to kick $touser</p>";

	if ($user_access_lvl < $kick_player)
	{
		echo "<p class=\"popup\">You don't have sufficient access.</p>";
		echo "</center></body></html>";
		return 0;
	}
	else
	{
		// If we have entered with an item, then attempt to give it.
		if ($kick)
		{
			// Look up the user and see if they are on line.
			$sql = "select online from characters where char_name = '$touser'";
			$result_i = mysql_query($sql,$con);
			$is_online = mysql_result($result_i,0,"online");
			if (!$is_online)
			{	echo "<p class=\"popup\">$touser is not currently on-line</p>";	}
			else
			{
				$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
				if($usetelnet)
				{
					$give_string = 'kick ' . utf82lang ($touser) ;
					$give_string2 = 'msg ' . utf82lang ($touser) . ' ADMIN HAS KICKED YOUR CHARACTER.';
					$give_string3 = 'msg ' . utf82lang ($touser) . ' PLEASE TALK WITH YOUR ADMIN TEAM.';
					fputs($usetelnet, $telnet_password);
					fputs($usetelnet, "\r\n");
					fputs($usetelnet, $give_string2);
					fputs($usetelnet, "\r\n");
					fputs($usetelnet, $give_string3);
					fputs($usetelnet, "\r\n");
					fputs($usetelnet, $give_string);
					fputs($usetelnet, "\r\n");
					fputs($usetelnet, "exit\r\n");
					fclose($usetelnet);
					echo "<p class=\"popup\">Kick command sent</p>";
				}
				else
				{	echo "<p class=\"popup\">Couldn't connect to telnet</p>";	}
			}

		}


		// Echo out the giving form.
		echo "	
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"popup\">
		<tr>
		<td class=\"popuptrans\"><form action=\"kickp.php\">
		<input name=\"username\" type=\"hidden\" value=\"$username\">
		<input name=\"token\" type=\"hidden\" value=\"$token\">
		<input name=\"langval\" type=\"hidden\" value=\"$langval\"><input name=\"server_id\" value=\"$server_id\" type=\"hidden\"><input name=\"skin_id\" value=\"$skin_id\" type=\"hidden\">
		<input name=\"touser\" type=\"hidden\" value=\"$touser\">
		<input name=\"kick\" type=\"hidden\" value=\"1\">
		<input value=\"Kick Player\" onclick=\"submit\" height=\"9\" type=\"submit\" class=\"popupcentre\">
		</form></td>
		</tr></table>

		";
	}
}

echo "</center></body></html>";

?>
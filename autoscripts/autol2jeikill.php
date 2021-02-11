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

//Gamserverdb
$db_location = "localhost";
$db_l2jdb = "gameserver";
$db_user = "username";
$db_psswd = "password";

// Gameserver Telnet Settings
$telnet_host = "localhost";
$telnet_port = "15557";
$telnet_password = "L2Jtelnet";
$telnet_timeout = 10;

$knight_db = "lithraknight";

//Logonserverdb
$logsvr_location = "192.168.0.3";
$dblog_l2jdb = "login";
$dblog_user = "username";
$dblog_psswd = "password";

$ei_max = 50000;		// Anything of this level and above, will be removed from the database if held by a non GM character.
$sec_inc_gmlevel = 100;		// Starting level of GM characters
$ban_accounts = 0;			// Ban accounts of characters in breach of the rules.
$enchntgmaccallow = 1;		// Ignore over enchanted items that are owned by non-GM characters that are linked to GM accounts.

	$con = mysql_connect($db_location,$db_user,$db_psswd);
	if (!$con)
	{
		echo "Could Not Connect";
		die('Could not connect: ' . mysql_error());
	}		
	if (!mysql_select_db("$db_l2jdb",$con))
	{
		die('Could not change to L2J database: ' . mysql_error());
	}
	$con2 = mysql_connect($logsvr_location,$dblog_user,$dblog_psswd);
	if (!$con2)
	{
		echo "Could Not Connect";
		die('Evaluser could not connect to logondb: ' . mysql_error());
	}		
	if (!mysql_select_db("$dblog_l2jdb",$con2))
	{	die('Evaluser could not change to logondb: ' . mysql_error());	}

	if ($enchntgmaccallow)
	{
		$sql = "select login from $dblog_l2jdb.accounts where accessLevel >= '$sec_inc_gmlevel'";
		$result = mysql_query($sql,$con2);
	
		if ($result)
		{
			$sql = "select obj_id from characters where account_name in (";
			$count = mysql_num_rows($result);
			$i = 0;
			while ($i < $count)
			{
				$gm_id = mysql_result($result,$i,"login");	
				$sql = $sql . "'" . $gm_id . "'";
				$i++;
				if ($i < $count)
				{	$sql = $sql . ", ";	}
			}
			$sql = $sql . ")";
		}
	}
	else
	{	$sql = "select obj_id from characters where accesslevel >= '$sec_inc_gmlevel'";	}
	
	$result = mysql_query($sql,$con);
	$sql = "delete from items where enchant_level > '$ei_max' ";
	$sql2 = "select unique owner_id from items where enchant_level > '$ei_max' ";
	if ($result)
	{
		$sql = $sql . " and owner_id not in (";
		$sql2 = $sql2 . " and owner_id not in (";
		$count = mysql_num_rows($result);
		$i = 0;
		while ($i < $count)
		{
			$gm_id = mysql_result($result,$i,"obj_id");	
			
			$sql = $sql . $gm_id;
			$sql2 = $sql2 . $gm_id;
			$i++;
			if ($i < $count)
			{	$sql = $sql . ", ";	}
		}
		$sql = $sql . ")";
		$sql2 = $sql2 . ")";
	}
	$result = mysql_query($sql2,$con);
	while ($r_array = mysql_fetch_assoc($result))
	{
		$char_id = $r_array['owner_id'];
		$sql2 = "select account_name, char_name, online from characters where obj_id = '$char_id'";
		$result2 = mysql_query($sql2,$con);
		while ($r_array = mysql_fetch_assoc($result2))
		{
			$account_name = $r_array['account_name'];
			$charname = $r_array['char_name'];
			$online = $r_array['online'];
			if ($online == 1);
			{
				$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
				if($usetelnet)
				{
					$charname = $r_array['char_name'];
					$give_string = 'announce ' . $charname . ' being kicked for over enchanted item.';
					fputs($usetelnet, $telnet_password);
					fputs($usetelnet, "\r\n");
					fputs($usetelnet, $give_string);
					fputs($usetelnet, "\r\n");
					fputs($usetelnet, "exit\r\n");
					fclose($usetelnet);
					sleep(5);
					$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
					fputs($usetelnet, $telnet_password);
					fputs($usetelnet, "\r\n");
					$give_string = 'kick ' . $charname ;
					fputs($usetelnet, $give_string);
					fputs($usetelnet, "\r\n");
					fputs($usetelnet, "exit\r\n");
					fclose($usetelnet);
				}
			}
			if ($ban_accounts == 1);
			{
				$sql2 = "update $dblog_l2jdb.accounts set accessLevel = -1 where login = '$account_name'";
				$result3 = mysql_query($sql2,$con2);
			}
			$count = 0;
			$name_user = "Enchant watcher - " . date('dS F Y \- h:iA T');
			// Find the highest numbered note in the table against this account.
			$result2 = mysql_query("select notenum from $knight_db.accnotes where charname = '$account_name' order by notenum DESC limit 1",$con);
			$query_count = mysql_num_rows($result2);
			if ($query_count)									// If a previous note exists, then extract its number.
			{	$count = mysql_result($result2,0,"notenum");	}
			$count++;		// Increase the index of the note, and record it.
			$result = mysql_query("insert into $knight_db.accnotes (`charname`, `notenum`, `notemaker`, `note`) values('$account_name', '$count', 'AutoBan', 'Kicked for overenchanted item.')",$con);
		}
	}
	if (!$result = mysql_query($sql,$con))
	{
		die('Could not retrieve from items database: ' . mysql_error());
	}
?>
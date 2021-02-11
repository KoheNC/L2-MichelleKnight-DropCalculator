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
$db_l2jdb = "l2jdblog";
$db_user = "username";
$db_psswd = "password";

// Gameserver Telnet Settings
$telnet_host = "localhost";
$telnet_port = "15557";
$telnet_password = "password";
$telnet_timeout = 10;

$knight_db = "lithraknight";

//Logonserverdb
$logsvr_location = "localhost";
$dblog_l2jdb = "l2jdblog";
$dblog_user = "username";
$dblog_psswd = "password";

$allow_dual_boxing = 0;		// Set to 1 if you do NOT want to check for dual boxers.
$gm_access_level = 100;		// characters of this level or above will not be included in the dual box check.


// Delete a character, and all associated items, from the database.
function delete_char($character, $db_location, $db_user, $db_psswd, $db_l2jdb, $dblog_location, $dblog_user, $dblog_psswd, $dblog_l2jdb)
{
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
	$result = mysql_query("select charId from characters where char_name = '$character'",$con);
	$char_id = mysql_result($result,0,"charId");

	$result = mysql_query("DELETE FROM pets WHERE item_charId IN (SELECT object_id FROM items where owner_id = '$char_id')",$con);
	$result = mysql_query("DELETE FROM character_friends WHERE char_id = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_subclasses WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_hennas WHERE charId = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_macroses WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_quests WHERE char_id  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_recipebook WHERE char_id  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_shortcuts WHERE charId = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_skills WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_skills_save WHERE charId = '$char_id'",$con);
	$result = mysql_query("DELETE FROM items WHERE owner_id = '$char_id'",$con);
	$result = mysql_query("DELETE FROM seven_signs WHERE charId = '$char_id'",$con); 
	$result = mysql_query("DELETE FROM characters WHERE charId = '$char_id'",$con); 
	$result = mysql_query("DELETE FROM knighttrust WHERE char_name = '$character'",$con); 
}

// Delete an account from the gameserver database
function delete_acc($account, $db_location, $db_user, $db_psswd, $db_l2jdb, $dblog_location, $dblog_user, $dblog_psswd, $dblog_l2jdb)
{
	$con2 = mysql_connect($dblog_location,$dblog_user,$dblog_psswd);
	if (!$con2)
	{
		echo "Could Not Connect";
		die('Wrap_start could not connect to logserver database: ' . mysql_error());
	}		
	if (!mysql_select_db("$dblog_l2jdb",$con2))
	{	die('Wrap_start could not change to logserver database: ' . mysql_error());	}
	$con = mysql_connect($db_location,$db_user,$db_psswd);
	if (!$con)
	{
		echo "Could Not Connect";
		die('Could not connect: ' . mysql_error());
	}		
	if (!mysql_select_db("$db_l2jdb",$con))
	{	die('Could not change to L2J database: ' . mysql_error());	}

	$result = mysql_query("select char_name from characters where account_name = '$account'",$con);
	while ($r_array = mysql_fetch_assoc($result))
	{	
		$char = $r_array['char_name'];
		delete_char($char, $db_location, $db_user, $db_psswd, $db_l2jdb, $dblog_location, $dblog_user, $dblog_psswd, $dblog_l2jdb);
	}
	$result = mysql_query("DELETE FROM $dblog_l2jdb.accounts WHERE login = '$account'",$con2);
	$result = mysql_query("DELETE FROM $dblog_l2jdb.knightdrop WHERE name = '$account'",$con2); 
	$result = mysql_query("DELETE FROM $knight_db.accnotes WHERE `charname` = '$account'",$con);
}



// Connect to DB, and try to retrieve the user details.
$con2 = mysql_connect($logsvr_location,$dblog_user,$dblog_psswd);
if (!$con2)
{
	echo "Could Not Connect";
	die('Evaluser could not connect to logondb: ' . mysql_error());
}		
if (!mysql_select_db("$dblog_l2jdb",$con2))
{	die('Evaluser could not change to logondb: ' . mysql_error());	}
$con = mysql_connect($db_location,$db_user,$db_psswd);
if (!$con)
{
	writeerror("Could Not Connect");
	die('Evaluser could not connect to gamedb: ' . mysql_error());
}		
if (!mysql_select_db("$db_l2jdb",$con))
{
	writeerror("Evaluser could not change to gamedb");
	die('Evaluser could not change to gamedb: ' . mysql_error());
}


// Create the knight database, taking advantage of the normal configuration that grants the creation of databases and that their
// creators have full permissions on the created DB.  This is where the notes will be stored.
$result = mysql_query("create database if not exists $knight_db",$con);
echo mysql_error()."<br>\n";
$result = mysql_query("CREATE TABLE if not exists $knight_db.accnotes (`charname` varchar(45) NOT NULL default '', `notenum` int(5) default NULL, `notemaker` varchar(50) default NULL, `note` varchar(300) default NULL,  PRIMARY KEY  (`charname`, `notenum`))",$con);
echo mysql_error()."<br>\n";

	// Find any characters which have a mpxhp or maxmp greater than 50,000 and delete them and their property.
	$result = mysql_query("select account_name, char_name from characters where (maxhp > 50000) or (maxmp > 50000)",$con);
	while ($r_array = mysql_fetch_assoc($result))
	{	
		$char = $r_array['char_name'];
		$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
		if($usetelnet)
		{
			$give_string = 'announce ' . $char . ' being kicked and deleted for botting.';
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
			$give_string = 'kick ' . $char ;
			fputs($usetelnet, $give_string);
			fputs($usetelnet, "\r\n");
			fputs($usetelnet, "exit\r\n");
			fclose($usetelnet);
		}
		echo "<p>Deleting character - $charname</p>";
		delete_char($char, $db_location, $db_user, $db_psswd, $db_l2jdb, $logsvr_location, $dblog_user, $dblog_psswd, $dblog_l2jdb);
	}
	
	// Find any characters which have greater than normal access.
	$result = mysql_query("select distinct account_name from characters where accesslevel > 1",$con);
	while ($r_array = mysql_fetch_assoc($result))
	{	
		$accname = $r_array['account_name'];
		// Pull back the GM access status for that characters account.
		$sql = "select access_level from $dblog_l2jdb.knightdrop where name = '$accname'";
		$result2 = mysql_query($sql,$con2);
		$acclevel = mysql_result($result2,0,"access_level");
		// If the account owning the GM character isn't GM level itself, then ban the account and characters.
		if ($acclevel < $sec_inc_gmlevel)
		{
			// Any online characters have to be kicked before they can be banned.
			$result2 = mysql_query("select char_name from characters where account_name = '$accname' and online = 1",$con);
			while ($r_array = mysql_fetch_assoc($result2))
			{
				$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
				if($usetelnet)
				{
					$charname = $r_array['char_name'];
					$give_string = 'announce ' . $charname . ' being kicked and banned for access violation.';
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
				echo "<p>Banning character - $charname</p>";
			}
			// Ban the account
			$result2 = mysql_query("update $dblog_l2jdb.accounts set accessLevel = -1 where login = '$accname'",$con2);
			sleep(5);
			$result2 = mysql_query("update characters set accesslevel = -1 where account_name = '$accname'",$con);
			echo "<p>Banning account - $accname</p>";
			
			// Record the banning event in the notes database.
			$newtime = preg_replace('/\'/','`',$newtime);
			$count = 0;
			$name_user = "Auto Ban System - " . date('dS F Y \- h:iA T');
			// Find the highest numbered note in the table against this account.
			$result = mysql_query("select notenum from $knight_db.accnotes where charname = '$accountname' order by notenum DESC limit 1",$con);
			$query_count = mysql_num_rows($result);
			if ($query_count)									// If a previous note exists, then extract its number.
			{	$count = mysql_result($result,0,"notenum");	}
			$count++;		// Increase the index of the note, and record it.
			$result = mysql_query("insert into $knight_db.accnotes (`charname`, `notenum`, `notemaker`, `note`) values('$accname', '$count', 'AutoBan', 'Account banned - GM character owned by non GM account')",$con);
		}
	}
	
	// Recall all accounts that have above standard access level.
	$result = mysql_query("select login, accessLevel from $dblog_l2jdb.accounts where accessLevel > 1",$con2);
	while ($r_array = mysql_fetch_assoc($result))
	{	
		$accname = $r_array['login'];
		$accesslevel = $r_array['accessLevel'];
		// Recall the checked access level for the account.  Assume 0 if it doesn't exist in the knightdrop table.
		$result2 = mysql_query("select access_level from $dblog_l2jdb.knightdrop where name = '$accname'",$con2);
		if (mysql_num_rows($result2) > 0)
		{	$acclevel = mysql_result($result2,0,"access_level");	}
		else
		{	$acclevel = 0;	}
		// If the account has GM access level, but the level doesn't agree with the knightdrop level, ban the account and characters.
		if (($acclevel >= $sec_inc_gmlevel) && ($acclevel <> $accesslevel))
		{
			// All online characters have to be kicked before being banned.
			$result2 = mysql_query("select char_name from characters where account_name = '$accname' and online = 1",$con);
			while ($r_array = mysql_fetch_assoc($result2))
			{
				$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
				if($usetelnet)
				{
					$charname = $r_array['char_name'];
					$give_string = 'announce ' . $charname . ' being kicked and banned for access violation.';
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
				echo "<p>Banning character - $charname</p>";
			}
			// Ban the account
			$result2 = mysql_query("update $dblog_l2jdb.accounts set accessLevel = -1 where login = '$accname'",$con2);
			sleep(5);
			$result2 = mysql_query("update characters set accesslevel = -1 where account_name = '$accname'",$con);
			echo "<p>Banning account - $accname</p>";
			
			// Record the banning event in the notes database.
			$newtime = preg_replace('/\'/','`',$newtime);
			$count = 0;
			$name_user = "Auto Ban System - " . date('dS F Y \- h:iA T');
			// Find the highest numbered note in the table against this account.
			$result2 = mysql_query("select notenum from $knight_db.accnotes where charname = '$accountname' order by notenum DESC limit 1",$con);
			$query_count = mysql_num_rows($result2);
			if ($query_count)									// If a previous note exists, then extract its number.
			{	$count = mysql_result($result2,0,"notenum");	}
			$count++;		// Increase the index of the note, and record it.
			$result = mysql_query("insert into $knight_db.accnotes (`charname`, `notenum`, `notemaker`, `note`) values('$accname', '$count', 'AutoBan', 'Account banned - account value verification failed')",$con);
		}
	}

	if (!$allow_dual_boxing)
	{
		$last_name = "";
		$result = mysql_query("select account_name, char_name from characters where online = 1 and accesslevel < '$gm_access_level' order by account_name",$con);
		while ($r_array = mysql_fetch_assoc($result))
		{	
			$accname = $r_array['account_name'];
			$charname = $r_array['char_name'];
			if ($accname == $last_name)
			{
				$result2 = mysql_query("select boxingok from $dblog_l2jdb.knightdrop where name = '$accname'",$con2);
				$query_count = mysql_num_rows($result2);
				if ($query_count)
				{	
					$boxingok = mysql_result($result2,0,"boxingok");	
					if (!$boxingok)
					{
						$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
						if($usetelnet)
						{
							$charname = $r_array['char_name'];
							$give_string = 'announce ' . $charname . ' being kicked for dual boxing by account.';
							fputs($usetelnet, $telnet_password);
							fputs($usetelnet, "\r\n");
							fputs($usetelnet, $give_string);
							fputs($usetelnet, "\r\n");
							$give_string = 'announce Dual Boxing must be granted by admins.  Please seek a GM.';
							fputs($usetelnet, $give_string);
							fputs($usetelnet, "\r\n");
							fputs($usetelnet, "exit\r\n");
							fclose($usetelnet);
							sleep(5);
							$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
							fputs($usetelnet, $telnet_password);
							fputs($usetelnet, "\r\n");
							$give_string = 'kick ' . $charname;
							fputs($usetelnet, $give_string);
							fputs($usetelnet, "\r\n");
							fputs($usetelnet, "exit\r\n");
							fclose($usetelnet);
						}
					}
				}
			}
			$last_name = $accname;
		}
		$last_ip = "dummy";
		$result = mysql_query("select lastIP, login from $dblog_l2jdb.accounts where accessLevel < '$gm_access_level' and login in (select account_name from $db_l2jdb.characters where online = 1) order by lastIP",$con2);
		while ($r_array = mysql_fetch_assoc($result))
		{	
			$accname = $r_array['login'];
			$ipaddr = $r_array['lastIP'];
			if ($ipaddr == $last_ip)
			{
				$result2 = mysql_query("select ip_addr from $dblog_l2jdb.knightipok where ip_addr = '$ipaddr'",$con2);
				$query_count = mysql_num_rows($result2);
				if (!$query_count)
				{	
					$result3 = mysql_query("select boxingok from $dblog_l2jdb.knightdrop where name = '$accname'",$con2);
					$query_count = mysql_num_rows($result3);
					if ($query_count)
					{	
						$boxingok = mysql_result($result3,0,"boxingok");	
						if (!$boxingok)
						{
							$result4 = mysql_query("select char_name from $db_l2jdb.characters where online = 1 and account_name = '$accname'",$con2);
							while ($r_array = mysql_fetch_assoc($result4))
							{
								$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
								if($usetelnet)
								{
									$charname = $r_array['char_name'];
									$give_string = 'announce ' . $charname . ' being kicked for dual boxing by IP.';
									fputs($usetelnet, $telnet_password);
									fputs($usetelnet, "\r\n");
									fputs($usetelnet, $give_string);
									fputs($usetelnet, "\r\n");
									$give_string = 'announce Dual Boxing must be granted by admins.  Please seek a GM.';
									fputs($usetelnet, $give_string);
									fputs($usetelnet, "\r\n");
									fputs($usetelnet, "exit\r\n");
									fclose($usetelnet);
									sleep(5);
									$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
									fputs($usetelnet, $telnet_password);
									fputs($usetelnet, "\r\n");
									$give_string = 'kick ' . $charname;
									fputs($usetelnet, $give_string);
									fputs($usetelnet, "\r\n");
									fputs($usetelnet, "exit\r\n");
									fclose($usetelnet);
								}
							}
						}
					}
				}
			}
			$last_ip = $ipaddr;
		}
	}
?>
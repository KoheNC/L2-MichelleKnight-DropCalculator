<?php 
error_reporting(E_ERROR | E_WARNING | E_PARSE);

/*
Michelle Knight's Drop Calc - Version 4
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

//Gamserverdbs
$gameserver_array = ARRAY(ARRAY("db_location", "db_database", "db_username", "db_password", 0),
						ARRAY("db_location", "db_database", "db_username", "db_password", 0));

//Logonserverdb
$logsvr_location = "localhost";
$dblog_l2jdb = "l2jdblog";
$dblog_user = "username";
$dblog_psswd = "password";

$month_dormant = 3;		// Accounts over these months old get trashed.  0 to deactivate feature.
$days_no_char = 3;		// Accounts over these days old  without chars get trashed.  less than 1 deactivates feature.

$kill_dormant_gm = 0;		// 1 will include GM accounts in the dormant deletion
$kill_nochar_gm = 0;		// 1 will include GM accounts with no characters in the deletion
$kill_dormant_banned = 0;		// 1 will include banned accounts in the dormant deletion 
$kill_nochar_banned = 0;		// cross reference your setting for preventing banned IPs reregistering.
					// Defaulting to 0 will thus keep banned accounts on the system to prevent them registering new accounts.
$safe_mode = 1;	

function delete_char($character, $db_location, $db_user, $db_psswd, $db_l2jdb, $logsvr_location, $dblog_user, $dblog_psswd, $dblog_l2jdb)
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
	$result = mysql_query("DELETE FROM pets WHERE item_obj_id IN (SELECT object_id FROM items where owner_id = '$char_id')",$con);
	$result = mysql_query("DELETE FROM character_friends WHERE charId = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_subclasses WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_hennas WHERE charId = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_macroses WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_quests WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_recipebook WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_shortcuts WHERE charId = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_skills WHERE charId  = '$char_id'",$con);
	$result = mysql_query("DELETE FROM character_skills_save WHERE charId = '$char_id'",$con);
	$result = mysql_query("DELETE FROM items WHERE owner_id = '$char_id'",$con);
	$result = mysql_query("DELETE FROM seven_signs WHERE charId = '$char_id'",$con); 
	$result = mysql_query("DELETE FROM characters WHERE charId = '$char_id'",$con); 
	$result = mysql_query("DELETE FROM knighttrust WHERE char_name = '$character'",$con); 
}


function delete_acc($account, $db_location, $db_user, $db_psswd, $db_l2jdb, $logsvr_location, $dblog_user, $dblog_psswd, $dblog_l2jdb, $knight_db)
{
	$con2 = mysql_connect($logsvr_location,$dblog_user,$dblog_psswd);
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
		delete_char($char, $db_location, $db_user, $db_psswd, $db_l2jdb, $logsvr_location, $dblog_user, $dblog_psswd, $dblog_l2jdb);
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

$array_count = count($gameserver_array);		// Done by index as I need to keep $gameserver_array intact and program an array variable.
$i = 0;
while ($i < $array_count)
{
	$db_location = $gameserver_array[$i][0];
	$db_user = $gameserver_array[$i][2];
	$db_psswd = $gameserver_array[$i][3];
	$con = mysql_connect($db_location,$db_user,$db_psswd);
	$gameserver_array[$i][4] = $con;
	if (!$con)
	{
		writeerror("Could Not Connect");
		die('Evaluser could not connect to gamedb: ' . mysql_error());
	}		
	echo "<p>$element</p>";
	$i++;
}

$today = time();
if ($month_dormant >= 1)
{
	$month_dormant = intval($month_dormant);								// Calculate cutoff time for dormant accounts.
	$cutoff = $borderline = (time() - (2678400 * $month_dormant)) * 1000;
	$ct = date('l dS \of F Y h:m.s A', ($cutoff / 1000));
	echo "<p>Dormant Account cutoff target = $ct - $cutoff</p>";						// Announce the date and time we are using.
	
	$sql = "select login from $dblog_l2jdb.accounts where lastactive < '$cutoff'";
	if ($kill_dormant_gm == 0)
	{	$sql = $sql . " and accesslevel < 1";	}
	if ($kill_dormant_banned == 0)
	{	$sql = $sql . " and accesslevel >= 0";	}		// Create sql depending on whether we are keeping certain types of accounts.
	$result = mysql_query($sql,$con2);

	while ($r_array = mysql_fetch_assoc($result))		// Delete all the accounts which were returned by the SQL query.
	{
		$accountname = $r_array['login'];
		echo "<p>Deleting - $accountname</p>";
		$i2 = 0;
		while ($i2 < $array_count)		// Run the qoery for each gameserver, to make sure all characters details and belongings are deleted.
		{
			$db_location = $gameserver_array[$i2][0];
			$db_l2jdb = $gameserver_array[$i2][1];
			$db_user = $gameserver_array[$i2][2];
			$db_psswd = $gameserver_array[$i2][3];
			delete_acc($accountname, $db_location, $db_user, $db_psswd, $db_l2jdb, $logsvr_location, $dblog_user, $dblog_psswd, $dblog_l2jdb, $knight_db);
			$i2++;
		}
	}
}

// The deletion of dormant accounts means that there are less accounts for the second routine to go through.
$con2 = mysql_connect($logsvr_location,$dblog_user,$dblog_psswd);
	if (!$con2)
	{
		echo "Could Not Connect";
		die('Wrap_start could not connect to logserver database: ' . mysql_error());
	}		
	if (!mysql_select_db("$dblog_l2jdb",$con2))
	{	die('Wrap_start could not change to logserver database: ' . mysql_error());	}
	
if ($days_no_char >= 1)		// If we have enabled the check for deleting blank accounts ...
{
	$days_no_char = intval($days_no_char);								// Calculate the cutoff date
	$cutoff = $borderline = (time() - (86400 * $days_no_char)) * 1000;
	$ct = date('l dS \of F Y h:m.s A', ($cutoff / 1000));
	echo "<p>Account with no character cutoff target = $ct - $cutoff</p>";		// Display the cutoff date and time we are using.
	
	$sql = "select login from $dblog_l2jdb.accounts where lastactive < '$cutoff'";		// Find the character list
	if ($kill_nochar_gm == 0)
	{	$sql = $sql . " and accesslevel < 1";	}
	if ($kill_nochar_banned == 0)
	{	$sql = $sql . " and accesslevel >= 0";	}
	$result = mysql_query($sql,$con2);

	while ($r_array = mysql_fetch_assoc($result))	// For each account found ...
	{
		$accountname = $r_array['login'];
		$chars_found = 0;
		$i2 = 0;
		while ($i2 < $array_count)				// Go through all the gameservers to find if there are any characters registered against the account.
		{
			$con = $gameserver_array[$i2][4];
			$db_table = $gameserver_array[$i2][1];
			$result2 = mysql_query("select COUNT(*) from $db_table.characters where account_name = '$accountname'",$con);
			if (!$result2)			// In case of database access failure, fix it so we don't delete anything.
			{	
				echo "<p>Database access failure server - array index $i2</p>";
				$chars_found = 1;	
			}
			else
			{
				$char_nums = mysql_result($result2,0,"COUNT(*)");  // Once we actually find some characters, no need to keep checking.
				if ($char_nums > 0)
				{
					$chars_found = 1;	
					$i2 = $array_count;
				}
			}
			$i2++;
		}
		if ($chars_found == 0)		// If we didn't find any characters against the account, then simply delete it.
		{	
			echo "<p>$accountname - Deleted</p>";	
			if ($safe_mode == 0)
			{
				$result2 = mysql_query("DELETE FROM $dblog_l2jdb.accounts WHERE login = '$accountname'",$con2);
				$result2 = mysql_query("DELETE FROM $dblog_l2jdb.knightdrop WHERE name = '$accountname'",$con2);
			}
			else
			{	echo "Safe mode on - record not deleted";	}
		}
	}
}

?>
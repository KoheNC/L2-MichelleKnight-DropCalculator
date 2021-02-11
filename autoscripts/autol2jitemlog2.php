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

//Gamserverdbs - copied straight from the config.php file.
$gameservers = ARRAY(
ARRAY("Title", "db_location", "db_database", "db_username", "db_password", defaultskin, "server files location", gameserver_port, "knight_db", "gameserver_telnet_ip", "gameserver_telnet_port", "gameserver_Telnet_password", gameserver_timeout)
// ,ARRAY("Title", "db_location", "db_database", "db_username", "db_password", defaultskin, "server files location", gameserver_port, "knight_db", "gameserver_telnet_ip", "gameserver_telnet_port", "gameserver_Telnet_password", gameserver_timeout)
);

$g_array_count = count($gameservers);
$i = 0;
while ($i < $g_array_count)
{
	$db_location = $gameservers[$i][1];
	$db_l2jdb = $gameservers[$i][2];	
	$db_user = $gameservers[$i][3];
	$db_psswd = $gameservers[$i][4];
	$knight_db = $gameservers[$i][8];
		$con = mysql_connect($db_location,$db_user,$db_psswd);
		if (!$con)
			{
			echo "<p class=\"popup\">Could Not Connect</p>";
			die('Could not connect: ' . mysql_error());
			}		
		if (!mysql_select_db("$db_l2jdb",$con))
			{
			die('Could not change to L2J database: ' . mysql_error());
			}

		$sql = "select COUNT(*) from $knight_db.itemlog where `this_run` = 1";	
		$result = mysql_query($sql,$con);
		$t_count = 0;
		while ($r_array = mysql_fetch_assoc($result))
		{	$t_count = $r_array['COUNT(*)'];	}

		$sql = "select * from $knight_db.itemlog where `this_run` = 1";	
		$result = mysql_query($sql,$con);
		$count = 0;
		while ($r_array = mysql_fetch_assoc($result))
		{
			$owner_id = $r_array[owner_id];
			$object_id = $r_array[object_id];
			$enchant_level = $r_array[enchant_level];
			$timestamp = $r_array[timestamp];
			$sql = "select index_id, owner_id, enchant_level from $knight_db.itemlog where object_id = $object_id and this_run = 0 order by index_id desc limit 1";
			$result2 = mysql_query($sql,$con);
			$index_id = 0;
			while ($r_array = mysql_fetch_assoc($result2))
			{	
				$index_id = $r_array[index_id];	
				// If there is no change in owner or enchantment, then there is no change.
				$own_id = $r_array[owner_id];	
				$ench = $r_array[enchant_level];	
				if (($ench == $enchant_level) and ($own_id == $owner_id))
				{	$index_id = -1;	}
			}
			$index_id++;
			if ($index_id > 0)
			{
				$sql = "update $knight_db.itemlog set index_id = $index_id, this_run = 0 where `object_id` = $object_id and timestamp = $timestamp";
				$result2 = mysql_query($sql,$con);
			}
			else 
			{
				$sql = "delete from $knight_db.itemlog where `object_id` = $object_id and timestamp = $timestamp";
				$result2 = mysql_query($sql,$con);
			}
			$count++;
		}
	$i++;
}

?>
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
		$sql = "select * from $knight_db.itemlog where object_id not in (select object_id from items);";
		$result = mysql_query($sql,$con);
		while ($r_array = mysql_fetch_assoc($result))
		{
			$owner_id = $r_array[owner_id];
			$object_id = $r_array[item_id];
			$timestamp = $r_array[timestamp];
			$enchant_level = $r_array[enchant_level];
			$object_name = "unknown";
			$owner_name = "unknown";
			if ($object_id > 0)
			{
				$sql = "select name from armor where item_id = $object_id";
				$result2 = mysql_query($sql,$con);
				while ($r_array = mysql_fetch_assoc($result2))
				{	$object_name = $r_array[name];	}
				$sql = "select name from weapon where item_id = $object_id";
				$result2 = mysql_query($sql,$con);
				while ($r_array = mysql_fetch_assoc($result2))
				{	$object_name = $r_array[name];	}
			}
			$sql = "select char_name from characters where charId = $owner_id";
			$result2 = mysql_query($sql,$con);
			while ($r_array = mysql_fetch_assoc($result2))
			{	$owner_name = $r_array[char_name];	}
			$sql = "insert into $knight_db.itemloghist (`objectname`, `owner`, timestamp, `enchant_level`) value ('$object_name', '$owner_name', $timestamp, $enchant_level)";
			$result3 = mysql_query($sql,$con);
		}
		$sql = "delete from $knight_db.itemlog where object_id not in (select object_id from items);";
		$result = mysql_query($sql,$con);
		$sql = "select owner_id, object_id, item_id, enchant_level from items where item_id in (select item_id from armor) or item_id in (select item_id from weapon);";	
		$result = mysql_query($sql,$con);
		$timestamp =  time();
		$count = 0;
		while ($r_array = mysql_fetch_assoc($result))
		{
			$owner_id = $r_array[owner_id];
			$object_id = $r_array[object_id];
			$item_id = $r_array[item_id];
			$enchant_level = $r_array[enchant_level];
			if (strlen($enchant_level) == 0)
			{	$enchant_level = 0;	}
			$sql = "insert into $knight_db.itemlog (`object_id`, `index_id`, `timestamp`, `owner_id`, `enchant_level`, `this_run`, `item_id`) values ($object_id, 0, $timestamp, $owner_id, $enchant_level, 1, $item_id)";
			$result2 = mysql_query($sql,$con);
			$count++;
		}
	$i++;
}

?>
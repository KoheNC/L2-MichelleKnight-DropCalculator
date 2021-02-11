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

//Gamserverdb
$db_location = "localhost";
$db_l2jdb = "l2jdb";
$db_user = "username";
$db_psswd = "password";

// Gameserver Telnet Settings
$telnet_host = "localhost";
$telnet_port = "15555";
$telnet_password = "password";
$telnet_timeout = 20;

function give_item($character, $item, $db_location, $db_user, $db_psswd, $db_l2jdb, $telnet_host, $telnet_port, $telnet_timeout, $telnet_password)
{
	$con3 = mysql_connect($db_location,$db_user,$db_psswd);
	if (!$con3)
	{
		echo "Could Not Connect";
		die('Could not connect: ' . mysql_error());
	}		
	if (!mysql_select_db("$db_l2jdb",$con3))
	{
		die('Could not change to L2J database: ' . mysql_error());
	}
	
	$result = mysql_query("select COUNT(*) from items where owner_id = '$character' and item_id = '$item'",$con3);
	$char_count = mysql_result($result,0,"COUNT(*)");

	if ($char_count == 0)
	{
		$sql = "select online from characters where obj_id = '$character'";
		$result2 = mysql_query($sql,$con3);
		$useronline = mysql_result($result2,0,"online");
		if ($useronline)
		{
			$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
			if($usetelnet)
			{
				$give_string = 'give ' . $touser . ' ' . $itemid . ' ' . $qty;
				fputs($usetelnet, $telnet_password);
				fputs($usetelnet, "\r\n");
				fputs($usetelnet, $give_string);
				fputs($usetelnet, "\r\n");
				fputs($usetelnet, "exit\r\n");
				fclose($usetelnet);
			}
		}
		else
		{
			$sql = "select object_id from items order by object_id desc limit 1";
			$result3 = mysql_query($sql,$con3);
			if (mysql_num_rows($result3) > 0)
			{	$new_id = mysql_result($result3,0,"object_id") + 1;	}
			else
			{	$new_id = 268435456;	}
			$sql = "insert into items (`owner_id`, `object_id`, `item_id`, `count`, `loc`, `loc_data`) values ('$character', '$new_id', '$item', '1', 'INVENTORY', '0')";
			$result3 = mysql_query($sql,$con3);
		}
	}
}


function circlet_check($castle, $db_location, $db_user, $db_psswd, $db_l2jdb, $telnet_host, $telnet_port, $telnet_timeout, $telnet_password)
{
	$con3 = mysql_connect($db_location,$db_user,$db_psswd);
	if (!$con3)
	{
		echo "Could Not Connect";
		die('Could not connect: ' . mysql_error());
	}		
	if (!mysql_select_db("$db_l2jdb",$con3))
	{
		die('Could not change to L2J database: ' . mysql_error());
	}
	$item_id = -45;
	if ($castle == 1)
	{	$item_id = 6838;	}
	elseif ($castle == 2)
	{	$item_id = 6835;	}
	if ($castle == 3)
	{	$item_id = 6839;	}
	if ($castle == 4)
	{	$item_id = 6837;	}
	if ($castle == 5)
	{	$item_id = 6840;	}
	if ($castle == 6)
	{	$item_id = 6834;	}
	if ($castle == 7)
	{	$item_id = 6836;	}
	if ($castle == 8)
	{	$item_id = 8182;	}
	if ($castle == 9)
	{	$item_id = 8183;	}
	
	$result = mysql_query("select COUNT(*) from clan_data where hascastle = $castle",$con3);
	$count = mysql_result($result,$i,"COUNT(*)");
	if ($count > 0)
	{
		$result = mysql_query("select clan_id, leader_id from clan_data where hascastle = $castle",$con3);
		$leader = mysql_result($result,$i,"leader_id");
		$clan_id = mysql_result($result,$i,"clan_id");
		$result = mysql_query("select obj_id from characters where clanid = '$clan_id' and obj_id <> '$leader'",$con3);
		$legit_chars = "(";
		$count = mysql_num_rows($result);
		$i = 0;
		while ($i < $count)
		{
			$gm_id = mysql_result($result,$i,"obj_id");
			give_item($gm_id, $item_id, $db_location, $db_user, $db_psswd, $db_l2jdb, $telnet_host, $telnet_port, $telnet_timeout, $telnet_password);
			$legit_chars = $legit_chars . "'" . $gm_id . "'";
			$i++;
			if ($i < $count)
			{	$legit_chars = $legit_chars . ", ";	}
		}
		if ($count == 0)
		{	$legit_chars = $legit_chars . "''";	}
		$legit_clans = $legit_clans . ")";
		$illegit_clans = "delete from items where item_id = $item_id and owner_id not in " . $legit_chars;
		$result = mysql_query($illegit_clans,$con3);
	}
	else
	{	$result = mysql_query("delete from items where item_id = $item_id",$con3);	}
}

// Connect to DB, and try to retrieve the user details.
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


$result = mysql_query("select clan_id, hascastle, leader_id from clan_data where hascastle > 0",$con);
$legit_clans = "(";
$count = mysql_num_rows($result);
$i = 0;
while ($i < $count)
{
	$gm_id = mysql_result($result,$i,"leader_id");	
	give_item($gm_id, 6841, $db_location, $db_user, $db_psswd, $db_l2jdb, $telnet_host, $telnet_port, $telnet_timeout, $telnet_password);
	$legit_clans = $legit_clans . "'" . $gm_id . "'";
	$i++;
	if ($i < $count)
	{	$legit_clans = $legit_clans . ", ";	}
}
if ($count == 0)
{	$legit_clans = $legit_clans . "''";	}
$legit_clans = $legit_clans . ")";
$illegit_clans = "delete from items where item_id = 6841 and owner_id not in " . $legit_clans;
$result = mysql_query($illegit_clans,$con);

$i = 1;
while ($i < 10)
{
	circlet_check($i, $db_location, $db_user, $db_psswd, $db_l2jdb, $telnet_host, $telnet_port, $telnet_timeout, $telnet_password);
	$i++;
}
?>
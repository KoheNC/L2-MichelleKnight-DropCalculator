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

//Gamserverdbs - copied straight from the config.php file.
$gameservers = ARRAY(
ARRAY("Title", "db_location", "db_database", "db_username", "db_password", defaultskin, "server files location", gameserver_port, "knight_db", "gameserver_telnet_ip", "gameserver_telnet_port", "gameserver_Telnet_password", gameserver_timeout)
// ,ARRAY("Title", "db_location", "db_database", "db_username", "db_password", defaultskin, "server files location", gameserver_port, "knight_db", "gameserver_telnet_ip", "gameserver_telnet_port", "gameserver_Telnet_password", gameserver_timeout)
);

$safe_mode = 1;

$g_array_count = count($gameservers);
$i = 0;
while ($i < $g_array_count)
{
	$g_name = $gameservers[$i][0];
	$telnet_host = $gameservers[$i][9];
	$telnet_port = $gameservers[$i][10];
	$telnet_timeout = $gameservers[$i][12];
	$telnet_password = $gameservers[$i][11];

	if ($safe_mode)
	{	echo "Would have issued restart for $g_name";	}
	else
	{
		$usetelnet = fsockopen($telnet_host, $telnet_port, $errno, $errstr, $telnet_timeout);
		if($usetelnet)
		{
			$give_string = 'restart 600';
			fputs($usetelnet, $telnet_password);
			fputs($usetelnet, "\r\n");
			fputs($usetelnet, $give_string);
			fputs($usetelnet, "\r\n");
			fputs($usetelnet, "exit\r\n");
			fclose($usetelnet);
		}
		else
		{	echo "<p class=\"popup\">Couldn't connect to telnet for $g_name</p>";	}
	}
	$i++;
}

?>
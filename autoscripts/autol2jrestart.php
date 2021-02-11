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

//Logonserverdb
$logsvr_location = "localhost";
$dblog_dir = "/login/";
$dblog_port = 1234;

$safe_mode = 1;

$start_string = "startGameServer.sh";		// For Linux
$start_l_string = "startLoginServer.sh";
$start_prefix = "./";
$start_suffix = "  > /dev/null";
$directory_separator = '/';
// $start_string = "startGameServer.bat";		// For Windows
// $start_l_string = "startLoginServer.bat";
// $start_prefix = "start /b ";
// $start_suffix = "";
// $directory_separator = '\\';

function checkport($ip, $port) 
{
        if ($check = @fsockopen($ip, $port, $errno, $errstr, 1.0)) 
		{
                fclose($check);
                return 1;
        }
        return 0;
}

if (!checkport($logsvr_location, $dblog_port))
{	
	sleep(30);		// If loginserver is down wait 30 seconds and try again in case it is restarting itself.
	if (!checkport($logsvr_location, $dblog_port))
	{
		chdir($dblog_dir);
		$shell_cmd = $start_prefix . $start_l_string . $start_suffix;
		if ($safe_mode < 1)
		{	
		   exec($shell_cmd);	
			echo "Issued $shell_cmd\n";
		}
		else
		{	echo "Would have issued for logon - $shell_cmd\n";	}
	}
}

$g_array_count = count($gameservers);
$i = 0;
while ($i < $g_array_count)
{
	$g_name = $gameservers[$i][0];
	$g_ip = $gameservers[$i][1];
	$g_user = $gameservers[$i][3];
	$g_passwd = $gameservers[$i][4];
	$g_port = $gameservers[$i][7];
	$g_location = $gameservers[$i][6];
	$g_knightdb = $gameservers[$i][8];
	if (!checkport($g_ip, $g_port))
	{	
		sleep(30);		// If gameserver is down wait 30 seconds and try again in case it is restarting itself.
		if (!checkport($g_ip, $g_port))
		{
			chdir($g_location);
			$shell_cmd = $start_prefix . $start_string . $start_suffix;
			if ($safe_mode < 1)
			{	
				$con = mysql_connect($g_ip,$g_user,$g_passwd);
				if ($con)
				{
					mysql_select_db("$g_knightdb",$con);
					$file_loc = $g_location . "log" . $directory_separator . "stdout.log";
					$handle = fopen($file_loc, "r");
					fseek($handle, -10000, SEEK_END);
					while (!feof($handle))
					{
						$line = fgets($handle, 1000);
						if ($i == 1)
						{	$lines = array($line);	}
						else
						{	array_push($lines, $line);	}
						$i++;
					}
					$start = $i - 60;
					if ($start < 1)
					{	$start = 1;	}
					while ($start < $i)
					{	
						$time = date('d M Y H:i:s') . " - " . $start . " - " . $lines[$start];
						$result = mysql_query("insert into $g_knightdb.restartlog (`lines`) values ('$time')",$con);
							$start++; 
					}
					$time = date('d M Y H:i:s') . " - " . $start . " -------------------------------";
					$result = mysql_query("insert into $g_knightdb.restartlog (`lines`) values ('$time')",$con);
				}
				exec($shell_cmd);	
				echo "Issued $shell_cmd\n";
			}
			else
			{	echo "Would have issued for $g_name - $shell_cmd\n";	}
		}
	}

	$i++;
}

?>
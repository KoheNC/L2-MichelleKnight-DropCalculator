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

$days_to_keep = 7;	// Number of days worth of records to keep.
$hour_split = 4;	// Chunks of an hour to record in.  1 = hourly figures, 2, half hourly, 6 ten minute chunks. 

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

// Create the statistics table if it doesn't exist.

$result = mysql_query("CREATE TABLE IF NOT EXISTS knightstats ( 
				`date` int(20) default NULL, 
				`hour` int(2) default NULL, 
				`period` int(2) default NULL, 
				`maxplayers` int(6) default NULL,
				`count` decimal(8,2) default NULL, 
				`reports` int(5) default NULL )",$con);

//Retrieve the settings that tell us what time zone is being used.
$result = mysql_query("select auto_prune from knightsettings",$con);
$timezone_adjust = mysql_result($result,0,"auto_prune");

// Get a count of the online characters.
$result = mysql_query("select COUNT(*) from characters where online = 1",$con);
$count = mysql_num_rows($result);

if ($count)		// If we got a result from the database ...
{	
	$char_count = mysql_result($result,0,"COUNT(*)");		// How many people are online ?
	
	$minutes = intval(time() / 60);						// Split the time in to the following ...
	$minutes = $minutes + ($timezone_adjust * 60);		// Period - portion of the hour.
	$hours = intval($minutes / 60);						// Hour - Number of the hour in the day, adjusted for timezone.
	$minutes = $minutes - ($hours * 60);				// Day number since Epoch.
	$date = intval($hours / 24);
	$hours = $hours - ($date * 24);						// Doing things this way makes displaying the statistics much easier.
	$hour_sp = intval(60 / $hour_split);
	$period = intval($minutes / $hour_sp);
	
	$result = mysql_query("select `count`, `reports`, `maxplayers` from knightstats where `date` = '$date' and `hour` = '$hours' and `period` = '$period'",$con);
	$count = mysql_num_rows($result);
	if ($count)								// Do we already have an entry for this period in the statistics ?
	{	
		$s_count = mysql_result($result,0,"count");			// If we do, then work out the average number of people online during the period.
		$s_reports = mysql_result($result,0,"reports");
		$s_maxp = mysql_result($result,0,"maxplayers");
		if ($char_count > $s_maxp)
		{	$s_maxp = $char_count;	}
		$s_count = $s_count * $s_reports;
		$s_count = $s_count + $char_count;
		$s_reports++;
		$s_count = $s_count / $s_reports;
		$sql = "update knightstats set `count` = '$s_count', `reports` = '$s_reports', `maxplayers` = '$s_maxp' where `date` = '$date' and `hour` = '$hours' and `period` = '$period'";
		$result = mysql_query($sql,$con);
	}
	else		// If not, then this is the first sample of people online during this period.
	{	$result = mysql_query("insert into knightstats (`date`, `hour`, `period`, `count`, `reports`, `maxplayers`) VALUES ('$date', '$hours', '$period', '$char_count', '1', '$char_count')",$con);	}
}

// This section deals with the maximum number of players we have seen online at any one time.
// First - try and recall the entry of the previous maximum number we have seen online.
$result = mysql_query("select `count`, `reports`, `maxplayers` from knightstats where date = '2147483647'",$con);	
$count = mysql_num_rows($result);
if ($count)
{
	$s_maxp = mysql_result($result,0,"maxplayers");		// If there is a previous maximum, and we have exceeded it, then record the new figure.
	if ($char_count > $s_maxp)
	{	$result = mysql_query("update knightstats set `maxplayers` = '$char_count' where `date` = '2147483647'",$con);	}
}
else		// If no previous maximum exists, then the current IS the new maximum.
{	$result = mysql_query("insert into knightstats (`date`, `hour`, `period`, `count`, `reports`, `maxplayers`) VALUES ('2147483647', '0', '0', '0', '1', '$char_count')",$con);	}

// Delete any entries that are too old.
$date =  $date - $days_to_keep;
$result = mysql_query("delete from knightstats where `date` < $date",$con);
?>
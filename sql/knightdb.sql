/*
SQLyog - Free MySQL GUI v5.11
Host - 4.1.10a : Database - l2jdb
*********************************************************************
Server version : 4.1.10a
*/ 

/* USE `l2jdb`; */


/*
THESE TABLES ARE NEEDED FOR THE KNIGHTDB DATABASE
Create a knightdb for every gameserver that will be accessed.

These tables can be put in the gameserver db if you wish, but when you start using
the database comparison system, the table will become very large - so it is better
to keep this separate from the gameserver db.
*/


/* Table structure for table `accnotes` */

CREATE TABLE `accnotes` (
  `charname` varchar(45) NOT NULL default '',
  `notenum` int(5) NOT NULL default '0',
  `notemaker` varchar(50) default NULL,
  `note` text,
  PRIMARY KEY  (`charname`,`notenum`)
);

/*Table structure for table `errors` */

CREATE TABLE `errors` (
  `error_text` text,
  `reason` text
);


/*Table structure for table `restartlog` */

CREATE TABLE `restartlog` (
  `lines` text
);


/*Table structure for the game items database */
create table itemlog (                      
	`object_id` int(11) NOT NULL default '0',  
	`index_id` int(11) NOT NULL default '0',  
	`timestamp` int(11) NOT NULL default '0', 
	`owner_id` int(11) default NULL,                 
	`enchant_level` int(11) default NULL, 
	`this_run` int(2) NOT NULL default '0',    
	`item_id` int(11) NOT NULL default '0', 
            PRIMARY KEY  (`object_id`,`index_id`)          
          );

/*Table structure for the game items database */
create table itemloghistory (                      
	`object_id` int(11) NOT NULL default '0', 
	`timestamp` int(11) NOT NULL default '0', 
	`owner_id` int(11) default NULL,                 
	`enchant_level` int(11) default NULL, 
            PRIMARY KEY  (`object_id`, `timestamp`)          
          );

/*Table structure for the game action log database */
create table actionlog (                      
	`timestamp` int(11) NOT NULL default '0', 
	`message` text         
          );
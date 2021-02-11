/*
SQLyog - Free MySQL GUI v5.11
Host - 4.1.10a : Database - l2jdb
*********************************************************************
Server version : 4.1.10a
*/ 

/* USE `l2jdb`; */

/*
THESE TABLES ARE NEEDED IN YOUR LOGONSERVER TABLE
*/

CREATE TABLE knightdrop ( 
	`name` varchar(45) NOT NULL default '', 
	`lastaction` int(11) default NULL, 
	`token` varchar(10) default NULL, 
	`mapaccess` int(20) default 0, 
	`recipeaccess` int(20) default 0,
	`gdaccess` int(20) default 0, 
	`boxingok` int(1) NULL, 
	`warnlevel` int(1) NULL, 
	`characcess` int(20) default '0',    
	`lastheard` int(20) default '0',   
	`ipaddr` varchar(30) default NULL, 
	`access_level` int(11) default 0,
	`email` varchar(50) default '',
	`request_time` int(20) default 0,
	`request_key` varchar(45),
	`emailcheck` int(1) NOT NULL default '0',
	`password` varchar(45) default NULL,
	`verified` int(1) NOT NULL default '0',
	PRIMARY KEY  (`name`), 
	UNIQUE KEY `id` (`name`) );

CREATE TABLE knightipok (  
	`ip_addr` varchar(45) default NULL,  
	PRIMARY KEY  (`ip_addr`));




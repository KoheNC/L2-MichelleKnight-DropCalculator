/*
	TO BE RUN AGAINST THE MAIN KNIGHT TABLES IN THE GAMESERVER DATABASE
*/

/* This adds a setting option to set the recording of actions in the system. */

alter table knightsettings add column `log_actions` int(1) NOT NULL default '0';     
alter table knightsettings add column `log_duration` int(1) NOT NULL default '1';   



/*
	TO BE RUN AGAINST THE SEPARATE KNIGHT DATABASE
*/

/*Table structure for the game action log database */
create table actionlog (                      
	`timestamp` int(11) NOT NULL default '0', 
	`message` text         
          );
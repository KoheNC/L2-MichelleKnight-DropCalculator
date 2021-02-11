//
//
// This query is to be executed against the knightdb that also contains 
// the account notes tables.
//
//


/*Table structure for the game items log database */
create table itemlog (                      
	`object_id` int(11) NOT NULL default '0',  
	`index_id` int(11) NOT NULL default '0',  
	`timestamp` int(11) NOT NULL default '0', 
	`owner_id` int(11) default NULL,                 
	`enchant_level` int(11) default NULL, 
	`this_run` int(2) NOT NULL default '0',  
            PRIMARY KEY  (`object_id`,`index_id`)          
          );

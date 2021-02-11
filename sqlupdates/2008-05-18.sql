// These commands to be run against the night logging section of the dropcalc tables.

alter table itemlog add column `item_id` int(11) default NULL;

CREATE TABLE `itemloghist` (                 
               `objectname` varchar(70) default NULL,     
               `owner` varchar(35) default NULL,          
               `timestamp` int(11) NOT NULL default '0',  
               `enchant_level` int(11) default NULL       
             );


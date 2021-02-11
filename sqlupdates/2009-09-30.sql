alter table knightdrop add column `verified` int(1) NOT NULL default '0';
update knightdrop set verified = 1 where access_level > 0;

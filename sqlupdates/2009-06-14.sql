alter table knightsettings add column `sec_enchant` int(3) NOT NULL default '0';
update knightsettings set sec_enchant = sec_inc_admin;

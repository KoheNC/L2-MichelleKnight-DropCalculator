 
CREATE TABLE `knightdungeon` (
  `dungeon_name` varchar(40) NOT NULL default '',
  `sub_name` varchar(40) NOT NULL default '',
  `mapname` varchar(20) NOT NULL default '0',
  `xmin` int(11) NOT NULL default '0',
  `xmax` int(3) NOT NULL default '0',
  `ymin` int(11) NOT NULL default '0',
  `ymax` int(11) NOT NULL default '0',
  `zmin` int(11) NOT NULL default '0',
  `zmax` int(11) NOT NULL default '0',
  `mapx` int(11) NOT NULL default '0',
  `mapy` int(11) NOT NULL default '0',
  PRIMARY KEY  (`dungeon_name`,`sub_name`)
); 

INSERT INTO `knightdungeon` VALUES  ('Tower of Insolence','Floor 02','toi02',110461,118848,11878,20073,-3609,-2188,800,800),
 ('Tower of Insolence','Floor 01','toi01',110461,118848,11878,20073,-5200,-3650,800,800),
 ('Ants Nest','','antsnest',-31800,-9783,173000,194500,-9000,-4972,800,800),
 ('Tower of Insolence','Floor 03','toi03',110461,118848,11878,20073,-2140,-680,800,800),
 ('Tower of Insolence','Floor 07','toi07',110461,118848,11878,20073,2950,3900,800,800),
 ('Tower of Insolence','Floor 06','toi06',110461,118848,11878,20073,1940,2900,800,800),
 ('Tower of Insolence','Floor 05','toi05',110461,118848,11878,20073,820,1900,800,800),
 ('Tower of Insolence','Floor 04','toi04',110461,118848,11878,20073,-650,800,800,800),
 ('Tower of Insolence','Floor 11','toi11',110461,118848,11878,20073,6970,7900,800,800),
 ('Tower of Insolence','Floor 10','toi10',110461,118848,11878,20073,5980,6900,800,800),
 ('Tower of Insolence','Floor 09','toi09',110461,118848,11878,20073,4970,5900,800,800),
 ('Tower of Insolence','Floor 08','toi08',110461,118848,11878,20073,3930,4900,800,800),
 ('Tower of Insolence','Floor 13','toi13',110461,118848,11878,20073,9000,10060,800,800),
 ('Tower of Insolence','Floor 14','toi14',110461,118848,11878,20073,10070,11000,800,800),
 ('Tower of Insolence','Floor 12','toi12',110461,118848,11878,20073,7990,8990,800,800);

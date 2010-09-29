DROP TABLE IF EXISTS `j16_jupgrade_categories`;
CREATE TABLE IF NOT EXISTS `j16_jupgrade_categories` (
  `old` int(11) NOT NULL,
  `new` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

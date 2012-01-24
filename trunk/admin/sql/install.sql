--
-- Table structure for table `jupgrade_categories`
--

DROP TABLE IF EXISTS `jupgrade_categories`;
CREATE TABLE IF NOT EXISTS `jupgrade_categories` (
  `old` int(11) NOT NULL,
  `new` int(11) NOT NULL,
  `section` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `jupgrade_menus`
--

DROP TABLE IF EXISTS `jupgrade_menus`;
CREATE TABLE IF NOT EXISTS `jupgrade_menus` (
  `old` int(11) NOT NULL,
  `new` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `jupgrade_menus`
--

INSERT INTO `jupgrade_menus` VALUES(0, 0);

--
-- Table structure for table `jupgrade_steps`
--

DROP TABLE IF EXISTS `jupgrade_steps`;
CREATE TABLE IF NOT EXISTS `jupgrade_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `extension` int(1) NOT NULL DEFAULT '0',
  `state` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `jupgrade_steps`
--

INSERT INTO `jupgrade_steps` VALUES(1, 'users', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(2, 'categories', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(3, 'content', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(4, 'menus', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(5, 'modules', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(6, 'banners', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(7, 'contacts', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(8, 'newsfeeds', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(9, 'weblinks', 0, 0, '');
INSERT INTO `jupgrade_steps` VALUES(10, 'extensions', 0, 1, '');

--
-- Table structure for table `jupgrade_modules`
--

DROP TABLE IF EXISTS `jupgrade_modules`;
CREATE TABLE IF NOT EXISTS `jupgrade_modules` (
  `old` int(11) NOT NULL,
  `new` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

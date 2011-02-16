--
-- Table structure for table `j16_jupgrade_categories`
--

DROP TABLE IF EXISTS `j16_jupgrade_categories`;
CREATE TABLE IF NOT EXISTS `j16_jupgrade_categories` (
  `old` int(11) NOT NULL,
  `new` int(11) NOT NULL,
  `section` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `j16_jupgrade_menus`
--

DROP TABLE IF EXISTS `j16_jupgrade_menus`;
CREATE TABLE IF NOT EXISTS `j16_jupgrade_menus` (
  `old` int(11) NOT NULL,
  `new` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `j16_jupgrade_steps`
--

DROP TABLE IF EXISTS `j16_jupgrade_steps`;
CREATE TABLE IF NOT EXISTS `j16_jupgrade_steps` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `j16_jupgrade_steps`
--

INSERT INTO `j16_jupgrade_steps` VALUES(1, 'users', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(2, 'modules', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(3, 'categories', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(4, 'content', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(5, 'menus', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(6, 'banners', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(7, 'contacts', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(8, 'newsfeeds', 0);
INSERT INTO `j16_jupgrade_steps` VALUES(9, 'weblinks', 0);

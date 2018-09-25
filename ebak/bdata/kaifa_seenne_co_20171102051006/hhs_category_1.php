<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_category`;");
E_C("CREATE TABLE `hhs_category` (
  `cat_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(90) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `cat_desc` varchar(255) NOT NULL DEFAULT '',
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sort_order` tinyint(1) unsigned NOT NULL DEFAULT '50',
  `template_file` varchar(50) NOT NULL DEFAULT '',
  `measure_unit` varchar(15) NOT NULL DEFAULT '',
  `show_in_nav` tinyint(1) NOT NULL DEFAULT '0',
  `style` varchar(150) NOT NULL,
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `grade` tinyint(4) NOT NULL DEFAULT '0',
  `filter_attr` varchar(255) NOT NULL DEFAULT '0',
  `cat_img` varchar(200) DEFAULT NULL,
  `commission` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`cat_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_category` values('1','水果','','','0','50','','','0','','1','0','0','images/201710/1508909517788569851.png','0.00');");
E_D("replace into `hhs_category` values('2','零食馆','','','0','50','','','0','','1','0','0','images/201710/1508909574157074790.png','0.00');");
E_D("replace into `hhs_category` values('3','手机','','','0','50','','','0','','1','0','0','images/201710/1508909559178837991.jpg','0.00');");
E_D("replace into `hhs_category` values('4','海淘馆','','','0','50','','','0','','1','0','0','images/201710/1508909496141219625.png','0.00');");
E_D("replace into `hhs_category` values('5','玩具馆','','','0','50','','','0','','1','0','0','images/201710/1508909490247583229.png','0.00');");
E_D("replace into `hhs_category` values('6','服装馆','','','0','50','','','0','','1','0','0','images/201710/1508909460995385502.png','0.00');");
E_D("replace into `hhs_category` values('7','母婴管','','','0','50','','','0','','1','0','0','images/201710/1508909531344986790.png','0.00');");
E_D("replace into `hhs_category` values('8','美妆区','','','0','50','','','0','','1','0','0','images/201710/1508909467809935764.png','0.00');");
E_D("replace into `hhs_category` values('9','鲜果区','','','0','50','','','0','','1','0','0','images/201710/1508909587925507065.png','0.00');");
E_D("replace into `hhs_category` values('10','百货馆','','','0','50','','','0','','1','0','0','images/201710/1508909600978296636.png','0.00');");
E_D("replace into `hhs_category` values('11','苹果','','','1','50','','','0','','1','0','0',NULL,'0.00');");

require("../../inc/footer.php");
?>
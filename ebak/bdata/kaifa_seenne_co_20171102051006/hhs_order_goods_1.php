<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_order_goods`;");
E_C("CREATE TABLE `hhs_order_goods` (
  `rec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_name` varchar(120) NOT NULL DEFAULT '',
  `goods_sn` varchar(60) NOT NULL DEFAULT '',
  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_number` smallint(5) unsigned NOT NULL DEFAULT '1',
  `market_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_attr` text NOT NULL,
  `send_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_real` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `extension_code` varchar(30) NOT NULL DEFAULT '',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_gift` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_attr_id` varchar(255) NOT NULL DEFAULT '',
  `refund_reason` varchar(255) NOT NULL DEFAULT '',
  `refund_desc` text NOT NULL,
  `refund_pic1` varchar(255) NOT NULL DEFAULT '',
  `refund_pic2` varchar(255) NOT NULL DEFAULT '',
  `refund_pic3` varchar(255) NOT NULL DEFAULT '',
  `refund_add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `refund_confirm_time` int(10) unsigned NOT NULL DEFAULT '0',
  `refund_confirm_desc` text NOT NULL,
  `refund_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `commission` decimal(10,2) DEFAULT NULL,
  `city_id` int(10) DEFAULT NULL,
  `district_id` int(10) DEFAULT NULL,
  `suppliers_id` int(10) DEFAULT NULL,
  `rate_1` decimal(10,2) DEFAULT '0.00',
  `rate_2` decimal(10,2) DEFAULT '0.00',
  `rate_3` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`rec_id`),
  KEY `order_id` (`order_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_order_goods` values('1','1','1','ceshi ','HHS000000','0','1','1.20','1.00','','0','1','team_goods','0','0','','','','','','','0','0','','0',NULL,'1','0','0','0.00','0.00','0.00');");
E_D("replace into `hhs_order_goods` values('2','2','1','ceshi ','HHS000000','0','1','1.20','0.01','','1','1','team_goods','0','0','','','','','','','0','0','','0',NULL,'1','0','0','0.00','0.00','0.00');");
E_D("replace into `hhs_order_goods` values('3','3','1','ceshi ','HHS000000','0','1','1.20','0.01','','0','1','team_goods','0','0','','','','','','','0','0','','0',NULL,'1','0','0','0.00','0.00','0.00');");
E_D("replace into `hhs_order_goods` values('4','4','2','测试测试','11','0','1','111.00','1.00','','0','1','team_goods','0','0','','','','','','','0','0','','0',NULL,'1','0','6','0.00','0.00','0.00');");
E_D("replace into `hhs_order_goods` values('5','5','2','测试测试','11','0','1','111.00','1.00','','0','1','team_goods','0','0','','','','','','','0','0','','0',NULL,'1','0','6','0.00','0.00','0.00');");
E_D("replace into `hhs_order_goods` values('6','6','14','学生办公中性笔0.5mm子弹头笔芯办公用品黑红蓝学生水性写字商务签字笔','HHS000014','0','1','12.00','5.00','','0','1','team_goods','0','0','','','','','','','0','0','','0',NULL,'1','0','0','0.00','0.00','0.00');");
E_D("replace into `hhs_order_goods` values('7','7','10','睡衣女秋冬季女士加厚 睡衣女长袖法兰绒睡衣开衫套装','HHS000010','0','1','54.00','29.00','','0','1','team_goods','0','0','','','','','','','0','0','','0',NULL,'1','0','0','0.00','0.00','0.00');");
E_D("replace into `hhs_order_goods` values('8','8','13','金丝绒卫衣秋衣女韩版秋冬大码女装中长款加绒加厚卫衣宽松学生卫衣打底金丝绒上衣秋季外套','HHS000013','0','1','84.00','70.00','','0','1','','0','0','','','','','','','0','0','','0',NULL,NULL,NULL,NULL,'0.00','0.00','0.00');");

require("../../inc/footer.php");
?>
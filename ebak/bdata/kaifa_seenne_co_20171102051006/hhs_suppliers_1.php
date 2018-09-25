<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_suppliers`;");
E_C("CREATE TABLE `hhs_suppliers` (
  `suppliers_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `suppliers_name` varchar(255) DEFAULT NULL,
  `suppliers_desc` mediumtext,
  `is_check` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '-1未过，1已过，2再审',
  `qq` varchar(300) NOT NULL,
  `city_id` varchar(10) NOT NULL DEFAULT '1',
  `province_id` varchar(10) NOT NULL,
  `supp_logo` varchar(100) NOT NULL,
  `supp_banner` varchar(100) NOT NULL,
  `district_id` varchar(10) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `announcement` varchar(255) NOT NULL,
  `rank_id` varchar(10) NOT NULL,
  `password` varchar(40) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `business_license` varchar(100) NOT NULL,
  `cards` varchar(100) NOT NULL,
  `show_type` int(10) NOT NULL,
  `business_scope` varchar(100) NOT NULL COMMENT '组织机构代码证',
  `map_info` varchar(30) NOT NULL,
  `real_name` varchar(30) NOT NULL,
  `identification_card` varchar(100) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `business_license_number` varchar(100) NOT NULL,
  `certificate` varchar(100) NOT NULL COMMENT '税务登记证',
  `check_desc` varchar(255) NOT NULL,
  `phone1` varchar(30) NOT NULL,
  `email1` varchar(30) NOT NULL,
  `is_top` int(10) NOT NULL DEFAULT '0',
  `sort_order` int(10) NOT NULL DEFAULT '0',
  `is_delete` int(10) NOT NULL DEFAULT '0',
  `is_oneshow` int(10) NOT NULL DEFAULT '0',
  `is_twoshow` int(10) NOT NULL DEFAULT '0',
  `comprehensive_score` int(10) NOT NULL DEFAULT '0' COMMENT '综合',
  `description_score` int(10) NOT NULL DEFAULT '0' COMMENT '描述',
  `service_score` int(10) NOT NULL DEFAULT '0' COMMENT '服务',
  `delivery_score` int(10) NOT NULL DEFAULT '0' COMMENT '发货',
  `factory_authorized` text NOT NULL,
  `url_name` varchar(100) NOT NULL COMMENT '二级域名',
  `supp_type` tinyint(1) NOT NULL COMMENT '商家类型(0默认商家/1个人商家/2厂家商家)',
  `supp_type_id` int(10) NOT NULL COMMENT '个人ID或厂家ID',
  `recommend_person` varchar(25) NOT NULL COMMENT '推荐人',
  `recommend_supp` varchar(25) NOT NULL COMMENT '推荐商家',
  `add_time` varchar(15) NOT NULL COMMENT '添加时间',
  `shopowner_phone` varchar(15) DEFAULT NULL COMMENT '店长手机',
  `longitude` varchar(100) NOT NULL,
  `latitude` varchar(100) NOT NULL,
  `openid` varchar(300) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `percentage` decimal(10,2) DEFAULT '0.00' COMMENT '平台佣金比例',
  `rate_1` decimal(10,2) DEFAULT '0.00' COMMENT '1级分销佣金比例',
  `rate_2` decimal(10,2) DEFAULT '0.00' COMMENT '2级分销佣金比例',
  `rate_3` decimal(10,2) DEFAULT '0.00' COMMENT '3级分销佣金比例',
  `hangye_id` int(10) DEFAULT '0' COMMENT '行业',
  `is_you` smallint(1) NOT NULL DEFAULT '0',
  `card_z` varchar(255) NOT NULL,
  `card_f` varchar(255) NOT NULL,
  `business_license2` varchar(255) NOT NULL,
  `certificate2` varchar(255) NOT NULL,
  `card_u` varchar(255) DEFAULT NULL,
  `card_w` varchar(255) DEFAULT NULL,
  `card_i` varchar(255) NOT NULL,
  PRIMARY KEY (`suppliers_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_suppliers` values('1','1e21e21e21e','','1','','1','','','','0','','15088888888','','','','e10adc3949ba59abbe56e057f20f883e','ceshi','','','0','','','','','','','','','','','0','0','0','0','0','0','0','0','0','','','0','0','','','1496968320',NULL,'','',NULL,NULL,'0.00','0.00','0.00','0.00','0','0','0','','','',NULL,NULL,'');");
E_D("replace into `hhs_suppliers` values('6','125','','0','','1','','','','0','趣图','15050687504','1449917676@qq.com','','','e10adc3949ba59abbe56e057f20f883e','admin888','','','0','','','调整下','','','','','353','','','0','0','0','0','0','0','0','0','0','','','0','0','','','1508456543',NULL,'','','oWqSPuDp-4QXPYdATR0kxhYkAa2I','3','0.00','0.00','0.00','0.00','0','0','data/business_file/1508456538378993586.png','data/business_file/1508456541841762688.png','','',NULL,NULL,'');");
E_D("replace into `hhs_suppliers` values('7','57454','','2','','1','','','','0','5646584','15050687809','1449917676@qq.com','','','e10adc3949ba59abbe56e057f20f883e','15445','','','0','','','4544','','','','','','','','0','0','0','0','0','0','0','0','0','','','0','0','','','1508915687',NULL,'','',NULL,NULL,'123456.00','0.00','0.00','0.00','0','0','','','','',NULL,NULL,'');");
E_D("replace into `hhs_suppliers` values('8','狐狸精',NULL,'0','','1','','','','0','1255','15050687504','18588744@qq.com','','','25d55ad283aa400af464c76d713c07ad','后天','','','0','','','回来','','','','','','','','0','0','0','0','0','0','0','0','0','','','0','0','','','1508996286',NULL,'','','oWqSPuDp-4QXPYdATR0kxhYkAa2I','3','0.00','0.00','0.00','0.00','0','0','data/business_file/1508996230591622915.png','data/business_file/1508996232954097445.png','','',NULL,NULL,'');");

require("../../inc/footer.php");
?>
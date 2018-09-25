<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_weixin_menu`;");
E_C("CREATE TABLE `hhs_weixin_menu` (
  `cat_id` smallint(5) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL DEFAULT '',
  `cat_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `weixin_key` varchar(255) NOT NULL DEFAULT '',
  `links` varchar(255) NOT NULL DEFAULT '',
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '50',
  `weixin_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `cat_type` (`cat_type`),
  KEY `sort_order` (`sort_order`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_weixin_menu` values('59','购买系统','1','','','http://kaifa.seenne.com/user.php?act=appoint&id=12','2','1','0');");
E_D("replace into `hhs_weixin_menu` values('70','系统演示','1','','','http://kaifa.seenne.com/index.php','1','1','0');");
E_D("replace into `hhs_weixin_menu` values('90','商城首页','1','','','http://kaifa.seenne.com/index.php','50','1','70');");
E_D("replace into `hhs_weixin_menu` values('91','会员中心','1','','','http://kaifa.seenne.com/user.php','50','1','70');");
E_D("replace into `hhs_weixin_menu` values('92','拼团广场','1','','','http://kaifa.seenne.com/square.php','50','1','70');");
E_D("replace into `hhs_weixin_menu` values('93','双十一专题','1','','','http://kaifa.seenne.com/topic.php?topic_id=12','50','1','70');");
E_D("replace into `hhs_weixin_menu` values('94','抽奖活动','1','','','http://kaifa.seenne.com/luckdraw.php','50','1','70');");
E_D("replace into `hhs_weixin_menu` values('95','购买源码','1','','','http://kaifa.seenne.com/user.php?act=appoint&id=12','50','1','59');");
E_D("replace into `hhs_weixin_menu` values('96','服务器版','1','','','http://kaifa.seenne.com/user.php?act=appoint&id=13','50','1','59');");

require("../../inc/footer.php");
?>
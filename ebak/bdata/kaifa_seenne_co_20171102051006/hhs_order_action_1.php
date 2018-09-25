<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_order_action`;");
E_C("CREATE TABLE `hhs_order_action` (
  `action_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `action_user` varchar(30) NOT NULL DEFAULT '',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `action_place` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `action_note` varchar(255) NOT NULL DEFAULT '',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`action_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_order_action` values('1','2','买家','1','0','2','0','','1508807886');");
E_D("replace into `hhs_order_action` values('2','1','admin','0','0','0','0','订单支付提醒','1508869857');");
E_D("replace into `hhs_order_action` values('3','1','admin','2','0','0','0','系统取消未支付单','1508869909');");
E_D("replace into `hhs_order_action` values('4','2','admin','5','5','2','0','225','1508891407');");
E_D("replace into `hhs_order_action` values('5','3','买家','1','0','2','0','','1508891513');");
E_D("replace into `hhs_order_action` values('6','5','admin','1','0','2','0','54','1508891795');");
E_D("replace into `hhs_order_action` values('7','4','admin','0','0','0','0','订单支付提醒','1508908808');");
E_D("replace into `hhs_order_action` values('8','4','admin','2','0','0','0','系统取消未支付单','1508908858');");
E_D("replace into `hhs_order_action` values('9','6','admin','0','0','0','0','订单支付提醒','1509134584');");
E_D("replace into `hhs_order_action` values('10','7','admin','0','0','0','0','订单支付提醒','1509134585');");
E_D("replace into `hhs_order_action` values('11','8','admin','0','0','0','0','订单支付提醒','1509134585');");
E_D("replace into `hhs_order_action` values('12','6','admin','2','0','0','0','系统取消未支付单','1509320215');");
E_D("replace into `hhs_order_action` values('13','7','admin','2','0','0','0','系统取消未支付单','1509320215');");
E_D("replace into `hhs_order_action` values('14','8','admin','2','0','0','0','系统取消未支付单','1509320215');");

require("../../inc/footer.php");
?>
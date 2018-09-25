<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_share_log`;");
E_C("CREATE TABLE `hhs_share_log` (
  `goods_id` int(10) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8");
E_D("replace into `hhs_share_log` values('1','/images/share/1508807714.jpg');");
E_D("replace into `hhs_share_log` values('2','/images/share/1508891715.jpg');");
E_D("replace into `hhs_share_log` values('3','/images/share/1508893005.jpg');");
E_D("replace into `hhs_share_log` values('4','/images/share/1508893704.jpg');");
E_D("replace into `hhs_share_log` values('5','/images/share/1508894603.jpg');");
E_D("replace into `hhs_share_log` values('6','/images/share/1508894909.jpg');");
E_D("replace into `hhs_share_log` values('7','/images/share/1508895332.jpg');");
E_D("replace into `hhs_share_log` values('9','/images/share/1508895874.jpg');");
E_D("replace into `hhs_share_log` values('10','/images/share/1508896142.jpg');");
E_D("replace into `hhs_share_log` values('11','/images/share/1508896411.jpg');");
E_D("replace into `hhs_share_log` values('12','/images/share/1508896665.jpg');");
E_D("replace into `hhs_share_log` values('14','/images/share/1508898390.jpg');");
E_D("replace into `hhs_share_log` values('13','/images/share/1508999732.jpg');");
E_D("replace into `hhs_share_log` values('8','/images/share/1509405790.jpg');");

require("../../inc/footer.php");
?>
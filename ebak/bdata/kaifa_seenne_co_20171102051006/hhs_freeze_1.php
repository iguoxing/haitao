<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_freeze`;");
E_C("CREATE TABLE `hhs_freeze` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `price` float(10,2) NOT NULL,
  `uid` int(11) NOT NULL,
  `createtime` varchar(255) NOT NULL,
  `apply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否申请',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_freeze` values('20','1','0.01','3','1508406968','2');");

require("../../inc/footer.php");
?>
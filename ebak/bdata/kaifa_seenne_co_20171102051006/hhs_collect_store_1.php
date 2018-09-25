<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_collect_store`;");
E_C("CREATE TABLE `hhs_collect_store` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(30) NOT NULL,
  `suppliers_id` int(30) NOT NULL,
  `add_time` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='收藏店铺'");
E_D("replace into `hhs_collect_store` values('1','14','6','1509490362');");

require("../../inc/footer.php");
?>
<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_appoint_list`;");
E_C("CREATE TABLE `hhs_appoint_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dec` longtext NOT NULL,
  `input` varchar(255) NOT NULL COMMENT '输入框',
  `createtime` varchar(255) NOT NULL,
  `price` float(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_appoint_list` values('12','源码在线购买','<p>\r\n	<img src=\"/images/upload/image/20171025/20171025231802_34481.png\" alt=\"\" /> \r\n</p>\r\n<p style=\"text-align:center;\">\r\n	<span style=\"font-size:32px;color:#E53333;\">ceshi ceshiceiceicheiwceich</span> \r\n</p>','1,2,3','1508984417','0.01');");
E_D("replace into `hhs_appoint_list` values('13','服务器版在线购买','<img src=\"/images/upload/image/20171025/20171025231748_99714.png\" alt=\"\" />','1,2,3','1508944669','800.00');");
E_D("replace into `hhs_appoint_list` values('14','ceshi ','fergre&nbsp;','1','1508955694','0.01');");

require("../../inc/footer.php");
?>
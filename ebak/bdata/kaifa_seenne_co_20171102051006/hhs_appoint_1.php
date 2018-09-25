<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_appoint`;");
E_C("CREATE TABLE `hhs_appoint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(4) NOT NULL,
  `uid` int(4) NOT NULL,
  `content` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `createtime` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=186 DEFAULT CHARSET=utf8 COMMENT='预约'");
E_D("replace into `hhs_appoint` values('175','10','3','普通','15050687502','48854@qq.com','突尼斯','1','1508899178');");
E_D("replace into `hhs_appoint` values('176','11','3','程云强','15050687504','1449917676@qq.com','','1','1508940763');");
E_D("replace into `hhs_appoint` values('177','12','0','橙','15050687504','1449917676@qq.com','','0','1508941374');");
E_D("replace into `hhs_appoint` values('178','12','0','橙','15050687504','1449917676@qq.com','','0','1508941376');");
E_D("replace into `hhs_appoint` values('179','12','3','程咬金','15050687502','1449917676@qq.com','','1','1508941405');");
E_D("replace into `hhs_appoint` values('180','12','3','程','15050687504','1779917676@qq.com','','0','1508942147');");
E_D("replace into `hhs_appoint` values('181','12','3','程序','15050687504','14477887586@qq.com','','0','1508942555');");
E_D("replace into `hhs_appoint` values('182','13','3','程度','15050687504','14499767676@qq.com','','0','1508944629');");
E_D("replace into `hhs_appoint` values('183','12','3','冲了','15050687502','1758826@qq.com','','1','1508984447');");
E_D("replace into `hhs_appoint` values('184','14','3','程','','','','1','1508984512');");
E_D("replace into `hhs_appoint` values('185','12','3','嗖嗖嗖','15050687504','4676766886@qq.com','','0','1509028511');");

require("../../inc/footer.php");
?>
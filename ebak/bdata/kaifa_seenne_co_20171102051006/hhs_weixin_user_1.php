<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_weixin_user`;");
E_C("CREATE TABLE `hhs_weixin_user` (
  `uid` int(7) NOT NULL AUTO_INCREMENT,
  `subscribe` tinyint(1) unsigned NOT NULL,
  `wxid` char(28) NOT NULL,
  `nickname` varchar(200) NOT NULL,
  `sex` tinyint(1) unsigned NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `language` varchar(50) NOT NULL,
  `headimgurl` varchar(200) NOT NULL,
  `subscribe_time` int(10) unsigned NOT NULL,
  `localimgurl` varchar(200) NOT NULL,
  `setp` smallint(2) unsigned NOT NULL,
  `uname` varchar(50) NOT NULL,
  `coupon` varchar(30) NOT NULL,
  `lat` decimal(10,5) DEFAULT NULL,
  `lng` decimal(10,5) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `idx_wxid` (`wxid`),
  KEY `idx_wx_se_un` (`wxid`,`setp`,`uname`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_weixin_user` values('1','0','oL2Bv1IGjwMvevWt3IsODz3PYTWU','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('2','0','oWqSPuDp-4QXPYdATR0kxhYkAa2I','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('3','0','oWqSPuLaIJFlg1QZFzh9JP1eJr58','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('4','0','oWqSPuFJwpoIjmdvSIkDB3IlunV4','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('5','0','oWqSPuCtPGF_KExMaOj3pHZOwHio','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('6','0','oWqSPuLQGpQAsHCcSw3Ow6YVrnx4','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('7','0','oWqSPuGFIP1yhFoDH_89ZTzIsaRE','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('8','0','oWqSPuLNlgP6UfXp3cKo0t-As34A','','0','','','','','','0','','0','','',NULL,NULL);");
E_D("replace into `hhs_weixin_user` values('9','0','oWqSPuF0Ui-JchpAxoNqTANj_yLE','','0','','','','','','0','','0','','',NULL,NULL);");

require("../../inc/footer.php");
?>
<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `xcx_config`;");
E_C("CREATE TABLE `xcx_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `xcx_name` varchar(50) NOT NULL DEFAULT '微信小程序',
  `appid` varchar(50) NOT NULL,
  `appsecret` varchar(50) NOT NULL,
  `mchid` varchar(50) NOT NULL,
  `key` varchar(50) NOT NULL,
  `url` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8");
E_D("replace into `xcx_config` values('1','拼团','wx41379de397f80022','f1b3b843c3f2c73c34a93d47e8b40adb','1234268302','chengyunqiang123chengyunqiang123','kaifa.seenne.com');");

require("../../inc/footer.php");
?>
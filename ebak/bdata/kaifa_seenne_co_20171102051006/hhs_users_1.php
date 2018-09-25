<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_users`;");
E_C("CREATE TABLE `hhs_users` (
  `user_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `aite_id` text NOT NULL,
  `email` varchar(60) NOT NULL DEFAULT '',
  `user_name` varchar(60) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `question` varchar(255) NOT NULL DEFAULT '',
  `answer` varchar(255) NOT NULL DEFAULT '',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `birthday` date NOT NULL DEFAULT '0000-00-00',
  `user_money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `frozen_money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_points` int(10) unsigned NOT NULL DEFAULT '0',
  `rank_points` int(10) unsigned NOT NULL DEFAULT '0',
  `address_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0',
  `last_login` int(11) unsigned NOT NULL DEFAULT '0',
  `last_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_ip` varchar(15) NOT NULL DEFAULT '',
  `visit_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `user_rank` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_special` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ec_salt` varchar(10) DEFAULT NULL,
  `salt` varchar(10) NOT NULL DEFAULT '0',
  `parent_id` mediumint(9) NOT NULL DEFAULT '0',
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `alias` varchar(60) NOT NULL,
  `msn` varchar(60) NOT NULL,
  `qq` varchar(20) NOT NULL,
  `office_phone` varchar(20) NOT NULL,
  `home_phone` varchar(20) NOT NULL,
  `mobile_phone` varchar(20) NOT NULL,
  `is_validated` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `credit_line` decimal(10,2) unsigned NOT NULL,
  `passwd_question` varchar(50) DEFAULT NULL,
  `passwd_answer` varchar(255) DEFAULT NULL,
  `wxid` char(28) NOT NULL,
  `wxch_bd` char(2) NOT NULL,
  `openid` varchar(50) NOT NULL,
  `headimgurl` varchar(255) NOT NULL,
  `lng` decimal(10,5) DEFAULT NULL,
  `lat` decimal(10,5) DEFAULT NULL,
  `uname` varchar(100) NOT NULL,
  `is_subscribe` tinyint(4) NOT NULL DEFAULT '0',
  `is_send` int(10) DEFAULT '0',
  `uid_1` int(10) DEFAULT '0' COMMENT '一级分销',
  `uid_2` int(10) DEFAULT '0' COMMENT '二级分销',
  `uid_3` int(10) DEFAULT '0' COMMENT '三级分销',
  `registration_time` varchar(15) NOT NULL DEFAULT '',
  `unionid` varchar(100) DEFAULT NULL,
  `app_openid` varchar(100) DEFAULT NULL,
  `devicetoken` varchar(100) NOT NULL COMMENT '安装APP 的手机 umemg token',
  `u_point` int(10) unsigned NOT NULL DEFAULT '0',
  `u_mobile` varchar(30) NOT NULL,
  `is_false` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟用户',
  `comment_num` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '会员评论次数',
  `sup_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '所属商家',
  `wx_open_id` varchar(50) NOT NULL,
  `wx_session_key` varchar(50) NOT NULL,
  `wx_name` varchar(50) NOT NULL,
  `headimg` varchar(255) NOT NULL,
  `sign` varchar(50) NOT NULL,
  `validated` int(2) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `email` (`email`),
  KEY `parent_id` (`parent_id`),
  KEY `flag` (`flag`),
  KEY `uid_1` (`uid_1`),
  KEY `uid_2` (`uid_2`),
  KEY `uid_3` (`uid_3`),
  KEY `idx_openid` (`openid`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_users` values('1','','','wx110','3ceb4599778bcb9f75e59a2c5b67b8e5','','','0','0000-00-00','0.00','0.00','0','0','0','1496964993','1497227118','0000-00-00 00:00:00','218.90.35.137','3708','0','0',NULL,'0','0','0','','','','','','17751506118','0','0.00',NULL,NULL,'','','','',NULL,NULL,'','0','0','0','0','0','',NULL,NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('2','','','wx257','30f6134ff57cb4f472d8924abb1a1cf5','','','0','0000-00-00','0.00','0.00','0','0','0','1507926990','1508310819','0000-00-00 00:00:00','183.210.35.116','5','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oL2Bv1IGjwMvevWt3IsODz3PYTWU','http://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83ere7s8neY7rOw5BicQM3hwtFrolvT4smJET4x9F68vLosVVg16CZ5eNNGJTn8Vg8JaImv5vc01unlQ/64',NULL,NULL,'三舍','1','0','0','0','0','',NULL,NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('3','','','wx330','281225524b01ae0a44b7d2687e38a624','','','0','0000-00-00','0.01','0.00','0','0','2','1508377644','1509001592','0000-00-00 00:00:00','183.210.34.71','6183','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oWqSPuDp-4QXPYdATR0kxhYkAa2I','http://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eqaENDk5Arb6sNJz07tWxZWogYpYdapGRGbf6rNq6A4YkLndbRj3lSaFct42OQJYVV6nOsCAWbEeg/64',NULL,NULL,'三舍','1','0','0','0','0','','',NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('4','','','wx434','ca489cbc5c5c641c37fab4fca92c6812','','','0','0000-00-00','0.00','0.00','0','0','0','1508807992','1508915759','0000-00-00 00:00:00','183.210.61.220','46','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oWqSPuDcdn0YwNvago8__clNUHcE','http://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eohSzicVCPct30G2j3fGoEhFjmq7qlIF1oj7tJSw2NggHXUCnQiapAefOEMAUbPggBtiapypjcg1Ve0g/64',NULL,NULL,'一路踩着香蕉皮','0','0','3','0','0','','',NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('5','','','wx574','3b7e1e86fa3e0c4e813bb5cf7984e030','','','0','0000-00-00','0.00','0.00','0','0','0','1508915793','1508917444','0000-00-00 00:00:00','36.149.143.39','85','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oWqSPuPehDenolHha6p0AU5FEhnw','http://wx.qlogo.cn/mmhead/1OF6hlAJeVzE5Hgwn7zvqSq6qViaeHOnibW7NicEicPYkr8/64',NULL,NULL,'江南听雨 ','1','0','0','0','0','','',NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('6','','','wx635','d911f3b0adf7559f48ab9511f32669a4','','','0','0000-00-00','0.00','0.00','0','0','0','1508996117','1508996122','0000-00-00 00:00:00','183.210.34.71','4','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oWqSPuGFIP1yhFoDH_89ZTzIsaRE','http://wx.qlogo.cn/mmhead/PiajxSqBRaEKrvUMcXm5EdRERXPZiaBvpGFeh9sJg4norLFcPYIHVBqw/64',NULL,NULL,'中国加油','1','0','0','0','0','','',NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('7','','','wx74','da24909b4bac96dd44dc0d810ec90e05','','','0','0000-00-00','0.00','0.00','3','0','0','1509321860','1509508163','0000-00-00 00:00:00','183.226.112.54','192','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oWqSPuDkJExGKFXCjAOzs1q1uL2c','http://wx.qlogo.cn/mmhead/g9RQicMD01M26q5qQgEPbufwtTQ7keH28UW6HhRwoF3qBftibiayD4p4Q/64',NULL,NULL,'echo','0','0','0','0','0','1509405797','',NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('12','','0131sJuF1OM1c30CTKtF1oNUuF11sJuZ@vqibu.com','wx_013FCFFFTQKuZ','7fef6171469e80d32c0559f88b377245','','','1','0000-00-00','0.00','0.00','0','0','0','1509344988','1509344988','0000-00-00 00:00:00','183.226.112.93','1','0','0','0','0','0','0','','','','','','','0','0.00',NULL,NULL,'','','','',NULL,NULL,'','0','0','0','0','0','',NULL,NULL,'','0','','0','0','0','ogdgh0agAE9C26DAfoFFRwId1rsE','fmZu2fvwMZd71qK0j+wa6Q==','echo','https://wx.qlogo.cn/mmopen/vi_32/BlYGpyBedENayZhTLkC3AFBDtaD1XZohWS1oEDdLK6DQDcR8FbEuSpgczCe2BPkwzOxZrenlGWOia108jlKPGpA/0','1ee368e076e99a09e168784e60ccc436','0');");
E_D("replace into `hhs_users` values('13','','','wx1310','31a6b8094af341f812843cb9d146781a','','','0','0000-00-00','0.00','0.00','0','0','0','1509470815','1509470964','0000-00-00 00:00:00','218.16.129.64','74','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oWqSPuLNlgP6UfXp3cKo0t-As34A','http://wx.qlogo.cn/mmhead/qWIQ0w7U6N2TlxozaRAYcwFvSmNeXfwuClvk91iacy8E/64',NULL,NULL,'小饼干','1','0','0','0','0','','',NULL,'','0','','0','0','0','','','','','','0');");
E_D("replace into `hhs_users` values('14','','','wx1482','5ccfb00a44e22fdbb6ed6fb34d8828b7','','','0','0000-00-00','0.00','0.00','0','0','0','1509490208','1509490412','0000-00-00 00:00:00','183.246.148.140','82','0','0',NULL,'0','0','0','','','','','','','0','0.00',NULL,NULL,'','','oWqSPuP4oZs6IHOof2HJbJYZsLO4','http://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTK7OWuS6AsYFTDZN4ntYicIibDxkZILGeOPPtSOEsV7rHlMaW8lrGHVITugIPphH73Tzgzxa92orvuA/64',NULL,NULL,'那就这样吧。','0','0','0','0','0','','',NULL,'','0','','0','0','0','','','','','','0');");

require("../../inc/footer.php");
?>
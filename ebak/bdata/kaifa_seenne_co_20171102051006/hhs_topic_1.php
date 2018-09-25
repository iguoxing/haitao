<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_topic`;");
E_C("CREATE TABLE `hhs_topic` (
  `topic_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '''''',
  `intro` text NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(10) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT '''''',
  `css` text NOT NULL,
  `topic_img` varchar(255) DEFAULT NULL,
  `title_pic` varchar(255) DEFAULT NULL,
  `base_style` char(6) DEFAULT NULL,
  `htmls` mediumtext,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_topic` values('12','双十一特惠','','1508832000','1572422400','O:8:\"stdClass\":1:{s:15:\"双十一特惠\";a:2:{i:0;s:100:\"【宝娜斯正品】【100%全棉】秋冬换季全棉四件套 100%斜纹纯棉 亲肤四件套|12\";i:1;s:129:\"金丝绒卫衣秋衣女韩版秋冬大码女装中长款加绒加厚卫衣宽松学生卫衣打底金丝绒上衣秋季外套|13\";}}','','','data/afficheimg/20171025klvheq.png','','','','','');");

require("../../inc/footer.php");
?>
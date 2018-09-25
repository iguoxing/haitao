<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_goods_gallery`;");
E_C("CREATE TABLE `hhs_goods_gallery` (
  `img_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `img_url` varchar(255) NOT NULL DEFAULT '',
  `img_desc` varchar(255) NOT NULL DEFAULT '',
  `thumb_url` varchar(255) NOT NULL DEFAULT '',
  `img_original` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`img_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_goods_gallery` values('4','3','images/201710/goods_img/3_P_1508893099587.jpg','','images/201710/thumb_img/3_thumb_P_1508893099455.jpg','images/201710/source_img/3_P_1508893099528.jpg');");
E_D("replace into `hhs_goods_gallery` values('5','4','images/201710/goods_img/4_P_1508893717463.jpg','','images/201710/thumb_img/4_thumb_P_1508893717903.jpg','images/201710/source_img/4_P_1508893717657.jpg');");
E_D("replace into `hhs_goods_gallery` values('6','5','images/201710/goods_img/5_P_1508894597866.jpg','','images/201710/thumb_img/5_thumb_P_1508894597933.jpg','images/201710/source_img/5_P_1508894597536.jpg');");
E_D("replace into `hhs_goods_gallery` values('7','6','images/201710/goods_img/6_P_1508894903410.jpg','','images/201710/thumb_img/6_thumb_P_1508894903698.jpg','images/201710/source_img/6_P_1508894903496.jpg');");
E_D("replace into `hhs_goods_gallery` values('8','7','images/201710/goods_img/7_P_1508895322878.jpg','','images/201710/thumb_img/7_thumb_P_1508895322369.jpg','images/201710/source_img/7_P_1508895322070.jpg');");
E_D("replace into `hhs_goods_gallery` values('9','8','images/201710/goods_img/8_P_1508895571867.jpg','','images/201710/thumb_img/8_thumb_P_1508895571399.jpg','images/201710/source_img/8_P_1508895571774.jpg');");
E_D("replace into `hhs_goods_gallery` values('10','9','images/201710/goods_img/9_P_1508895870414.jpg','','images/201710/thumb_img/9_thumb_P_1508895870812.jpg','images/201710/source_img/9_P_1508895870371.jpg');");
E_D("replace into `hhs_goods_gallery` values('11','10','images/201710/goods_img/10_P_1508896139465.jpg','','images/201710/thumb_img/10_thumb_P_1508896139839.jpg','images/201710/source_img/10_P_1508896139577.jpg');");
E_D("replace into `hhs_goods_gallery` values('12','11','images/201710/goods_img/11_P_1508896406547.jpg','','images/201710/thumb_img/11_thumb_P_1508896406170.jpg','images/201710/source_img/11_P_1508896406162.jpg');");
E_D("replace into `hhs_goods_gallery` values('13','12','images/201710/goods_img/12_P_1508896660349.jpg','','images/201710/thumb_img/12_thumb_P_1508896660379.jpg','images/201710/source_img/12_P_1508896660631.jpg');");
E_D("replace into `hhs_goods_gallery` values('14','13','images/201710/goods_img/13_P_1508897105950.jpg','','images/201710/thumb_img/13_thumb_P_1508897105960.jpg','images/201710/source_img/13_P_1508897105416.jpg');");
E_D("replace into `hhs_goods_gallery` values('15','14','images/201710/goods_img/14_P_1508898387894.jpg','','images/201710/thumb_img/14_thumb_P_1508898387354.jpg','images/201710/source_img/14_P_1508898387114.jpg');");

require("../../inc/footer.php");
?>
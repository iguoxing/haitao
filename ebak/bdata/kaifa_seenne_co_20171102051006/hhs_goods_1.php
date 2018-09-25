<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_goods`;");
E_C("CREATE TABLE `hhs_goods` (
  `goods_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_sn` varchar(60) NOT NULL DEFAULT '',
  `goods_name` varchar(120) NOT NULL DEFAULT '',
  `goods_name_style` varchar(60) NOT NULL DEFAULT '+',
  `click_count` int(10) unsigned NOT NULL DEFAULT '0',
  `brand_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `provider_name` varchar(100) NOT NULL DEFAULT '',
  `goods_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_weight` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `shop_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `promote_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `promote_start_date` int(11) unsigned NOT NULL DEFAULT '0',
  `promote_end_date` int(11) unsigned NOT NULL DEFAULT '0',
  `warn_number` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `goods_brief` varchar(255) NOT NULL DEFAULT '',
  `goods_desc` text NOT NULL,
  `goods_thumb` varchar(255) NOT NULL DEFAULT '',
  `goods_img` varchar(255) NOT NULL DEFAULT '',
  `original_img` varchar(255) NOT NULL DEFAULT '',
  `is_real` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `extension_code` varchar(30) NOT NULL DEFAULT '',
  `is_on_sale` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_alone_sale` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_shipping` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `integral` int(10) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `sort_order` smallint(4) unsigned NOT NULL DEFAULT '100',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_best` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_new` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_promote` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `bonus_type_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `share_bonus_type` int(11) DEFAULT '0' COMMENT '分享红包类型id',
  `last_update` int(10) unsigned NOT NULL DEFAULT '0',
  `goods_type` smallint(5) unsigned NOT NULL DEFAULT '0',
  `seller_note` varchar(255) NOT NULL DEFAULT '',
  `give_integral` int(11) NOT NULL DEFAULT '-1',
  `rank_integral` int(11) NOT NULL DEFAULT '-1',
  `suppliers_id` int(5) unsigned DEFAULT '0',
  `is_check` tinyint(1) unsigned DEFAULT '0',
  `team_num` int(11) NOT NULL DEFAULT '5' COMMENT '参团人数',
  `team_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团购价',
  `little_img` varchar(100) DEFAULT NULL COMMENT '小图片',
  `sales_num` int(10) NOT NULL COMMENT '销量',
  `check_desc` varchar(255) DEFAULT NULL,
  `is_mall` tinyint(4) NOT NULL DEFAULT '0' COMMENT '微商城',
  `is_team` tinyint(4) DEFAULT '0' COMMENT '团购',
  `is_zero` tinyint(1) DEFAULT '0' COMMENT '零元购',
  `is_nearby` tinyint(4) NOT NULL DEFAULT '1' COMMENT '附近的团是否开启',
  `use_goods_sn` varchar(30) NOT NULL,
  `guige` varchar(100) NOT NULL,
  `limit_buy_bumber` int(10) NOT NULL DEFAULT '0',
  `limit_buy_one` int(10) NOT NULL DEFAULT '0',
  `city_id` int(10) NOT NULL,
  `province_id` int(10) NOT NULL,
  `district_id` int(10) NOT NULL,
  `subscribe` tinyint(4) NOT NULL DEFAULT '0' COMMENT '关注后购买',
  `shared_allow` tinyint(1) DEFAULT '0' COMMENT '是否允许分成',
  `shared_money` decimal(10,2) DEFAULT '0.00' COMMENT '团员佣金',
  `discount_type` tinyint(1) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `bonus_allowed` tinyint(1) DEFAULT '1' COMMENT '是否能使用优惠券',
  `default_shipping_id` smallint(3) DEFAULT '0' COMMENT '指定快递方式',
  `default_shipping_fee` decimal(10,2) DEFAULT NULL COMMENT '默认快递费',
  `default_step_fee` decimal(10,2) DEFAULT NULL COMMENT '每次加价',
  `spe_shipping_fee` decimal(10,2) DEFAULT NULL COMMENT '指定区域快递费',
  `spe_step_fee` decimal(10,2) DEFAULT NULL COMMENT '指定区域每次加价',
  `spe_region` varchar(250) DEFAULT NULL COMMENT '指定区域',
  `express` text COMMENT '配送列表',
  `allow_fenxiao` tinyint(1) DEFAULT '0' COMMENT '是否允许分销',
  `shipping_fee` decimal(10,2) DEFAULT '0.00' COMMENT '邮费',
  `is_tejia` tinyint(1) DEFAULT '0' COMMENT '特价',
  `is_fresh` tinyint(1) DEFAULT '0' COMMENT '新人',
  `is_miao` tinyint(1) DEFAULT '0' COMMENT '秒杀',
  `is_luck` tinyint(1) DEFAULT '0' COMMENT '夺宝',
  `luck_times` int(10) DEFAULT '1' COMMENT '当前期数',
  `max_luck_times` int(10) DEFAULT '1' COMMENT '最大期数',
  `bonus_free_all` int(10) DEFAULT '0' COMMENT '免单券',
  `rate_1` decimal(10,2) DEFAULT '0.00',
  `rate_2` decimal(10,2) DEFAULT '0.00',
  `rate_3` decimal(10,2) DEFAULT '0.00',
  `dbgz` varchar(255) DEFAULT NULL,
  `lab_qgby` varchar(100) NOT NULL,
  `lab_zpbz` varchar(100) NOT NULL,
  `lab_qtth` varchar(100) NOT NULL,
  `lab_jkbs` varchar(100) NOT NULL,
  `lab_hwzy` varchar(100) NOT NULL,
  `ts_a` varchar(100) NOT NULL,
  `ts_b` varchar(100) NOT NULL,
  `ts_c` varchar(100) NOT NULL,
  `allow_sharej` tinyint(1) NOT NULL DEFAULT '0',
  `share_j` int(10) DEFAULT NULL,
  `is_app` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'APP专享',
  PRIMARY KEY (`goods_id`),
  KEY `goods_sn` (`goods_sn`),
  KEY `cat_id` (`cat_id`),
  KEY `last_update` (`last_update`),
  KEY `brand_id` (`brand_id`),
  KEY `goods_weight` (`goods_weight`),
  KEY `promote_end_date` (`promote_end_date`),
  KEY `promote_start_date` (`promote_start_date`),
  KEY `goods_number` (`goods_number`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_goods` values('1','1','HHS000000','ceshi ','+','29','13','','9998','0.000','1.20','1.00','0.00','0','0','1','','','sdfsd&nbsp;','images/201710/thumb_img/1_thumb_G_1508807687297.jpg','images/201710/goods_img/1_G_1508807687287.jpg','images/201710/source_img/1_G_1508807687454.jpg','1','','1','1','0','0','1508807687','100','0','1','0','0','0','0','0','1508807823','0','','-1','-1','0','1','5','0.01','images/201710/1508807687217176950.png','546354',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','111','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('2','1','11','测试测试','+','2','0','','9','10.000','111.00','11.00','0.00','0','0','1','','啊啊啊','524254254','images/201710/1508891676562877999.jpg','business/uploads/6/20171025163410sytftroa.jpg','business/uploads/6/20171025163410sytftroa.jpg','1','','1','1','0','0','0','100','0','1','0','0','0','0','0','1508920476','0','','-1','-1','6','1','5','1.00','business/uploads/6/20171025163433kagbuswr.jpg','11','','0','1','0','1','','10','1','0','1','0','0','0','0','0.00','0','0.00','0','1',NULL,NULL,'0.00','0.00',NULL,'','0','0.00','0','0','0','0','1','1','0','0.00','0.00','0.00',NULL,'221','','','','','23.','','','0',NULL,'0');");
E_D("replace into `hhs_goods` values('3','1','HHS000003','【5双装】秋冬季女士保暖袜毛线袜加厚中筒袜子','+','26','0','','10000','0.000','60.00','50.00','0.00','0','0','1','','','<img src=\"/includes/kindeditor/php/../../../images/upload/image/20171025/20171025165745_69237.jpg\" alt=\"\" /><img src=\"/includes/kindeditor/php/../../../images/upload/image/20171025/20171025165745_72782.jpg\" alt=\"\" /><img src=\"/includes/kindeditor/php/../../../images/upload/image/20171025/20171025165745_53402.jpg\" alt=\"\" />','images/201710/thumb_img/3_thumb_G_1508892963940.jpg','images/201710/goods_img/3_G_1508892963607.jpg','images/201710/source_img/3_G_1508892963942.jpg','1','','1','1','0','0','1508892963','100','0','1','0','0','0','0','0','1508893099','0','','-1','-1','0','1','5','30.00','images/201710/1508892963193679689.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('4','1','HHS000004','【双面绒加绒加厚】【上衣+裤子】男童女童秋冬款保暖套装，中大童卫衣运动班服两件套','+','24','0','','10000','0.000','47.87','39.90','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025170749_33678.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025170758_48672.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025170809_43911.jpg\" alt=\"\" />','images/201710/thumb_img/4_thumb_G_1508893646099.jpg','images/201710/goods_img/4_G_1508893646282.jpg','images/201710/source_img/4_G_1508893646737.jpg','1','','1','1','0','0','1508893646','100','0','1','0','0','0','0','0','1508893717','0','','-1','-1','0','1','5','32.90','images/201710/1508893646330395857.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('5','1','HHS000005','【多买多送】【5盒一疗程】中医睡睡瘦 一疗程10斤 告别大肚腩胖身材 一盒10贴','+','22','0','','10000','0.000','22.80','19.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025172220_27649.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025172232_45800.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025172241_46590.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025172250_46627.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025172301_27928.jpg\" alt=\"\" />','images/201710/thumb_img/5_thumb_G_1508894520906.jpg','images/201710/goods_img/5_G_1508894520707.jpg','images/201710/source_img/5_G_1508894520773.jpg','1','','1','1','0','0','1508894520','100','0','1','0','0','0','0','0','1508894597','0','','-1','-1','0','1','5','6.80','images/201710/1508894520496554873.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('6','1','HHS000006','加厚坐垫椅垫办公室办公椅电脑椅坐垫员工通用带绑带可爱卡通学生教室板凳凳子椅子垫屁股垫屁垫地上地板椅子垫子','+','24','0','','10000','0.000','8.40','7.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025172728_68685.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025172739_74163.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025172751_76308.jpg\" alt=\"\" />','images/201710/thumb_img/6_thumb_G_1508894830867.jpg','images/201710/goods_img/6_G_1508894830927.jpg','images/201710/source_img/6_G_1508894830495.jpg','1','','1','1','0','0','1508894830','100','0','1','0','0','0','0','0','1508894902','0','','-1','-1','0','1','5','5.50','images/201710/1508894830664840829.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('7','1','HHS000007','【减龄+显瘦+显白】【加厚】欧洲站时尚毛呢外套女中长款2017新款韩版羊羔毛呢子大衣女秋冬款','+','24','0','','10000','0.000','94.80','79.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025173506_81454.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025173513_47790.jpg\" alt=\"\" />','images/201710/thumb_img/7_thumb_G_1508895293705.jpg','images/201710/goods_img/7_G_1508895293945.jpg','images/201710/source_img/7_G_1508895293580.jpg','1','','1','1','0','0','1508895293','100','0','1','0','0','0','0','0','1508895322','0','','-1','-1','0','1','5','69.00','images/201710/1508895293021839053.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('8','1','HHS000008','冬季新款促销毛绒棉拖鞋居家保暖厚底室内可爱包根毛毛拖鞋孕妇月子鞋学生鞋室内外毛毛拖鞋','+','22','0','','10000','0.000','34.80','29.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025173840_91248.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025173851_32584.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025173904_63866.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025173911_83642.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025173918_37995.jpg\" alt=\"\" />','images/201710/thumb_img/8_thumb_G_1508895507550.jpg','images/201710/goods_img/8_G_1508895507024.jpg','images/201710/source_img/8_G_1508895507137.jpg','1','','1','1','0','0','1508895507','100','0','1','0','0','0','0','0','1508895571','0','','-1','-1','0','1','5','15.00','images/201710/1508895507755538678.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('9','1','HHS000009','亲爽 原木抽纸3层100抽300张/包纸巾 孕婴可用原生态抑菌面巾纸','+','24','0','','10000','0.000','13.20','11.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025174347_75483.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025174353_73860.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025174359_97416.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025174404_71665.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025174412_75848.jpg\" alt=\"\" />','images/201710/thumb_img/9_thumb_G_1508895814795.jpg','images/201710/goods_img/9_G_1508895814864.jpg','images/201710/source_img/9_G_1508895814387.jpg','1','','1','1','0','0','1508895814','100','0','1','0','0','0','0','0','1508895870','0','','-1','-1','0','1','5','10.00','images/201710/1508895814349030097.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('10','1','HHS000010','睡衣女秋冬季女士加厚 睡衣女长袖法兰绒睡衣开衫套装','+','26','0','','10000','0.000','54.00','45.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025174842_93943.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025174847_54165.jpg\" alt=\"\" />','images/201710/thumb_img/10_thumb_G_1508896109409.jpg','images/201710/goods_img/10_G_1508896109923.jpg','images/201710/source_img/10_G_1508896109768.jpg','1','','1','1','0','0','1508896109','100','0','1','0','0','0','0','0','1508896139','0','','-1','-1','0','1','5','29.00','images/201710/1508896109357560413.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('11','1','HHS000011','【2017年新货】新炒坚果 巴旦木 碧根果 开心果 夏威夷果 瓜子 坚果组合 240g/360g/480g','+','30','0','','10000','0.000','13.20','11.00','0.00','0','0','1','','商品简单描述','<img src=\"/images/upload/image/20171025/20171025175308_28581.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025175315_69143.jpg\" alt=\"\" />','images/201710/thumb_img/11_thumb_G_1508896375138.jpg','images/201710/goods_img/11_G_1508896375709.jpg','images/201710/source_img/11_G_1508896375071.jpg','1','','1','1','0','0','1508896375','100','0','1','0','0','0','0','0','1509503707','0','','-1','-1','0','1','5','10.00','images/201710/1508896375902781204.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('12','7','HHS000012','【宝娜斯正品】【100%全棉】秋冬换季全棉四件套 100%斜纹纯棉 亲肤四件套','+','27','0','','10000','0.000','2400.00','2000.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025175705_13924.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025175711_99160.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025175717_46028.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025175722_62596.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025175729_72276.jpg\" alt=\"\" />','images/201710/thumb_img/12_thumb_G_1508896614724.jpg','images/201710/goods_img/12_G_1508896614001.jpg','images/201710/source_img/12_G_1508896614189.jpg','1','','1','1','0','0','1508896614','100','0','1','0','0','0','0','0','1508910689','0','','-1','-1','0','1','5','1.00','images/201710/1508896614684863428.jpg','0',NULL,'1','0','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('13','6','HHS000013','金丝绒卫衣秋衣女韩版秋冬大码女装中长款加绒加厚卫衣宽松学生卫衣打底金丝绒上衣秋季外套','+','34','0','','10000','0.000','84.00','70.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025180441_13172.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025180452_11604.jpg\" alt=\"\" />','images/201710/thumb_img/13_thumb_G_1508897064126.jpg','images/201710/goods_img/13_G_1508897064716.jpg','images/201710/source_img/13_G_1508897064951.jpg','1','','1','1','0','0','1508897064','100','0','1','0','0','0','0','0','1508910736','0','','-1','-1','0','1','5','37.00','images/201710/1508897064819066663.jpg','0',NULL,'1','0','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");
E_D("replace into `hhs_goods` values('14','10','HHS000014','学生办公中性笔0.5mm子弹头笔芯办公用品黑红蓝学生水性写字商务签字笔','+','33','0','','10000','0.000','12.00','10.00','0.00','0','0','1','','','<img src=\"/images/upload/image/20171025/20171025182509_69086.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025182516_66468.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025182531_65674.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025182541_71373.jpg\" alt=\"\" /><img src=\"/images/upload/image/20171025/20171025182553_11616.jpg\" alt=\"\" />','images/201710/thumb_img/14_thumb_G_1508898294135.jpg','images/201710/goods_img/14_G_1508898294632.jpg','images/201710/source_img/14_G_1508898294688.jpg','1','','1','1','0','0','1508898294','100','0','1','0','0','0','0','0','1508909639','0','','-1','-1','0','1','5','5.00','images/201710/1508898294452583380.jpg','0',NULL,'0','1','0','0','','','0','0','1','0','0','0','0','0.00','0','0.00','0','1','0.00','0.00','0.00','0.00','','[]','0','0.00','0','0','0','0','1','0','0','0.00','0.00','0.00','','','','','','','','','','0','0','0');");

require("../../inc/footer.php");
?>
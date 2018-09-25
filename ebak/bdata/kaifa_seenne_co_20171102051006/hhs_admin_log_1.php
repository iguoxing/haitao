<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_admin_log`;");
E_C("CREATE TABLE `hhs_admin_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_time` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `log_info` varchar(255) NOT NULL DEFAULT '',
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`log_id`),
  KEY `log_time` (`log_time`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=155 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_admin_log` values('1','1495045716','1','删除文章: 近期天气','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('2','1495045718','1','删除文章: 520','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('3','1495045722','1','删除文章: 测试测试','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('4','1495045725','1','删除文章: 六一快乐','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('5','1495045727','1','删除文章: 123','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('6','1495045731','1','删除文章: test111111','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('7','1495045736','1','删除文章: 广场页面美化中','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('8','1495045741','1','删除文章: 如何兑换商品','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('9','1495045746','1','删除文章: 测试','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('10','1495045856','1','删除商品分类: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('11','1495045859','1','删除商品分类: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('12','1495045861','1','删除商品分类: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('13','1495045878','1','编辑支付方式: 支付宝','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('14','1495045898','1','编辑支付方式: 微信支付','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('15','1495046012','1','编辑商店设置: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('16','1495046677','1','编辑商店设置: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('17','1496963990','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('18','1496964062','1','编辑权限管理: admin','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('19','1496964559','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('20','1496964937','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('21','1496966138','1','编辑广告: PC1','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('22','1496966151','1','编辑广告: PC2','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('23','1496966167','1','编辑广告: PC3','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('24','1496966183','1','编辑广告: PC4','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('25','1496966192','1','编辑广告: PC2','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('26','1496966807','1','编辑文章分类: 首页','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('27','1496966815','1','编辑文章分类: 拼团','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('28','1496966984','1','删除文章: 微营销APP即将上线，现订购APP端7折优惠','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('29','1496966992','1','删除文章: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('30','1496967327','1','删除文章: APP推送测试','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('31','1496967333','1','删除文章: 跨年钜惠，好礼送不停','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('32','1496967476','1','删除文章: 收银系统即将上线','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('33','1496967505','1','编辑文章: 关于我们','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('34','1496967525','1','编辑文章: 广场功能调整中','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('35','1496967554','1','编辑文章: 积分兑换流程','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('36','1496967797','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('37','1496967827','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('38','1496968062','1','编辑支付方式: 微信支付','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('39','1496968320','1','添加供货商管理: 1e21e21e21e','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('40','1497220655','1','编辑广告: 秒杀','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('41','1497220668','1','编辑广告: 抽奖','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('42','1497220675','1','编辑广告: 众筹','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('43','1497220681','1','编辑广告: hanfneg','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('44','1497227163','1','编辑权限管理: admin','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('45','1507926967','1','编辑文章分类: 首页','49.66.39.31');");
E_D("replace into `hhs_admin_log` values('46','1507926973','1','编辑文章分类: 拼团','49.66.39.31');");
E_D("replace into `hhs_admin_log` values('47','1508310409','1','添加广告位置: 首页开启广告','183.210.35.116');");
E_D("replace into `hhs_admin_log` values('48','1508310472','1','添加广告: 首页开启广告','183.210.35.116');");
E_D("replace into `hhs_admin_log` values('49','1508310751','1','删除供货商管理: 好了|萝莉控|呵呵呵|黑墨水|好了|萝莉控|呵呵呵|黑墨水|','183.210.35.116');");
E_D("replace into `hhs_admin_log` values('50','1508365723','1','编辑商店设置: ','49.66.33.149');");
E_D("replace into `hhs_admin_log` values('51','1508375729','1','编辑商店设置: ','49.66.33.149');");
E_D("replace into `hhs_admin_log` values('52','1508376208','1','编辑支付方式: 微信支付','49.66.33.149');");
E_D("replace into `hhs_admin_log` values('53','1508457356','1','编辑商店设置: ','49.66.39.124');");
E_D("replace into `hhs_admin_log` values('54','1508652139','1','编辑商店设置: ','183.210.34.196');");
E_D("replace into `hhs_admin_log` values('55','1508652224','1','编辑商店设置: ','183.210.34.196');");
E_D("replace into `hhs_admin_log` values('56','1508652236','1','编辑商店设置: ','183.210.34.196');");
E_D("replace into `hhs_admin_log` values('57','1508652449','1','编辑供货商管理: 125','183.210.34.196');");
E_D("replace into `hhs_admin_log` values('58','1508807277','1','编辑商店设置: ','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('59','1508807284','1','编辑商店设置: ','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('60','1508807355','1','添加商品分类: ceshi ','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('61','1508807687','1','添加商品: ceshi ','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('62','1508807749','1','安装配送方式: 市内快递','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('63','1508807759','1','安装配送方式: 汇通快递','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('64','1508807776','1','添加配送区域: 汇通快递','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('65','1508807823','1','编辑商品: ceshi ','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('66','1508807880','1','编辑支付方式: 微信支付','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('67','1508807921','1','编辑广告: 首页开启广告','49.66.37.16');");
E_D("replace into `hhs_admin_log` values('68','1508891582','1','编辑供货商管理: 125','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('69','1508892963','1','添加商品: 【5双装】秋冬季女士保暖袜毛线袜加厚中筒袜子','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('70','1508892994','1','编辑商品: 【5双装】秋冬季女士保暖袜毛线袜加厚中筒袜子','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('71','1508893070','1','编辑商品: 【5双装】秋冬季女士保暖袜毛线袜加厚中筒袜子','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('72','1508893099','1','编辑商品: 【5双装】秋冬季女士保暖袜毛线袜加厚中筒袜子','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('73','1508893646','1','添加商品: 【双面绒加绒加厚】【上衣+裤子】男童女童秋冬款保暖套装，中大童卫衣运动班服两件套','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('74','1508893693','1','编辑商品: 【双面绒加绒加厚】【上衣+裤子】男童女童秋冬款保暖套装，中大童卫衣运动班服两件套','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('75','1508893717','1','编辑商品: 【双面绒加绒加厚】【上衣+裤子】男童女童秋冬款保暖套装，中大童卫衣运动班服两件套','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('76','1508894520','1','添加商品: 【多买多送】【5盒一疗程】中医睡睡瘦 一疗程10斤 告别大肚腩胖身材 一盒10贴','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('77','1508894597','1','编辑商品: 【多买多送】【5盒一疗程】中医睡睡瘦 一疗程10斤 告别大肚腩胖身材 一盒10贴','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('78','1508894830','1','添加商品: 加厚坐垫椅垫办公室办公椅电脑椅坐垫员工通用带绑带可爱卡通学生教室板凳凳子椅子垫屁股垫屁垫地上地板椅子垫子','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('79','1508894902','1','编辑商品: 加厚坐垫椅垫办公室办公椅电脑椅坐垫员工通用带绑带可爱卡通学生教室板凳凳子椅子垫屁股垫屁垫地上地板椅子垫子','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('80','1508895293','1','添加商品: 【减龄+显瘦+显白】【加厚】欧洲站时尚毛呢外套女中长款2017新款韩版羊羔毛呢子大衣女秋冬款','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('81','1508895322','1','编辑商品: 【减龄+显瘦+显白】【加厚】欧洲站时尚毛呢外套女中长款2017新款韩版羊羔毛呢子大衣女秋冬款','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('82','1508895507','1','添加商品: 冬季新款促销毛绒棉拖鞋居家保暖厚底室内可爱包根毛毛拖鞋孕妇月子鞋学生鞋室内外毛毛拖鞋','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('83','1508895571','1','编辑商品: 冬季新款促销毛绒棉拖鞋居家保暖厚底室内可爱包根毛毛拖鞋孕妇月子鞋学生鞋室内外毛毛拖鞋','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('84','1508895814','1','添加商品: 亲爽 原木抽纸3层100抽300张/包纸巾 孕婴可用原生态抑菌面巾纸','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('85','1508895870','1','编辑商品: 亲爽 原木抽纸3层100抽300张/包纸巾 孕婴可用原生态抑菌面巾纸','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('86','1508896109','1','添加商品: 睡衣女秋冬季女士加厚 睡衣女长袖法兰绒睡衣开衫套装','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('87','1508896139','1','编辑商品: 睡衣女秋冬季女士加厚 睡衣女长袖法兰绒睡衣开衫套装','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('88','1508896375','1','添加商品: 【2017年新货】新炒坚果 巴旦木 碧根果 开心果 夏威夷果 瓜子 坚果组合 240g/360g/480g','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('89','1508896406','1','编辑商品: 【2017年新货】新炒坚果 巴旦木 碧根果 开心果 夏威夷果 瓜子 坚果组合 240g/360g/480g','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('90','1508896614','1','添加商品: 【宝娜斯正品】【100%全棉】秋冬换季全棉四件套 100%斜纹纯棉 亲肤四件套','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('91','1508896660','1','编辑商品: 【宝娜斯正品】【100%全棉】秋冬换季全棉四件套 100%斜纹纯棉 亲肤四件套','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('92','1508897064','1','添加商品: 金丝绒卫衣秋衣女韩版秋冬大码女装中长款加绒加厚卫衣宽松学生卫衣打底金丝绒上衣秋季外套','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('93','1508897105','1','编辑商品: 金丝绒卫衣秋衣女韩版秋冬大码女装中长款加绒加厚卫衣宽松学生卫衣打底金丝绒上衣秋季外套','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('94','1508898294','1','添加商品: 学生办公中性笔0.5mm子弹头笔芯办公用品黑红蓝学生水性写字商务签字笔','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('95','1508898387','1','编辑商品: 学生办公中性笔0.5mm子弹头笔芯办公用品黑红蓝学生水性写字商务签字笔','49.66.37.186');");
E_D("replace into `hhs_admin_log` values('96','1508909221','1','编辑商品分类: 水果','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('97','1508909232','1','添加商品分类: 零时','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('98','1508909237','1','添加商品分类: 手机','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('99','1508909263','1','添加商品分类: 海淘馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('100','1508909273','1','添加商品分类: 玩具馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('101','1508909285','1','添加商品分类: 服装馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('102','1508909292','1','添加商品分类: 母婴管','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('103','1508909322','1','添加商品分类: 美妆区','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('104','1508909335','1','添加商品分类: 鲜果区','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('105','1508909359','1','添加商品分类: 百货馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('106','1508909392','1','编辑商品分类: 零食馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('107','1508909431','1','编辑商品分类: 母婴管','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('108','1508909460','1','编辑商品分类: 服装馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('109','1508909467','1','编辑商品分类: 美妆区','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('110','1508909490','1','编辑商品分类: 玩具馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('111','1508909496','1','编辑商品分类: 海淘馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('112','1508909507','1','编辑商品分类: 母婴管','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('113','1508909517','1','编辑商品分类: 水果','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('114','1508909531','1','编辑商品分类: 母婴管','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('115','1508909559','1','编辑商品分类: 手机','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('116','1508909574','1','编辑商品分类: 零食馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('117','1508909587','1','编辑商品分类: 鲜果区','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('118','1508909600','1','编辑商品分类: 百货馆','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('119','1508909639','1','编辑商品: 学生办公中性笔0.5mm子弹头笔芯办公用品黑红蓝学生水性写字商务签字笔','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('120','1508909650','1','编辑商品: 金丝绒卫衣秋衣女韩版秋冬大码女装中长款加绒加厚卫衣宽松学生卫衣打底金丝绒上衣秋季外套','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('121','1508909662','1','编辑商品: 【宝娜斯正品】【100%全棉】秋冬换季全棉四件套 100%斜纹纯棉 亲肤四件套','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('122','1508910159','1','编辑广告: 首页开启广告','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('123','1508910689','1','编辑商品: 【宝娜斯正品】【100%全棉】秋冬换季全棉四件套 100%斜纹纯棉 亲肤四件套','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('124','1508910736','1','编辑商品: 金丝绒卫衣秋衣女韩版秋冬大码女装中长款加绒加厚卫衣宽松学生卫衣打底金丝绒上衣秋季外套','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('125','1508910786','1','编辑广告: 首页开启广告','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('126','1508910879','1','编辑文章分类: 系统演示','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('127','1508910894','1','添加文章分类: 商城首页','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('128','1508910915','1','添加文章分类: 会员中心','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('129','1508910978','1','添加文章分类: 拼团广场','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('130','1508911040','1','添加文章分类: 双十一专题','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('131','1508911233','1','添加: 抽奖测试','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('132','1508911324','1','添加文章分类: 抽奖活动','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('133','1508913227','1','编辑文章分类: 购买系统','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('134','1508913479','1','添加文章分类: 购买源码','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('135','1508913551','1','添加文章分类: 服务器版','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('136','1508915687','1','添加供货商管理: 57454','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('137','1508958773','1','编辑商店设置: ','49.66.33.144');");
E_D("replace into `hhs_admin_log` values('138','1508976356','1','编辑商店设置: ','49.66.33.144');");
E_D("replace into `hhs_admin_log` values('139','1508992764','1','编辑商店设置: ','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('140','1508996997','1','编辑广告: 首页开启广告','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('141','1508997040','1','编辑广告: 首页开启广告','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('142','1508997276','1','编辑广告位置: 首页开启广告','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('143','1508997296','1','编辑广告: 首页开启广告','183.210.34.71');");
E_D("replace into `hhs_admin_log` values('144','1509320241','1','删除广告: ','49.66.32.225');");
E_D("replace into `hhs_admin_log` values('145','1509322164','1','编辑商店设置: ','49.66.32.225');");
E_D("replace into `hhs_admin_log` values('146','1509344969','1','删除会员账号: wx_013LQQYTDUNWG','183.226.112.93');");
E_D("replace into `hhs_admin_log` values('147','1509344972','1','删除会员账号: wx_013JYBGQVHAli','183.226.112.93');");
E_D("replace into `hhs_admin_log` values('148','1509344976','1','删除会员账号: wx_003XPETTZRM3n','183.226.112.93');");
E_D("replace into `hhs_admin_log` values('149','1509344980','1','删除会员账号: wx_003EYAWRVLJ6E','183.226.112.93');");
E_D("replace into `hhs_admin_log` values('150','1509412110','1','编辑会员账号: wx_013FCFFFTQKuZ','183.226.112.54');");
E_D("replace into `hhs_admin_log` values('151','1509412638','1','添加文章: 头条快报','183.226.112.54');");
E_D("replace into `hhs_admin_log` values('152','1509418241','1','添加文章: 头条快报2','183.226.112.54');");
E_D("replace into `hhs_admin_log` values('153','1509503707','1','编辑商品: 【2017年新货】新炒坚果 巴旦木 碧根果 开心果 夏威夷果 瓜子 坚果组合 240g/360g/480g','183.226.112.54');");
E_D("replace into `hhs_admin_log` values('154','1509540341','1','添加商品分类: 苹果','183.226.112.10');");

require("../../inc/footer.php");
?>
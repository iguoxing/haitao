<?php 
/*
微起步商城首页
*/

define('IN_HHS', true);

require ('../includes/init.php');
require('functions.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

//接受传过来的参数
$act = empty($_REQUEST['act']) ? null : test_input($_REQUEST['act']);
$GLOBALS['ecs'] = $hhs;

/*路由*/
if ($act != null) {

	$function_name = 'action_' . $act;
	if(! function_exists($function_name))
	{
		$function_name = "action_default";
	}
}
else
{
	$function_name = "action_default";
}

/*读取小程序配置*/
$sql = "SELECT * FROM `xcx_config` WHERE id=1";
$xcx_config = $GLOBALS['db']->getRow($sql);

if (!$xcx_config) {
	$res = array('err' => '888','msg'=>'小程序初始化失败！');

	echo json_encode($res);

	exit();
}
else
{
	$GLOBALS['xcx_config'] = $xcx_config;
}


call_user_func($function_name);
/*
act无效时的默认函数
*/
function action_default()
{
	//初始化返回值  error = 1初始值,说明还无任何函数被请求
	$res = array('err' => '999','msg'=>'默认函数');

	echo json_encode($res);

	exit();
}

function action_index(){

	check_xcx_team_refund();//检查是否有团需要退款

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);


	// 取得拼团精品商品
	$sql = "SELECT g.goods_id,g.goods_name,g.shop_price,g.market_price,g.goods_thumb,g.team_num,g.little_img,g.team_price FROM ".$GLOBALS['ecs']->table('goods')." AS g WHERE is_team=1 AND is_luck=0 AND is_miao=0 AND is_best=1 AND is_delete=0 AND is_on_sale=1 ORDER BY goods_id DESC LIMIT 0,20";
	$best_list = $GLOBALS['db']->getAll($sql);

	foreach ($best_list as $key => $value) {


		if (!$best_list[$key]['goods_thumb']) {
			$best_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$best_list[$key]['goods_thumb'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$key]['goods_thumb']; 
		}

		if (!$best_list[$key]['little_img']) {
			$best_list[$key]['little_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$best_list[$key]['little_img'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$key]['little_img']; 
		}

	}

    //正在进行的拼团
    $sql="SELECT o.order_id,o.team_sign FROM ".$GLOBALS['ecs']->table('order_info')." AS o WHERE o.extension_code='team_goods' AND o.team_status=1 Group BY o.team_sign ASC LIMIT 0,20";
//    $sql="SELECT o.order_id,o.team_sign FROM ".$GLOBALS['ecs']->table('order_info')." AS o WHERE o.extension_code='team_goods' AND o.team_status=1 ORDER BY o.order_id ASC LIMIT 0,20";
    $buying_list = $GLOBALS['db']->getAll($sql);

    $systime = gmtime();//当前时间
    $team_suc_time = $GLOBALS['_CFG']['team_suc_time'] * 86400;//团购限时-总秒数

    foreach($buying_list as $key =>$value){
        $order_id_tmp = $buying_list[$key]['order_id'];
        $temp_team_sign = $buying_list[$key]['team_sign'];

        //查询商品名称，商品图片，数量，价格
        $sql = "SELECT g.goods_id,g.goods_name,g.goods_thumb,g.little_img,g.team_num,g.team_price FROM ".$GLOBALS['ecs']->table('order_goods')." AS o,".$GLOBALS['ecs']->table('goods')." AS g WHERE o.order_id='$order_id_tmp' AND o.goods_id=g.goods_id";
        $buying_list[$key]['goods_info'] = $GLOBALS['db']->getAll($sql);

        if(!$buying_list[$key]['goods_info'][0]['goods_thumb']){
            $buying_list[$key]['goods_info'][0]['goods_thumb']='http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
        }else{
            $buying_list[$key]['goods_info'][0]['goods_thumb']='http://'.$GLOBALS['xcx_config']['url'].'/'.$buying_list[$key]['goods_info'][0]['goods_thumb'];
        }

        if (!$buying_list[$key]['goods_info'][0]['little_img']) {
            $buying_list[$key]['goods_info'][0]['little_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
        }else {
            $buying_list[$key]['goods_info'][0]['little_img'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$buying_list[$key]['goods_info'][0]['little_img'];
        }

        //参团的人
        $sql="select u.user_name,u.uname,u.uname,u.headimgurl,u.headimg,o.pay_time,o.team_first,o.is_lucker from ".$GLOBALS['ecs']->table('order_info')." as o left join ".$GLOBALS['ecs']->table('users')." as u on o.user_id=u.user_id where team_sign=".$temp_team_sign." AND team_status=1 order by order_id ";
        $temp_team_mem=$GLOBALS['db']->getAll($sql);
        $buying_list[$key]['team_mem']=$temp_team_mem;

        //参团倒计时
        if ($temp_team_mem) {
            foreach($temp_team_mem as $k=>$v)
            {
                $temp_team_mem[$k]['date']=local_date('Y-m-d H:i:s',$v['pay_time']);
            }

            $buying_list[$key]['team_start'] = $temp_team_mem[0]['pay_time'];//开团时间

            // 限时剩余时间总秒数
            if($temp_team_mem[0]['pay_time']){
                $buying_list[$key]['s_miao'] = ($team_suc_time+$buying_list[$key]['team_start'])-$systime;
            }else{
                $buying_list[$key]['s_miao'] = 88888;
            }

        }

        //缺几人
        $sql=" SELECT count(*) FROM ".$GLOBALS['ecs']->table('order_info')." where team_sign=".$temp_team_sign." and team_status>0" ;
        $count=$GLOBALS['db']->getOne($sql);

        if($buying_list){
            if($buying_list[$key]['goods_info']){
                $buying_list[$key]['differ_num'] = $buying_list[$key]['goods_info'][0]['team_num']-$count;
            }else{
                $buying_list[$key]['differ_num']=0;
            }
        }

    }


	// 取得幻灯片

	$sql = "SELECT ad_link as url,ad_code as src FROM ".$GLOBALS['ecs']->table('ad')." WHERE enabled=1 AND position_id=1";

	$flash_xml = $GLOBALS['db']->getAll($sql);

	foreach ($flash_xml as $key => $value) {
		
		$flash_xml[$key]['id'] = getQuerystr($flash_xml[$key]['url'],'id');

		// 分辨地址类型
		if (strpos($flash_xml[$key]['url'],"goods") === false) {
			$flash_xml[$key]['url_type'] = 'category';
		}
		else
		{
			$flash_xml[$key]['url_type'] = 'goods';
		}

		// 处理图片地址
		$flash_xml[$key]['src'] = 'http://'.$GLOBALS['xcx_config']['url'].'/data/afficheimg/'.$flash_xml[$key]['src'];

	}

	// 取得精品非拼团商品
	$sql = "SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb FROM ".$GLOBALS['ecs']->table('goods')." AS g WHERE is_mall=1 AND is_best=1 AND is_delete=0 AND is_on_sale=1 AND is_miao=0 ORDER BY goods_id DESC LIMIT 0,20";
	$mall_goods_list = $GLOBALS['db']->getAll($sql);

	foreach ($mall_goods_list as $key => $value) {


		if (!$mall_goods_list[$key]['goods_thumb']) {
			$mall_goods_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$mall_goods_list[$key]['goods_thumb'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$mall_goods_list[$key]['goods_thumb']; 
		}

	}

	// 取得一级分类列表

	$sql = "SELECT cat_id,cat_name,sort_order FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show !=0 AND parent_id=0 ORDER BY sort_order ASC";

	$cat_list = $GLOBALS['db']->getAll($sql);

	// 取得新闻头条

	$sql = "SELECT a.title,a.article_id,c.cat_id FROM ".$GLOBALS['ecs']->table('article_cat')." AS c,".$GLOBALS['ecs']->table('article')." AS a WHERE c.cat_id=38 AND a.cat_id=c.cat_id AND a.is_open=1 ORDER BY a.article_id DESC";//a.title,a.article_id,c.cat_id

	$news = $GLOBALS['db']->getAll($sql);

	
	$res = array('err' => '0','best_list'=>$best_list,'flash_xml'=>$flash_xml,'mall_goods_list'=>$mall_goods_list,'cat_list'=>$cat_list,'news'=>$news,'miao_goods'=>get_promote_goods(),'buying_list'=>$buying_list);

	exit(json_encode($res));
	
}

function action_check_xcx_team_refund()
{
	check_xcx_team_refund();
}

function action_spike()
{
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 and g.is_miao = 1 and g.is_mall = 1 ";
	
	/*//获得区域级别
	$current_region_type=get_region_type($_SESSION['cid']); 
	if($current_region_type<=2){
	     $where.=" and (g.city_id='".$_SESSION['cid'] . "' or g.city_id=1) ";
	}elseif($current_region_type==3){
	    $where.=" and (g.district_id='".$_SESSION['cid'] . "' or g.city_id=1) ";
	}*/

    $pageSize = 100;
    //统计页面总数
    $sql    = "select count(*) FROM ".$GLOBALS['hhs']->table('goods')." as g WHERE " . $where;
    $allNum = $GLOBALS['db']->getOne($sql);//总记录数
    $pages  = ceil($allNum/$pageSize);//总页数
    //page 溢出
    $page   = $page<=$pages ? $page : $pages;
    $skip     = ($page - 1) * $pageSize;
	if($skip<0)
	{
		$skip=0;
	}
    $limit = " limit " . $skip . "," . $pageSize;
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.goods_name_style, g.market_price, g.shop_price AS shop_price, ' .
                " g.promote_price, g.goods_type, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img,g.little_img ' .
            ' ,g.team_num,g.team_price,g.promote_price '.
            'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' .
            "WHERE $where ORDER BY g.sort_order, g.goods_id DESC" . $limit;
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
	$gtime = gmtime();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['goods_id']         = $row['goods_id'];
        $arr[$idx]['goods_name']         = $row['goods_name'];
        $arr[$idx]['goods_brief']        = $row['goods_brief'];
        $arr[$idx]['goods_number']       = $row['goods_number'];
        
        $arr[$idx]['market_price']       = price_format($row['market_price'],false);
        $arr[$idx]['shop_price']         = price_format($row['shop_price'],false);
        
        $arr[$idx]['goods_img']          = get_image_path($row['goods_id'], $row['goods_img']);

        if ($arr[$idx]['goods_img']) {
        	$arr[$idx]['goods_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$arr[$idx]['goods_img'];
        }
        else
        {
        	$arr[$idx]['goods_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
        }

        $arr[$idx]['url']                = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        $arr[$idx]['team_num']           = $row['team_num'];
        $arr[$idx]['team_price']         = price_format($row['promote_price'],false);
		
		$arr[$idx]['promote_price']         = price_format($row['promote_price'],false);
        
        $arr[$idx]['team_discount']      = number_format($row['promote_price']/$row['market_price']*10,1);

        $arr[$idx]['promote_start_date1']        = ($row['promote_start_date']);
        $arr[$idx]['promote_end_date1']        = ($row['promote_end_date']);


        $arr[$idx]['start_date'] = local_date("Y-m-d H:i:s",$row['promote_start_date']);
        $arr[$idx]['end_date']   = local_date("Y-m-d H:i:s",$row['promote_end_date']);

        $arr[$idx]['promote_start_date']        = strtotime($arr[$idx]['start_date']);
        $arr[$idx]['promote_end_date']        = strtotime($arr[$idx]['end_date']);
		
		if($row['promote_start_date']>$gtime)
		{
			$arr[$idx]['sort_order'] =2;//即将开始
		}
		elseif($row['promote_start_date']<$gtime && $gtime<$row['promote_end_date'])
		{
			$arr[$idx]['sort_order'] =1;//已开始未结束
		}
		else
		{
			$arr[$idx]['sort_order'] =3;//已结束
		}

		if ($gtime >= $row['promote_start_date'] && $gtime <= $row['promote_end_date'])
        {
             $arr[$idx]['gmt_end_time_xcx']  = $row['promote_end_date'] - $gtime;
        }
        else
        {
            $arr[$idx]['gmt_end_time_xcx']  = 0;
        }

    }
	$arr =   array_sort($arr,'sort_order','asc');
    //下一页是否存在
    $nextPage  = $page < $pages ? (++$page) : 0;
    
    $res = array('err' => '0','nextPage'=>$nextPage,'goodslist'=>$arr);
	exit(json_encode($res));
}

/*
店铺街
*/
function action_stores()
{
	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
	$hangye_id = isset($_REQUEST['hangye_id']) ? test_input($_REQUEST['hangye_id']) : 0;//行业

	$count = 20;// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}

	$where = " where is_check=1  ";

	if($hangye_id>0){
		$children = getHangye_children($hangye_id);
		$str="".$hangye_id;
		if(!empty($children)){
			$str.= ",".implode(',',$children);
			
		}
		$where .=" and hangye_id in (".$str .")" ;
	}

	$sql = "select suppliers_id,suppliers_name,supp_logo,suppliers_desc,hangye_id,province_id,city_id,address from ".$GLOBALS['hhs']->table('suppliers').$where." order by sort_order LIMIT $min,$count";
	$res = $GLOBALS['db']->getAll($sql);

	foreach ($res AS $k=>$row)
	{
		
	    $sql = "SELECT count(*) FROM ".$GLOBALS['hhs']->table('goods')." WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $row['suppliers_id'];
	    $res[$k]['goods_num'] = $GLOBALS['db']->getOne($sql);
		
		
	    $sql = "SELECT sum(`sales_num`) FROM ".$GLOBALS['hhs']->table('goods')." WHERE `suppliers_id` = " .$row['suppliers_id'];
	    $res[$k]['sales_num'] = $GLOBALS['db']->getOne($sql);
		
	    $sql = "SELECT count(*) FROM  ".$GLOBALS['hhs']->table('order_goods')." as o,".$GLOBALS['hhs']->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$row['suppliers_id'];
	    $res[$k]['sales_num'] += $GLOBALS['db']->getOne($sql);

	    if ($res[$k]['supp_logo']) {
	    	$res[$k]['supp_logo'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$res[$k]['supp_logo']; 
	    }
	    
	
	}


	if ($page == 1) {
		$Hangye = $GLOBALS['db']->getAll("select a.*,(SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('suppliers')." AS b WHERE b.hangye_id=a.id) AS num from ".$GLOBALS['hhs']->table('hangye')." as a where a.pid='0'");
	}
	else
	{
		$Hangye = array();
	}

	$load_more = count($res) < $count ? 0:1;

	$res = array('err' => '0','Hangye'=>$Hangye,'page'=>$page,'res'=>$res,'load_more'=>$load_more);
	exit(json_encode($res));
}

function action_store_info()
{
	$suppliers_id = isset($_REQUEST['id']) ? test_input($_REQUEST['id']) : 1;//页码

	$suppliers = getSuppliers($suppliers_id);

	if ($suppliers['supp_logo']) {
    	$suppliers['supp_logo'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$suppliers['supp_logo']; 
    }


	$sql = "SELECT count(*) FROM ".$GLOBALS['hhs']->table('goods')." WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $suppliers_id;
    $suppliers['goods_num'] = $GLOBALS['db']->getOne($sql);
		
		
    $sql = "SELECT sum(`sales_num`) FROM ".$GLOBALS['hhs']->table('goods')." WHERE `suppliers_id` = " .$suppliers_id;
    $suppliers['sales_num'] = $GLOBALS['db']->getOne($sql);
	
    $sql = "SELECT count(*) FROM  ".$GLOBALS['hhs']->table('order_goods')." as o,".$GLOBALS['hhs']->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$suppliers_id;
    $suppliers['sales_num'] += $GLOBALS['db']->getOne($sql);

	// 获取此店铺的拼团商品
	$sql = "SELECT g.goods_id,g.goods_name,g.shop_price,g.market_price,g.goods_thumb,g.team_num,g.little_img,g.team_price FROM ".$GLOBALS['ecs']->table('goods')." AS g WHERE is_team=1 AND is_luck=0 AND is_miao=0 AND is_delete=0 AND is_on_sale=1 AND suppliers_id='$suppliers_id' ORDER BY goods_id DESC";

	$best_list = $GLOBALS['db']->getAll($sql);

	foreach ($best_list as $key => $value) {


		if (!$best_list[$key]['goods_thumb']) {
			$best_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$best_list[$key]['goods_thumb'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$key]['goods_thumb']; 
		}

		if (!$best_list[$key]['little_img']) {
			$best_list[$key]['little_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$best_list[$key]['little_img'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$key]['little_img']; 
		}

	}


	// 获取单品

	$sql = "SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb FROM ".$GLOBALS['ecs']->table('goods')." AS g WHERE is_mall=1 AND is_delete=0 AND is_on_sale=1 AND suppliers_id='$suppliers_id' ORDER BY goods_id DESC";

	$mall_goods_list = $GLOBALS['db']->getAll($sql);

	foreach ($mall_goods_list as $key => $value) {


		if (!$mall_goods_list[$key]['goods_thumb']) {
			$mall_goods_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$mall_goods_list[$key]['goods_thumb'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$mall_goods_list[$key]['goods_thumb']; 
		}

	}

	$res = array('err'=>0,'suppliers'=>$suppliers,'best_list'=>$best_list,'mall_goods_list'=>$mall_goods_list);
	exit(json_encode($res));
}

function getSuppliers($suppliers_id){
    $sql = 'SELECT `suppliers_name` ,`user_id`,`supp_logo` '.
            'FROM ' . $GLOBALS['hhs']->table('suppliers') . 
            "WHERE `suppliers_id` = " . $suppliers_id;
    return $GLOBALS['db']->getRow($sql);
}

function getHangye_children($pid=0)
{
	return $GLOBALS['db']->getCol("select id from ".$GLOBALS['hhs']->table('hangye')." as a where a.pid= ".$pid);
}

/*解析经纬度*/
function action_get_location_textname()
{
	$latitude = empty($_REQUEST['latitude']) ? 0 : test_input($_REQUEST['latitude']);
	$longitude = empty($_REQUEST['longitude']) ? 0 : test_input($_REQUEST['longitude']);

	if ($latitude == 0 && $longitude == 0) {

        $res = array('err'=>0,'city'=>'中国');
    	exit(json_encode($res));
    }
    else
    {
        $url='http://apis.map.qq.com/ws/geocoder/v1/?location='.$latitude.','.$longitude.'&get_poi=0&key=VISBZ-7KU3X-BNC4F-7XL2A-5UDTJ-HGFFH';

        $map_res = file_get_contents($url);

        $map_res = json_decode($map_res,true);

        if ($map_res['status'] == 0) {

            $res = array('err'=>0,'city'=>$map_res['result']['address_component']['city']);
        	exit(json_encode($res));

        }
        else
        {
            $res = array('err'=>0,'city'=>'中国');
        	exit(json_encode($res));
        }

    }
}

/*发布到广场*/
function action_release_square()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $order_id = empty($_REQUEST['order_id']) ? 0 : test_input($_REQUEST['order_id']);
    $bindinput_textarea = empty($_REQUEST['bindinput_textarea']) ? null : test_input($_REQUEST['bindinput_textarea']);

    if ($order_id == 0) {
    	$res = array('err'=>3,'msg'=>'您要发布的订单不存在！');
        exit(json_encode($res));
    }

    if ($bindinput_textarea == null) {
    	$res = array('err'=>4,'msg'=>'评论内容不能为空！');
        exit(json_encode($res));
    }

    // 检查通过，处理发布
    $sql = "update ".$GLOBALS['ecs']->table('order_info')." set `square` = '".$bindinput_textarea."' where user_id = '".$uid."' and `order_id` = '".$order_id."'";
    $GLOBALS['db']->query($sql);

    // 查询goods_id
    $sql = "SELECT goods_id FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE order_id='$order_id'";

    $goods_id = $GLOBALS['db']->getOne($sql);

    if (!$goods_id) {
    	$res = array('err'=>5,'msg'=>'订单中的商品找不到了！');
        exit(json_encode($res));
    }

    $insert_square_sql =  "INSERT INTO ".$GLOBALS['ecs']->table('square_mes')." ( order_id,goods_id,square_add_time) VALUES (".$order_id.",".$goods_id.",".gmtime().")";
    $GLOBALS['db']->query($insert_square_sql);

    $res = array('err'=>0,'msg'=>'已成功提交到广场！');
    exit(json_encode($res));
}

/*广场列表*/
function action_ajax_get_square()
{
	$hhs = $GLOBALS['ecs'];
	$db = $GLOBALS['db'];

	$keywords = isset($_REQUEST['keywords']) ? test_input($_REQUEST['keywords']) : null;//关键词

	$orderby = 'order_id desc';

	$where = " AND o.`square`<> '' ";

	if (!empty($keywords))
    {
        $where = " AND g.goods_name LIKE '%" . mysql_like_quote($keywords) . "%'";

        $sql = "select o.square,o.order_id,o.team_sign,o.team_num,o.teammen_num,(team_num - teammen_num) as need, o.add_time,u.wx_name,u.headimg from ".$hhs->table('order_info')." as o,".$hhs->table('order_goods')." as g,".$hhs->table('users')." as u where o.show_square = 1   and o.team_status = 1 and o.user_id = u.user_id AND g.`order_id` = o.`order_id` ".$where." order by " . $orderby;        // and o.team_status = 1 AND o.order_status = 1
    }
    else{
        $sql = "select o.square,o.order_id,o.team_sign,o.team_num,o.teammen_num,(team_num - teammen_num) as need, o.add_time,u.wx_name,u.headimg from ".$hhs->table('order_info')." as o,".$hhs->table('users')." as u where o.show_square = 1 and o.team_status = 1  and o.user_id = u.user_id  ".$where." order by " . $orderby; //and o.team_status = 1 AND o.order_status = 1
    }

    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();

    foreach ($res AS $idx => $row)
    {
        $sql = "select g.is_on_sale,g.is_delete,g.goods_name,g.goods_id, g.goods_number, g.goods_thumb,g.little_img,g.goods_img, g.market_price, g.shop_price,g.team_price  from ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g where g.`goods_id` = o.`goods_id` and o.`order_id` = '".$row['order_id']."'";
        $goods = $db->getRow($sql);
		if($goods['is_on_sale'] == 1 && $goods['is_delete'] == 0)
		{
			$arr[$idx]['goods_id']   = $goods['goods_id'];
			$arr[$idx]['goods_name']   = $goods['goods_name'];
			$arr[$idx]['goods_number'] = $goods['goods_number'];
			$arr[$idx]['market_price'] = price_format($goods['market_price'],false);
			$arr[$idx]['shop_price']   = price_format($goods['shop_price'],false);
			
			$arr[$idx]['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);
			$arr[$idx]['little_img']  = get_image_path($goods['goods_id'], $goods['little_img'], true);
			$arr[$idx]['goods_img']   = get_image_path($goods['goods_id'], $goods['goods_img']);
			$arr[$idx]['url']         = build_uri('goods', array('gid'=>$goods['goods_id']), $goods['goods_name']);
			$arr[$idx]['team_price']  = price_format($goods['team_price'],false);
			$arr[$idx]['team_num']    = $row['team_num'];
			$arr[$idx]['need']    = $row['team_num'] - $row['teammen_num'];
			$arr[$idx]['square']    = $row['square'];
			$arr[$idx]['team_id']    = $row['team_sign'];
			$arr[$idx]['wx_name']       = $row['wx_name'];
			$arr[$idx]['headimg']  = $row['headimg'];
			$arr[$idx]['add_time']    = local_date("Y-m-d H:i:s",$row['add_time']);
			
			$arr[$idx]['team_discount']    = @number_format($goods['team_price']/$goods['market_price']*10,1);
	
			$arr[$idx]['buy_nums']    = $db->getOne("select count(*) from ".$hhs->table('order_goods')." where goods_id = '".$goods['goods_id']."'");
	
	
			$arr[$idx]['gallery']   = $db->getAll("select thumb_url from ".$hhs->table('goods_gallery')." where goods_id = '".$goods['goods_id']."' limit 3");

			if ($arr[$idx]['gallery']) {
				foreach ($arr[$idx]['gallery'] as $gallerykey => $galleryvalue) {
					if ($arr[$idx]['gallery'][$gallerykey]['thumb_url']) {
						$arr[$idx]['gallery'][$gallerykey]['thumb_url'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$arr[$idx]['gallery'][$gallerykey]['thumb_url'];
					}
				}
			}
			else
			{
				$arr[$idx]['gallery'][0]['thumb_url'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
			}

			if (!$arr[$idx]['headimg']) {
				$arr[$idx]['headimg'] = 'http://'.$GLOBALS['xcx_config']['url'].'/themes/haohai2017/images/mr_head.png';
			}

			
	  	}
    }

    foreach ($arr as $key => $value) {
        $todayDate = gmtime();
        $luckdraw_goods_sql = "SELECT goods_id FROM " . $GLOBALS['hhs']->table('luckdraw') . " WHERE start_time < ".$todayDate." and end_time >".$todayDate." and goods_id = ".$value['goods_id'];
        $luckdraw_goods = $GLOBALS['db']->getOne($luckdraw_goods_sql);
        if($luckdraw_goods > 0)
        {
            unset($arr[$key]);
        }
    }

    $res = array('err'=>0,'list'=>$arr);
    exit(json_encode($res));
}

/*
分类页面
*/
function action_category()

{

	// 接受分类ID，如果有传，读取该分类的上级分类，并读取该上级分类的所有下级分类，并返回分类列表

	$category_id = empty($_REQUEST['category_id']) ? 0 : test_input($_REQUEST['category_id']);

	if ($category_id > 0 && $category_id != 'undefined') {
		$sql = "SELECT parent_id FROM ".$GLOBALS['ecs']->table('category')." WHERE cat_id='$category_id'";

		$cat_parent_id = $GLOBALS['db']->getOne($sql);

		$sql = "SELECT cat_id,cat_name,sort_order FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show !=0 AND parent_id='$cat_parent_id' ORDER BY sort_order ASC";

		$cat_list = $GLOBALS['db']->getAll($sql);

		$res = array('err' => '0','cat_list'=>$cat_list,'cat_parent_id'=>$cat_parent_id);
		exit(json_encode($res));
	}


	// 获取全部1级分类

	$sql = "SELECT cat_id,cat_name,sort_order FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show !=0 AND parent_id=0 ORDER BY sort_order ASC";



	$cat_list = $GLOBALS['db']->getAll($sql);

	foreach ($cat_list as $cat_listkey => $cat_listvalue) {

		$cat_id = $cat_list[$cat_listkey]['cat_id'];



		$sql = "SELECT cat_id,cat_name,cat_img FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show=1 AND parent_id='$cat_id' ORDER BY sort_order ASC";



		$cat_list[$cat_listkey]['parent'] = $GLOBALS['db']->getAll($sql);//2级分类列表

		foreach ($cat_list[$cat_listkey]['parent'] as $pkey => $pvalue) {

			if ($cat_list[$cat_listkey]['parent'][$pkey]['cat_img']) {
				$cat_list[$cat_listkey]['parent'][$pkey]['cat_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$cat_list[$cat_listkey]['parent'][$pkey]['cat_img'];
			}
			else
			{
				$cat_list[$cat_listkey]['parent'][$pkey]['cat_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/images/no_pic.jpg';
			}
		}

		// 读取此分类下的热门品牌 $cat_id
		// 读取此分类下的商品，再读取这些商品的品牌
		$sql = "SELECT DISTINCT b.* FROM ".$GLOBALS['ecs']->table('goods')." AS g,".$GLOBALS['ecs']->table('brand')." AS b WHERE g.cat_id='$cat_id' AND g.brand_id>0 AND g.brand_id=b.brand_id";

		$cat_list[$cat_listkey]['brand_list'] = $GLOBALS['db']->getAll($sql);

		foreach ($cat_list[$cat_listkey]['brand_list'] as $brand_listkey => $brand_listvalue) {
			
			if ($cat_list[$cat_listkey]['brand_list'][$brand_listkey]['brand_logo']) {
				$cat_list[$cat_listkey]['brand_list'][$brand_listkey]['brand_logo'] = 'http://'.$GLOBALS['xcx_config']['url'].'/data/brandlogo/'.$cat_list[$cat_listkey]['brand_list'][$brand_listkey]['brand_logo'];
			}
			else
			{
				$cat_list[$cat_listkey]['brand_list'][$brand_listkey]['brand_logo'] = 'http://'.$GLOBALS['xcx_config']['url'].'/images/no_pic.jpg';
			}
		}


	}


    $res = array('err' => '0','cat_list'=>$cat_list,'cat_parent_id'=>0);



	exit(json_encode($res));



}

/*
外卖系统返回数据接口
*/
function action_goods_category()
{

	// 清空该会员购物车

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid != 0)
    {
    	// 清空购物车
    	$sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id='$uid'";

    	$GLOBALS['db']->query($sql);
    }

	// 获取全部1级分类
	$sql = "SELECT cat_id as id,cat_name as name,thumb as icon FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show !=0 AND parent_id=0 ORDER BY sort_order ASC";

	$cat_list = $GLOBALS['db']->getAll($sql);

	foreach ($cat_list as $key => $value) {
		$cat_list[$key]['tag'] = 'tag'.$cat_list[$key]['id'];

		if ($cat_list[$key]['icon']) {
			$cat_list[$key]['icon'] = 'http://'.$GLOBALS['xcx_config']['url'].'/data/catthumb/'.$cat_list[$key]['icon'];
		}
		else
		{
			$cat_list[$key]['icon'] = '/pages/index/resources/pic/ding-ico1.png';
		}

		// 取得分类下的子分类
		$where = ' ';

		$cat_id = $cat_list[$key]['id'];

		if ($cat_id != 0) {
			// $where .= " AND cat_id='$cat_id'";

			/*获取该分类的下级分类*/

			$cat_id_list = array();

			$sql = "SELECT cat_id FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show=1 AND parent_id='$cat_id'";

			$cat_id_list_tmp = $GLOBALS['db']->getAll($sql);

			if (is_array($cat_id_list_tmp)) {
				foreach ($cat_id_list_tmp as $tmpkey => $tmpvalue) {
					$cat_id_list[] = $tmpvalue['cat_id'];

					// 读取第三级分类
					$sql = "SELECT cat_id FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show=1 AND parent_id='{$tmpvalue['cat_id']}'";

					$cat_id_list_tmp3 = $GLOBALS['db']->getAll($sql);

					if (is_array($cat_id_list_tmp3)) {
						foreach ($cat_id_list_tmp3 as $key3 => $value3) {
							$cat_id_list[] = $value3['cat_id'];
						}
					}

				}
			}

			$cat_id_list = implode(',',$cat_id_list);

			if ($cat_id_list) {
				$cat_id_list = $cat_id . ',' . $cat_id_list;
			}
			else
			{
				$cat_id_list = $cat_id;
			}

			$where .= ' cat_id IN (' . $cat_id_list . ')';

		}
		else
		{
			$where = ' 1';
		}

		// 取得商品
		$sql = "SELECT goods_id as id,goods_name as name,shop_price as price,goods_thumb as pic FROM ".$GLOBALS['ecs']->table('goods')." WHERE".$where." AND is_delete=0 AND is_on_sale=1";
		$best_list = $GLOBALS['db']->getAll($sql);

		// 处理商品列表数量，销量

		foreach ($best_list as $bestkey => $bestvalue) {
			$best_list[$bestkey]['count'] = 0;
			$best_list[$bestkey]['price'] = (int)$best_list[$bestkey]['price'];

			// 计算销量
			$sql = "SELECT sum(goods_number) FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE goods_id='{$best_list[$bestkey]['id']}'";

			$best_list[$bestkey]['sales'] = $GLOBALS['db']->getOne($sql);

			if (!$best_list[$bestkey]['sales']) {
				$best_list[$bestkey]['sales'] = 0;
			}

			// 取短标题
			if (mb_strlen($best_list[$bestkey]['name']) > 10) {
				$best_list[$bestkey]['name'] = mb_substr($best_list[$bestkey]['name'],0,10,'utf-8')."...";
			}

			$best_list[$bestkey]['pic'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$bestkey]['pic'];

			// 取该商品的规格
			$goods_properties = get_goods_properties($best_list[$bestkey]['id']);

			if (!$goods_properties['spe']) {
				$best_list[$bestkey]['spe'] = 'null';
			}
			else
			{
				
				foreach ($goods_properties['spe'] as $spekey => $spevalue) {
					$goods_properties['spe'][$spekey]['attr_id'] = $spekey;
				}

				$best_list[$bestkey]['spe'] = $goods_properties['spe'];
			}
		}
	

		$cat_list[$key]['dishs'] = $best_list;
	}

	$selectedMenuId = $cat_list[0]['id'];

	// 获取优惠卷列表

	$conpons = get_conpons($uid,0);

	if (!$conpons) {
		$conpons = 'null';
		$count_conpons = 0;
	}
	else
	{
		$count_conpons = count($conpons);

		if (!$count_conpons) {
			$count_conpons = 0;
		}

		$conpons = reset($conpons);
	}

	$res = array('err' => '0','cat_list'=>$cat_list,'selectedMenuId'=>$selectedMenuId,'conpons'=>$conpons,'count_conpons'=>$count_conpons);

	exit(json_encode($res));
}

/*获取会员拥有的优惠卷*/

function action_user_conpons_list()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $conpons_list = get_conpons($uid,1);

    if (!$conpons_list) {
    	$conpons_list = 'null';
    	$res = array('err'=>3,'conpons_list'=>$conpons_list);
    	exit(json_encode($res));
    }

    $res = array('err'=>0,'conpons_list'=>$conpons_list);
    exit(json_encode($res));
}
/*
获取优惠卷列表
*/
function action_conpons_list()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $conpons_list = get_conpons($uid,0);

    if (!$conpons_list) {
    	$conpons_list = 'null';
    	$res = array('err'=>3,'conpons_list'=>$conpons_list);
    	exit(json_encode($res));
    }


    $res = array('err'=>0,'conpons_list'=>$conpons_list);
    exit(json_encode($res));
}

/*
优惠卷领取功能
*/
function action_user_get_conpons()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

	$conpons_id = empty($_REQUEST['conpons_id']) ? 0 : test_input($_REQUEST['conpons_id']);

	$sql = "SELECT c_get_limit FROM ".$GLOBALS['ecs']->table('conpons')." WHERE id='$conpons_id' AND is_open_get=1";

	$c_get_limit = $GLOBALS['db']->getOne($sql);//最高领取次数

	if (!$c_get_limit) {
		$res = array('err'=>4,'msg'=>'您要的优惠卷不存在！');
        exit(json_encode($res));
	}

	// 查询该会员是否已经有该优惠卷了
	$sql = "SELECT count(*) FROM ".$GLOBALS['ecs']->table('user_conpons')." WHERE user_id='$uid' AND conpons_id='$conpons_id'";

    $user_conpons = $GLOBALS['db']->getOne($sql);//用户持有张数

    // 检查已领取数量

    if ($user_conpons >= $c_get_limit) {
    	$res = array('err'=>5,'msg'=>'该优惠卷您已经领取过了！');
        exit(json_encode($res));
    }

	// 插入优惠卷
	$sql = "INSERT INTO ".$GLOBALS['ecs']->table('user_conpons')." (`conpons_id`,`is_use_order`,`user_id`,`number`) VALUES ('$conpons_id','0','$uid','1')";
    

    	// 更新优惠卷
		// $sql = "UPDATE ".$GLOBALS['ecs']->table('user_conpons')." SET number=number+1 WHERE user_id='$uid' AND conpons_id='$conpons_id'";
    

    if ($GLOBALS['db']->query($sql)) {
    	$res = array('err'=>0,'msg'=>'ok');
        exit(json_encode($res));
    }
    else
    {
    	$res = array('err'=>3,'msg'=>'网络异常,领取失败,请稍后重试！');
        exit(json_encode($res));
    }

}

/*
批量加入购物车
*/
function action_batch_add_cart()
{

	require('../includes/lib_order.php');

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$check_goods_list = empty($_REQUEST['check_goods_list']) ? '' : test_input($_REQUEST['check_goods_list']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    
	// 将商品批量添加到购物车，不验证商品规格

	if ($check_goods_list == '') {
		$res = array('err'=>3,'msg'=>'您还没有选择商品哦！');
        exit(json_encode($res));
	}
	else
	{
		// 切割商品列表，循环加入购物车  107|1,113|1,

		$check_goods_list = explode(',', $check_goods_list);

		$is_goods = 0;//是否有商品

		foreach ($check_goods_list as $key => $value) {
			
			$check_goods_one = explode('|', $check_goods_list[$key]);

			if (isset($check_goods_one[1]) && $check_goods_one[1] != 0) {
				addto_cart_fast($check_goods_one[0],$check_goods_one[1],array(),$uid);
				$is_goods = 1;
			}
		}

		

		$res = array('err'=>0,'msg'=>'ok','is_goods'=>$is_goods);
        exit(json_encode($res));
	}
}

/*
ajax 获取文章列表
*/
function action_ajax_article_list()
{
	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
	$cat_id = isset($_REQUEST['cat_id']) ? test_input($_REQUEST['cat_id']) : 0;//分类

	$count = 20;// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}

	$where = '';

	if ($cat_id != 0) {

		/*获取该分类的下级分类*/

		$cat_id_list = array();

		$sql = "SELECT cat_id FROM ".$GLOBALS['ecs']->table('article_cat')." WHERE parent_id='$cat_id'";

		$cat_id_list_tmp = $GLOBALS['db']->getAll($sql);

		if (is_array($cat_id_list_tmp)) {
			foreach ($cat_id_list_tmp as $key => $value) {
				$cat_id_list[] = $value['cat_id'];

				// 读取第三级分类
				$sql = "SELECT cat_id FROM ".$GLOBALS['ecs']->table('article_cat')." WHERE parent_id='{$value['cat_id']}'";

				$cat_id_list_tmp3 = $GLOBALS['db']->getAll($sql);

				if (is_array($cat_id_list_tmp3)) {
					foreach ($cat_id_list_tmp3 as $key3 => $value3) {
						$cat_id_list[] = $value3['cat_id'];
					}
				}

			}
		}

		$cat_id_list = implode(',',$cat_id_list);

		if ($cat_id_list) {
			$cat_id_list = $cat_id . ',' . $cat_id_list;
		}
		else
		{
			$cat_id_list = $cat_id;
		}

		$where .= ' cat_id IN (' . $cat_id_list . ')';

	}
	else
	{
		$where = ' 1';
	}

	// 取得文章
	$sql = "SELECT a.title,a.article_id FROM ".$GLOBALS['ecs']->table('article')." AS a WHERE".$where." AND is_open=1 ORDER BY a.article_id DESC LIMIT $min,$count";
	$list = $GLOBALS['db']->getAll($sql);

	foreach ($list as $key => $value) {
		// 取短标题
		if (mb_strlen($list[$key]['title']) > 30) {
			$list[$key]['title'] = mb_substr($list[$key]['title'],0,30,'utf-8')."...";
		}
	}

	$list_count = count($list) < $count ? 0:1;

	$res = array('err' => '0','best_list'=>$list,'list_count'=>$list_count,'cat_id_list'=>$cat_id_list);

	exit(json_encode($res));

}

/*
ajax获取商品列表
*/
function action_ajax_goods_list(){
	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
	$is_best = isset($_REQUEST['is_best']) ? test_input($_REQUEST['is_best']) : 0;//是否精品
	$cat_id = isset($_REQUEST['cat_id']) ? test_input($_REQUEST['cat_id']) : 0;//分类
	$cat_type = isset($_REQUEST['cat_type']) ? test_input($_REQUEST['cat_type']) : 'tuan';//团购 or 单售商品品 or 秒杀商品

	$count = 20;

	// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}

	$where = '';

	if ($cat_id != 0) {
		// $where .= " AND cat_id='$cat_id'";

		/*获取该分类的下级分类*/

		$cat_id_list = array();

		$sql = "SELECT cat_id FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show=1 AND parent_id='$cat_id'";

		$cat_id_list_tmp = $GLOBALS['db']->getAll($sql);

		if (is_array($cat_id_list_tmp)) {
			foreach ($cat_id_list_tmp as $key => $value) {
				$cat_id_list[] = $value['cat_id'];

				// 读取第三级分类
				$sql = "SELECT cat_id FROM ".$GLOBALS['ecs']->table('category')." WHERE is_show=1 AND parent_id='{$value['cat_id']}'";

				$cat_id_list_tmp3 = $GLOBALS['db']->getAll($sql);

				if (is_array($cat_id_list_tmp3)) {
					foreach ($cat_id_list_tmp3 as $key3 => $value3) {
						$cat_id_list[] = $value3['cat_id'];
					}
				}

			}
		}

		$cat_id_list = implode(',',$cat_id_list);

		if ($cat_id_list) {
			$cat_id_list = $cat_id . ',' . $cat_id_list;
		}
		else
		{
			$cat_id_list = $cat_id;
		}

		$where .= ' cat_id IN (' . $cat_id_list . ')';

	}
	else
	{
		$where = ' 1';
	}

	if ($cat_type == 'tuan') {
		$where .= ' AND is_team=1 AND is_miao=0';
	}
	elseif($cat_type == 'one'){
		$where .= ' AND is_mall=1 AND is_miao=0';
	}
	elseif ($cat_type == 'miao') {
		$where .= ' AND is_mall=1 AND is_miao=1';
	}


	// 取得精品商品
	$sql = "SELECT g.goods_id,g.goods_name,g.shop_price,g.market_price,g.goods_thumb,g.team_num,g.goods_brief,g.little_img FROM ".$GLOBALS['ecs']->table('goods')." AS g WHERE".$where." AND is_delete=0 AND is_luck=0 AND is_on_sale=1 ORDER BY g.goods_id DESC LIMIT $min,$count";
	$best_list = $GLOBALS['db']->getAll($sql);

	// echo $sql;

	foreach ($best_list as $key => $value) {

		if (!$best_list[$key]['goods_thumb']) {
			$best_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$best_list[$key]['goods_thumb'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$key]['goods_thumb']; 
		}

		if (!$best_list[$key]['little_img']) {
			$best_list[$key]['little_img'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$best_list[$key]['little_img'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$key]['little_img']; 
		}

	}

	$list_count = count($best_list) < $count ? 0:1;

	$res = array('err' => '0','best_list'=>$best_list,'list_count'=>$list_count);

	exit(json_encode($res));
}

/*品牌详情-品牌商品列表*/
function action_brand_info()
{
	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
	$brand_id = isset($_REQUEST['brand_id']) ? test_input($_REQUEST['brand_id']) : 0;//品牌ID

	// 获取品牌详情
	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('brand')." WHERE brand_id='$brand_id'";
	$brand_info = $GLOBALS['db']->getRow($sql);

	if (!$brand_info) {
		$res = array('err' => '1','msg'=>'您访问的品牌不存在！');

		exit(json_encode($res));
	}

	if ($brand_info['brand_logo']) {
		$brand_info['brand_logo'] = 'http://'.$GLOBALS['xcx_config']['url'].'/data/brandlogo/'.$brand_info['brand_logo'];
	}
	else
	{
		$brand_info['brand_logo'] = 'http://'.$GLOBALS['xcx_config']['url'].'/images/no_pic.jpg';
	}

	$count = 20;

	// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}

	// 取得品牌下商品
	$sql = "SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.market_price FROM ".$GLOBALS['ecs']->table('goods')." AS g WHERE is_delete=0 AND is_on_sale=1 AND brand_id='$brand_id' LIMIT $min,$count";
	$best_list = $GLOBALS['db']->getAll($sql);

	// echo $sql;

	foreach ($best_list as $key => $value) {

		if (!$best_list[$key]['goods_thumb']) {
			$best_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
		else
		{
			$best_list[$key]['goods_thumb'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$best_list[$key]['goods_thumb']; 
		}

	}

	$list_count = count($best_list) < $count ? 0:1;

	$res = array('err' => '0','best_list'=>$best_list,'list_count'=>$list_count,'brand_info'=>$brand_info);

	exit(json_encode($res));

}

/*
购物车,编辑数量，是否勾选，删除
*/
function action_cart(){
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$checked = empty($_REQUEST['checked']) ? '' : test_input($_REQUEST['checked']);
	$rec_id = empty($_REQUEST['rec_id']) ? 0 : test_input($_REQUEST['rec_id']);
	$number_jia = empty($_REQUEST['number_jia']) ? 0 : test_input($_REQUEST['number_jia']);
	$number_jian = empty($_REQUEST['number_jian']) ? 0 : test_input($_REQUEST['number_jian']);
	$number_change = empty($_REQUEST['number_change']) ? 0 : test_input($_REQUEST['number_change']);
	$is_del = empty($_REQUEST['is_del']) ? 0 : test_input($_REQUEST['is_del']);

	if ($checked != '' && $checked != 'no' && $checked != 'quanxuan') {
		$checked = explode('|', $checked);

		array_pop($checked);

		// print_r($checked);

		// 将这个数组的值设为选中，其他都设为未选中
	}
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'缺少参数！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    if ($rec_id) {
    	if ($number_jia) {
	    	$sqlr = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=goods_number+1 WHERE rec_id='$rec_id'";
    	}
    	elseif ($number_jian) {
	    	$sqlr = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number=goods_number-1 WHERE rec_id='$rec_id'";
    	}
    	elseif ($number_change) {
    		$sqlr = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number='$number_change' WHERE rec_id='$rec_id'";
    	}
    	elseif ($is_del) {
    		$sqlr = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE rec_id='$rec_id'";
    	}

    	if (isset($sqlr)) {
    		$GLOBALS['db']->query($sqlr);
    	}
    	
    }

    // $uid = 59;

    $sql = "SELECT c.rec_id,c.user_id,c.goods_id,c.goods_name,c.market_price,c.goods_price,c.goods_number,c.goods_attr,c.checked,g.goods_thumb FROM ".$GLOBALS['ecs']->table('cart')." AS c, ".$GLOBALS['ecs']->table('goods')." AS g WHERE c.user_id='$uid' AND c.goods_id=g.goods_id";

    $cart_list = $GLOBALS['db']->getAll($sql);

    // echo $sql;

    if ($cart_list) {
    	$sum_goods_price = 0;//总价
    	$is_quanxuan = 1;//是否全选
    	foreach ($cart_list as $key => $value) {
    		// 取商品图片

    		// 取短标题
			if (strlen($cart_list[$key]['goods_name'])/2 > 35) {
				$cart_list[$key]['goods_name'] = mb_substr($cart_list[$key]['goods_name'],0,35,'utf-8')."...";
			}

	    	if ($cart_list[$key]['goods_thumb']) {
	    		$cart_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$cart_list[$key]['goods_thumb'];
	    	}
	    	else
	    	{
	    		$cart_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
	    	}


	    	// 处理选中状态

	    	if ($checked) {

	    		if ($checked == 'no') {
	    			$cart_list[$key]['checked'] = '';
	    			// 全部未选中
	    			$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET checked=0 WHERE rec_id='{$cart_list[$key]['rec_id']}'";

	    			$GLOBALS['db']->query($sql);
    			}
    			else
    			{	

    				if ($checked == 'quanxuan') {
    					// 全部选择
    					$cart_list[$key]['checked'] = 'checked';
		    			$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET checked=1 WHERE rec_id='{$cart_list[$key]['rec_id']}'";

		    			$GLOBALS['db']->query($sql);
    				}
    				else
    				{
    					if (in_array($cart_list[$key]['rec_id'], $checked)) {
		    			$cart_list[$key]['checked'] = 'checked';

		    			$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET checked=1 WHERE rec_id='{$cart_list[$key]['rec_id']}'";

		    			$GLOBALS['db']->query($sql);

			    		}
			    		else
			    		{
			    			$cart_list[$key]['checked'] = '';
			    			// 将购物车中该商品改为未选中

			    			$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET checked=0 WHERE rec_id='{$cart_list[$key]['rec_id']}'";

			    			$GLOBALS['db']->query($sql);
			    		}

    				}
	
	    		}
		    	

	    	}
	    	else
	    	{

    			// $cart_list[$key]['checked'] = 'checked';

    			if ($cart_list[$key]['checked'] == 1) {
    				$cart_list[$key]['checked'] = 'checked';
    			}
    			else{
    				$cart_list[$key]['checked'] = '';
    			}
	    		
	    	}

	    	// 检查商品数量，如果小于1改为1
	    	if ($cart_list[$key]['goods_number'] < 1) {
	    		$cart_list[$key]['goods_number'] = 1;
	    		$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET goods_number='1' WHERE rec_id='{$cart_list[$key]['rec_id']}'";
	    		$GLOBALS['db']->query($sql);
	    	}

	    	// 计算总价
	    	
	    	if ($cart_list[$key]['checked'] == 'checked') {
	    		$sum_goods_price += ($cart_list[$key]['goods_price'] * $cart_list[$key]['goods_number']);
	    	}

	    	// 处理属性列表
	    	if ($cart_list[$key]['goods_attr']) {
	    		$cart_list[$key]['goods_attr'] = str_replace(array("\r\n", "\r", "\n"), ";", $cart_list[$key]['goods_attr']);
	    	}

	    	// 取短属性
			if (mb_strlen($cart_list[$key]['goods_attr']) > 40) {
				$cart_list[$key]['goods_attr'] = mb_substr($cart_list[$key]['goods_attr'],0,40,'utf-8')."...";
			}

			// 判断是否全选
			if ($cart_list[$key]['checked'] != 'checked') {
				$is_quanxuan = 0;
			}
    	}


    	$res = array('err' => '0','cart_list'=>$cart_list,'sum_goods_price'=>$sum_goods_price,'is_quanxuan'=>$is_quanxuan);

		exit(json_encode($res));
    }
    else
    {
    	$res = array('err' => '0','cart_list'=>'null','sum_goods_price'=>0);

		exit(json_encode($res));
    }

}

/*
商品详情页
*/
function action_goods(){
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : test_input($_REQUEST['goods_id']);
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);

	if (!$goods_id) {
		$res = array('err' => '1','msg'=>'商品不存在');
		exit(json_encode($res));
	}

	// 获取商品相册
	$sql = 'SELECT img_url' .
        ' FROM ' . $GLOBALS['ecs']->table('goods_gallery') .
        " WHERE goods_id = '$goods_id' LIMIT " . $GLOBALS['_CFG']['goods_gallery_number'];
    $goods_gallery = $GLOBALS['db']->getAll($sql);

    $goods_gallery_new = array();
    foreach ($goods_gallery as $key => $value) {
    	$goods_gallery_new[] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$goods_gallery[$key]['img_url'];
    }

    // 获取商品信息
    $sql = "SELECT g.goods_id,g.goods_name,g.goods_number,g.market_price,g.promote_price,g.team_price,g.team_num,g.promote_start_date,g.promote_end_date,g.shop_price,g.give_integral,g.goods_desc,g.rank_integral,g.goods_brief,g.goods_weight,g.is_shipping,g.brand_id,g.goods_thumb,g.is_team,g.is_mall,g.is_miao,g.suppliers_id,b.brand_name,g.promote_start_date,g.promote_end_date FROM ".
    $GLOBALS['ecs']->table('goods')." AS g ".
    "LEFT JOIN ".$GLOBALS['ecs']->table('brand')." AS b".
    " ON g.brand_id=b.brand_id WHERE goods_id='$goods_id'";

    $goods_info = $GLOBALS['db']->getRow($sql);

	if ($goods_info['goods_thumb']) {
		$goods_info['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$goods_info['goods_thumb'];
	}
	else
	{
		$goods_info['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
	}
	
	if (!$goods_info['brand_name']) {
		$goods_info['brand_name'] = 'Other';
	}

	if ($goods_info['give_integral'] == '-1') {
		$goods_info['give_integral'] = $goods_info['shop_price'];
	}

	if ($goods_info['rank_integral'] == '-1') {
		$goods_info['rank_integral'] = $goods_info['shop_price'];
	}

	// 销量
	$sql = "SELECT sum(goods_number) FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE goods_id='{$goods_info['goods_id']}'";
	$goods_info['xiaoliang'] = $GLOBALS['db']->getOne($sql);

	if (!$goods_info['xiaoliang']) {
		$goods_info['xiaoliang'] = 0;
	}

	// 团购省多少钱
	$goods_info['tuansheng_price'] = $goods_info['shop_price'] - $goods_info['team_price'];

	// 检查促销

	if ($goods_info['promote_price'] > 0) {
		$tady_time = time();
		if ($tady_time > $goods_info['promote_start_date'] && $tady_time < $goods_info['promote_end_date']) {
			$goods_info['shop_price'] = $goods_info['promote_price'];
			$goods_info['promote_price'] = 1;
		}
		else
		{
			$goods_info['promote_price'] = 0;//没有促销
		}
	}

	// 准备详细介绍

	if ($goods_info['goods_desc']) {

		// 匹配出img标签，为标签加上width和height以及处理src

	    $preg = '/<img.*?src="(.*?)".*?>/is';

		preg_match_all($preg,$goods_info['goods_desc'],$result,PREG_PATTERN_ORDER);//匹配img的src

		foreach ($result[1] as $key => $value) {
			$result[1][$key] = "<img class='rich_img' src='http://".$GLOBALS['xcx_config']['url'].$value."'>";
		}


		$goods_info['goods_desc'] = str_replace($result[0],$result[1],$goods_info['goods_desc']);

	}

	else

	{

		$goods_info['goods_desc'] = '';

	}

	// 当前会员是否收藏了该商品？
	$sql = "SELECT rec_id FROM ".$GLOBALS['ecs']->table('collect_goods')." WHERE goods_id='$goods_id' AND user_id='$uid'";

	if ($GLOBALS['db']->getOne($sql)) {
		$user_is_collect = 1;
	}
	else
	{
		$user_is_collect = 0;
	}

	// 准备商品属性
	$goods_properties = get_goods_properties($goods_id);

	// print_r($goods_properties['spe']);

	if (!$goods_properties['spe']) {
		$goods_properties['spe'] = 'null';
	}
	else
	{
		foreach ($goods_properties['spe'] as $key => $value) {
			$goods_properties['spe'][$key]['attr_id'] = $key;
		}
	}


	// 获取购物车商品数量

	// 取购物车商品数量
	if ($uid) {
		$sql="SELECT count(*) FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id='$uid'";

		$cart_number = $GLOBALS['db']->getOne($sql);
	}
	else
	{
		$cart_number = 0;
	}

	// 获取商品的商家信息
	if ($goods_info['suppliers_id'] > 0) {
		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('suppliers')." WHERE suppliers_id='{$goods_info['suppliers_id']}'";
		$suppliers = $GLOBALS['db']->getRow($sql);

		if (!$suppliers) {
			$suppliers = 0;
		}
	}
	else
	{
		$suppliers = 0;
	}

	$time = gmtime();

	if ($goods_info['is_miao'] == 1 && $goods_info['promote_start_date'] <= $time && $goods_info['promote_end_date'] >= $time && $goods_info['promote_end_date'] > $goods_info['promote_start_date']) {
		$goods_info['is_miao'] = 1;
		$goods_info['gmt_end_time_xcx'] = $goods_info['promote_end_date'] - $time;

		$goods_info['gmt_end_time_text'] = '请稍后,正在载入中...';
	}
	else
	{
		// $goods_info['is_miao'] = 0;
		$goods_info['gmt_end_time_xcx'] = 0;
	}

	if($goods_info['promote_start_date']>$time)
	{
		$goods_info['sort_order'] =2;//即将开始
	}
	elseif($goods_info['promote_start_date']<$time && $time<$goods_info['promote_end_date'])
	{
		$goods_info['sort_order'] =1;//已开始未结束
	}
	else
	{
		$goods_info['sort_order'] =3;//已结束
	}

	if ($goods_info['is_team'] == 1) {
		// 获取未成团的团购订单
		// 筛选出本商品的团购订单
		$sql = "SELECT g.goods_id,o.order_id,o.team_sign,o.team_status,o.user_id,o.team_num,o.teammen_num,u.headimgurl,u.headimg,u.uname,u.wx_name FROM ".$GLOBALS['ecs']->table('order_goods')." as g,".$GLOBALS['ecs']->table('order_info')." as o,".$GLOBALS['ecs']->table('users')." as u WHERE g.goods_id='$goods_id' AND o.order_id=g.order_id AND o.extension_code='team_goods' AND o.team_status=1 AND o.team_first=1 AND o.user_id=u.user_id AND u.wx_name <> '' ORDER BY order_id ASC LIMIT 6";

		$goods_team_order = $GLOBALS['db']->getAll($sql);

		$systime = gmtime();//当前时间

		$team_suc_time = $GLOBALS['_CFG']['team_suc_time'] * 86400;//团购限时-总秒数

		if ($goods_team_order) {

			foreach ($goods_team_order as $key => $value) {

				// 团人数处理
				$goods_team_order[$key]['db_num'] = $goods_team_order[$key]['team_num']-$goods_team_order[$key]['teammen_num'];//也是还缺多少人

				// 开团的人
				if (!$goods_team_order[$key]['headimg']) {
					$goods_team_order[$key]['headimg'] = $goods_team_order[$key]['headimgurl'];
				}

				if (!$goods_team_order[$key]['uname']) {
					$goods_team_order[$key]['uname'] = $goods_team_order[$key]['wx_name'];
				}

				//参团的人
				$temp_team_sign = $goods_team_order[$key]['team_sign'];

			    $sql="select u.user_name,u.uname,u.uname,u.headimgurl,u.headimg,o.pay_time,o.team_first,o.is_lucker from ".$GLOBALS['ecs']->table('order_info')." as o left join ".$GLOBALS['ecs']->table('users')." as u on o.user_id=u.user_id where team_sign=".$temp_team_sign." order by order_id ";
			    $temp_team_mem=$GLOBALS['db']->getAll($sql);

			    if ($temp_team_mem) {
			    	foreach($temp_team_mem as $k=>$v)
				    {
				        $temp_team_mem[$k]['date']=local_date('Y-m-d H:i:s',$v['pay_time']);
				    }

				    $goods_team_order[$key]['team_start'] = $temp_team_mem[0]['pay_time'];//开团时间

				    // 计算本团购剩余天数
				    $goods_team_order[$key]['s_miao'] = ($team_suc_time+$goods_team_order[$key]['team_start'])-$systime;//限时剩余时间总秒数

				    if ($goods_team_order[$key]['s_miao'] < 0) {
				    	unset($goods_team_order[$key]);
				    }
			    }
			    else
			    {
			    	unset($goods_team_order[$key]);
			    }

			}
			
		}
	}
	else
	{
		$goods_team_order = array();
	}

	$res = array('err' => '0','goods_gallery'=>$goods_gallery_new,'goods_info'=>$goods_info,'user_is_collect'=>$user_is_collect,'goods_properties'=>$goods_properties,'cart_number'=>$cart_number,'suppliers'=>$suppliers,'goods_team_order'=>$goods_team_order,'goods_team_order_length'=>count($goods_team_order));
	exit(json_encode($res));
}

function action_goods_tuan()
{
	require_once(ROOT_PATH . 'includes/lib_orders.php');
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : test_input($_REQUEST['goods_id']);
	$number = empty($_REQUEST['number']) ? 0 : test_input($_REQUEST['number']);

	$attr_id    = isset($_REQUEST['attr'])&&!empty($_REQUEST['attr'])&&$_REQUEST['attr']!='undefined' ? explode(',', $_REQUEST['attr']) : 'null';//attr列表用逗号分隔

	if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'登录已失效,请重新登录！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录失效,请重新登录！');
        exit(json_encode($res));
    }

    if (!$goods_id) {
    	$res = array('err'=>3,'msg'=>'网络异常,请重试');
        exit(json_encode($res));
    }

    // 获取商品信息，费用信息，配送信息，支付方式
    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id='$goods_id'";

    $goods_info = $GLOBALS['db']->getRow($sql);

    if (!$goods_info) {
    	$res = array('err'=>4,'msg'=>'您访问的商品不存在！');
        exit(json_encode($res));
    }

    if ($goods_info['goods_thumb']) {
		$goods_info['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$goods_info['goods_thumb'];
	}
	else
	{
		$goods_info['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
	}

	$goods_info['number'] = $number;

    // 返回默认收货地址

    $sql = "SELECT u.address_id,a.* FROM ".$GLOBALS['ecs']->table('users')." AS u,".$GLOBALS['ecs']->table('user_address')." AS a WHERE u.user_id='$uid' AND a.address_id=u.address_id";

    $user_default_address = $GLOBALS['db']->getRow($sql);

    if (!$user_default_address) {
    	$res = array('err'=>5,'msg'=>'请设置您的收货地址！');
        exit(json_encode($res));
    }
    else
    {
    	// 读取收货地址的真名
	    if ($user_default_address['country']) {
	    	$user_default_address['country_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['country']}'");
	    }
	    if ($user_default_address['province']) {
	    	$user_default_address['province_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['province']}'");
	    }
	    if ($user_default_address['city']) {
	    	$user_default_address['city_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['city']}'");
	    }
	    if ($user_default_address['district']) {
	    	$user_default_address['district_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['district']}'");
	    }
    }

    // 返回配送方式列表
    // include_once('includes/lib_order.php');

    $_SESSION['goods_suppliers_id'] = $goods_info['suppliers_id'];

    $region            = array($user_default_address['country'], $user_default_address['province'], $user_default_address['city'], $user_default_address['district']);

    $shipping_list     = available_shipping_list($region,$goods_info['suppliers_id']);


    if (!$shipping_list) {
    	$res = array('err'=>6,'msg'=>'该商户尚未设置配送方式,无法交易！');
        exit(json_encode($res));
    }

    // 商品邮费模板

    $goods_express = $GLOBALS['db']->getAll('select * from '.$GLOBALS['ecs']->table('goods_express').' WHERE goods_id = ' . $goods_id .' AND region_id ' . db_create_in($region) .        ' group by shipping_id');

    $default_express_id = 'null';

    // 如果有设置邮费模板，替换配送方式列表
    if(!empty($goods_express)){
    	$shipping_list = $goods_express;

    	// 选择第一个配送方式
    	$default_express_id = $shipping_list[0]['id'];
    	$default_shipping_id = 'null';
    }
    else
    {
    	$default_express_id = 'null';
    	$default_shipping_id = $shipping_list[0]['shipping_id'];
    }

    $shipping_list[0]['checked'] = 'true';

    foreach ($shipping_list as $key => $shipping) {
    	if ($shipping['shipping_code'] == 'cac') {

            $point_list = available_point_list($region,$goods_info['suppliers_id']);

            if (empty($point_list)) {

                unset($shipping_list[$key]);

            }
            else
            {
            	$goods_info['point_list'] = $point_list;
            }

            break;

        }
    }

    // 计算订单费用
    // 读取商品的重量，数量，价格
    $goods_list = array();

    $goods_list[0] = $goods_info;
    $goods_list[0]['weight'] = $goods_info['goods_weight'];
    $goods_list[0]['amount'] = $goods_info['shop_price'];
    $goods_list[0]['number'] = $number;

    $_SESSION['goods_suppliers_id'] = $goods_info['suppliers_id'];

    $order_fee = order_fee2($goods_list,$user_default_address,$default_express_id,$default_shipping_id);

    if ($attr_id != 'null') {
    	$shop_price  = get_final_price($goods_id, $number, true, $attr_id);//最终价格
	    $price_res['result'] = $shop_price * $number;//商品价格

	    $price_res['goods_attr_number'] = get_product_attr_num($goods_id,$_REQUEST['attr']);//库存

	    $price_res['goods_attr'] = get_goods_attr_str($attr_id);//当前选择的属性

	    // 计算团购价格
	    if ($goods_info['team_price']>0){
	        $attr_price  = spec_price($attr_id,true);
	        $team_price  = $goods_info['team_price'] + $attr_price;
	        $price_res['team_price'] = price_format($team_price * $number);
	    }
	    else
	    {
	    	$price_res['team_price'] = 0;
	    }
    }
    else
    {
    	$price_res['goods_attr'] = '';//当前选择的属性
    	$price_res['goods_attr_number'] = $goods_info['goods_number'];
    	$price_res['team_price'] = $goods_info['team_price'] * $number;
    }


    $payment_list = array();

    // 确定支付方式

    $sql = "SELECT enabled FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_code='wxpay'";

    if ($GLOBALS['db']->getOne($sql)) {
    	$payment_list[0]['pay_name'] = '微信支付';
    	$payment_list[0]['pay_id'] = 1;
    	$payment_list[0]['pay_code'] = 'wxpay';
    }

    $sql = "SELECT enabled FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_code='balance'";

    if ($GLOBALS['db']->getOne($sql)) {
    	$payment_list[1]['pay_name'] = '余额支付';
    	$payment_list[1]['pay_id'] = 2;
    	$payment_list[1]['pay_code'] = 'balance';
    }

    // 处理价格
    //$order_fee['shipping_fee'];//运费
    //$price_res['team_price'];//带属性的商品团购总额

    $order_fee['team_price'] = $order_fee['shipping_fee'] + $price_res['team_price'];

    


    $res = array('err'=>0,'goods_info'=>$goods_info,'user_default_address'=>$user_default_address,'shipping_list'=>$shipping_list,'order_fee'=>$order_fee,'payment_list'=>$payment_list,'default_express_id'=>$default_express_id,'default_shipping_id'=>$default_shipping_id,'price_res'=>$price_res);
    exit(json_encode($res));
}

// 单商品用户选择配送方式，重新计算价格
function action_select_shipping()
{
	include_once('includes/lib_order.php');
	$flow_type = empty($_REQUEST['flow_type']) ? 'tuan' : test_input($_REQUEST['flow_type']);
	$express_id = empty($_REQUEST['express_id']) ? 'null' : test_input($_REQUEST['express_id']);
	$shipping_id = empty($_REQUEST['shipping_id']) ? 'null' : test_input($_REQUEST['shipping_id']);
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);

	if ($express_id == 0 || $express_id == 'undefined') {
		$express_id = 'null';
	}

	if ($shipping_id == 0 || $shipping_id == 'undefined') {
		$shipping_id = 'null';
	}

	if ($express_id == 'null' && $shipping_id == 'null') {
		$res = array('err'=>1,'msg'=>'配送方式不存在！');
        exit(json_encode($res));
	}

	// 用户默认收货地址

    $sql = "SELECT u.address_id,a.* FROM ".$GLOBALS['ecs']->table('users')." AS u,".$GLOBALS['ecs']->table('user_address')." AS a WHERE u.user_id='$uid' AND a.address_id=u.address_id";

    $user_default_address = $GLOBALS['db']->getRow($sql);

    if (!$user_default_address) {
    	$res = array('err'=>2,'msg'=>'请设置您的收货地址！');
        exit(json_encode($res));
    }

	if ($flow_type == 'tuan') {
		// 接受商品ID
		$goods_id = empty($_REQUEST['goods_id']) ? 0 : test_input($_REQUEST['goods_id']);//商品ID
		$number = empty($_REQUEST['number']) ? 0 : test_input($_REQUEST['number']);//购买数量
		$attr_id    = isset($_REQUEST['attr'])&&!empty($_REQUEST['attr'])&&$_REQUEST['attr']!='undefined' ? explode(',', $_REQUEST['attr']) : 'null';//attr列表用逗号分隔
		// 计算订单费用
	    // 读取商品的重量，数量，价格
	    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id='$goods_id'";
    	$goods_info = $GLOBALS['db']->getRow($sql);

    	if (!$goods_info) {
	    	$res = array('err'=>3,'msg'=>'网络异常！');
			exit(json_encode($res));
    	}

	    $goods_list = array();
	    $goods_list[0] = $goods_info;
	    $goods_list[0]['weight'] = $goods_info['goods_weight'];
	    $goods_list[0]['amount'] = $goods_info['shop_price'];
	    $goods_list[0]['number'] = $number;

	    $_SESSION['goods_suppliers_id'] = $goods_info['suppliers_id'];

	    $order_fee = order_fee2($goods_list,$user_default_address,$express_id,$shipping_id);

	    // 加入属性价格计算
	    if ($attr_id != 'null') {
	    	$shop_price  = get_final_price($goods_id, $number, true, $attr_id);//最终价格

		    // 计算团购价格
		    if ($goods_info['team_price']>0){
		        $attr_price  = spec_price($attr_id,true);
		        $team_price  = $goods_info['team_price'] + $attr_price;
		        $price_res['team_price'] = price_format($team_price * $number);
		    }
		    else
		    {
		    	$price_res['team_price'] = 0;
		    }
	    }
	    else
	    {
	    	$price_res['team_price'] = $goods_info['team_price'] * $number;
	    }

	    $order_fee['team_price'] = $order_fee['shipping_fee'] + $price_res['team_price'];

		$res = array('err'=>0,'order_fee'=>$order_fee);
	    exit(json_encode($res));

	}
	elseif ($flow_type == 'one')
	{
		$suppliers_id = empty($_REQUEST['suppliers_id']) ? 0 : test_input($_REQUEST['suppliers_id']);

		// 获取购物车的商品列表,链表查询商品的重量
		$sql = "SELECT c.*,c.goods_price as shop_price,g.goods_weight,g.team_price,g.suppliers_id FROM ".$GLOBALS['ecs']->table('cart')." AS c,".$GLOBALS['ecs']->table('goods')." AS g WHERE c.checked=1 AND c.user_id='$uid' AND c.suppliers_id='$suppliers_id' AND g.goods_id=c.goods_id";

    	$goods_list = $GLOBALS['db']->getAll($sql);

    	foreach ($goods_list as $key => $value) {
    		$goods_list[$key]['number'] = $goods_list[$key]['goods_number'];
    		$goods_list[$key]['weight'] = $goods_list[$key]['goods_weight'];
    		$goods_list[$key]['amount'] = $goods_list[$key]['shop_price'];
    	}

    	// 将商品列表以店铺ID分类，循环商品列表并计算费用

    	$new_goods_list = array();
    	foreach ($goods_list as $key => $value) {
    		$suppliers_id = $goods_list[$key]['suppliers_id'];
    		$new_goods_list[$suppliers_id][] = $goods_list[$key];
    	}

    	$order_fee['shipping_fee'] = 0;//运费
    	$order_fee['goods_price'] = 0;//商品总额

    	foreach ($new_goods_list as $suppliers_id => $value) {
    		// 开始计算费用
    		$_SESSION['goods_suppliers_id'] = $suppliers_id;

    		$fee = order_fee2($new_goods_list[$suppliers_id],$user_default_address,$express_id,$shipping_id);

    		// 累计运费及商品总额

    		$order_fee['shipping_fee'] += $fee['shipping_fee'];
    		$order_fee['goods_price'] += $fee['goods_price'];
    	}

		$res = array('err'=>0,'order_fee'=>$order_fee);
	    exit(json_encode($res));
	}

	
}

function action_tuan_done()
{

	include_once('includes/lib_clips.php');
	require(ROOT_PATH . 'includes/lib_order.php');

	/* 载入语言文件 */

	require_once(ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/user.php');

	require_once(ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/shopping_flow.php');

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : test_input($_REQUEST['goods_id']);
	$express_id = empty($_REQUEST['express_id']) ? 'null' : test_input($_REQUEST['express_id']);
	$shipping_id = empty($_REQUEST['shipping_id']) ? 'null' : test_input($_REQUEST['shipping_id']);
	$pay_id = empty($_REQUEST['pay_id']) ? 0 : test_input($_REQUEST['pay_id']);
	$number = empty($_REQUEST['number']) ? 0 : test_input($_REQUEST['number']);//购买数量
	$postscript = empty($_REQUEST['postscript']) ? '' : test_input($_REQUEST['postscript']);
	$team_sign = empty($_REQUEST['team_sign']) ? '' : test_input($_REQUEST['team_sign']);//是否是参团

	$point_id = empty($_REQUEST['point_id']) ? 0 : test_input($_REQUEST['point_id']);//自提点
	$checked_mobile = empty($_REQUEST['checked_mobile']) ? 0 : test_input($_REQUEST['checked_mobile']);//自提手机号
	$best_time = empty($_REQUEST['best_time']) ? '送货时间不限' : test_input($_REQUEST['best_time']);//时间

	$attr_id    = isset($_REQUEST['attr'])&&!empty($_REQUEST['attr'])&&$_REQUEST['attr']!='undefined' ? explode(',', $_REQUEST['attr']) : 'null';//attr列表用逗号分隔

	if ($team_sign == 'undefined' || $team_sign == 0) {
		$team_sign = '';
	}

	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'登录已失效,请重新登录！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录失效,请重新登录！');
        exit(json_encode($res));
    }

    if (!$goods_id) {
    	$res = array('err'=>3,'msg'=>'网络异常,请重试');
        exit(json_encode($res));
    }

    // 用户默认收货地址

    $sql = "SELECT u.address_id,a.* FROM ".$GLOBALS['ecs']->table('users')." AS u,".$GLOBALS['ecs']->table('user_address')." AS a WHERE u.user_id='$uid' AND a.address_id=u.address_id";

    $user_default_address = $GLOBALS['db']->getRow($sql);

    if (!$user_default_address) {
    	$res = array('err'=>2,'msg'=>'请设置您的收货地址！');
        exit(json_encode($res));
    }

    // 计算订单费用
    // 读取商品的重量，数量，价格
    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id='$goods_id'";
	$goods_info = $GLOBALS['db']->getRow($sql);

	if (!$goods_info) {
    	$res = array('err'=>3,'msg'=>'商品不存在！');
		exit(json_encode($res));
	}

    $goods_list = array();
    $goods_list[0] = $goods_info;
    $goods_list[0]['weight'] = $goods_info['goods_weight'];
    $goods_list[0]['amount'] = $goods_info['shop_price'];
    $goods_list[0]['number'] = $number;

    $_SESSION['goods_suppliers_id'] = $goods_info['suppliers_id'];

    $order_fee = order_fee2($goods_list,$user_default_address,$express_id,$shipping_id);

    // 加入属性价格计算
    if ($attr_id != 'null') {
    	$shop_price  = get_final_price($goods_id, $number, true, $attr_id);//最终价格

	    // 计算团购价格
	    if ($goods_info['team_price']>0){
	        $attr_price  = spec_price($attr_id,true);
	        $team_price  = $goods_info['team_price'] + $attr_price;
	        $price_res['team_price'] = price_format($team_price * $number);

	        $price_res['goods_attr'] = get_goods_attr_str($attr_id);//当前选择的属性
	    }
	    else
	    {
	    	$price_res['team_price'] = 0;
	    	$price_res['goods_attr'] = '';//当前选择的属性
	    }
    }
    else
    {
    	$price_res['goods_attr'] = '';//当前选择的属性
    	$price_res['team_price'] = $goods_info['team_price'] * $number;
    }

    $order_fee['team_price'] = $order_fee['shipping_fee'] + $price_res['team_price'];

    // 获取用户是否有，有此商品的未成团订单，如果有，直接跳转到分享页面

    $sql = "SELECT o.order_id,o.team_num,o.team_sign,g.goods_id FROM ".$GLOBALS['ecs']->table('order_info')." AS o,".$GLOBALS['ecs']->table('order_goods')." AS g WHERE g.goods_id='$goods_id' AND g.order_id=o.order_id AND o.team_status=1 AND o.user_id='$uid'";

    $user_team_list = $GLOBALS['db']->getRow($sql);

    if ($user_team_list && $team_sign == '') {
    	$res = array('err'=>4,'msg'=>'您已有此商品的未成团订单,将为您跳转！','user_team'=>$user_team_list['team_sign']);
		exit(json_encode($res));
    }

    /* 检查商品库存 */

    /* 如果使用库存，且下订单时减库存，则减少库存 */

    if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE)

    {
    	foreach ($goods_list as $key => $value) {
    		if ($value['goods_number'] >= $number) {
    			$sql = "UPDATE ".$GLOBALS['ecs']->table('goods')." SET goods_number=goods_number-'$number' WHERE goods_id='{$value['goods_id']}'";
    			$GLOBALS['db']->query($sql);
    		}
    		else
    		{
    			$res = array('err'=>5,'msg'=>'商品'.$value['goods_name'].'库存不足！');
				exit(json_encode($res));
    		}
    	}
    }

    // 获取配送方式信息 express_id

    if ($express_id == 'null') {
    	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('shipping')." WHERE shipping_id='$shipping_id'";

    	$shipping_info = $GLOBALS['db']->getRow($sql);
    }
    else
    {
    	$shipping_info = $GLOBALS['db']->getRow('SELECT * FROM '.$GLOBALS['ecs']->table('goods_express').' WHERE id=' . $express_id);
    }

    // 获取支付方式信息
    $pay_code = '';

    if ($pay_id == 1) {
    	// 读取微信支付的配置信息
    	$pay_code = 'wxpay';
    }
    else if ($pay_id == 2) {
    	// 余额
    	$pay_code = 'balance';
    }
    else
    {
    	$res = array('err'=>6,'msg'=>'您选择的支付方式找不到了');
		exit(json_encode($res));
    }

    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_code='$pay_code'";

	$pay_info = $GLOBALS['db']->getRow($sql);

   
    $order = array(

    'shipping_id'     =>intval($shipping_info['shipping_id']),

    'shipping_name'     =>$shipping_info['shipping_name'],

    'suppliers_id'     =>$goods_info['suppliers_id'],

    'point_id'         =>$point_id,

    'pay_id'          => intval($pay_info['pay_id']),

    'pack_id'         => 0,

    'card_id'         => 0,

    'card_message'    => '',

    'surplus'         => 0.00,

    'integral'        => 0,

    'bonus_id'        => 0,

    'need_inv'        => 0,

    'inv_type'        => 0,

    'inv_payee'       => 0,

    'inv_content'     => 0,

    'postscript'      => $postscript,

    'how_oos'         => '等待所有商品备齐后再发',

    'user_id'         => $uid,

    'is_miao'         => 0, //秒杀产品

    'is_luck'         => 0, //抽奖产品

    'luck_times'      => 0, //抽奖产品

    'add_time'        => gmtime(),

    'order_status'    => OS_UNCONFIRMED,

    'shipping_status' => SS_UNSHIPPED,

    'pay_status'      => PS_UNPAYED,

    'agency_id'       => 0,

    'lat'           =>0,

    'lng'           =>0,

    'city_id'           =>intval($user_default_address['city']),

    'district_id'           =>intval($user_default_address['district']),

    'consignee' => $user_default_address['consignee'],
    'email' => $user_default_address['email'],
    'country' => $user_default_address['country'],
    'province' => $user_default_address['province'],
    'city' => $user_default_address['city'],
    'district' => $user_default_address['district'],
    'address' => $user_default_address['address'],
    'zipcode' => $user_default_address['zipcode'],
    'tel' => $user_default_address['tel'],
    'mobile' => $user_default_address['mobile'],

    'package_one'    =>0,
    'checked_mobile' => $checked_mobile,
    'best_time' => $best_time

    );

    if($shipping_info['shipping_code'] != 'cac')
    {
        $order['point_id'] = 0;
    }

    if (! empty($order['checked_mobile'])) {

        $GLOBALS['db']->query('update '.$GLOBALS['ecs']->table('user_address').' set `mobile` = "'.$order['checked_mobile'].'" where user_id = "'.$uid.'" AND address_id = "'.$user_default_address['address_id'].'"');

    }

	$order['order_type'] = 2;//团购
	$order['extension_code'] = 'team_goods';//团购
	$order['extension_id'] = $goods_id;

	// 费用信息$order_fee

	$order['bonus']        = 0;

    $order['goods_amount'] = $price_res['team_price'];//商品团购总额

    $order['discount']     = 0;

    $order['shipping_fee']     = $order_fee['shipping_fee'];//运费
    $order['insure_fee']   = 0;
    $order['money_paid']   = 0;//已付款金额

    $order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'];
	

	// 扩展信息
	$order['team_sign'] = $team_sign;//是否是参团


	// 获取会员信息
	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
	$user_info = $GLOBALS['db']->getRow($sql);

	// 如果是余额支付，检查余额是否足够，并修改订单的状态已确认、已付款和余额支付额度
	if ($pay_id == 2) {

		$user_money = $user_info['user_money'];

		if ($user_money < $order['order_amount']) {

    		$order['surplus'] = $user_money;//余额支付额度
    		$order['money_paid'] = $user_money;//已支付
    		$order['order_amount'] = $order['order_amount'] - $user_money;//待支付

    		/*log_account_change($uid, (- 1) * $user_money, 0, 0, 0, '订单付款:'.$order_id);//改变账户

    		$res = array('err'=>6,'msg'=>'您的余额不足,订单已结'.$user_money.'元,剩余:'.$order['order_amount'].'元应付。');
			exit(json_encode($res));*/
    	}
    	else
    	{

    		$order['surplus'] = $order['order_amount'];//余额支付额度
    		$order['money_paid'] = $order['order_amount'];//已支付
    		$order['order_amount'] = 0;//待支付

    		/*log_account_change($uid, (- 1) * $order['order_amount'], 0, 0, 0, '订单付款:'.$order_id);//改变账户

    		$res = array('err'=>6,'msg'=>'使用余额支付成功,共'.$amount_sum.'元');
			exit(json_encode($res));*/
    	}
	}

	/* 如果订单金额为0（使用余额或积分或优惠劵支付），修改订单状态为已确认、已付款 */

    if ($order['order_amount'] <= 0)

    {

        $order['order_status'] = OS_CONFIRMED;

        $order['confirm_time'] = gmtime();

        $order['pay_status']   = PS_PAYED;

        $order['pay_time']     = gmtime();

        $order['order_amount'] = 0;

    }

	/* 插入订单表 */

    $error_no = 0;

    do

    {

        $order['order_sn'] = get_order_sn(); //获取新订单号

        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

        $error_no = $GLOBALS['db']->errno();

        if ($error_no > 0 && $error_no != 1062)

        {

            $res = array('err'=>8,'msg'=>'提交订单失败,下单错误！');
			exit(json_encode($res));

        }

    }

    while ($error_no == 1062); //如果是订单号重复则重新提交数据

    $new_order_id = $GLOBALS['db']->insert_id();

    $order['order_id'] = $new_order_id;

    foreach ($goods_list as $key => $value) {
    	//订单信息入order_goods表
		$sql = "INSERT INTO " . $GLOBALS['ecs']->table('order_goods'). "( " .

		                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

		                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) ".

		            " VALUES( '$new_order_id', '{$goods_list[$key]['goods_id']}', '{$goods_list[$key]['goods_name']}', '{$goods_list[$key]['goods_sn']}', 0, '{$goods_list[$key]['number']}', '{$goods_list[$key]['market_price']}', ".

		                "'{$goods_list[$key]['team_price']}', '{$price_res['goods_attr']}', 1, '', 0, 0, '$attr_id' )";

	    $GLOBALS['db']->query($sql);
    }

    // 处理拼团订单的资料

    if($order['team_sign'] == ''){

        $sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET team_sign=".$order['order_id'].",team_first=1,team_num='{$goods_info['team_num']}'  WHERE order_id=".$order['order_id'];

        $GLOBALS['db']->query($sql);

        $order['team_sign']=$order['order_id'];

        $order['team_first']=1;

    }else{

        $sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET team_first=2  WHERE order_id=".$order['order_id'];

        $GLOBALS['db']->query($sql);

        $order['team_first']=2;

    }

    /* 处理余额、积分、优惠劵 */

    if ($order['user_id'] > 0 && $order['surplus'] > 0)

    {

        log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));

    }

    // 团购 team_sign

    if ($order['team_sign'] == '') {
		// 参团，将team_sign反馈到前台
		$team_sign = $order['order_id'];
	}
	else
	{
		$team_sign = $order['team_sign'];
	}

	/*插入一条支付记录*/
    // $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

    // 在线支付
	if ($order['order_amount'] > 0) {
		// 使用在线支付，支付$order['order_amount']

		// 取用户的open_id
    	$sql = "SELECT wx_open_id FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
    	$openId = $GLOBALS['db']->getOne($sql);

    	if (!$openId) {
    		// 不是微信注册用户,获取用户的open_id
			$user_code = empty($_REQUEST['user_code']) ? '' : test_input($_REQUEST['user_code']);
    		if ($user_code == '' || $user_code == 0) {
    			$res = array('err'=>6,'msg'=>'获取您的授权信息失败,无法使用微信支付！');
				exit(json_encode($res));
    		}
    		else
    		{
    			$url='https://api.weixin.qq.com/sns/jscode2session?appid='.$GLOBALS['xcx_config']['appid'].'&secret='.$GLOBALS['xcx_config']['appsecret'].'&js_code='.$user_code.'&grant_type=authorization_code';

				$wx_res = file_get_contents($url);

				$wx_res = json_decode($wx_res,true);

				if (isset($wx_res['errcode'])) {
					// code失效
					if ($wx_res['errcode']== '40029') {
						$res = array('err' => 7,'msg'=>'获取信息授权已失效，请重试！');
						echo json_encode($res);
						return;
					}
					else
					{
						$res = array('err' => 7,'msg'=>'获取支付信息失败，请重试！');
						echo json_encode($res);
						return;
					}
				}
				else
				{
					$res = array('err' => 3,'msg'=>'获取支付信息失败，请重试！');
				}

				$openId = $wx_res['openid'];
    		}
    	}

    	// 至此已拿到用户的open_id,统一下单
    	// 引入api类
    	require_once dirname(__FILE__)."/wxpayAPI/lib/WxPay.Api.php";
		require_once dirname(__FILE__)."/wxpayAPI/example/WxPay.JsApiPay.php";
		require_once dirname(__FILE__).'/wxpayAPI/example/log.php';
		//初始化日志
		$logHandler= new CLogFileHandler(dirname(__FILE__)."/wxpayAPI/logs/".date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 15);

		$Time_expire = (string)local_date("YmdHis", time() + 800);
		
		$input = new WxPayUnifiedOrder();
		$input->SetBody($GLOBALS['xcx_config']['xcx_name'].$order['order_sn']);
		$input->SetAttach($GLOBALS['xcx_config']['xcx_name']."小程序支付");
		$input->SetOut_trade_no($order['order_id']."e".$Time_expire);
		$input->SetTotal_fee($order['order_amount'] * 100);
		$input->SetTime_start(local_date("YmdHis"));
		$input->SetTime_expire($Time_expire);
		// $input->SetGoods_tag("test");
		$input->SetNotify_url("http://".$GLOBALS['xcx_config']['url']."/xcx/notify2.php");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);

		/*print_r($order);

		exit();*/

		$order_new['order_id'] = $new_order_id;
		$order_new['timeStamp'] = time();
		$order_new['nonceStr'] = md5($order['nonce_str']);
		$order_new['signType'] = 'MD5';
		$order_new['package'] = "prepay_id=".$order['prepay_id'];

		unset($order);
		
		// 拼接签名
		$stringA = "appId=".$GLOBALS['xcx_config']['appid']."&nonceStr=".$order_new['nonceStr']."&package=".$order_new['package']."&signType=".$order_new['signType']."&timeStamp=".$order_new['timeStamp'];

		$stringSignTemp = $stringA."&key=".$GLOBALS['xcx_config']['key'];

		$order_new['paySign'] = strtoupper(md5($stringSignTemp));//转换为大写

		$order_new['stringSignTemp'] = $stringSignTemp;



		if ($pay_id == 2) {
			$res = array('err'=>0,'msg'=>'订单提交成功 余额不足 请使用微信支付!','pay_id'=>$pay_id,'order'=>$order_new,'team_sign'=>$team_sign);
			exit(json_encode($res));
		}
		else
		{
			$res = array('err'=>0,'msg'=>'订单提交成功!','pay_id'=>$pay_id,'order'=>$order_new,'team_sign'=>$team_sign);
			exit(json_encode($res));
		}
	}
	else
	{
		// 处理拼团信息
		// 处理库存
		foreach ($goods_list as $key => $value) {
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('goods')." SET goods_number=goods_number-'{$value['number']}' WHERE goods_id='{$value['goods_id']}'";
			$GLOBALS['db']->query($sql);
    	}

    	$order_info = $order;

    	if($order_info['extension_code']=='team_goods'){

    		$team_sign=$order_info['team_sign'];

    		$sql="select team_num from ".$GLOBALS['ecs']->table('order_info') ." where order_id=".$order_info['team_sign'];

        	$team_num=$GLOBALS['db']->getOne($sql);

        	//团共需人数和状态

        	$sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET team_status=1,team_num='$team_num' WHERE order_id=".$order_info['order_id'];

        	$GLOBALS['db']->query($sql);

        	// 团购方式，以订单号为依据

            $sql="select count(*) from ".$GLOBALS['ecs']->table('order_info')." where team_sign=".$team_sign." and team_status>0 ";

            $rel_num=$GLOBALS['db']->getOne($sql);

            //存储实际人数

	        $sql="update ".$GLOBALS['ecs']->table('order_info')." set teammen_num='$rel_num' where team_sign=".$team_sign;

	        $GLOBALS['db']->query($sql);

	        if($team_num<=$rel_num){
	        	$sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET team_status=2 WHERE team_status=1 and team_sign=".$team_sign;

	            $GLOBALS['db']->query($sql);

	            //取消未参团订单

	            $sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$team_sign;

	            $GLOBALS['db']->query($sql);

	            //判断团长是否有优惠，要重新取数据

	            /*$sql="select order_id,user_id,refund_sign,discount_type, transaction_id, discount_amount, money_paid,order_amount,order_sn,shared_allow,shared_money from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$team_sign;

                $r=$GLOBALS['db']->getRow($sql);*/
	        }

    	}

    	$msg = '提交订单成功!';

    	if ($order_info['surplus'] > 0) {
    		$msg = '订单使用余额支付成功，总计:￥'.$order_info['surplus'].'元';
    	}

		$res = array('err'=>89,'msg'=>$msg,'pay_id'=>0,'order'=>$order_info,'team_sign'=>$order_info['team_sign']);
		exit(json_encode($res));
	}

}

/*分享团和参团*/
function action_share_tuan()
{
	require_once(ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/user.php');
	include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_image.php');
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$team_sign = empty($_REQUEST['team_sign']) ? 0 : test_input($_REQUEST['team_sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'登录已失效,请重新登录！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录失效,请重新登录！');
        exit(json_encode($res));
    }

    if (!$team_sign) {
    	$res = array('err'=>3,'msg'=>'网络异常,请重试');
        exit(json_encode($res));
    }

    $sql="select * from ".$GLOBALS['ecs']->table('users')." where user_id=".$uid;
	$user_info=$GLOBALS['db']->getRow($sql);

	//$is_team是否可以参团 $is_teammen是否在团里
	$is_team=0;
	$is_teammen = 0;
	$is_loding_pay = 0;//是否是已参团等待支付

	$sql="SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." where team_sign=".$team_sign." and team_first=1 " ;
	$team_info=$GLOBALS['db']->getRow($sql);

	if (!$team_info) {
		$res = array('err'=>4,'msg'=>'团购信息不存在！');
        exit(json_encode($res));
	}

	if($team_info['pay_time'] && ($team_info['pay_time']+$GLOBALS['_CFG']['team_suc_time']*24*3600<gmtime())&&$team_info['team_status']==1 && $team_info['is_luck'] == 0 && $team_info['is_miao'] == 0)
    {
        //处理退款
        // do_team_refund($team_sign);
        // 团状态

        // echo "退款";

        xcx_team_refund($team_sign);

        action_share_tuan();

        exit();

        /*$sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET team_status=3,order_status=2 WHERE team_status=1 and team_sign=".$team_sign;

        $GLOBALS['db']->query($sql);

        //订单状态

        $sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$team_sign;

        $GLOBALS['db']->query($sql);
        $team_info['team_status'] = 0;*/
    }

    if($team_info['team_status']!=1)
    {
	    $is_team=0;
	}

	$sql=" SELECT count(*) FROM ".$GLOBALS['ecs']->table('order_info')." where team_sign=".$team_sign." and team_status>0" ;
	$count=$GLOBALS['db']->getOne($sql);

	//还差多少人
	$team_info['d_num'] = $team_info['team_num']-$count;//还缺多少人
	$team_info['db_num'] = $team_info['team_num']-$team_info['teammen_num'];//也是还缺多少人
	$team_info['d_num_arr'] = array();

	for($i=0;$i<$team_info['d_num'];$i++)
    {
		$team_info['d_num_arr'][]=$i;
	}

	//用户是否参团
	$sql=" SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." where team_sign=".$team_sign ." and user_id=".$uid;
	$row=$GLOBALS['db']->getRow($sql);

	if(empty($row))
    {
    	// 未查到数据，可以参团
	    $is_team=1;
	    $is_teammen = 0;//用户不在团内
	}
	else
	{
		$is_team=0;//不可以参团，已有订单
	    $is_teammen = 1;//用户在团内
	    if($row['pay_status']==0&&$row['team_status']==0&&($row['order_status']==0||$row['order_status']==1))
        {
	        // 已参团，等待支付
	        $is_loding_pay = $row['order_id'];
	    }
	}

	/* 订单商品 */
    $goods_list = order_goods($team_sign);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
		$goods_id = $goods_list[$key]['goods_id'];
    }

    $goods_info = goods_info($goods_id);

    if (!$goods_info['goods_thumb']) {
		$goods_info['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
	}
	else
	{
		$goods_info['goods_thumb'] ='http://'.$GLOBALS['xcx_config']['url'].'/'.$goods_info['goods_thumb'];
	}

    //参团的人
    $sql="select u.user_name,u.uname,u.uname,u.headimgurl,u.headimg,o.pay_time,o.team_first,o.is_lucker from ".$GLOBALS['ecs']->table('order_info')." as o left join ".$GLOBALS['ecs']->table('users')." as u on o.user_id=u.user_id where team_sign=".$team_sign." order by order_id ";
    $team_mem=$GLOBALS['db']->getAll($sql);

    // print_r($team_mem);

    foreach($team_mem as $k=>$v)
    {
        $team_mem[$k]['date']=local_date('Y-m-d H:i:s',$v['pay_time']);
    }

    $team_start=$team_mem[0]['pay_time'];//开团时间

    $systime = gmtime();//当前时间
    $team_suc_time = $GLOBALS['_CFG']['team_suc_time'] * 86400;//团购限时-总秒数

    // 计算本团购剩余天数
    $s_miao = ($team_suc_time+$team_start)-$systime;//限时剩余时间总秒数

    $res = array('err'=>0,'user_info'=>$user_info,'is_team'=>$is_team,'is_teammen'=>$is_teammen,'is_loding_pay'=>$is_loding_pay,'team_info'=>$team_info,'goods_info'=>$goods_info,'team_mem'=>$team_mem,'team_start'=>$team_start,'team_suc_time'=>$team_suc_time,'s_miao'=>$s_miao);
    exit(json_encode($res));
}

/*
收藏商品
*/
function action_goods_collect(){
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : test_input($_REQUEST['goods_id']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'登录已失效,请重新登录！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录失效,请重新登录！');
        exit(json_encode($res));
    }

    if (!$goods_id) {
    	$res = array('err'=>3,'msg'=>'网络异常,请重试');
        exit(json_encode($res));
    }

    $sql = "SELECT rec_id FROM ".$GLOBALS['ecs']->table('collect_goods')." WHERE goods_id='$goods_id' AND user_id='$uid'";
    if ($GLOBALS['db']->getOne($sql)) {

		// 取消收藏
		$sql = "DELETE FROM ".$GLOBALS['ecs']->table('collect_goods')." WHERE goods_id='$goods_id' AND user_id='$uid'";
		if ($GLOBALS['db']->query($sql)) {
			$res = array('err'=>0,'user_is_collect'=>0);
        	exit(json_encode($res));
		}
		else
		{
			$res = array('err'=>4,'user_is_collect'=>1,'msg'=>'网络异常,取消收藏失败');
        	exit(json_encode($res));
		}
	}
	else
	{
		// 收藏起来
		$time = time();
		$sql = "INSERT INTO ".$GLOBALS['ecs']->table('collect_goods')." (`user_id`,`goods_id`,`add_time`,`is_attention`) VALUES ('$uid','$goods_id','$time',1)";
		if ($GLOBALS['db']->query($sql)) {
			$res = array('err'=>0,'user_is_collect'=>1);
        	exit(json_encode($res));
		}
		else
		{
			$res = array('err'=>3,'user_is_collect'=>0,'msg'=>'网络异常,收藏失败');
        	exit(json_encode($res));
		}
	}
}

/*------------------------------------------------------ */
//-- 改变属性、数量时重新计算商品价格
/*------------------------------------------------------ */

function action_change_attr()
{
    $attr_id    = isset($_REQUEST['attr'])&&!empty($_REQUEST['attr']) ? explode(',', $_REQUEST['attr']) : array();//attr列表用逗号分隔
    $number     = (isset($_REQUEST['number'])) ? intval($_REQUEST['number']) : 1;//商品数量
	$goods_id = empty($_REQUEST['goods_id']) ? 0 : test_input($_REQUEST['goods_id']);//商品ID
	$flow_type = empty($_REQUEST['flow_type']) ? 0 : test_input($_REQUEST['flow_type']);//购买类型，tuan团购，one单独购买


    if ($goods_id == 0)
    {
        $res = array('err'=>0,'msg'=>'网络异常,请重试!');
    	exit(json_encode($res));
    }
    else
    {
    	$sql = "SELECT goods_id,team_price FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id='$goods_id'";

    	$goods = $GLOBALS['db']->getRow($sql);

        if ($number == 0)
        {
            $number = 1;
        }
        

        $shop_price  = get_final_price($goods_id, $number, true, $attr_id);//最终价格
        $res['result'] = $shop_price * $number;//商品价格

        $res['goods_attr_number'] = get_product_attr_num($goods_id,$_REQUEST['attr']);//库存

        $res['goods_attr'] = get_goods_attr_str($attr_id);//当前选择的属性

        // 计算团购价格
        if ($goods['team_price']>0){
            $attr_price  = spec_price($attr_id,true);
            $team_price  = $goods['team_price'] + $attr_price;
            $res['team_price'] = price_format($team_price * $number);
        }
        else
        {
        	$res['team_price'] = 0;
        }
    }


    $goods_properties = get_goods_properties($goods_id);//处理已选属性
    
    if (!$goods_properties['spe']) {
		$goods_properties['spe'] = 'null';
	}
	else
	{
	    foreach ($goods_properties['spe'] as $key => $value) {
	    	$attr_values = $goods_properties['spe'][$key]['values'];

	    	$goods_properties['spe'][$key]['attr_id'] = $key;

	    	foreach ($attr_values as $skey => $svalue) {
	    		$id = $attr_values[$skey]['id'];
	    		$search = array_search($id,$attr_id);
	    		// echo $search;
	    		if ($search !== false) {
	    			// 搜索到，说明该属性已被选择
	    			$goods_properties['spe'][$key]['values'][$skey]['attr_item_select'] = 'attr_item_select';
	    		}
	    		else
	    		{
	    			$goods_properties['spe'][$key]['values'][$skey]['attr_item_select'] = '';
	    		}
	    		
	    	}

		}
	}




	$res = array('err'=>0,'res'=>$res,'number'=>$number,'goods_properties'=>$goods_properties);
	exit(json_encode($res)); 	
}

// 商品入购物车
function action_to_cart()
{
	
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	$is_miao = empty($_REQUEST['is_miao']) ? 0 : test_input($_REQUEST['is_miao']);

	if ($is_miao == 1) {
		require('../includes/lib_orders.php');
	}
	else
	{
		require('../includes/lib_order.php');
	}
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $is_spec = empty($_REQUEST['is_spec']) ? 0 : test_input($_REQUEST['is_spec']);//是否有规格
    $goods_id = empty($_REQUEST['goods_id']) ? 0 : test_input($_REQUEST['goods_id']);//商品id
    $number = empty($_REQUEST['number']) ? 0 : test_input($_REQUEST['number']);//购买数量
    $spec = empty($_REQUEST['spec']) ? 'null' : test_input($_REQUEST['spec']);//选择的属性数组
    $is_liji_flow = empty($_REQUEST['is_liji_flow']) ? 0 : test_input($_REQUEST['is_liji_flow']);//是否直接跳转到购物车


    if ($goods_id == 0) {
    	$res = array('err'=>3,'msg'=>'网络异常,请刷新重试！');
        exit(json_encode($res));
    }

    /* 检查：商品数量是否合法 */
    if (!is_numeric($number) || intval($number) <= 0)
    {
        $res = array('err'=>4,'msg'=>'商品数量不能为0');
        exit(json_encode($res));
    }


	if ($is_spec != 0 && $spec == 'null') {
		$res = array('err'=>6,'msg'=>'您还没有选择规格。');
        exit(json_encode($res));
	}
	
	// 处理属性
	if($spec != 'null')
    {
        $spec = explode(',', $spec);
    }
    else
    {
    	$spec = array();
    }
    $_SESSION['user_id'] = $uid;
    // 更新：添加到购物车
    if (addto_cart($goods_id, $number, $spec, 0))
    {
        // 添加成功

    	if ($is_liji_flow == 1) {
    		// 取消该用户所有购物车商品的选中状态
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET checked=0 WHERE checked=1 AND user_id='$uid'";

    		$GLOBALS['db']->query($sql);

    		// 选中当前商品
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('cart')." SET checked=1 WHERE goods_id='$goods_id' AND user_id='$uid'";

    		$GLOBALS['db']->query($sql);
    	}

        $res = array('err'=>0,'msg'=>'添加成功了!');
        exit(json_encode($res));
    }
    else
    {
    	// 添加失败
        $result['message']  = $GLOBALS['err']->last_message();
        $result['error']    = $GLOBALS['err']->error_no;
        $result['goods_id'] = stripslashes($goods_id);
        if (is_array($spec))
        {
            $result['product_spec'] = implode(',', $spec);
        }
        else
        {
            $result['product_spec'] = $spec;
        }
        $res = array('err'=>3,'msg'=>$result['message']);
        exit(json_encode($res));
    }
	    
}

/*
读取收货地址
*/
function action_address_list()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    // 从数据库取出列表
    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE user_id='$uid'";
    $user_address = $GLOBALS['db']->getAll($sql);

    if (!$user_address) {
    	$res = array('err'=>0,'user_address'=>'null');
        exit(json_encode($res));
    }

    $sql = "SELECT address_id FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";

    $user_default_address = $GLOBALS['db']->getOne($sql);

    

	foreach ($user_address as $key => $value) {
    	$address_id = $user_address[$key]['address_id'];
    	// 确认默认地址
    	if ($user_default_address) {
	    	if ($address_id == $user_default_address) {
	    		$user_address[$key]['is_default'] = 1;
	    	}
	    	else
	    	{
	    		$user_address[$key]['is_default'] = 0;
	    	}
	    }

	    // 读取各个收货地址的真名
	    if ($user_address[$key]['country']) {
	    	$user_address[$key]['country'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_address[$key]['country']}'");
	    }
	    if ($user_address[$key]['province']) {
	    	$user_address[$key]['province'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_address[$key]['province']}'");
	    }
	    if ($user_address[$key]['city']) {
	    	$user_address[$key]['city'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_address[$key]['city']}'");
	    }
	    if ($user_address[$key]['district']) {
	    	$user_address[$key]['district'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_address[$key]['district']}'");
	    }
    }

    

    
    $res = array('err'=>0,'user_address'=>$user_address);
    exit(json_encode($res));
    
}

/*
收货地址设为默认
*/
function action_set_address()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $address_id = empty($_REQUEST['address_id'])?0:intval($_REQUEST['address_id']);

    if($GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('users') . " set address_id = $address_id  WHERE user_id='$uid'")){
        $res = array('err'=>0,'msg'=>'默认地址设置成功！');
    	exit(json_encode($res));
    }
}

/*
编辑收货地址
*/
function action_edit_address()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$address_id = empty($_REQUEST['address_id'])?0:intval($_REQUEST['address_id']);

    if ($address_id == 0) {
        // 只返回地区列表

    	$user_address_new['consignee'] = '';
    	$user_address_new['mobile'] = '';
    	$user_address_new['address'] = '';
    	$user_address_new['zipcode'] = '';
    	$user_address_new['email'] = '';

        $res = array('err'=>0,'province_list'=>get_region(1),'user_address'=>$user_address_new);
        exit(json_encode($res));
    }

    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE address_id='$address_id'";

    $user_address = $GLOBALS['db']->getRow($sql);

    if (!$user_address) {
    	$res = array('err'=>4,'msg'=>'您要编辑的收货地址不存在!');
        exit(json_encode($res));
    }
    else
    {
    	
		if ($user_address['email'] == 'null') {
			$user_address['email'] = '';
		}
		if ($user_address['zipcode'] == 'null') {
			$user_address['zipcode'] = '';
		}
    	
    	$res = array('err'=>0,'user_address'=>$user_address,'province_list'=>get_region(1));
        exit(json_encode($res));
    }
}

function action_del_address()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	$address_id = empty($_REQUEST['address_id'])?0:intval($_REQUEST['address_id']);

	if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    if ($address_id == 0) {
    	$res = array('err'=>3,'msg'=>'被删除的地址不能为空喔！');
        exit(json_encode($res));
    }
    else
    {
    	$sql = "DELETE FROM ".$GLOBALS['ecs']->table('user_address')." WHERE address_id='$address_id'";

    	$GLOBALS['db']->query($sql);

    	$res = array('err'=>0,'msg'=>'删除地址成功！');
        exit(json_encode($res));
    }

}

/*
编码地区列表
parent_id 上级id
*/

function get_region($parent_id=1)
{
	$sql = "SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE parent_id='$parent_id'";

	$region_list = $GLOBALS['db']->getAll($sql);

	$region_list_new = array();

	foreach ($region_list as $key => $value) {
		$region_list_new[] = $region_list[$key]['region_name'];
	}

	return $region_list_new;
}

/*
解码地区列表，并返回该地区的所有下级
*/

function action_get_region()
{
	$parent_id = empty($_REQUEST['parent_id'])?1:intval($_REQUEST['parent_id']);//上级地区

	$address_id = empty($_REQUEST['address_id'])?0:intval($_REQUEST['address_id']);//用户选中的第几个
	

	$sql = "SELECT region_id FROM ".$GLOBALS['ecs']->table('region')." WHERE parent_id='$parent_id'";

	$region_list = $GLOBALS['db']->getAll($sql);

	$region_list_new = array();

	foreach ($region_list as $key => $value) {
		$region_list_new[] = $region_list[$key]['region_id'];
	}

	$region_id = $region_list_new[$address_id];//取到用户选中地区的id
	unset($region_list_new);

	$sql = "SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE parent_id='$region_id'";

	$region_list = $GLOBALS['db']->getAll($sql);

	$region_list_new = array();
	foreach ($region_list as $key => $value) {
		$region_list_new[] = $region_list[$key]['region_name'];
	}

	$res = array('err'=>0,'list'=>$region_list_new,'parent_id'=>$region_id);
    exit(json_encode($res));
}

/*
保存收货地址
*/

function action_save_address()
{
	include_once ('../includes/lib_transaction.php');

	// 接收所有变量
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $address['consignee'] = empty($_REQUEST['consignee']) ? 'null' : test_input($_REQUEST['consignee']);//收货人
    $address['mobile'] = empty($_REQUEST['mobile']) ? 'null' : test_input($_REQUEST['mobile']);//手机号
    $address['country'] = 1;//国家
    $address['province'] = empty($_REQUEST['province']) ? '0' : test_input($_REQUEST['province']);//省份
    $address['city'] = empty($_REQUEST['city']) ? '0' : test_input($_REQUEST['city']);//城市
    $address['district'] = empty($_REQUEST['district']) ? '0' : test_input($_REQUEST['district']);//地区
    $address['address'] = empty($_REQUEST['address']) ? 'null' : test_input($_REQUEST['address']);//详细地址
    $address['zipcode'] = empty($_REQUEST['zipcode']) ? 'null' : test_input($_REQUEST['zipcode']);//邮编
    $address['email'] = empty($_REQUEST['email']) ? 'null' : test_input($_REQUEST['email']);//邮箱
    $address['user_id'] = $uid;
    $address['address_id'] = empty($_REQUEST['address_id']) ? 0 : test_input($_REQUEST['address_id']);//被编辑的id

    $address['default'] = empty($_REQUEST['defalut']) ? 0 : test_input($_REQUEST['defalut']);//是否设为默认地址


    if ($address['consignee'] == 'null') {
    	$res = array('err'=>3,'msg'=>'您还没有填写收货人!');
    	exit(json_encode($res));
    }
    elseif ($address['mobile'] == 'null') {
    	$res = array('err'=>4,'msg'=>'您还没有手机号码!');
    	exit(json_encode($res));
    }
    elseif (!$address['province']) {
    	$res = array('err'=>5,'msg'=>'您还没有选择省份!');
    	exit(json_encode($res));
    }
    elseif (!$address['city']) {
    	$res = array('err'=>6,'msg'=>'您还没有选择城市!');
    	exit(json_encode($res));
    }
    elseif (!$address['district']) {
    	$res = array('err'=>7,'msg'=>'您还没有选择地区!');
    	exit(json_encode($res));
    }
    elseif ($address['address'] == 'null') {
    	$res = array('err'=>8,'msg'=>'您还没有填写详细地址!');
    	exit(json_encode($res));
    }

    if(update_address($address))
	{
		$res = array('err'=>0,'msg'=>'地址保存成功!');
    	exit(json_encode($res));
	}


}

/*
结算功能
*/
function action_flow()
{

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }


    // 返回默认收货地址，若无，让用户选择一个，若用户无收货地址，显示一个收货地址添加表单

    $sql = "SELECT u.address_id,a.* FROM ".$GLOBALS['ecs']->table('users')." AS u,".$GLOBALS['ecs']->table('user_address')." AS a WHERE u.user_id='$uid' AND a.address_id=u.address_id";

    $user_default_address = $GLOBALS['db']->getRow($sql);

    if (!$user_default_address) {
    	$res = array('err'=>5,'msg'=>'请设置您的收货地址！');
        exit(json_encode($res));
    }
    else
    {
    	// 读取各个收货地址的真名
	    if ($user_default_address['country']) {
	    	$user_default_address['country_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['country']}'");
	    }
	    if ($user_default_address['province']) {
	    	$user_default_address['province_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['province']}'");
	    }
	    if ($user_default_address['city']) {
	    	$user_default_address['city_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['city']}'");
	    }
	    if ($user_default_address['district']) {
	    	$user_default_address['district_name'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$user_default_address['district']}'");
	    }
    }

    // 取出要结算的商品列表

    $sql = "SELECT c.*,g.goods_thumb,g.suppliers_id FROM ".$GLOBALS['ecs']->table('cart')." AS c,".$GLOBALS['ecs']->table('goods')." AS g WHERE c.checked=1 AND c.user_id='$uid' AND g.goods_id=c.goods_id";

    $cart_list = $GLOBALS['db']->getAll($sql);

    $goods_amount = 0;//商品总费用

    if (!$cart_list) {
    	$cart_list = 0;//此时应提示用户，没有待结商品

    	$res = array('err'=>4,'msg'=>'请先在购物车勾选待结商品哦！','is_back'=>1);
    	exit(json_encode($res));
    }
    else
    {
    	foreach ($cart_list as $key => $value) {
    		// 处理商品图片
    		if ($cart_list[$key]['goods_thumb']) {
    			$cart_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$cart_list[$key]['goods_thumb'];
    		}
    		else
    		{
    			$cart_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
    		}

    		// 处理属性列表
	    	if ($cart_list[$key]['goods_attr']) {
	    		$cart_list[$key]['goods_attr'] = str_replace(PHP_EOL, ";", $cart_list[$key]['goods_attr']);
	    	}


	    	$goods_amount += $cart_list[$key]['goods_price'] * $cart_list[$key]['goods_number'];
    	}

    	// 根据店铺为商品分类

    	$new_cart_list = array();
    	foreach ($cart_list as $key => $value) {
    		$suppliers_id = $cart_list[$key]['suppliers_id'];
    		$new_cart_list[$suppliers_id]['goods_list'][] = $cart_list[$key];
    	}

    	// 获取店铺信息
    	foreach ($new_cart_list as $suppliers_id => $value) {
    		if ($suppliers_id > 0) {
    			$sql = "SELECT suppliers_name FROM ".$GLOBALS['ecs']->table('suppliers')." WHERE suppliers_id='$suppliers_id'";

    			$new_cart_list[$suppliers_id]['suppliers_name'] = $GLOBALS['db']->getOne($sql);

    			$new_cart_list[$suppliers_id]['suppliers_id'] = $suppliers_id;
    		}
    		else{
    			$new_cart_list[$suppliers_id]['suppliers_name'] = '自营店';
    			$new_cart_list[$suppliers_id]['suppliers_id'] = 0;

    		}
    	}

    }

    // 返回配送方式列表,有默认取默认，无默认取第一个
    // 返回配送方式列表
    include_once('includes/lib_orders.php');

    $region            = array($user_default_address['country'], $user_default_address['province'], $user_default_address['city'], $user_default_address['district']);

    foreach ($new_cart_list as $key => $value) {
    	// 循环获取配送方式列表
    	
    	if ($new_cart_list[$key]['suppliers_id'] > 0) {
    		$_SESSION['goods_suppliers_id'] = $new_cart_list[$key]['suppliers_id'];
    	}

    	$new_cart_list[$key]['shipping_list']     = available_shipping_list($region,$new_cart_list[$key]['suppliers_id']);

    	// 获取商品列表的邮费模板

    	// 商品邮费模板

    	$goods_express = $GLOBALS['db']->getAll('select * from '.$GLOBALS['ecs']->table('goods_express').' WHERE goods_id = ' . $new_cart_list[$key]['goods_list'][0]['goods_id'] .' AND region_id ' . db_create_in($region) .        ' group by shipping_id');

    	// 如果有设置邮费模板，替换配送方式列表
	    if(!empty($goods_express)){
	    	$new_cart_list[$key]['shipping_list'] = $goods_express;
	    	$new_cart_list[$key]['is_express'] = 1;
	    }
	    else
	    {
	    	$new_cart_list[$key]['is_express'] = 0;
	    }

	    $new_cart_list[$key]['point_list'] = 'null';

	    foreach ($new_cart_list[$key]['shipping_list'] as $skey => $shipping) {
	    	if ($shipping['shipping_code'] == 'cac') {

	            $point_list = available_point_list($region,$new_cart_list[$key]['suppliers_id']);

	            if (empty($point_list)) {

	                unset($new_cart_list[$key]['shipping_list'][$skey]);

	                $new_cart_list[$key]['point_list'] = 'null';

	            }
	            else
	            {
	            	$new_cart_list[$key]['point_list'] = $point_list;
	            }

	            break;

	        }
	    }

    }

    // 支付方式
    $payment_list = array();

    $sql = "SELECT enabled FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_code='wxpay'";

    if ($GLOBALS['db']->getOne($sql)) {
    	$payment_list[0]['pay_name'] = '微信支付';
    	$payment_list[0]['pay_id'] = 1;
    	$payment_list[0]['pay_code'] = 'wxpay';
    }

    $sql = "SELECT enabled FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_code='balance'";

    if ($GLOBALS['db']->getOne($sql)) {
    	$payment_list[1]['pay_name'] = '余额支付';
    	$payment_list[1]['pay_id'] = 2;
    	$payment_list[1]['pay_code'] = 'balance';
    }

    // ,'goods_suppliers_id'=>$_SESSION['goods_suppliers_id']
    $res = array('err'=>0,'user_default_address'=>$user_default_address,'cart_list'=>$new_cart_list,'payment_list'=>$payment_list,'goods_amount'=>$goods_amount);
    exit(json_encode($res));
}

function action_get_order_amount()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);//无需检查身份

	$address_id = empty($_REQUEST['address_id']) ? 0 : test_input($_REQUEST['address_id']);//用户选择的收货地址id


	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE address_id='$address_id'";

	$address = $GLOBALS['db']->getRow($sql);

    $region_id_list = array($address['country'],$address['province'],$address['city'],$address['district']);//收货地址的地区id列表


    // 获取商品列表
    $sql = "SELECT c.goods_price,c.goods_number,c.goods_attr_id,g.goods_name,g.goods_weight,g.is_shipping FROM ".$GLOBALS['ecs']->table('cart')." AS c,".$GLOBALS['ecs']->table('goods')." AS g WHERE c.checked=1 AND c.user_id='$uid' AND g.goods_id=c.goods_id";

    $cart_list = $GLOBALS['db']->getAll($sql);


	$cart_list_new = array();

    foreach ($cart_list as $key => $value) {
    	// 商家订单分类
		// $supplier_id = $cart_list[$key]['supplier_id'];

    	$cart_list_new[0][] = $cart_list[$key];
    }

    $shipping_fee = 0;//自营商品运费
    $amount_sum = 0;//商品总额
    $supp_shipping_fee = 0;//供应商商品运费

    foreach ($cart_list_new as $key => $value) {

    	$amount = 0;
    	$weight = 0;
    	
    	// 循环商品列表
    	$is_shipping = 0;//是否免运费
    	foreach ($cart_list_new[$key] as $ckey => $cvalue) {
    		$amount += $cart_list_new[$key][$ckey]['goods_price'] * $cart_list_new[$key][$ckey]['goods_number'];

    		// echo $cart_list_new[$key][$ckey]['goods_name']."=".$cart_list_new[$key][$ckey]['is_shipping'];

    		if ($cart_list_new[$key][$ckey]['is_shipping'] == 1) {
    			$weight += 0;
    		}
    		else
    		{
    			$weight += $cart_list_new[$key][$ckey]['goods_weight'] * $cart_list_new[$key][$ckey]['goods_number'];
    		}
    	}

    	$cart_list_new[$key]['amount'] = $amount;
    	$cart_list_new[$key]['weight'] = $weight;

    	$shipping_id = get_shipping_type();//获取第一个配送方式

    	$shipping_area = shipping_area_info2($shipping_id, $region_id_list);//匹配地区后的序列化数组


    	if ($weight <= 0) {
    		$cart_list_new[$key]['shipping_fee'] = 0;
    	}
    	else
    	{
    		$cart_list_new[$key]['shipping_fee'] = shipping_fee2($shipping_area['shipping_code'],unserialize($shipping_area['configure']), $weight, $amount, 1);
    	}

    	//运费
    	if ($key == 0) {
    		$shipping_fee = $cart_list_new[$key]['shipping_fee'];
    	}
    	else
    	{
    		$supp_shipping_fee += $cart_list_new[$key]['shipping_fee'];
    	}
    	// 商品价格
    	$amount_sum += $amount;

    }

    // 前台加减不方便，此处加好总额

    // 使用优惠卷
    $yhj_info = 0;
	$yhj_id = test_input($_REQUEST['yhj_id']);//用户选择的优惠卷ID

	// 获取该会员有哪些优惠卷
    $conpons_list = get_conpons($uid,1);
    $tady_date = local_date('Ymd',time());

    if (!$conpons_list) {
    	$conpons_list_new = 'null';
    	$conpons_list_id[0] = 0;
    }
    else
    {
    	$conpons_list_new = array();
    	foreach ($conpons_list as $key => $value) {

    		$promote_start_date = str_replace('-','',$conpons_list[$key]['promote_start_date']);
            $promote_end_date = str_replace('-','',$conpons_list[$key]['promote_end_date']);


            if ($tady_date > $promote_end_date) {
                // 过期
                unset($conpons_list[$key]);
            }
            if ($tady_date < $promote_start_date) {
                // 未开始
                unset($conpons_list[$key]);
            }

            // 优惠卷使用限制
            if ($amount_sum < $conpons_list[$key]['c_conditions']) {
            	unset($conpons_list[$key]);
            }

            if (!empty($conpons_list[$key])) {
            	// 处理为一维数组
    			$conpons_list_new[] = $conpons_list[$key]['c_name']."-抵".$conpons_list[$key]['c_amount']."元";
    			$conpons_list_id[] = $conpons_list[$key]['id'];
            }

    	}

    	// 处理不使用优惠卷的功能
    	$conpons_count = count($conpons_list_new);

    	if ($conpons_count >= 1) {
    		$conpons_list_new[$conpons_count] = '不使用优惠卷';
    		$conpons_list_id[$conpons_count] = 0;
    	}
    	else
    	{
    		$conpons_list_new[$conpons_count] = '没有可用优惠卷';
    		$conpons_list_id[$conpons_count] = 0;

    	}

    	$yhj_info = $conpons_list_id[$yhj_id];

    	// 重新给出价格
    	if ($yhj_info != 0) {
    		// 查询优惠卷信息
    		$sql = "SELECT uc.*,c.* FROM ".$GLOBALS['ecs']->table('user_conpons')." AS uc,".$GLOBALS['ecs']->table('conpons')." AS c WHERE uc.id='$yhj_info' AND uc.conpons_id=c.id";

    		$yhj_info = $GLOBALS['db']->getRow($sql);

    		if (!$yhj_info) {
    			$yhj_info = 0;
    		}
    		else
    		{
    			// 处理价格
    			$amount_sum = $amount_sum - $yhj_info['c_amount'];

    			if ($amount_sum < 0) {
    				$amount_sum = 0;
    			}
    		}

    	}
    }

    // 免运费功能
    $sql = "SELECT value FROM ".$GLOBALS['ecs']->table('shop_config')." WHERE code='free_shipping_amount'";

    $free_shipping_amount = $GLOBALS['db']->getOne($sql);

    if ($free_shipping_amount >= $amount_sum) {
    	// 免除运费
    	$shipping_fee = 0;
    	$is_free_shipping = 1;
    }
    else
    {
    	$is_free_shipping = 0;
    }


    $res = array('err'=>0,'shipping_fee'=>$shipping_fee,'amount'=>$amount_sum,'supp_shipping_fee'=>$supp_shipping_fee,'goods_amount_sum'=>$shipping_fee+$amount_sum+$supp_shipping_fee,'yhj_info'=>$yhj_info,'is_free_shipping'=>$is_free_shipping);
    exit(json_encode($res));
}

/*取得评价列表*/
function action_shop_comment_list()
{
	$sql = "SELECT c.*,u.wx_name,u.headimg FROM ".$GLOBALS['ecs']->table('comment')." AS c LEFT JOIN ".$GLOBALS['ecs']->table('users')." AS u ON u.user_id=c.user_id WHERE c.comment_type=0 AND c.status=1";

	$comment_list = $GLOBALS['db']->getAll($sql);

	foreach ($comment_list as $key => $value) {
		if (!$comment_list[$key]['headimg']) {
			$comment_list[$key]['headimg'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/default_tx.png';
		}

		if (!$comment_list[$key]['wx_name']) {
			if (!$comment_list[$key]['user_name']) {
				$comment_list[$key]['wx_name'] = '匿名用户';
			}
			else
			{
				$comment_list[$key]['wx_name'] = $comment_list[$key]['user_name'];
			}
		}

		if ($comment_list[$key]['wx_name'] != '匿名用户') {
			$comment_list[$key]['wx_name'] = substr_replace($comment_list[$key]['wx_name'],'**','1','2');
		}

		$comment_list[$key]['add_time'] = local_date('Y-m-d',$comment_list[$key]['add_time']);

	}

	$res = array('err'=>0,'comment_list'=>$comment_list);
    exit(json_encode($res));
}

/*
提交订单
*/
function action_flow_done()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);

	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	$pay_id = empty($_REQUEST['pay_id']) ? 0 : test_input($_REQUEST['pay_id']);//用户选择的付款方式,1微信，2余额

	$postscript = empty($_REQUEST['postscript']) ? null : test_input($_REQUEST['postscript']);//留言

	$address_id = empty($_REQUEST['address_id']) ? 0 : test_input($_REQUEST['address_id']);//用户选择的收货地址ID

	$goods_list_tmp = empty($_REQUEST['goods_list_tmp']) ? null : stripslashes($_REQUEST['goods_list_tmp']);

	// $express_id = empty($_REQUEST['express_id']) ? 'null' : test_input($_REQUEST['express_id']);//用户选择配送方式，express_id

	// $shipping_id = empty($_REQUEST['shipping_id']) ? 'null' : test_input($_REQUEST['shipping_id']);//用户选择配送方式，shipping_id

	if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    if ($goods_list_tmp == null) {
    	$res = array('err'=>3,'msg'=>'网络异常,下单失败,请稍后尝试！');
        exit(json_encode($res));
    }
    else
    {
		$goods_list_tmp = json_decode($goods_list_tmp,true);
    }

    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE address_id='$address_id'";

	$address = $GLOBALS['db']->getRow($sql);

	if (!$address) {
		$res = array('err'=>3,'msg'=>'请选择收货地址！');
        exit(json_encode($res));
	}

    include_once('includes/lib_clips.php');
    include_once('includes/lib_payment.php');
    include_once('includes/lib_order.php');

    // 通过 goods_list_tmp 取得代结商品

    $cart_list = array();

    foreach ($goods_list_tmp as $key => $value) {
    	$goods_suppliers_id_tmp = $goods_list_tmp[$key]['suppliers_id'];

    	$sql = "SELECT c.rec_id,c.goods_price as shop_price,c.goods_sn,c.goods_name,c.market_price,c.goods_number,c.goods_attr,c.goods_attr_id,g.goods_weight,g.goods_id,g.is_shipping,g.suppliers_id,g.is_real,g.team_price FROM ".$GLOBALS['ecs']->table('cart')." AS c,".$GLOBALS['ecs']->table('goods')." AS g WHERE c.checked=1 AND c.user_id='$uid' AND c.suppliers_id='$goods_suppliers_id_tmp' AND g.goods_id=c.goods_id";

    	$cart_list[$key]['goods_list'] = $GLOBALS['db']->getAll($sql);

    	if (!$cart_list[$key]['goods_list']) {
    		$res = array('err'=>5,'msg'=>'您的购物车中没有商品，请尝试重新下单！');
        	exit(json_encode($res));
    	}

    	$cart_list[$key]['shipping_fee'] = $goods_list_tmp[$key]['shipping_fee'];
    	$cart_list[$key]['shipping_id'] = $goods_list_tmp[$key]['shipping_id'];
    	$cart_list[$key]['express_id'] = $goods_list_tmp[$key]['express_id'];
    	$cart_list[$key]['point_id'] = $goods_list_tmp[$key]['point_id'];
    	$cart_list[$key]['log_point_phone'] = $goods_list_tmp[$key]['log_point_phone'];
    	$cart_list[$key]['point_data'] = $goods_list_tmp[$key]['point_data'];
    	$cart_list[$key]['point_time'] = $goods_list_tmp[$key]['point_time'];
    }


    /* 检查商品库存 */
    /* 如果使用库存，且下订单时减库存，则减少库存 */

    if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE)
    {	
    	foreach ($cart_list as $ckey => $value) {
    		
	        foreach ($cart_list[$ckey]['goods_list'] as $key => $value) {
	        	$sql = "SELECT g.goods_id,g.goods_name, g.goods_number, c.product_id ".
	                "FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
	                    $GLOBALS['ecs']->table('cart'). " AS c ".
	                "WHERE g.goods_id = c.goods_id AND c.rec_id = '{$cart_list[$ckey]['goods_list'][$key]['rec_id']}'";
	        	$row = $GLOBALS['db']->getRow($sql);

	        	if ($row['product_id'] > 0) {
	        		// 是货品
	        		$sql = "SELECT product_number FROM ".$GLOBALS['ecs']->table('products')." WHERE product_id='{$row['product_id']}'";
	        		$product_number = $GLOBALS['db']->getOne($sql);

	        		if ($product_number < $cart_list[$ckey]['goods_list'][$key]['goods_number']) {
	        			$res = array('err'=>4,'msg'=>'商品:'.$row['goods_name']."没有库存啦！");
	        			exit(json_encode($res));
	        		}
	        		else
	        		{
	        			// 货品减少
	        			$sql = "UPDATE ".$GLOBALS['ecs']->table('products')." SET product_number=product_number-{$cart_list[$ckey]['goods_list'][$key]['goods_number']} WHERE product_id='{$row['product_id']}'";
	        			$GLOBALS['db']->query($sql);
	        		}
	        	}
	        	else
	        	{
	        		// 不是货品，检查商品数量
	        		if ($row['goods_number'] < $cart_list[$ckey]['goods_list'][$key]['goods_number']) {
	        			$res = array('err'=>4,'msg'=>'商品:'.$row['goods_name']."没有库存啦！");
	        			exit(json_encode($res));
	        		}
	        		else
	        		{
	        			// 数量减少
						$sql = "UPDATE ".$GLOBALS['ecs']->table('goods')." SET goods_number=goods_number-{$cart_list[$ckey]['goods_list'][$key]['goods_number']} WHERE goods_id='{$row['goods_id']}'";
						$GLOBALS['db']->query($sql);
	        		}
	        	}

	        }
	    }
    }

    // 检查是否有多个商家的商品，有则拆分订单

    if ($pay_id == 2) {
    	//获取余额支付的id
		$sql = 'SELECT pay_id,pay_name ' .
	            ' FROM ' . $GLOBALS['ecs']->table('payment') .
	            ' WHERE enabled = 1 and pay_code="balance"';
		$pay_id_new = $GLOBALS['db']->getRow($sql);
    }
    else if ($pay_id == 1) {
    	//获取微信支付的id
		$sql = 'SELECT pay_id,pay_name ' .
	            ' FROM ' . $GLOBALS['ecs']->table('payment') .
	            ' WHERE enabled = 1 and pay_code="wxpay"';
		$pay_id_new = $GLOBALS['db']->getRow($sql);
    }
    elseif ($pay_id == 3) {
    	//获取货到付款的id
		$sql = 'SELECT pay_id,pay_name ' .
	            ' FROM ' . $GLOBALS['ecs']->table('payment') .
	            ' WHERE enabled = 1 and pay_code="cod"';
		$pay_id_new = $GLOBALS['db']->getRow($sql);
    }

    $del_patent_id = 0;//如果有多个订单，准备一个后续要删除的父级订单

    if(count($cart_list)>1){
    	$error_no = 0;
	    do
	    {
	        $save['order_sn'] = get_order_sn(); //获取新订单号
	        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $save, 'INSERT');
	        $error_no = $GLOBALS['db']->errno();
	
	        if ($error_no > 0 && $error_no != 1062)
	        {
	            $res = array('err'=>5,'msg'=>$GLOBALS['db']->errorMsg());
    			exit(json_encode($res));
	        }
	    }
	    while ($error_no == 1062); //如果是订单号重复则重新提交数据

	    $del_patent_id = $parent_order_id = $GLOBALS['db']->insert_id();//母级订单id号
    }else{
    	$parent_order_id = 0;
    }

    // 计算各个订单的总额，运费额度
    $amount_sum = 0;//所有订单总额

    $cart_list_new = $cart_list;

    unset($cart_list);
    
    // 循环创建订单
    foreach ($cart_list_new as $key => $value) {

    	$order_sn = get_order_sn();//获取订单sn

    	$amount = 0;//此子订单的商品总额

    	$_SESSION['goods_suppliers_id'] = $key;//店铺ID

    	// $order_fee = order_fee2($cart_list_new[$key],$address,$express_id,$shipping_id);

    	$shipping_fee = $cart_list_new[$key]['shipping_fee'];//运费

    	$amount = 0;//商品总额

    	foreach ($cart_list_new[$key]['goods_list'] as $ggkey => $value) {
    		$amount += $cart_list_new[$key]['goods_list'][$ggkey]['shop_price'];
    	}

    	// 获取配送方式信息
		if ($cart_list_new[$key]['shipping_id'] > 0) {

	    	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('shipping')." WHERE shipping_id='{$cart_list_new[$key]['shipping_id']}'";

	    	$shipping_info = $GLOBALS['db']->getRow($sql);

	    }
	    else
	    {
	    	$shipping_info = $GLOBALS['db']->getRow('SELECT * FROM '.$GLOBALS['ecs']->table('goods_express').' WHERE id=' . $cart_list_new[$key]['express_id']);
	    }

	    $best_time = $cart_list_new[$key]['point_id'] > 0 ? $cart_list_new[$key]['point_data'] . ' ' . $cart_list_new[$key]['point_time']:'送货时间不限';

	    if($shipping_info['shipping_code'] != 'cac')
	    {
	        $cart_list_new[$key]['point_id'] = 0;
	        $best_time = '送货时间不限';
	    }

    	//要插入数据库的订单信息
    	$order = Array
		(
		    'pay_id' => $pay_id_new['pay_id'],
		    'pack_id' => 0,
		    'card_id' => 0,
		    'card_message' => '',
		    'surplus' => 0,
		    'integral' => 0,
		    'bonus_id' => 0,
		    'need_inv' => 0,
		    'postscript' => $postscript,
		    'how_oos' => '等待所有商品备齐后再发',
		    'need_insure' => 0,
		    'user_id' => $uid,
		    'add_time' => gmtime(),
		    'order_status' => 0,
		    'shipping_status' => 0,
		    'pay_status' => 0,
		    'agency_id' => 0,
		    'suppliers_id' => $key,
		    'defaultbank' => '',
		    'extension_code' => '',
		    'extension_id' => 0,
		    'shipping_id' => $shipping_info['shipping_id'],
		    'address_id' => 0,
		    'address_name' => '',
		    'consignee' => $address['consignee'],
		    'email' => $address['email'],
		    'country' => $address['country'],
		    'province' => $address['province'],
		    'city' => $address['city'],
		    'district' => $address['district'],
		    'address' => $address['address'],
		    'zipcode' => $address['zipcode'],
		    'tel' => $address['tel'],
		    'mobile' => $address['mobile'],
		    'sign_building' => '',
		    'best_time' => $best_time,
		    'shipping_pay' => Array(),
		    'bonus' => 0,
		    'goods_amount' => floatval($amount),
		    'discount' => '',
		    'tax' => 0,
		    'shipping_name' => $shipping_info['shipping_name'],
		    'is_pickup' => 1,
		    'shipping_fee' => $shipping_fee,
		    'insure_fee' => 0,
		    'pay_name' => $pay_id_new['pay_name'],
		    'pay_fee' => 0,
		    'cod_fee' => 0,
		    'pack_fee' => 0,
		    'card_fee' => 0,
		    'order_amount' => $amount+$shipping_fee,
		    'inv_money' => $amount,
		    'integral_money' => 0,
		    'from_ad' => 0,
		    'referer' => 'xcx',
		    'parent_id' => '',
		    'pickup_point' => 0,
		    'rebate_id' => 0,
		    'froms' => 'mobile',
		    'parent_order_id' => $parent_order_id,
		    'order_sn' => $order_sn,
		    'point_id' =>$cart_list_new[$key]['point_id'],
		    'checked_mobile'=>$cart_list_new[$key]['log_point_phone'],
		    'package_one'=>0
		);

		if (! empty($order['checked_mobile'])) {

	        $GLOBALS['db']->query('update '.$GLOBALS['ecs']->table('user_address').' set `mobile` = "'.$order['checked_mobile'].'" where user_id = "'.$uid.'" AND address_id = "'.$address['address_id'].'"');

	    }

		/* 插入订单表 */
	    $error_no = 0;
	    do
	    {
	        $order['order_sn'] = get_order_sn(); //获取新订单号
			
	        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');
	
	        $error_no = $GLOBALS['db']->errno();
	
	        if ($error_no > 0 && $error_no != 1062)
	        {
	            $res = array('err'=>5,'msg'=>$GLOBALS['db']->errorMsg());
    			exit(json_encode($res));
	        }
	    }
	    while ($error_no == 1062); //如果是订单号重复则重新提交数据
	
	    $new_order_id = $GLOBALS['db']->insert_id();

	    $parent_order_id = ($parent_order_id>0) ? $parent_order_id : $new_order_id;//拿来生成支付日志的id

	    foreach ($cart_list_new[$key]['goods_list'] as $ckey => $rvalue) {
	    	//订单信息入order_goods表
			$sql = "INSERT INTO " . $GLOBALS['ecs']->table('order_goods'). "( " .

			                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

			                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) ".

			            " VALUES( '$new_order_id', '{$cart_list_new[$key]['goods_list'][$ckey]['goods_id']}', '{$cart_list_new[$key]['goods_list'][$ckey]['goods_name']}', '{$cart_list_new[$key]['goods_list'][$ckey]['goods_sn']}', 0, '{$cart_list_new[$key]['goods_list'][$ckey]['goods_number']}', '{$cart_list_new[$key]['goods_list'][$ckey]['market_price']}', ".

			                "'{$cart_list_new[$key]['goods_list'][$ckey]['shop_price']}', '{$cart_list_new[$key]['goods_list'][$ckey]['goods_attr']}', 1, '', 0, 0, '{$cart_list_new[$key]['goods_list'][$ckey]['goods_attr_id']}' )";

		    $GLOBALS['db']->query($sql);
	    }

	    //为每一个订单生成一个支付日志记录
	    if ($del_patent_id !=0) {
	    	$order['log_id'] = insert_pay_log($new_order_id, $amount+$shipping_fee, PAY_ORDER);
	    }

	    $amount_sum += $amount+$shipping_fee;//所有订单总额的累计
    }

    // 如果有多个订单，则生成母订单号，并根据母订单号生成支付日志，支付成功后，将所有子订单的母订单为这个母订单的订单设为已付款
    $parent_pay_id = insert_pay_log($parent_order_id, $amount_sum, PAY_ORDER);//总支付log

    //删除父订单记录
    /*if($del_patent_id > 0){
    	$sql="delete from ".$GLOBALS['ecs']->table('order_info')." where order_id='$del_patent_id' ";
		$GLOBALS['db']->query($sql);
    }*/

    /*echo $parent_pay_id;

    exit();*/

    // 拿着支付log开始支付

    if ($pay_id == 1) {
    	// 取用户的open_id
    	$sql = "SELECT wx_open_id FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
    	$openId = $GLOBALS['db']->getOne($sql);

    	if (!$openId) {
    		// 不是微信注册用户,获取用户的open_id
			$user_code = empty($_REQUEST['user_code']) ? '' : test_input($_REQUEST['user_code']);
    		if ($user_code == '') {
    			$res = array('err'=>6,'msg'=>'获取您的授权信息失败,无法使用微信支付！');
				exit(json_encode($res));
    		}
    		else
    		{
    			$url='https://api.weixin.qq.com/sns/jscode2session?appid='.$GLOBALS['xcx_config']['appid'].'&secret='.$GLOBALS['xcx_config']['appsecret'].'&js_code='.$user_code.'&grant_type=authorization_code';

				$wx_res = file_get_contents($url);

				$wx_res = json_decode($wx_res,true);

				if (isset($wx_res['errcode'])) {
					// code失效
					if ($wx_res['errcode']== '40029') {
						$res = array('err' => 7,'msg'=>'获取信息授权已失效，请重试！');
						echo json_encode($res);
						return;
					}
					else
					{
						$res = array('err' => 7,'msg'=>'获取支付信息失败，请重试！');
						echo json_encode($res);
						return;
					}
				}
				else
				{
					$res = array('err' => 3,'msg'=>'获取支付信息失败，请重试！');
				}

				$openId = $wx_res['openid'];
    		}
    	}

    	// 至此已拿到用户的open_id,统一下单
    	// 引入api类
    	require_once dirname(__FILE__)."/wxpayAPI/lib/WxPay.Api.php";
		require_once dirname(__FILE__)."/wxpayAPI/example/WxPay.JsApiPay.php";
		require_once dirname(__FILE__).'/wxpayAPI/example/log.php';
		//初始化日志
		$logHandler= new CLogFileHandler(dirname(__FILE__)."/wxpayAPI/logs/".date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 15);

		$Time_expire = (string)local_date("YmdHis", time() + 800);
		
		$input = new WxPayUnifiedOrder();
		$input->SetBody($GLOBALS['xcx_config']['xcx_name'].$parent_pay_id);
		$input->SetAttach($GLOBALS['xcx_config']['xcx_name']."小程序支付");
		$input->SetOut_trade_no($parent_pay_id."e".$Time_expire);
		$input->SetTotal_fee($amount_sum * 100);
		$input->SetTime_start(local_date("YmdHis"));
		$input->SetTime_expire($Time_expire);
		// $input->SetGoods_tag("test");
		$input->SetNotify_url("http://".$GLOBALS['xcx_config']['url']."/xcx/notify.php");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);

		/*print_r($order);

		exit();*/

		$order_new['timeStamp'] = time();
		$order_new['nonceStr'] = md5($order['nonce_str']);
		$order_new['signType'] = 'MD5';
		$order_new['package'] = "prepay_id=".$order['prepay_id'];

		unset($order);
		
		// 拼接签名
		$stringA = "appId=".$GLOBALS['xcx_config']['appid']."&nonceStr=".$order_new['nonceStr']."&package=".$order_new['package']."&signType=".$order_new['signType']."&timeStamp=".$order_new['timeStamp'];

		$stringSignTemp = $stringA."&key=".$GLOBALS['xcx_config']['key'];

		$order_new['paySign'] = strtoupper(md5($stringSignTemp));//转换为大写

		$order_new['stringSignTemp'] = $stringSignTemp;


		// print_r($order);
    }
    else
    {
    	$order_new = array();//不是微信支付
    }

    // 将支付log发送到前台

    // 删除购物车中结算完成的商品
    $sql = "DELETE FROM ".$GLOBALS['ecs']->table('cart')." WHERE user_id='$uid' AND checked=1";
    $GLOBALS['db']->query($sql);


    $res = array('err'=>0,'msg'=>'订单提交成功！','pay_id'=>$pay_id,'pay_log'=>$parent_pay_id,'order'=>$order_new);
	exit(json_encode($res));    
}
/*
支付订单
*/
function action_pay_order()
{

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $pay_id = empty($_REQUEST['pay_id']) ? 0 : test_input($_REQUEST['pay_id']);//用户选择的支付方式
    $pay_log = empty($_REQUEST['pay_log']) ? 0 : test_input($_REQUEST['pay_log']);//订单的支付记录log

    if ($pay_id == 0) {
    	$res = array('err'=>3,'msg'=>'您选择的支付方式找不到了...');
        exit(json_encode($res));
    }

    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('pay_log')." WHERE log_id='$pay_log'";

    $pay_log = $GLOBALS['db']->getRow($sql);

    if ($pay_log) {
    	$amount_sum = $pay_log['order_amount'];
    	$order_id = $pay_log['order_id'];
    }
    else
    {
    	$res = array('err'=>4,'msg'=>'网络异常,请刷新重试...');
        exit(json_encode($res));
    }

	$pay_id = empty($_REQUEST['pay_id']) ? 0 : test_input($_REQUEST['pay_id']);//用户选择的支付方式
    if ($pay_id == 2) {
    	// 余额支付,获取用户余额值
    	$sql = "SELECT user_money FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
    	$user_money = $GLOBALS['db']->getOne($sql);

    	if ($user_money < $amount_sum) {
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET surplus='$user_money',money_paid=money_paid+'$user_money',order_amount=order_amount-'$user_money',order_status=1 WHERE order_id='$order_id'";
    		$GLOBALS['db']->query($sql);

    		$surplus = $amount_sum-$user_money;

    		log_account_change($uid, (- 1) * $user_money, 0, 0, 0, '订单付款');//改变账户

    		$res = array('err'=>6,'msg'=>'您的余额不足,订单已结'.$user_money.'元,剩余:'.$surplus.'元应付。');
			exit(json_encode($res));
    	}
    	else
    	{
    		$pay_time = time();
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET surplus='$amount_sum',money_paid=money_paid+'$amount_sum',order_amount=order_amount-'$amount_sum',order_status=1,pay_status=2,pay_time='$pay_time' WHERE order_id='$order_id'";
    		$GLOBALS['db']->query($sql);

    		log_account_change($uid, (- 1) * $amount_sum, 0, 0, 0, '订单付款');//改变账户

    		// 改变log支付状态
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('pay_log')." SET is_paid=1 WHERE log_id='{$pay_log['log_id']}'";

    		$GLOBALS['db']->query($sql);

    		// 查询是否有订单的母订单为 $order_id parent_order_id
    		$sql = "SELECT order_id FROM ".$GLOBALS['ecs']->table('order_info')." WHERE parent_order_id='$order_id'";

    		$order_id_list = $GLOBALS['db']->getAll($sql);
    		
    		if (!$order_id_list) {
    			// 无子订单
    		}
    		else
    		{
    			foreach ($order_id_list as $idkey => $idvalue) {

    				$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET surplus=order_amount,money_paid=order_amount,order_amount=0,order_status=1,pay_status=2,pay_time='$pay_time' WHERE order_id='{$order_id_list[$idkey]['order_id']}'";

    				$GLOBALS['db']->query($sql);

    			}

    			// 删除父订单
		    	$sql="delete from ".$GLOBALS['ecs']->table('order_info')." where order_id='$order_id' ";
				$GLOBALS['db']->query($sql);

    		}

    		$res = array('err'=>6,'msg'=>'使用余额支付成功,共'.$amount_sum.'元');
			exit(json_encode($res));
    	}

    }
    elseif ($pay_id == 1) {
    	// 读取支付状态
    	if ($pay_log['is_paid'] == 1) {

    		// 查询是否有订单的母订单为 $order_id parent_order_id
    		$sql = "SELECT order_id FROM ".$GLOBALS['ecs']->table('order_info')." WHERE parent_order_id='$order_id'";

    		$order_id_list = $GLOBALS['db']->getAll($sql);
    		
    		if (!$order_id_list) {
    			// 无子订单
    		}
    		else
    		{
    			foreach ($order_id_list as $idkey => $idvalue) {

    				$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET surplus=order_amount,money_paid=order_amount,order_amount=0,order_status=1,pay_status=2 WHERE order_id='{$order_id_list[$idkey]['order_id']}'";

    				$GLOBALS['db']->query($sql);

    			}

    			// 删除父订单
		    	$sql="delete from ".$GLOBALS['ecs']->table('order_info')." where order_id='$order_id' ";
				$GLOBALS['db']->query($sql);

    		}

    		$res = array('err'=>0,'msg'=>'使用微信支付成功,共'.$amount_sum.'元');
			exit(json_encode($res));
    	}
    	else
    	{
    		$res = array('err'=>1,'msg'=>'支付失败，请重试或联系客服...');
			exit(json_encode($res));
    	}
    }
}
/*聚合物流查询接口*/
function action_juhe_wuliu()
{

	require('juhe_class.php');

    $order_id = empty($_REQUEST['order_id']) ? 0 : test_input($_REQUEST['order_id']);//订单号

    if ($order_id == 0) {
    	$res = array('err'=>2,'msg'=>'网络异常！');
		exit(json_encode($res));
    }
    else
    {
    	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_id='$order_id'";

    	$order_info = $GLOBALS['db']->getRow($sql);


    	if (!$order_info) {
    		$res = array('err'=>3,'msg'=>'您要查找的订单不存在！');
			exit(json_encode($res));
    	}

    	if (!$order_info['shipping_name']) {
    		$res = array('err'=>4,'msg'=>'此订单所属的配送方式不存在！');
			exit(json_encode($res));
    	}

    	if (!$order_info['invoice_no']) {
    		$res = array('err'=>5,'msg'=>'物流编号没有填写请联系管理员索要！');
			exit(json_encode($res));
    	}

    }


	// 读取店铺设置聚合接口key
	$sql = "SELECT value FROM ".$GLOBALS['ecs']->table('shop_config')." WHERE code='juhekey'";

	$juhekey = $GLOBALS['db']->getOne($sql);

	if (!$juhekey) {
		$res = array('err'=>1,'msg'=>'系统未配置物流查询！');
		exit(json_encode($res));
	}


	// 开始获取物流数据
	$params = array(
	  'key' => $juhekey, //您申请的快递appkey
	  'com' => '', //快递公司编码，可以通过$exp->getComs()获取支持的公司列表
	  'no'  => $order_info['invoice_no']
	);

	$exp = new exp($params['key']); //初始化类

	$coms = $exp->getComs();

	if ($coms['error_code'] != 0) {
		$res = array('err'=>1,'msg'=>'物流查询失败,请手动查询！');
		exit(json_encode($res));
	}
	// 查找要查询的快递方式对应的快递公司编码

	$coms_result = $coms['result'];

	foreach ($coms_result as $key => $value) {

		// 使用简称在本订单的配送方式中去匹配
		if (strpos($order_info['shipping_name'],$coms_result[$key]['com']) !== false) {
			// 搜索到
			$params['com'] = $coms_result[$key]['no'];
		}
	}

	if ($params['com'] == '') {
		$res = array('err'=>1,'msg'=>'抱歉！该配送方式不支持在线查询！');
		exit(json_encode($res));
	}

	// print_r($params);

	$result = $exp->query($params['com'],$params['no']); //执行查询
 
	if($result['error_code'] == 0){

		//查询成功
		$list = $result['result']['list'];

		// 将list数组顺序翻转
		$list = array_reverse($list);

		$res = array('err'=>0,'list'=>$list,'shipping_name'=>$order_info['shipping_name'],'invoice_no'=>$order_info['invoice_no']);
		exit(json_encode($res));

	}else{
		$res = array('err'=>99,'msg'=>$result['reason'],'params'=>$params);
		exit(json_encode($res));
	}

}


function getQuerystr($url,$key){
    $res = '';
    $a = strpos($url,'?');
    if($a!==false){
        $str = substr($url,$a+1);
        $arr = explode('&',$str);
        foreach($arr as $k=>$v){
            $tmp = explode('=',$v);
            if(!empty($tmp[0]) && !empty($tmp[1])){
                $barr[$tmp[0]] = $tmp[1];
            }
        }
    }
    if(!empty($barr[$key])){
        $res = $barr[$key];
    }
    return $res;
}
?>
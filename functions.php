<?php

/*
检查所有团，是否有需要退款的
*/
function check_xcx_team_refund()
{
    $sql="select o.`order_sn`,o.`order_id`,o.`team_sign`,o.`xcx_pay_no`,o.`pay_time`,u.`openid` from ".$GLOBALS['ecs']->table('order_info')." as o LEFT JOIN ".$GLOBALS['ecs']->table('users')." as u on u.`user_id` = o.`user_id` where o.team_status=1 AND o.is_luck=0 AND o.is_miao=0 AND o.xcx_pay_no <> '' group by team_sign;";

    $team_list = $GLOBALS['db']->getAll($sql);

    $team_suc_time = $GLOBALS['_CFG']['team_suc_time']*24*3600;

    foreach ($team_list as $key => $value) {

        if ($team_list[$key]['pay_time'] && ($team_list[$key]['pay_time'] + $team_suc_time) < gmtime()) {
            // 退款
            xcx_team_refund($team_list[$key]['team_sign']);
        }

    }
}

/*小程序团退款*/

function xcx_team_refund($team_sign)
{
    require_once(ROOT_PATH . 'includes/lib_order.php');

    $sql="select o.`order_sn`,o.`user_id`,o.`order_id`,o.`money_paid`,o.`order_amount`,o.`team_sign`,o.surplus,o.`xcx_pay_no`,u.`openid` from ".$GLOBALS['ecs']->table('order_info')." as o LEFT JOIN ".$GLOBALS['ecs']->table('users')." as u on u.`user_id` = o.`user_id` where team_sign=".$team_sign;

    $team_list= $GLOBALS['db']->getAll($sql);

    $is_xcx_order = 0;

    foreach ($team_list as $f) {

        if ($f['xcx_pay_no'] or $f['surplus'] > 0) {

            $order_sn=$f['order_sn'];

            $money_paid = $f['money_paid'] - $f['surplus'];//退款额度

            if ($f['surplus'] > 0 && $f['user_id']) {
            	// 余额退款
	    		log_account_change($f['user_id'], $f['surplus'], 0, 0, 0, '订单退款'.$f['order_sn']);//改变账户

                $sql = "UPDATE ".$GLOBALS['hhs']->table('order_info')." SET surplus=0 WHERE order_sn='$order_sn'";

                $GLOBALS['db']->query($sql);
            }

            if ($money_paid > 0) {
            	$r= xcx_pay_refund($order_sn,$money_paid*100,$f['xcx_pay_no']);
            }
            else
            {
            	$r = 0;
            }

            if ($r or $f['surplus'] > 0) {
                $arr                    = array();

                $arr['order_status']    = OS_RETURNED;

                $arr['pay_status']      = PS_REFUNDED;

                $arr['shipping_status'] = 0;

                $arr['team_status']     = 3;

                $arr['money_paid']      = 0;

                $arr['order_amount']    = $f['money_paid'] + $f['order_amount'];

                update_order($f['order_id'], $arr);

                change_order_goods_storage($f['order_id'], false, SDT_PLACE);
            }

            unset($f);

            $is_xcx_order = 1;

        }
        
    }

    unset($team_list);


    if ($is_xcx_order == 1) {
        // 团状态

        $sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET team_status=3,order_status=2 WHERE team_status=1 and team_sign=".$team_sign;

        $GLOBALS['db']->query($sql);

        //订单状态

        $sql = "UPDATE ". $GLOBALS['ecs']->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$team_sign;

        $GLOBALS['db']->query($sql);
    }

    return 1;
}

/*
小程序订单退款
*/
function xcx_pay_refund($order_sn,$refund_money,$transaction_id)
{

    include_once (ROOT_PATH . 'xcx/wxpayAPI/lib/WxPay.Api.php');

    if(isset($transaction_id) && $transaction_id != ""){

        $input = new WxPayRefund();

        $input->SetTransaction_id($transaction_id);

        $input->SetTotal_fee($refund_money);
        $input->SetRefund_fee($refund_money);

        $input->SetOut_refund_no($order_sn);

        $input->SetOp_user_id(WxPayConfig::MCHID);

        $data = WxPayApi::refund($input);

        /*print_r($data);

        exit();*/

        if ($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
            return 1;
        }
        else
        {
            return 0;
        }

    }
    else
    {
        return 0;
    }
}

// 检查数据函数
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// 计算订单费用函数
function order_fee2($goods, $consignee,$express_id='null',$shipping_id='null')
{
    // team_price为商品总额+运费
    // goods_team_price为商品总额
    $total  = array('real_goods_count' => 0,
                    'goods_price'      => 0,
                    'market_price'     => 0,
                    'team_price'     => 0,
                    'goods_team_price'     => 0,
                    'shipping_fee'     => 0,
                    'surplus'          => 0,
                    'pay_fee'          => 0,
                    'amount_sum'                => 0
    );

    /* 商品总价 */
    foreach ($goods as $key => $value) {
        /* 统计实体商品的个数 */
        if ($value['is_real'])
        {
            $total['real_goods_count']++;
        }

        $total['goods_price']  += $value['shop_price'] * $value['number'];
        $total['market_price'] += $value['market_price'] * $value['number'];
        $total['team_price'] += $value['team_price'] * $value['number'];
    }

    $total['goods_team_price'] = $total['team_price'];

    // 优惠额度
    $total['goods_price_formated']  = price_format($total['goods_price'], false);
    $total['market_price_formated'] = price_format($total['market_price'], false);

    /* 配送费用 */

    // 邮费模板附带 express_id
    if($express_id != 'null')
    {

        $express = $GLOBALS['db']->getRow('SELECT * FROM '.$GLOBALS['ecs']->table('goods_express').' WHERE id=' . $express_id);
        $configure = array(
            array('name'=>'item_fee','value'=>$express['shipping_fee']),
            array('name'=>'base_fee','value'=>$express['shipping_fee']),
            array('name'=>'basic_fee','value'=>$express['shipping_fee']),
            array('name'=>'step_fee','value'=>$express['step_fee']),
            array('name'=>'fee_compute_mode','value'=>$express['fee_compute_mode']),
            array('name'=>'free_money','value'=>''),
            array('name'=>'pay_fee','value'=>''),
        );
        $shipping_info = array(
            'shipping_code' => $express['shipping_code'],
            'shipping_name' => $express['shipping_name'],
            'pay_fee'       => 0,
            'insure'        => 0,
            'support_cod'   => 0,
            'configure'     => $configure,
        );
    }
    else if ($shipping_id != 'null')
    {
        $region['country']  = $consignee['country'];
        $region['province'] = $consignee['province'];
        $region['city']     = $consignee['city'];
        $region['district'] = $consignee['district'];
        $shipping_info = shipping_area_info($shipping_id, $region);
    }
    else
    {
        $total['shipping_fee_formated']    = price_format(0, false);
        $total['shipping_insure_formated'] = price_format(0, false);
    }
    

    if (!empty($shipping_info))
    {
        $weight_price = cart_weight_price2($goods);

        // 查看购物车中是否全为免运费商品，若是则把运费赋为零
        $shipping_count = count($goods);

        $total['shipping_fee'] = ($shipping_count == 0) ? 0 :  shipping_fee($shipping_info['shipping_code'],$shipping_info['configure'], $weight_price['weight'], $total['goods_price'], $weight_price['number']);

        $total['shipping_fee_formated']    = price_format($total['shipping_fee'], false);

    }

    // print_r($total);

    // 合计加上运费
    $total['team_price'] += $total['shipping_fee'];
        
    return $total;
}

// 计算总重量,总金额,总数量
function cart_weight_price2($goods_list)
{
    $packages_row['weight'] = 0;
    $packages_row['amount'] = 0;
    $packages_row['number'] = 0;

    foreach ($goods_list as $key => $value) {
        $packages_row['weight'] += floatval($value['weight']);
        $packages_row['amount'] += floatval($value['amount']);
        $packages_row['number'] += intval($value['number']);
    }
    /* 格式化重量 */
    $packages_row['formated_weight'] = formated_weight($packages_row['weight']);

    return $packages_row;
}

// 验证用户的sign是否正确

function check_sign($uid,$sign)
{

	$sql = "SELECT sign FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";

	if ($GLOBALS['db']->getOne($sql) == $sign) {

		return true;
	}
	else
	{
		return false;
	}
}

// 检查是否是手机号

function is_telephone ($phone)
{

	// $chars = "/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/";
	$chars = "/^1[1-9]\d{9}$/";
	if(preg_match($chars, $phone))
	{

		return true;

	}
	else
	{
		return false;
	}

}

/**
 * 获取相关属性的库存
 * @param int $goodid 商品id
 * @param string(array) $attrids 商品属性id的数组或者逗号分开的字符串
 */
function get_product_attr_num($goodid,$attrids=0){
	$ret = array();
	
	/* 判断商品是否参与预售活动，如果参与则获取商品的（预售库存-已售出的数量） */
	if(!empty($_REQUEST['pre_sale_id']))
	{
		$pre_sale = pre_sale_info($_REQUEST['pre_sale_id'], $goods_num);
		//如果预售为空或者预售库存小于等于0则认为不限购
		if(!empty($pre_sale) && $pre_sale['restrict_amount'] > 0){
			
			$product_num = $pre_sale['restrict_amount'] - $pre_sale['valid_goods'];
			
			return $product_num;
		}
	}
	
	if(empty($attrids)){
		$ginfo = get_goods_attr_value($goodid,'goods_number');
		return $ginfo['goods_number'];
		//$ret[$attrids] = $ginfo['goods_number'];
		//return $ret;
	}
	if(!is_array($attrids)){
		$attrids = explode(',',$attrids);
	}

	$goods_attr_array = sort_goods_attr_id_array($attrids);

    if(isset($goods_attr_array['sort']))
    {
        $goods_attr = implode('|', $goods_attr_array['sort']);

		$sql = "SELECT product_id, goods_id, goods_attr, product_sn, product_number
                FROM " . $GLOBALS['ecs']->table('products') . " 
                WHERE goods_id = $goodid AND goods_attr = '".$goods_attr."' LIMIT 0, 1";
		$row = $GLOBALS['db']->getRow($sql);
		
		return empty($row['product_number'])?0:$row['product_number'];
    }
}

/**
 * 获取商品的相关信息
 * @param int $goodsid 商品id
 * @param string $name  要获取商品的属性名称,多个，就用逗号分隔
 */
function get_goods_attr_value($goodsid,$name='goods_sn,goods_name')
{
	$sql = "select ".$name." from ". $GLOBALS['ecs']->table('goods') ." where goods_id=".$goodsid;
	$row = $GLOBALS['db']->getRow($sql);
	return $row;
}

/**
 * 获取属性值
 * @param type $attr_id
 * @return type
 */
function get_goods_attr_str($attr_id){
    $goods_attr = "";
    $attr_price = "";

    if(!empty($attr_id)){
       $goods_attr_array = array(); 
    foreach($attr_id as $key=>$value){
        $sql = "select ga.attr_id, ga.attr_value, ga.attr_price, at.attr_name from ". $GLOBALS['ecs']->table('goods_attr') ." as ga left join ". $GLOBALS['ecs']->table('attribute') ." as at on ga.attr_id = at.attr_id  where ga.goods_attr_id = $value";
        $res = $GLOBALS['db']->getRow($sql);
        array_push($goods_attr_array,$res['attr_name']."：".$res['attr_value']);
        $attr_price = empty($res['attr_price'])?0:$res['attr_price']+$attr_price;
    }
        $goods_attr  =  implode(';',$goods_attr_array);
        //$goods_attr = $goods_attr.'['.$attr_price.']';
    }
    return $goods_attr;
}

/**
 * 取得某配送方式对应于某收货地址的区域信息
 * @param   int     $shipping_id        配送方式id
 * @param   array   $region_id_list     收货人地区id数组
 * @return  array   配送区域信息（config 对应着反序列化的 configure）
 */
function shipping_area_info2($shipping_id, $region_id_list)
{
    $sql = 'SELECT s.shipping_code, s.shipping_name, ' .
                's.shipping_desc, s.insure, s.support_cod, a.configure ' .
            'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' .
                $GLOBALS['ecs']->table('shipping_area') . ' AS a, ' .
                $GLOBALS['ecs']->table('area_region') . ' AS r ' .
            "WHERE s.shipping_id = '$shipping_id' " .
            'AND r.region_id ' . db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1';
    $row = $GLOBALS['db']->getRow($sql);

    if (!empty($row))
    {
        $shipping_config = unserialize_config2($row['configure']);
        if (isset($shipping_config['pay_fee']))
        {
            if (strpos($shipping_config['pay_fee'], '%') !== false)
            {
                $row['pay_fee'] = floatval($shipping_config['pay_fee']) . '%';
            }
            else
            {
                 $row['pay_fee'] = floatval($shipping_config['pay_fee']);
            }
        }
        else
        {
            $row['pay_fee'] = 0.00;
        }
    }

    return $row;
}

/**
 * 计算运费
 * @param   string  $shipping_code      配送方式代码
 * @param   mix     $shipping_config    配送方式配置信息
 * @param   float   $goods_weight       商品重量
 * @param   float   $goods_amount       商品金额
 * @param   float   $goods_number       商品数量
 * @return  float   运费
 */
function shipping_fee2($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number='')
{
    if (!is_array($shipping_config))
    {
        $shipping_config = unserialize($shipping_config);
    }

    $filename = '../includes/modules/shipping/' . $shipping_code . '.php';
    if (file_exists($filename))
    {
        include_once($filename);

        $obj = new $shipping_code($shipping_config);

        return $obj->calculate($goods_weight, $goods_amount, $goods_number);
    }
    else
    {
        return 0;
    }
}

/**
 * 处理序列化的支付、配送的配置参数
 * 返回一个以name为索引的数组
 *
 * @access  public
 * @param   string       $cfg
 * @return  void
 */
function unserialize_config2($cfg)
{
    if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
    {
        $config = array();

        foreach ($arr AS $key => $val)
        {
            $config[$val['name']] = $val['value'];
        }

        return $config;
    }
    else
    {
        return false;
    }
}

/*
给定一个供应商的id，获取到默认配送方式
*/

function get_shipping_type()
{
    $sql = "SELECT shipping_id FROM ".$GLOBALS['ecs']->table('shipping')." WHERE enabled=1 ORDER BY shipping_id ASC LIMIT 0,1";

    $shipping_id = $GLOBALS['db']->getOne($sql);

    return $shipping_id;
}

/*
获取某店铺有多少商品
*/
function get_street_goods_info($suppid){

    $db = $GLOBALS['db'];
    $ecs = $GLOBALS['ecs'];

    $sql = "SELECT g.goods_id, g.goods_name, g.goods_name_style, g.click_count, g.goods_number, g.market_price,  g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price,  IFNULL(mp.user_price, g.shop_price * '1') AS shop_price, g.promote_price,  IF(g.promote_price != ''  AND g.promote_start_date < 1439592730 AND g.promote_end_date > 1439592730, g.promote_price, shop_price)  AS shop_p, g.goods_type,  g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img  FROM ".$ecs->table('goods')." AS g  LEFT JOIN ".$ecs->table('member_price')." AS mp  ON mp.goods_id = g.goods_id  AND mp.user_rank = '0' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_virtual = 0 AND g.supplier_id=".$suppid." order by g.goods_id desc";


    $goodsInfo = $db->getAll($sql);

    $allnum = count($goodsInfo);
    if($allnum > 0){
        if($allnum > 4){
            array_splice($goodsInfo, 4);
        }
        foreach($goodsInfo as $key=>$row){
            $goodsInfo[$key]['shop_price']       = $row['shop_price'];
            $goodsInfo[$key]['goods_thumb']      = $row['goods_thumb'];
        }
    }
    return array('num'=>$allnum,'info'=>$goodsInfo);
}

/*
取消订单
*/
function xcx_order_cancel($order_id,$user_id)
{

    include_once (ROOT_PATH . 'includes/lib_transaction.php');
    include_once (ROOT_PATH . 'includes/lib_order.php');

    /* 查询订单信息，检查状态 */
    $sql = "SELECT user_id, order_id, order_sn , surplus , integral , bonus_id, order_status, shipping_status, pay_status, postscript FROM " .$GLOBALS['ecs']->table('order_info') ." WHERE order_id = '$order_id'";
    $order = $GLOBALS['db']->GetRow($sql);

    if (empty($order))
    {
        $res = array('err'=>8,'msg'=>'订单不存在喔！');
        exit(json_encode($res));
    }

    // 如果用户ID大于0，检查订单是否属于该用户
    if ($user_id > 0 && $order['user_id'] != $user_id)
    {
        $res = array('err'=>9,'msg'=>'订单不存在喔！');
        exit(json_encode($res));
    }

    // 订单状态只能是“未确认”或“已确认”
    if ($order['order_status'] != OS_UNCONFIRMED && $order['order_status'] != OS_CONFIRMED)
    {
        $res = array('err'=>10,'msg'=>'当前订单不是‘未确认’不能取消喔！');
        exit(json_encode($res));
    }

    //订单一旦确认，不允许用户取消
    if ($order['order_status'] == OS_CONFIRMED)
    {
        $res = array('err'=>11,'msg'=>'当前订单已确认不能取消喔');
        exit(json_encode($res));
    }
    // 发货状态只能是“未发货”
    if ($order['shipping_status'] != SS_UNSHIPPED)
    {
        $res = array('err'=>12,'msg'=>'只有未发货的订单可以取消喔！');
        exit(json_encode($res));
    }

    // 如果付款状态是“已付款”、“付款中”，不允许取消，要取消和商家联系
    if ($order['pay_status'] != PS_UNPAYED)
    {
        $res = array('err'=>13,'msg'=>'当前订单已付款,取消请和商家联系退款');
        exit(json_encode($res));
    }

        //将用户订单设置为取消
    $sql = "UPDATE ".$GLOBALS['ecs']->table('order_info') ." SET order_status = '".OS_CANCELED."' WHERE order_id = '$order_id'";
    if ($GLOBALS['db']->query($sql))
    {
        /* 记录log */
        order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED,'取消订单','buyer');
        /* 退货用户余额、积分、红包 */
        if ($order['user_id'] > 0 && $order['surplus'] > 0)
        {
            $_LANG['return_surplus_on_cancel'] = '取消订单 %s，退回支付订单时使用的预付款';
            $change_desc = sprintf($_LANG['return_surplus_on_cancel'], $order['order_sn']);
            log_account_change($order['user_id'], $order['surplus'], 0, 0, 0, $change_desc);
        }
        if ($order['user_id'] > 0 && $order['integral'] > 0)
        {
            $_LANG['return_integral_on_cancel'] = '取消订单 %s，退回支付订单时使用的积分';
            $change_desc = sprintf($_LANG['return_integral_on_cancel'], $order['order_sn']);
            log_account_change($order['user_id'], 0, 0, 0, $order['integral'], $change_desc);
        }
        if ($order['user_id'] > 0 && $order['bonus_id'] > 0)
        {
            change_user_bonus($order['bonus_id'], $order['order_id'], false);
        }

        /* 如果使用库存，且下订单时减库存，则增加库存 */
        if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE)
        {
            change_order_goods_storage($order['order_id'], false, 1);
        }

        // 退还优惠卷
        if ($order['postscript']) {
            $sql = "SELECT id FROM ".$GLOBALS['ecs']->table('user_conpons')." WHERE is_use_order='{$order['order_sn']}' AND user_id='{$order['user_id']}'";

            $use_conpons = $GLOBALS['db']->getOne($sql);

            if ($use_conpons) {
                $sql = "UPDATE ".$GLOBALS['ecs']->table('user_conpons')." SET is_use_order=0,use_time=0,number=1 WHERE is_use_order='{$order['order_sn']}' AND user_id='{$order['user_id']}'";

                $GLOBALS['db']->query($sql);
            }
        }

        /* 修改订单 */
        $arr = array(
            'bonus_id'  => 0,
            'bonus'     => 0,
            'integral'  => 0,
            'integral_money'    => 0,
            'surplus'   => 0
        );
        update_order($order['order_id'], $arr);

        $res = array('err'=>0,'msg'=>'订单取消成功!');
        exit(json_encode($res));
    }
    else
    {
        $res = array('err'=>14,'msg'=>$GLOBALS['db']->errorMsg());
        exit(json_encode($res));
    }
}

/*
订单确认收货
*/
function xcx_affirm_received($order_id,$user_id)
{
    require_once(ROOT_PATH . '/includes/lib_order.php');
    include_once (ROOT_PATH . 'includes/lib_transaction.php');
    /* 查询订单信息，检查状态 */
    $sql = "SELECT user_id, order_sn , order_status, shipping_status, pay_status FROM ".$GLOBALS['ecs']->table('order_info') ." WHERE order_id = '$order_id'";

    $order = $GLOBALS['db']->GetRow($sql);

    // 如果用户ID大于 0 。检查订单是否属于该用户
    if ($user_id > 0 && $order['user_id'] != $user_id)
    {
        $res = array('err'=>1,'msg'=>'订单不存在喔！');
        exit(json_encode($res));
    }
    /* 检查订单 */
    elseif ($order['shipping_status'] == SS_RECEIVED)
    {
        $res = array('err'=>2,'msg'=>'此订单已经确认过了。');
        exit(json_encode($res));
    }
    elseif ($order['shipping_status'] != SS_SHIPPED)
    {
        $res = array('err'=>3,'msg'=>'订单无效！');
        exit(json_encode($res));
    }
    /* 修改订单发货状态为“确认收货” */
    else
    {
        $sql = "UPDATE " . $GLOBALS['ecs']->table('order_info') . " SET shipping_status = '" . SS_RECEIVED . "' WHERE order_id = '$order_id'";
        if ($GLOBALS['db']->query($sql))
        {
            /* 记录日志 */
            order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], '', $GLOBALS['_LANG']['buyer']);

            $res = array('err'=>0,'msg'=>'确认收货成功！');
            exit(json_encode($res));
        }
        else
        {
            $res = array('err'=>3,'msg'=>$GLOBALS['db']->errorMsg());
            exit(json_encode($res));
        }
    }
}

/*
商品快速入购物车
*/
function addto_cart_fast($goods_id,$num=1,$spec=array(),$uid)
{
    $GLOBALS['err']->clean();

    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id='$goods_id' AND is_delete=0";

    $goods = $GLOBALS['db']->getRow($sql);

    if (empty($goods))
    {
        // 商品没有找到
        return false;
    }
    else
    {
        /* 计算商品的促销价格 */
        // $spec_price             = spec_price($spec);
        $goods_price            = get_final_price($goods_id, $num, true, $spec);
        // $goods['market_price'] += $spec_price;
        // $goods_attr             = get_goods_attr_info($spec);
        $goods_attr = '';
        $goods_attr_id          = join(',', $spec);

        /* 初始化要插入购物车的基本件数据 */
        $parent = array(
            'user_id'       => $uid,
            'session_id'    => SESS_ID,
            'goods_id'      => $goods_id,
            'goods_sn'      => addslashes($goods['goods_sn']),
            'product_id'    => 0,
            'goods_name'    => addslashes($goods['goods_name']),
            'market_price'  => $goods['market_price'],
            'goods_attr'    => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'is_real'       => $goods['is_real'],
            'extension_code'=> $goods['extension_code'],
            'is_gift'       => 0,
            'is_shipping'   => $goods['is_shipping'],
            'rec_type'      => CART_GENERAL_GOODS
        );

        $parent['goods_price']  = (int)$goods_price;
        $parent['goods_number'] = $num;
        $parent['parent_id']    = 0;
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('cart'), $parent, 'INSERT');

        return true;
    }
}

/*
获取优惠卷列表
is_user_conpons 获取该会员的优惠列表
返回值：优惠卷数组
*/
function get_conpons($user_id = 0,$is_user_conpons = 0)
{

    $tady_date = local_date('Ymd',time());


    if ($is_user_conpons == 1) {
        // 获取当前会员的优惠卷列表

        $sql = "SELECT c.*,uc.* FROM ".$GLOBALS['ecs']->table('conpons')." AS c,".$GLOBALS['ecs']->table('user_conpons')." AS uc WHERE c.id=uc.conpons_id AND uc.user_id='$user_id' AND uc.is_use_order=0";

        $user_conpons = $GLOBALS['db']->getAll($sql);

        foreach ($user_conpons as $key => $value) {
            $user_conpons[$key]['c_amount'] = (int)$user_conpons[$key]['c_amount'];
            $user_conpons[$key]['c_conditions'] = (int)$user_conpons[$key]['c_conditions'];
        }


        return $user_conpons;
    }
    else
    {
        // 获取系统内的可用优惠卷
        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('conpons')." WHERE is_open_get=1";

        $conpons = $GLOBALS['db']->getAll($sql);
        

        // 筛选过期的和未开始的卷不显示
        foreach ($conpons as $key => $value) {
            $promote_start_date = str_replace('-','',$conpons[$key]['promote_start_date']);
            $promote_end_date = str_replace('-','',$conpons[$key]['promote_end_date']);

            $conpons[$key]['c_amount'] = (int)$conpons[$key]['c_amount'];
            $conpons[$key]['c_conditions'] = (int)$conpons[$key]['c_conditions'];

            $c_get_limit = $conpons[$key]['c_get_limit'];
            $id = $conpons[$key]['id'];

            if ($tady_date > $promote_end_date) {
                // 过期
                unset($conpons[$key]);
            }
            if ($tady_date < $promote_start_date) {
                // 未开始
                unset($conpons[$key]);
            }

            // 排除当前会员已有的优惠卷

            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('user_conpons')." WHERE user_id='$user_id'";

            $user_conpons = $GLOBALS['db']->getAll($sql);

            if (is_array($user_conpons)) {
                foreach ($user_conpons as $ukey => $uvalue) {
                    $user_conpons_id = $user_conpons[$ukey]['conpons_id'];

                    /*if ($id == $user_conpons_id && $user_conpons[$ukey]['number'] >= $c_get_limit) {
                        // 当前会员有此卷，检查数量为大于等于最多可领数量
                        unset($conpons[$key]);
                    }*/

                    if ($id == $user_conpons_id)
                    {
                        // 当前会员有此卷
                        // 查询会员持有份数
                        $sql = "SELECT count(*) FROM ".$GLOBALS['ecs']->table('user_conpons')." WHERE user_id='$user_id' AND conpons_id='$user_conpons_id'";

                        $user_number_conpons = $GLOBALS['db']->getOne($sql);

                        if (!$user_number_conpons) {
                            $user_number_conpons = 0;
                        }
                        
                        if ($user_number_conpons >= $c_get_limit) {
                            // 数量达到上限，删除该卷的显示
                            unset($conpons[$key]);
                        }
                        else
                        {
                            // 数量没有达到上限
                            $conpons[$key]['user_number'] = $user_number_conpons;

                        }
                    }
                    
                }
            }

            if (isset($conpons[$key]) && !isset($conpons[$key]['user_number'])) {
                $conpons[$key]['user_number'] = 0;
            }

            
        }
        // print_r($conpons);

        return $conpons;
    }
}
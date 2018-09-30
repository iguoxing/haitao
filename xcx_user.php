<?php 
/*
微起步话题首页
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

/*
获取用户基本信息
*/
function action_check_user_sign($value='')
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

    $sql = "SELECT email,user_name,sex,birthday,wx_name,mobile_phone,headimg FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
    $user_info = $GLOBALS['db']->getRow($sql);

    if (!$user_info['headimg']) {
    	$user_info['headimg'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/default_tx.png';
    }

    if (!$user_info['wx_name']) {
    	$user_info['wx_name'] = $user_info['user_name'];
    }

    /*switch ($user_info['sex']) {
    	case '1':
    		$user_info['sex'] = '男';
    		break;
    	case '2':
    		$user_info['sex'] = '女';
    		break;
    	
    	default:
    		$user_info['sex'] = '保密';
    		break;
    }*/

    if (!$user_info['mobile_phone']) {
    	$user_info['mobile_phone'] = '点击绑定';
    }

    if ($user_info['birthday'] == '0000-00-00') {
    	$user_info['birthday'] = '点击设置';
    }

    $res = array('err'=>0,'msg'=>'ok','user_info'=>$user_info);
    exit(json_encode($res));
}

// ajax获取团购订单列表
function action_tuan_order_list()
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

	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
	$type_id = isset($_REQUEST['type_id']) ? test_input($_REQUEST['type_id']) : 0;//类型，0全部，1进行中，2已成团，3组团失败

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

	if ($type_id != 0) {
		$where .= ' AND team_status='.$type_id;
	}

	// 取得团购订单

	$sql = "SELECT o.order_id,o.team_status,o.team_sign FROM ".$GLOBALS['ecs']->table('order_info')." AS o WHERE o.user_id='$uid' AND o.extension_code='team_goods'".$where." ORDER BY o.order_id DESC LIMIT $min,$count";

	$order_list = $GLOBALS['db']->getAll($sql);

	foreach ($order_list as $key => $value) {

		switch ($order_list[$key]['team_status']) {
			case 0:
				$order_list[$key]['team_status_text'] = '待支付';
				break;
			case 1:
				$order_list[$key]['team_status_text'] = '拼团正在进行中';
				break;
			case 2:
				$order_list[$key]['team_status_text'] = '拼团成功';
				break;
			case 3:
				$order_list[$key]['team_status_text'] = '拼团失败';
				break;
			case 4:
				$order_list[$key]['team_status_text'] = '拼团失败已退款';
				break;
			
			default:
				$order_list[$key]['team_status_text'] = '状态未知';
				break;
		}

		$order_id_tmp = $order_list[$key]['order_id'];

		$sql = "SELECT o.goods_id,g.goods_name,g.goods_thumb,g.team_num,g.team_price FROM ".$GLOBALS['ecs']->table('order_goods')." AS o,".$GLOBALS['ecs']->table('goods')." AS g WHERE o.order_id='$order_id_tmp' AND o.goods_id=g.goods_id";

		$order_list[$key]['goods_list'] = $GLOBALS['db']->getAll($sql);

		foreach ($order_list[$key]['goods_list'] as $gkey => $gvalue) {

			if ($order_list[$key]['goods_list'][$gkey]['goods_thumb']) {
				$order_list[$key]['goods_list'][$gkey]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$order_list[$key]['goods_list'][$gkey]['goods_thumb'];
			}
			else
			{
				$order_list[$key]['goods_list'][$gkey]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
			}

		}

	}

	$list_count = count($order_list) < $count ? 0:1;

	$res = array('err' => '0','order_list'=>$order_list,'list_count'=>$list_count);

	exit(json_encode($res));
}
/*
用户钱包
*/

function action_account_detail()
{
	$ecs = $GLOBALS['ecs'];
	$db = $GLOBALS['db'];
	$_CFG = $GLOBALS['_CFG'];

	include_once (ROOT_PATH . 'includes/lib_clips.php');

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	$page = isset($_REQUEST['pages']) ? test_input($_REQUEST['pages']) : 1;//页码

	$count = 20;// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
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

	// 获取剩余余额
	/*$surplus_amount = get_user_surplus($uid);
	if(empty($surplus_amount))
	{
		$surplus_amount = 0;
	}

	// 获取积分
	$sql = "SELECT pay_points FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";

	$pay_points = $GLOBALS['db']->getOne($sql);

	if (!$pay_points) {
		$pay_points = 0;
	}*/

	// 获取余额记录
	$account_log = array();
	$sql = "SELECT * FROM " . $ecs->table('account_log') . " WHERE user_id = '$uid'" . " AND user_money <> 0 " . " ORDER BY change_time DESC LIMIT $min,$count";

	$account_log = $GLOBALS['db']->getAll($sql);

	foreach ($account_log as $key => $value) {
		$value['change_time'] = local_date($_CFG['date_format'], $value['change_time']);
		$value['type'] = $value['user_money'] > 0 ? '增加' : '减少';
		$value['user_money'] = price_format(abs($value['user_money']), false);
		$value['frozen_money'] = price_format(abs($value['frozen_money']), false);
		$value['rank_points'] = abs($value['rank_points']);
		$value['pay_points'] = abs($value['pay_points']);
		$value['short_change_desc'] = sub_str($value['change_desc'], 60);
		$value['amount'] = $value['user_money'];

		$account_log[$key] = $value;
	}

	$list_count = count($account_log) < $count ? 0:1;


	if ($account_log) {
		$res = array('err'=>0,'account_log'=>$account_log,'list_count'=>$list_count);
	}
	else
	{
		$res = array('err'=>0,'account_log'=>array(),'list_count'=>$list_count);
	}

	exit(json_encode($res));

}

/*提交提现申请*/
function action_account_withdrawal()
{
    // 检查登录
    $uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

    $amount = isset($_REQUEST['amount']) ? test_input($_REQUEST['amount']) : 0;//提现金额
    $user_note = isset($_REQUEST['user_note']) ? test_input($_REQUEST['user_note']) : '';//会员备注


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

    $sql = "SELECT user_money,wx_open_id FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";

    $user_info = $GLOBALS['db']->getRow($sql);

    // 检查余额
    if ($amount < 1 or $amount > 200) {
        $res = array('err'=>3,'msg'=>'提现金额不能小于1和大于200哦！');
        exit(json_encode($res));
    }
    else
    {

        if ($amount > $user_info['user_money']) {
            $res = array('err'=>3,'msg'=>'您的余额不足！');
            exit(json_encode($res));
        }
    }

    $amount ='-'.$amount;
    $sql = 'INSERT INTO ' .$GLOBALS['ecs']->table('user_account').
           ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid)'.
            " VALUES ('$uid', '', '$amount', '".gmtime()."', 0, '', '$user_note', '1', '', 0)";
    $GLOBALS['db']->query($sql);

    $res = array('err'=>0,'msg'=>'提现申请已经提交,管理会尽快打款！');
    exit(json_encode($res));
}


/*AJAX获取提现记录*/
function action_get_withdrawal_log()
{
	$_CFG = $GLOBALS['_CFG'];
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

	$page = isset($_REQUEST['pages']) ? test_input($_REQUEST['pages']) : 1;//页码

	$count = 20;// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
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

	// 获取提现记录
	$account_log = array();
	$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('user_account') . " WHERE user_id = '$uid' ORDER BY id DESC LIMIT $min,$count";

	$account_log = $GLOBALS['db']->getAll($sql);

	foreach ($account_log as $key => $value) {
		$value['add_time'] = local_date($_CFG['date_format'], $value['add_time']);
		$value['process_type'] = $value['process_type'] == 0 ? '充值' : '提现';
		$value['is_paid'] = $value['is_paid'] == 0 ? '未确认' : '已确认';
		$value['amount'] = price_format(abs($value['amount']), false);

		$account_log[$key] = $value;
	}

	$list_count = count($account_log) < $count ? 0:1;


	if ($account_log) {
		$res = array('err'=>0,'account_log'=>$account_log,'list_count'=>$list_count);
	}
	else
	{
		$res = array('err'=>0,'account_log'=>array(),'list_count'=>$list_count);
	}

	exit(json_encode($res));
}

/*
订单列表
*/
function action_order_list()
{
	// 屏蔽警告
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

	include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    /* 载入语言文件 */
	require_once(ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/user.php');

	$GLOBALS['_LANG'] = $_LANG;

	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$composite_status = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;

	$where=" AND is_luck = 0 ";

	//未付款
    if($_REQUEST['composite_status'] =='100')
    {
        $where = " and order_status in (0,1,5)  and pay_status=0 ";
    }
    //待收货
    if($_REQUEST['composite_status'] =='180')
    {
        $where .= order_query_sql('await_ship')." and point_id = 0 ";
    }
    //待核销
    if($_REQUEST['composite_status'] =='102')
    {
        $where .= order_query_sql('await_ship')." and point_id > 0 ";
    }
    /* 已发货订单：不论是否付款 */
    if($_REQUEST['composite_status'] =='120')
    {
        $where .= order_query_sql('shipped2');
    }
    /* 已完成订单 */
    if($_REQUEST['composite_status'] =='999')
    {
        $where .= order_query_sql();
    }

    $action = 'order_list';

    $record_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info'). " WHERE user_id = '$uid'" .$where);

    $pager  = get_pager('user.php', array('act' => $action,'composite_status'=>$_REQUEST['composite_status']), $record_count, $page);

    $orders = @get_user_orders_ex($uid, $pager['size'], $pager['start'],$where);

    foreach ($orders as $key => $value) {
    	$sql = "SELECT pay_status,team_status,order_status,shipping_status,extension_code FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_id='{$orders[$key]['order_id']}'";
    	$order_info = $GLOBALS['db']->getRow($sql);

    	if ($order_info['order_status'] != 2 && $order_info['order_status'] != 3 && $order_info['order_status'] != 4 && $order_info['pay_status'] == 0) {
    		// 显示付款按钮
    		$orders[$key]['is_pay'] = 1;//未付款，需要支付
    	}
    	else
    	{
    		$orders[$key]['is_pay'] = 0;//无需支付
    	}

    	// 取消订单按钮
    	$orders[$key]['order_status_text'] = '';

    	if ($order_info['order_status'] == 2) {
    		// 显示付款按钮
    		$orders[$key]['order_status_text'] = '已取消';
    	}
    	else if ($order_info['order_status'] == 3) {
    		$orders[$key]['order_status_text'] = '无效订单';
    	}
    	else if ($order_info['order_status'] == 4) {
    		$orders[$key]['order_status_text'] = '退货订单';
    	}

    	$orders[$key]['is_shouhuo'] = 0;
    	// 是否可以确认收货
    	if ($order_info['order_status'] != 2 && $order_info['order_status'] != 3 && $order_info['order_status'] != 4 && $order_info['pay_status'] == 2 && $order_info['shipping_status'] == 1) {
    		$orders[$key]['is_shouhuo'] = 1;
    	}

    	$orders[$key]['is_fahuo'] = 0;
    	// 是否显示待发货
    	if ($order_info['order_status'] != 2 && $order_info['order_status'] != 3 && $order_info['order_status'] != 4 && $order_info['pay_status'] == 2 && $order_info['shipping_status'] == 0) {
    		$orders[$key]['is_fahuo'] = 1;
    	}

    	if ($order_info['extension_code'] == 'team_goods') {
    		// 订单的团购信息
	    	if ($order_info['team_status'] == 1) {
	    		$orders[$key]['team_status_text'] = '拼团中';
	    	}
	    	else if ($order_info['team_status'] == 2) {
	    		$orders[$key]['team_status_text'] = '拼团成功';
	    	}
	    	else if ($order_info['team_status'] == 3) {
	    		$orders[$key]['team_status_text'] = '拼团失败待退款';
	    	}
	    	else if ($order_info['team_status'] == 4) {
	    		$orders[$key]['team_status_text'] = '拼团失败已退款';
	    	}
	    	else
	    	{
	    		$orders[$key]['team_status_text'] = 'null';
	    	}
    	}
    	else
    	{
    		$orders[$key]['team_status_text'] = 'null';
    	}

    	$orders[$key]['is_ok'] = 0;
    	// 订单是否已完成
    	if ($order_info['order_status'] != 2 && $order_info['order_status'] != 3 && $order_info['order_status'] != 4 && $order_info['pay_status'] == 2 && $order_info['shipping_status'] == 2) {
    		$orders[$key]['is_ok'] = 1;
    	}

        //物流单号
        $sql="SELECT invoice_no from ".$GLOBALS['ecs']->table('delivery_order')." WHERE order_id='{$orders[$key]['order_id']}'";
        $orders[$key]['invoice_no']=$GLOBALS['db']->getOne($sql);
    }

    $res = array('err'=>0,'orders'=>$orders,'pager'=>$pager);

	exit(json_encode($res));
}

/*
订单付款
*/
function action_order_updata()
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

    $type = empty($_REQUEST['edit_type']) ? '' : test_input($_REQUEST['edit_type']);
    $order_id = empty($_REQUEST['order_id']) ? '' : test_input($_REQUEST['order_id']);

    if ($type == 'order_pay') {
    	// 订单付款-》0、识别支付方式-》1、如果是微信支付进行统一下单-》2、生成前台需要的签名等数据-》3、前台直接发起支付

		// 获取订单选择的支付方式
		$sql = "SELECT pay_id,order_amount,extension_code FROM ".$GLOBALS['ecs']->table('order_info')." WHERE user_id='$uid' AND order_id='$order_id'";

		$order_pay = $GLOBALS['db']->getRow($sql);
		$order_pay_id = $order_pay['pay_id'];

		if (!$order_pay_id) {
			$res = array('err'=>4,'msg'=>'无法付款.订单不存在喔！');
        	exit(json_encode($res));
		}
		else
		{
            if ($order_pay['extension_code'] == 'team_goods') {
                $res = array('err'=>88,'msg'=>'团购订单微信支付.正在获取code');
                exit(json_encode($res));
            }
			// 识别支付方式
			$sql = "SELECT pay_code FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_id='$order_pay_id' AND enabled = 1";

			$pay_code = $GLOBALS['db']->getOne($sql);

			if (!$pay_code) {
				$res = array('err'=>5,'msg'=>'无法付款.支付方式不存在喔！');
        		exit(json_encode($res));
			}
			else
			{
				// 取到订单的支付log
				$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('pay_log')." WHERE order_id='$order_id'";
				$pay_log = $GLOBALS['db']->getRow($sql);

				if ($pay_log['is_paid'] == 1 && $order_pay['order_amount'] == 0) {
					$res = array('err'=>8,'msg'=>'该订单已付款。');
        			exit(json_encode($res));
				}

				if ($pay_code == 'wxpay') {
					// 微信支付
					$res = array('err'=>07,'msg'=>'微信支付.正在获取code','log_id'=>$pay_log['log_id']);
        			exit(json_encode($res));

				}
				elseif ($pay_code == 'balance') {
					// 余额支付,获取用户余额值
			    	$sql = "SELECT user_money FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
			    	$user_money = $GLOBALS['db']->getOne($sql);

	    	    	if ($user_money < $order_pay['order_amount']) {
			    		$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET surplus='$user_money',money_paid=money_paid+'$user_money',order_amount=order_amount-'$user_money',order_status=1 WHERE order_id='$order_id'";
			    		$GLOBALS['db']->query($sql);

			    		$surplus = $order_pay['order_amount']-$user_money;

			    		log_account_change($uid, (- 1) * $user_money, 0, 0, 0, '订单付款:'.$order_id);//改变账户

			    		$res = array('err'=>6,'msg'=>'余额不足,订单已结'.$user_money.'元,剩余:'.$surplus.'元应付。');
						exit(json_encode($res));
			    	}
			    	else
			    	{
			    		$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET surplus='{$order_pay['order_amount']}',money_paid=money_paid+'{$order_pay['order_amount']}',order_amount=order_amount-'{$order_pay['order_amount']}',order_status=1,pay_status=2 WHERE order_id='$order_id'";
			    		$GLOBALS['db']->query($sql);

			    		log_account_change($uid, (- 1) * $order_pay['order_amount'], 0, 0, 0, '订单付款:'.$order_id);//改变账户

			    		// 改变log支付状态
			    		$sql = "UPDATE ".$GLOBALS['ecs']->table('pay_log')." SET is_paid=1 WHERE log_id='{$pay_log['log_id']}'";

			    		$GLOBALS['db']->query($sql);

			    		$res = array('err'=>6,'msg'=>'使用余额支付成功,共'.$order_pay['order_amount'].'元');
						exit(json_encode($res));
			    	}

				}
				else{
					$res = array('err'=>6,'msg'=>'无法付款.该支付方式不支持喔');
        			exit(json_encode($res));
				}
			}
		}

    }
    elseif ($type == 'order_cancel') {
		// 前台确认用户要取消吗？此处检查订单状态：未付款，未收货，未取消，则允许直接取消，提示成功并返回新的订单列表

		xcx_order_cancel($order_id,$uid);
    }
    elseif ($type == 'affirm_received') {
    	// 若订单是已付款，已发货状态，执行收货
    	xcx_affirm_received($order_id,$uid);
    }
    else
    {
    	$res = array('err'=>3,'msg'=>'未知操作！');
        exit(json_encode($res));
    }

}

/*
订单详情
*/

function action_order_detail()
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

	if ($order_id == 0) {
		$res = array('err'=>4,'msg'=>'订单不存在喔！');
        exit(json_encode($res));
	}
	else
	{
		// 取得订单详情
		$sql = "SELECT o.*," .
           "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee ".
           " FROM " .$GLOBALS['ecs']->table('order_info') . ' as o '.
           " WHERE user_id = '$uid' AND order_id='$order_id'";
	    $order_info = $GLOBALS['db']->getRow($sql);

    	// 取得商品列表

    	$sql = "SELECT o.*,g.goods_thumb FROM ".$GLOBALS['ecs']->table('order_goods')." AS o LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON o.goods_id=g.goods_id WHERE o.order_id='$order_id'";
    	$order_info['goods_list'] = $GLOBALS['db']->getAll($sql);

    	foreach ($order_info['goods_list'] as $listkey => $listvalue) {
    		if ($order_info['goods_list'][$listkey]['goods_thumb']) {
    			$order_info['goods_list'][$listkey]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$order_info['goods_list'][$listkey]['goods_thumb'];
    		}
    		else
    		{
    			$order_info['goods_list'][$listkey]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
    		}

    		// 处理属性列表
	    	if ($order_info['goods_list'][$listkey]['goods_attr']) {
	    		$order_info['goods_list'][$listkey]['goods_attr'] = str_replace(PHP_EOL, ";", $order_info['goods_list'][$listkey]['goods_attr']);
	    	}
    	}

    	// 取得收货人信息
    	// 读取各个收货地址的真名
	    if ($order_info['country']) {
	    	$order_info['country'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$order_info['country']}'");
	    }
	    if ($order_info['province']) {
	    	$order_info['province'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$order_info['province']}'");
	    }
	    if ($order_info['city']) {
	    	$order_info['city'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$order_info['city']}'");
	    }
	    if ($order_info['district']) {
	    	$order_info['district'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['ecs']->table('region')." WHERE region_id='{$order_info['district']}'");
	    }

    	// 将订单状态替换为中文
    	switch ($order_info['order_status']) {
    		case '0':
    			$order_info['order_status'] = '未确认';
    			break;
    		case '1':
    			$order_info['order_status'] = '已确认';
    			break;
    		case '2':
    			$order_info['order_status'] = '已取消';
    			break;
    		case '3':
    			$order_info['order_status'] = '无效';
    			break;
    		case '4':
    			$order_info['order_status'] = '退货';
    			break;
            case '5':
                $order_info['order_status'] = '商品已分单';
                break;
    		default:
    			$order_info['order_status'] = '未知';
    			break;
    	}
    	switch ($order_info['shipping_status']) {
    		case '0':
    			$order_info['shipping_status'] = '未发货';
    			break;
    		case '1':
    			$order_info['shipping_status'] = '已发货';
    			break;
    		case '2':
    			$order_info['shipping_status'] = '已收货';
    			break;
    		case '3':
    			$order_info['shipping_status'] = '';
    			break;
    		case '4':
    			$order_info['shipping_status'] = '';
    			break;
    		default:
    			$order_info['shipping_status'] = '未知';
    			break;
    	}
    	switch ($order_info['pay_status']) {
    		case '0':
    			$order_info['pay_status'] = '未付款';
    			break;
    		case '1':
    			$order_info['pay_status'] = '付款中';
    			break;
    		case '2':
    			$order_info['pay_status'] = '已付款';
    			break;
    		default:
    			$order_info['pay_status'] = '未知';
    			break;
    	}

    	$res = array('err'=>0,'order_info'=>$order_info);
        exit(json_encode($res));
	    
	}

}

/*微信支付统一下单-团购*/
function action_wxpay_done_tuan($order_id='null',$user_code='null')
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

    if ($order_id == 'null') {
        $order_id = empty($_REQUEST['order_id']) ? 0 : test_input($_REQUEST['order_id']);
    }

    if ($user_code == 'null') {
        $user_code = empty($_REQUEST['user_code']) ? '' : test_input($_REQUEST['user_code']);
    }

    if ($order_id == 0) {
        $res = array('err'=>3,'msg'=>'订单不存在,请重试！');
        exit(json_encode($res));
    }

    // 取得订单信息
    $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_id='$order_id'";

    $order = $GLOBALS['db']->getRow($sql);

    if (!$order) {
        $res = array('err'=>3,'msg'=>'订单不存在,请重试！');
        exit(json_encode($res));
    }

        // 在线支付
    if ($order['order_amount'] > 0) {

        // 取用户的open_id
        $sql = "SELECT wx_open_id FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
        $openId = $GLOBALS['db']->getOne($sql);

        if (!$openId) {
            // 不是微信注册用户,获取用户的open_id
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

        if ($order['return_code'] != 'SUCCESS') {
            $res = array('err' => 8,'msg'=>'请求支付通信失败,请重试！');
            echo json_encode($res);
            return;
        }
        else
        {
            if ($order['result_code'] != 'SUCCESS') {
                $res = array('err' => 9,'msg'=>$order['err_code_des']);
                echo json_encode($res);
                return;
            }
        }

        $order_new['order_id'] = $order_id;
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



        $res = array('err' => 0,'order'=>$order_new,'msg'=>'支付完成!');
        echo json_encode($res);
        return;
    }
    else
    {
        // 处理拼团信息
        // 处理库存
        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE order_id='$order_id'";
        $goods_list = $GLOBALS['db']->getAll($sql);

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

        $res = array('err' => 1,'msg'=>'支付完成!');
        echo json_encode($res);
        return;
    }
}

/*
微信支付统一下单
*/
function action_wxpay_done($log_id='null',$user_code='null')
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

    if ($log_id == 'null') {
    	$log_id = empty($_REQUEST['log_id']) ? 0 : test_input($_REQUEST['log_id']);
    }


    if ($log_id == 0) {
    	$res = array('err'=>3,'msg'=>'支付记录不存在,请重试！');
        exit(json_encode($res));
    }
    else
    {

    	// 获取订单支付log信息
    	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('pay_log')." WHERE log_id='$log_id'";

    	$pay_log = $GLOBALS['db']->getRow($sql);

    	if (!$pay_log) {
    		$res = array('err'=>4,'msg'=>'支付记录不存在,请重试！');
        	exit(json_encode($res));
    	}

    	// 统一下单

    	// 取用户的open_id
    	$sql = "SELECT wx_open_id FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
    	$openId = $GLOBALS['db']->getOne($sql);

    	if (!$openId) {
    		// 不是微信注册用户,获取用户的open_id

    		if ($user_code == 'null') {
    			$user_code = empty($_REQUEST['user_code']) ? '' : test_input($_REQUEST['user_code']);
    		}

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
		$input->SetBody($GLOBALS['xcx_config']['xcx_name'].$log_id);
		$input->SetAttach($GLOBALS['xcx_config']['xcx_name']."小程序支付");
		$input->SetOut_trade_no($log_id."e".$Time_expire);
		$input->SetTotal_fee($pay_log['order_amount'] * 100);
		$input->SetTime_start(local_date("YmdHis"));
		$input->SetTime_expire($Time_expire);
		// $input->SetGoods_tag("test");
		$input->SetNotify_url("http://".$GLOBALS['xcx_config']['url']."/xcx/notify.php");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);

		if ($order['return_code'] != 'SUCCESS') {
			$res = array('err' => 8,'msg'=>'请求支付通信失败,请重试！');
			echo json_encode($res);
			return;
		}
		else
		{
			if ($order['result_code'] != 'SUCCESS') {
				$res = array('err' => 9,'msg'=>$order['err_code_des']);
				echo json_encode($res);
				return;
			}
		}

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

		$res = array('err' => 0,'order'=>$order_new,'msg'=>'支付完成!');
		echo json_encode($res);
		return;

    }
}

/*
收藏
*/
function action_collection_list()
{
	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

	$count = 20;

	// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}

	// 取得收藏商品列表
	$sql = "SELECT c.rec_id,g.goods_id,g.goods_name,g.shop_price,g.goods_thumb FROM ".$GLOBALS['ecs']->table('collect_goods')." AS c LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON c.goods_id=g.goods_id WHERE c.user_id='$uid' ORDER BY c.add_time DESC LIMIT $min,$count";
	$collect_goods = $GLOBALS['db']->getAll($sql);

	foreach ($collect_goods as $key => $value) {
		if ($collect_goods[$key]['goods_thumb']) {
			$collect_goods[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$collect_goods[$key]['goods_thumb'];
		}
		else
		{
			$collect_goods[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
		}
	}

	if (!$collect_goods) {
		$collect_goods = 'null';
	}

	$list_count = count($collect_goods) < $count ? 0:1;

	$res = array('err' =>0,'collect_goods'=>$collect_goods,'list_count'=>$list_count);
	exit(json_encode($res));
}

/*
我的广场
*/
function action_gc_list()
{
    $page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
    $uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

    $count = 20;

    // 要读取的数据条数

    if ($page == 1) {
        $min = 0;
    }
    else
    {
        $min = ($page-1) * $count;
    }

    // 取得收藏商品列表

    $sql = "select o.square,o.order_id,g.goods_id from ".$GLOBALS['ecs']->table('order_info')." as o,".$GLOBALS['ecs']->table('order_goods')." as g where o.show_square = 1 and o.team_status = 1  and o.user_id='$uid' AND g.order_id=o.order_id";

    $collect_goods = $GLOBALS['db']->getAll($sql);

    foreach ($collect_goods as $key => $value) {

        // 查询商品名称和商品图片
        $sql = "SELECT goods_name,goods_thumb FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id='{$collect_goods[$key]['goods_id']}'";

        $goods = $GLOBALS['db']->getRow($sql);

        $collect_goods[$key]['goods_thumb'] = $goods['goods_thumb'];
        $collect_goods[$key]['goods_name'] = $goods['goods_name'];

        if ($collect_goods[$key]['goods_thumb']) {
            $collect_goods[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$collect_goods[$key]['goods_thumb'];
        }
        else
        {
            $collect_goods[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
        }
    }

    if (!$collect_goods) {
        $collect_goods = 'null';
    }

    $list_count = count($collect_goods) < $count ? 0:1;

    $res = array('err' =>0,'collect_goods'=>$collect_goods,'list_count'=>$list_count);
    exit(json_encode($res));
}
/*删除广场*/
function action_del_gc()
{
    $uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

    $coll_id = empty($_REQUEST['coll_id']) ? 0 : test_input($_REQUEST['coll_id']);

    if ($coll_id == 0) {
        $res = array('err'=>3,'msg'=>'网络错误！');
        exit(json_encode($res));
    }

    $sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET show_square=0 WHERE order_id='$coll_id'";

    $GLOBALS['db']->query($sql);

    action_gc_list();
}
/*
删除收藏
*/
function action_del_collection()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

	$coll_id = empty($_REQUEST['coll_id']) ? 0 : test_input($_REQUEST['coll_id']);

	if ($coll_id == 0) {
		$res = array('err'=>3,'msg'=>'该收藏不存在！');
        exit(json_encode($res));
	}

	$sql = "DELETE FROM ".$GLOBALS['ecs']->table('collect_goods')." WHERE user_id='$uid' AND rec_id='$coll_id'";

	$GLOBALS['db']->query($sql);

	action_collection_list();

}

/*
会员充值
*/

function action_account_deposit()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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
	
	$amount = empty($_REQUEST['amount']) ? 0 : test_input($_REQUEST['amount']);
	$user_note = empty($_REQUEST['user_note']) ? '' : test_input($_REQUEST['user_note']);
	$user_name = empty($_REQUEST['user_name']) ? '' : test_input($_REQUEST['user_name']);
	$user_code = empty($_REQUEST['user_code']) ? '' : test_input($_REQUEST['user_code']);

	if ($amount <= 0) {
		$res = array('err'=>3,'msg'=>'充值金额不能小于0');
        exit(json_encode($res));
	}

	//获取微信支付的id
	$sql = 'SELECT pay_id,pay_name ' .
            ' FROM ' . $GLOBALS['ecs']->table('payment') .
            ' WHERE enabled = 1 and pay_code="weixin"';
	$pay_wx = $GLOBALS['db']->getRow($sql);

	include_once (ROOT_PATH . 'includes/lib_clips.php');
	include_once (ROOT_PATH . 'includes/lib_order.php');

	// 插入会员账目明细
	$sql = 'INSERT INTO ' .$GLOBALS['ecs']->table('user_account').
           ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid)'.
            " VALUES ('$uid', '', '$amount', '".gmtime()."', 0, '', '$user_note', '0', '{$pay_wx['pay_name']}', 0)";
    $GLOBALS['db']->query($sql);

    $rec_id = $GLOBALS['db']->insert_id();

    // 生成伪订单号, 不足的时候补0
	$order = array();
	$order['order_sn'] = $rec_id;
	$order['user_name'] = $user_name;
	$order['surplus_amount'] = $amount;
	$order['order_id'] 	= $rec_id.'-'.$amount;
	// 计算支付手续费用
	$payment_info['pay_fee'] = pay_fee($pay_wx['pay_id'], $order['surplus_amount'], 0);
	
	// 计算此次预付款需要支付的总金额
	$order['order_amount'] = $amount + $payment_info['pay_fee'];
	
	// 记录支付log
	$order['log_id'] = insert_pay_log($rec_id, $order['order_amount'], $type = PAY_SURPLUS, 0);

	// 微信统一下单
	action_wxpay_done($order['log_id'],$user_code);

	return 0;

}

/*
商品评价列表
*/
function action_comment_list()
{
    
    $uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

    $page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码
    // $type = isset($_REQUEST['type']) ? test_input($_REQUEST['type']) : 1;//type，1全部，2待评价，3已评价

    $count = 20;// 要读取的数据条数

    if ($page == 1) {
        $min = 0;
    }
    else
    {
        $min = ($page-1) * $count;
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

    $limit = " limit $min,$count";//每次加载的个数

    $where = '';

    /* 取得评价列表 */
    $arr    = array();

    $sql = "SELECT c.*,g.goods_name,g.goods_thumb FROM ".$GLOBALS['ecs']->table('comment')." AS c LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON c.id_value=g.goods_id WHERE user_id='$uid' ORDER BY add_time DESC";
    $sql .= $limit;
    $comment_list = $GLOBALS['db']->getAll($sql);

    foreach ($comment_list as $key => $value) {
        if ($comment_list[$key]['goods_thumb']) {
            $comment_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$comment_list[$key]['goods_thumb'];
        }
        else
        {
            $comment_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
        }

        $comment_list[$key]['buy_time'] = local_date('y/m/d h:i',$comment_list[$key]['buy_time']);
    }


    $list_count = count($comment_list) < $count ? 0:1;

    if ($comment_list) {
        $res = array('err'=>0,'comment_list'=>$comment_list,'list_count'=>$list_count);
    }
    else
    {
        $res = array('err'=>1,'comment_list'=>'null','msg'=>'暂时还没有评价...','list_count'=>$list_count);
    }

    exit(json_encode($res));
}

/*
获取订单的商品列表
*/

function action_order_goods_list()
{
    $uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

    $order_id = isset($_REQUEST['order_id']) ? test_input($_REQUEST['order_id']) : 0;//订单号

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

    if (!$order_id) {
        $res = array('err'=>3,'msg'=>'订单不存在喔！');
        exit(json_encode($res));
    }


    $sql = "SELECT o.rec_id,o.order_id,o.goods_id,o.goods_name,o.goods_price,o.goods_number,g.goods_thumb FROM ".$GLOBALS['ecs']->table('order_goods')."AS o LEFT JOIN ".$GLOBALS['ecs']->table('goods')." AS g ON o.goods_id=g.goods_id WHERE o.order_id='$order_id'";

    $goods_list = $GLOBALS['db']->getAll($sql);

    foreach ($goods_list as $key => $value) {
        if ($goods_list[$key]['goods_thumb']) {
            $goods_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$goods_list[$key]['goods_thumb'];
        }
        else
        {
            $goods_list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
        }

        $goods_list[$key]['comment_rank'] = 1;
    }

    $res = array('err'=>0,'goods_list'=>$goods_list);
    exit(json_encode($res));

}

/*
处理评价
*/

function action_comment_send()
{
    $uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

    $goods_list = isset($_REQUEST['goods_list']) ? json_decode(str_replace('\\','',$_REQUEST['goods_list']),true) : 'null';//商品数组

    // print_r($goods_list);
    // 将评价插入数据库

    $sql = "SELECT user_name,email FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";

    $user_info = $GLOBALS['db']->getRow($sql);

    $add_time = time();

    foreach ($goods_list as $key => $value) {
    	// 检查是否评价
    	$order_id = $goods_list[$key]['order_id'];
    	$order_goods_id = $goods_list[$key]['goods_id'];

    	$sql = "SELECT comment_state FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE order_id='$order_id' AND goods_id='$order_goods_id'";

    	if ($GLOBALS['db']->getOne($sql)) {
            // 此商品已评价
        }
        else
        {
            // 插入评价
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('comment') . "(comment_type, id_value, email, user_name, content, comment_rank, add_time, ip_address, user_id, status, rec_id, comment_tag, buy_time, hide_username, order_id)" . "VALUES ('0', '$order_goods_id', '{$user_info['email']}', '{$user_info['user_name']}', '{$goods_list[$key]['comment_text']}', '{$goods_list[$key]['comment_rank']}', '$add_time', '0.0.0.0', '$uid', '1', '{$goods_list[$key]['rec_id']}', '', '$add_time', '', '')";

			$GLOBALS['db']->query($sql);
			$GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('order_goods') . " SET comment_state = 1 WHERE rec_id = '{$goods_list[$key]['rec_id']}'");

            // 修改评价状态

        }
    }

    $res = array('err'=>0,'msg'=>'评价成功！');
	exit(json_encode($res));

}

/*
用户留言列表
*/
function action_message_list()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

    $page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码

    $count = 20;// 要读取的数据条数

    if ($page == 1) {
        $min = 0;
    }
    else
    {
        $min = ($page-1) * $count;
    }

    $limit = " limit $min,$count";//每次加载的个数

    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('feedback')." WHERE parent_id = 0 AND user_id = '$uid' AND order_id=0 ORDER BY msg_time DESC";

    $sql .= $limit;

    $feedback_list = $GLOBALS['db']->getAll($sql);

    foreach ($feedback_list as $key => $value) {
    	$msg_type = $feedback_list[$key]['msg_type'];

    	switch ($msg_type) {
    		case '0':
    			$feedback_list[$key]['msg_type'] = '留言';
    			break;
    		case '1':
    			$feedback_list[$key]['msg_type'] = '投诉';
    			break;
    		case '2':
    			$feedback_list[$key]['msg_type'] = '询问';
    			break;
    		case '3':
    			$feedback_list[$key]['msg_type'] = '售后';
    			break;
    		case '4':
    			$feedback_list[$key]['msg_type'] = '求购';
    			break;
    		
    		default:
    			$feedback_list[$key]['msg_type'] = '留言';
    			break;
    	}

    	$feedback_list[$key]['msg_time'] = local_date('Y-m-d h:i:s',$feedback_list[$key]['msg_time']);

    	
    	if ($feedback_list[$key]['msg_content'] == 'null') {
    		$feedback_list[$key]['msg_content'] = '';
    	}
    }

    $list_count = count($feedback_list) < $count ? 0:1;

    if ($feedback_list) {
        $res = array('err'=>0,'feedback_list'=>$feedback_list,'list_count'=>$list_count);
    }
    else
    {
        $res = array('err'=>1,'feedback_list'=>'null','msg'=>'还没有您的留言...','list_count'=>$list_count);
    }
    exit(json_encode($res));
}

/*
用户提交留言
*/
function action_send_message()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

    $msg_type = empty($_REQUEST['msg_type']) ? 0 : test_input($_REQUEST['msg_type']);
    $msg_title = empty($_REQUEST['msg_title']) ? 'null' : test_input($_REQUEST['msg_title']);
    $msg_content = empty($_REQUEST['msg_content']) ? 'null' : test_input($_REQUEST['msg_content']);
    $status = 1 - $GLOBALS['_CFG']['message_check'];

    if ($msg_title == 'null') {
    	$res = array('err'=>3,'msg'=>'离发布成功就差一个标题了！');
        exit(json_encode($res));
    }

    // 查会员信息
    $sql = "SELECT user_name,email FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
    $user_info = $GLOBALS['db']->getRow($sql);

    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('feedback') .
            " (msg_id, parent_id, user_id, user_name, user_email, msg_title, msg_type, msg_status,  msg_content, msg_time, message_img, order_id, msg_area)".
            " VALUES (NULL, 0, '$uid', '$user_info[user_name]', '$user_info[email]', ".
            " '$msg_title', '$msg_type', '$status', '$msg_content', '".gmtime()."', '', '0', '')";

    if ($GLOBALS['db']->query($sql)) {
    	action_message_list();
        return 0;
    }
    else
    {
    	$res = array('err'=>3,'msg'=>'发布失败,请稍后再试！');
        exit(json_encode($res));
    }

}

/*
修改用户信息
*/
function action_edit_user_info()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
    $sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);

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

    $type = empty($_REQUEST['type']) ? null : test_input($_REQUEST['type']);//要修改的字段
    $value = isset($_REQUEST['value']) ? test_input($_REQUEST['value']) : null;//修改为

    if ($type == null || $value == null) {
    	$res = array('err'=>3,'msg'=>'不能修改为空喔！');
        exit(json_encode($res));
    }

    if ($type == 'password' || $type == 'salt') {
    	$res = array('err'=>4,'msg'=>'暂时无法修改此信息,请联系管理修改');
        exit(json_encode($res));
    }

    $sql = "UPDATE ".$GLOBALS['ecs']->table('users')." SET $type='$value' WHERE user_id='$uid'";

    if ($GLOBALS['db']->query($sql)) {
    	$res = array('err'=>0,'msg'=>'修改成功！');
        exit(json_encode($res));
    }
    else{
    	$res = array('err'=>6,'msg'=>'修改失败了,请重试！');
        exit(json_encode($res));
    }

}
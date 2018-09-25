<?php
// 团购订单支付回调

define('IN_HHS', true);

require_once ('../includes/init.php');

$GLOBALS['ecs'] = $hhs;

error_reporting(E_ALL);
ini_set('display_errors',0);    //将错误记录到日志
ini_set('log_errors', 1);
ini_set('error_log','./weblog.txt');

require_once dirname(__FILE__)."/wxpayAPI/lib/WxPay.Api.php";
require_once dirname(__FILE__).'/wxpayAPI/lib/WxPay.Notify.php';
require_once dirname(__FILE__).'/wxpayAPI/example/log.php';


//初始化日志
$logHandler= new CLogFileHandler(dirname(__FILE__)."/wxpayAPI/logs/".date('Y-m-d').'.txt');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		// Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back data:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}

		require(ROOT_PATH . 'includes/lib_payment.php');

		// 切割出订单号
		$order_id = explode("e", $data["out_trade_no"]);

		Log::DEBUG("order_id:".$order_id[0]);//记录订单号

		$sql="select * from ".$GLOBALS['ecs']->table('order_info')."  where order_id='".$order_id[0]."'";

    	$order_info=$GLOBALS['db']->getRow($sql);

    	// 減少庫存
    	Log::DEBUG("change_order_goods_storage0");
    	// 查order的goods列表，减少列表内商品的库存
    	$sql = "SELECT goods_id,goods_number FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE order_id='{$order_id[0]}'";

    	$goods_list = $GLOBALS['db']->getAll($sql);

    	foreach ($goods_list as $key => $value) {
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('goods')." SET goods_number=goods_number-'{$value['goods_number']}' WHERE goods_id='{$value['goods_id']}'";
			$GLOBALS['db']->query($sql);
    	}

    	Log::DEBUG("change_order_goods_storage1:ok");

    	// 修改订单状态为已确认，已付款

    	$gmtime = gmtime();

    	$total_fee = $data['total_fee']/100;

    	// 检查金额
    	if ($order_info['order_amount'] >= $total_fee) {

    		$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET order_status=1,confirm_time='$gmtime',pay_status=2,pay_time='$gmtime',money_paid=money_paid+'$total_fee',order_amount=0,xcx_pay_no='{$data['transaction_id']}' WHERE order_id='{$order_info['order_id']}'";

    		$GLOBALS['db']->query($sql);
    	}
    	else
    	{
    		$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET order_status=1,confirm_time='$gmtime',money_paid=money_paid+'$total_fee',order_amount=order_amount-'$total_fee' WHERE order_id='{$order_info['order_id']}'";

    		$GLOBALS['db']->query($sql);
    	}

    	Log::DEBUG("change_order_state:ok");

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



		Log::DEBUG("order_paid:".$data["out_trade_no"]);
		return true;
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);
